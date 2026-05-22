"""Shopee sync service: synchronize sold count and 5-star reviews from Shopee."""

from datetime import datetime
from typing import Optional

import httpx
from sqlalchemy.orm import Session

from app.models.product import Product, ProductReview
from app.schemas.inventory import ShopeeProductMetrics

SHOPEE_API_BASE = "https://shopee.vn/api/v4"
SHOPEE_HEADERS = {
    "User-Agent": (
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
        "(KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    ),
    "Accept": "application/json",
    "Referer": "https://shopee.vn/",
    "X-Requested-With": "XMLHttpRequest",
}


class ShopeeSyncService:
    """Sync product metrics (sold count, ratings, reviews) from Shopee."""

    def __init__(self, db: Session):
        self.db = db

    async def fetch_shop_info(self, username: str) -> Optional[dict]:
        """Fetch shop information by username."""
        url = f"{SHOPEE_API_BASE}/shop/get_shop_detail"
        params = {"username": username}

        async with httpx.AsyncClient(timeout=15.0, headers=SHOPEE_HEADERS) as client:
            try:
                response = await client.get(url, params=params)
                if response.status_code == 200:
                    data = response.json()
                    return data.get("data")
            except httpx.RequestError:
                return None
        return None

    async def fetch_product_metrics(self, shop_id: str, item_id: str) -> Optional[dict]:
        """Fetch a single product's metrics from Shopee."""
        url = f"{SHOPEE_API_BASE}/item/get"
        params = {"shopid": shop_id, "itemid": item_id}

        async with httpx.AsyncClient(timeout=15.0, headers=SHOPEE_HEADERS) as client:
            try:
                response = await client.get(url, params=params)
                if response.status_code == 200:
                    data = response.json()
                    item = data.get("data", {})
                    return {
                        "sold": item.get("historical_sold", 0),
                        "rating_star": item.get("item_rating", {}).get("rating_star", 0),
                        "rating_count": item.get("item_rating", {}).get("rating_count", [0, 0, 0, 0, 0, 0]),
                        "shop_location": item.get("shop_location"),
                        "name": item.get("name"),
                    }
            except httpx.RequestError:
                return None
        return None

    async def fetch_five_star_reviews(self, shop_id: str, item_id: str, limit: int = 10) -> list[dict]:
        """Fetch 5-star reviews for a product from Shopee."""
        url = f"{SHOPEE_API_BASE}/item/get_ratings"
        params = {
            "shopid": shop_id,
            "itemid": item_id,
            "type": 5,
            "limit": limit,
            "offset": 0,
            "flag": 1,
        }

        async with httpx.AsyncClient(timeout=15.0, headers=SHOPEE_HEADERS) as client:
            try:
                response = await client.get(url, params=params)
                if response.status_code == 200:
                    data = response.json()
                    return data.get("data", {}).get("ratings", []) or []
            except httpx.RequestError:
                return []
        return []

    async def sync_product(self, product: Product, shop_id: str) -> Optional[ShopeeProductMetrics]:
        """Sync a single product's metrics and 5-star reviews from Shopee."""
        if not product.shopee_product_id:
            return None

        metrics = await self.fetch_product_metrics(shop_id, product.shopee_product_id)
        if not metrics:
            return None

        rating_counts = metrics.get("rating_count", [0, 0, 0, 0, 0, 0])
        five_star_count = rating_counts[5] if len(rating_counts) > 5 else 0
        total_reviews = sum(rating_counts[1:6]) if len(rating_counts) > 5 else 0
        rating_star = metrics.get("rating_star", 0) or 0

        product.shopee_sold = metrics.get("sold", 0)
        product.shopee_rating = round(float(rating_star), 1)
        product.shopee_review_count = total_reviews
        product.shopee_last_synced_at = datetime.utcnow()

        reviews = await self.fetch_five_star_reviews(shop_id, product.shopee_product_id, limit=20)

        for review_data in reviews:
            review_id = str(review_data.get("cmtid") or review_data.get("orderid", ""))
            if not review_id:
                continue

            existing = (
                self.db.query(ProductReview)
                .filter(ProductReview.shopee_review_id == review_id)
                .first()
            )
            if existing:
                continue

            self.db.add(
                ProductReview(
                    product_id=product.id,
                    user_name=review_data.get("author_username", "Người dùng Shopee"),
                    avatar=review_data.get("author_portrait"),
                    rating=review_data.get("rating_star", 5),
                    comment=review_data.get("comment", ""),
                    images=[
                        f"https://cf.shopee.vn/file/{img}"
                        for img in (review_data.get("images") or [])
                    ],
                    variant=(review_data.get("product_items", [{}])[0].get("model_name") if review_data.get("product_items") else None),
                    likes=review_data.get("like_count", 0),
                    source="shopee",
                    shopee_review_id=review_id,
                )
            )

        return ShopeeProductMetrics(
            sku=product.sku,
            shopee_product_id=product.shopee_product_id,
            sold=product.shopee_sold,
            rating=product.shopee_rating,
            review_count=product.shopee_review_count,
            five_star_count=five_star_count,
            last_synced_at=product.shopee_last_synced_at,
        )

    async def sync_shop(self, shop_username: str, skus: Optional[list[str]] = None) -> tuple[list[ShopeeProductMetrics], list[str]]:
        """Sync all (or selected) products of the shop from Shopee."""
        shop_info = await self.fetch_shop_info(shop_username)
        if not shop_info:
            return [], ["Cannot fetch shop info"]

        shop_id = str(shop_info.get("shopid"))
        synced: list[ShopeeProductMetrics] = []
        failed: list[str] = []

        query = self.db.query(Product).filter(Product.shopee_product_id.isnot(None))
        if skus:
            query = query.filter(Product.sku.in_(skus))

        for product in query.all():
            result = await self.sync_product(product, shop_id)
            if result:
                synced.append(result)
            else:
                failed.append(product.sku)

        return synced, failed
