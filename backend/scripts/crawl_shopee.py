"""Crawl product data from a Shopee shop using a real browser.

Connects to a real Chrome via CDP (the same one a human is using) so Shopee's
anti-bot system does not flag the session. Captures product data from the
network responses Shopee itself fetches when rendering the shop page.

Usage:
    python -m scripts.crawl_shopee --username phukienhatde --output data/shopee.json
"""

from __future__ import annotations

import argparse
import asyncio
import json
import os
import re
from pathlib import Path
from typing import Any

from playwright.async_api import Response, async_playwright

CDP_URL = os.environ.get("CDP_URL", "http://localhost:29229")


def slugify(text: str) -> str:
    text = text.lower()
    replacements = {
        "à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ": "a",
        "è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ": "e",
        "ì|í|ị|ỉ|ĩ": "i",
        "ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ": "o",
        "ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ": "u",
        "ỳ|ý|ỵ|ỷ|ỹ": "y",
        "đ": "d",
    }
    for pattern, replacement in replacements.items():
        text = re.sub(pattern, replacement, text)
    text = re.sub(r"[^a-z0-9]+", "-", text)
    return text.strip("-")[:200]


def normalize_image(image_hash: str | None) -> str:
    if not image_hash:
        return ""
    if image_hash.startswith("http"):
        return image_hash
    return f"https://down-vn.img.susercontent.com/file/{image_hash}"


def transform_basic_item(basic: dict) -> dict:
    """Transform raw Shopee `item_basic` block into our product schema."""
    name = basic.get("name", "")
    item_id = basic.get("itemid")

    price = (basic.get("price") or 0) / 100000
    original_price = (basic.get("price_before_discount") or basic.get("price") or 0) / 100000
    if original_price <= 0:
        original_price = price

    discount = (
        int(round((1 - price / original_price) * 100))
        if original_price > 0 and price < original_price
        else 0
    )

    images = [normalize_image(img) for img in (basic.get("images") or []) if img]
    if not images and basic.get("image"):
        images = [normalize_image(basic["image"])]

    rating_info = basic.get("item_rating") or {}
    rating_counts = rating_info.get("rating_count") or [0, 0, 0, 0, 0, 0]
    total_reviews = sum(rating_counts[1:6]) if len(rating_counts) > 5 else 0
    rating_star = round(float(rating_info.get("rating_star") or 0), 1)

    models = basic.get("models") or []
    variants = [
        {
            "name": "Phân loại",
            "value": m.get("name", ""),
            "sku": f"V-{m.get('modelid')}",
            "price_diff": ((m.get("price") or 0) / 100000) - price,
            "stock": m.get("stock", 0) or 0,
        }
        for m in models
    ]

    return {
        "shopee_product_id": str(item_id),
        "sku": f"SP-{item_id}",
        "name": name,
        "slug": f"{slugify(name)}-{item_id}",
        "description": (basic.get("description") or "").strip(),
        "images": images[:9],
        "price": price,
        "original_price": original_price,
        "discount": discount,
        "stock": basic.get("stock") or 0,
        "sold": basic.get("historical_sold", 0) or 0,
        "shopee_sold": basic.get("historical_sold", 0) or 0,
        "shopee_rating": rating_star,
        "shopee_review_count": total_reviews,
        "shopee_category_id": basic.get("catid"),
        "variants": variants,
        "five_star_reviews": [],
    }


async def crawl(username: str, output: Path, max_products: int = 100) -> None:
    output.parent.mkdir(parents=True, exist_ok=True)

    products_by_id: dict[int, dict] = {}
    captured: list[dict] = []
    shop_info: dict[str, Any] = {}

    async with async_playwright() as pw:
        print(f"→ Connecting to Chrome via CDP at {CDP_URL}")
        browser = await pw.chromium.connect_over_cdp(CDP_URL)
        context = browser.contexts[0] if browser.contexts else await browser.new_context()

        page = await context.new_page()

        async def on_response(resp: Response) -> None:
            url = resp.url
            try:
                if "shopee.vn/api/" not in url:
                    return
                if "/shop/get_shop_detail" in url and resp.status == 200:
                    data = await resp.json()
                    if data.get("data"):
                        shop_info.update(data["data"])
                elif ("/shop/search_items" in url or "/recommend/recommend" in url) and resp.status == 200:
                    data = await resp.json()
                    items = data.get("items") or []
                    if isinstance(items, list):
                        captured.extend(items)
                    sections = data.get("data", {}).get("sections", []) if isinstance(data.get("data"), dict) else []
                    for sec in sections:
                        for it in (sec.get("data", {}).get("item") or []):
                            captured.append(it)
            except Exception:
                pass

        page.on("response", on_response)

        print(f"→ Opening https://shopee.vn/{username}")
        await page.goto(f"https://shopee.vn/{username}", wait_until="domcontentloaded", timeout=60000)
        await asyncio.sleep(5)

        print("→ Scrolling to load products ...")
        for _ in range(10):
            await page.mouse.wheel(0, 2500)
            await asyncio.sleep(1.5)

        for raw in captured:
            basic = raw.get("item_basic") or raw
            item_id = basic.get("itemid")
            if not item_id or item_id in products_by_id:
                continue
            products_by_id[item_id] = transform_basic_item(basic)

        print(f"✓ Captured {len(products_by_id)} products from listing")

        shop_id = shop_info.get("shopid")
        product_list = list(products_by_id.values())[:max_products]

        for idx, product in enumerate(product_list, 1):
            item_id = int(product["shopee_product_id"])
            detail_data: dict[str, Any] = {}
            reviews_data: list[dict] = []

            async def on_detail(resp: Response) -> None:
                u = resp.url
                try:
                    if "shopee.vn/api/" not in u:
                        return
                    if "/item/get?" in u and resp.status == 200:
                        d = await resp.json()
                        if d.get("data"):
                            detail_data.update(d["data"])
                    elif "/item/get_ratings" in u and "type=5" in u and resp.status == 200:
                        d = await resp.json()
                        ratings = (d.get("data") or {}).get("ratings") or []
                        if ratings and not reviews_data:
                            reviews_data.extend(ratings)
                except Exception:
                    pass

            page.on("response", on_detail)
            try:
                url = f"https://shopee.vn/product/{shop_id}/{item_id}"
                await page.goto(url, wait_until="domcontentloaded", timeout=45000)
                await asyncio.sleep(2)
                await page.mouse.wheel(0, 4000)
                await asyncio.sleep(1.5)
                await page.mouse.wheel(0, 4000)
                await asyncio.sleep(1.5)
            except Exception as exc:
                print(f"  [{idx}/{len(product_list)}] ✘ goto error: {exc}")
                page.remove_listener("response", on_detail)
                continue
            page.remove_listener("response", on_detail)

            if detail_data:
                merged = transform_basic_item(detail_data)
                product["description"] = (detail_data.get("description") or "").strip()
                product["images"] = (
                    [normalize_image(img) for img in (detail_data.get("images") or []) if img][:9]
                    or product["images"]
                )
                if merged["variants"]:
                    product["variants"] = merged["variants"]

            product["five_star_reviews"] = [
                {
                    "shopee_review_id": str(r.get("cmtid") or r.get("orderid", "")),
                    "user_name": r.get("author_username") or "Người dùng Shopee",
                    "avatar": normalize_image(r.get("author_portrait")) if r.get("author_portrait") else None,
                    "rating": r.get("rating_star", 5),
                    "comment": (r.get("comment") or "").strip(),
                    "images": [normalize_image(img) for img in (r.get("images") or [])],
                    "variant": (
                        r.get("product_items", [{}])[0].get("model_name")
                        if r.get("product_items") else None
                    ),
                    "likes": r.get("like_count", 0),
                }
                for r in reviews_data[:6]
            ]
            print(
                f"  [{idx}/{len(product_list)}] ✓ {product['name'][:55]} | "
                f"Sold:{product['shopee_sold']} | Rating:{product['shopee_rating']} | "
                f"Reviews:{len(product['five_star_reviews'])}"
            )

        await page.close()

    payload = {
        "shop": {
            "shopid": shop_info.get("shopid"),
            "username": username,
            "name": shop_info.get("name"),
            "item_count": shop_info.get("item_count"),
            "rating_good": shop_info.get("rating_good"),
            "follower_count": shop_info.get("follower_count"),
        },
        "products": list(products_by_id.values())[:max_products],
    }
    output.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    print(f"\n✓ Saved {len(payload['products'])} products to {output}")


def main() -> None:
    parser = argparse.ArgumentParser(description="Crawl a Shopee shop into local JSON")
    parser.add_argument("--username", default="phukienhatde")
    parser.add_argument("--output", default="data/shopee_import.json")
    parser.add_argument("--max", type=int, default=100)
    args = parser.parse_args()

    asyncio.run(crawl(args.username, Path(args.output), max_products=args.max))


if __name__ == "__main__":
    main()
