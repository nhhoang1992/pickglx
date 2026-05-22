"""Seed sample data into the database for development."""

from datetime import datetime, timedelta

from app.db.session import Base, SessionLocal, engine
from app.models.order import Customer, Order, OrderItem  # noqa: F401
from app.models.product import (
    Category,
    InventoryLog,  # noqa: F401
    Product,
    ProductReview,
    ProductVariant,
)


def seed():
    Base.metadata.create_all(bind=engine)
    db = SessionLocal()

    try:
        if db.query(Category).first():
            print("Database already seeded.")
            return

        categories_data = [
            ("Kính cường lực", "kinh-cuong-luc", "🔲", 1),
            ("Ốp lưng", "op-lung", "📱", 2),
            ("Sạc & Adapter", "sac-adapter", "🔌", 3),
            ("Cáp sạc", "cap-sac", "🔗", 4),
            ("Tai nghe", "tai-nghe", "🎧", 5),
            ("Pin dự phòng", "pin-du-phong", "🔋", 6),
            ("Giá đỡ điện thoại", "gia-do-dien-thoai", "📲", 7),
            ("Phụ kiện khác", "phu-kien-khac", "📦", 8),
        ]
        categories: dict[str, Category] = {}
        for name, slug, icon, order in categories_data:
            cat = Category(name=name, slug=slug, icon=icon, sort_order=order)
            db.add(cat)
            categories[slug] = cat
        db.flush()

        products_data = [
            {
                "sku": "KC-IP15PM-001",
                "name": "Kính cường lực iPhone 15 Pro Max full màn hình 9D",
                "slug": "kinh-cuong-luc-iphone-15-pro-max-full-man-9d",
                "category": "kinh-cuong-luc",
                "price": 45000,
                "original_price": 120000,
                "stock": 120,
                "sold": 2847,
                "shopee_sold": 2847,
                "shopee_rating": 4.9,
                "shopee_review_count": 1523,
                "is_flash_sale": True,
                "flash_sale_price": 35000,
                "compatible_models": ["iPhone 15 Pro Max"],
                "specifications": {"Độ cứng": "9H", "Độ dày": "0.33mm"},
            },
            {
                "sku": "OL-IP15PM-002",
                "name": "Ốp lưng iPhone 15 Pro Max chống sốc Xundd",
                "slug": "op-lung-iphone-15-pro-max-chong-soc-xundd",
                "category": "op-lung",
                "price": 89000,
                "original_price": 250000,
                "stock": 185,
                "sold": 5432,
                "shopee_sold": 5432,
                "shopee_rating": 4.8,
                "shopee_review_count": 2891,
                "is_flash_sale": True,
                "flash_sale_price": 69000,
                "compatible_models": ["iPhone 15 Pro Max"],
                "specifications": {"Chất liệu": "TPU + PC"},
            },
            {
                "sku": "SC-20W-003",
                "name": "Sạc nhanh 20W PD cho iPhone Type-C chính hãng",
                "slug": "sac-nhanh-20w-pd-iphone-type-c",
                "category": "sac-adapter",
                "price": 79000,
                "original_price": 200000,
                "stock": 200,
                "sold": 8921,
                "shopee_sold": 8921,
                "shopee_rating": 4.9,
                "shopee_review_count": 4567,
                "compatible_models": ["iPhone 15", "Samsung", "Xiaomi"],
                "specifications": {"Công suất": "20W", "Cổng": "Type-C PD"},
            },
            {
                "sku": "CAP-CTL-004",
                "name": "Cáp sạc nhanh Type-C to Lightning MFi 1m",
                "slug": "cap-sac-nhanh-type-c-lightning-mfi-1m",
                "category": "cap-sac",
                "price": 59000,
                "original_price": 150000,
                "stock": 170,
                "sold": 3456,
                "shopee_sold": 3456,
                "shopee_rating": 4.7,
                "shopee_review_count": 1890,
                "is_flash_sale": True,
                "flash_sale_price": 45000,
                "compatible_models": ["iPhone 15", "iPad"],
                "specifications": {"Chiều dài": "1m", "Chuẩn": "MFi"},
            },
            {
                "sku": "TN-TWS6-005",
                "name": "Tai nghe Bluetooth TWS Pro 6 âm thanh HiFi",
                "slug": "tai-nghe-bluetooth-tws-pro-6-hifi",
                "category": "tai-nghe",
                "price": 69000,
                "original_price": 350000,
                "stock": 175,
                "sold": 12543,
                "shopee_sold": 12543,
                "shopee_rating": 4.6,
                "shopee_review_count": 6789,
                "is_flash_sale": True,
                "flash_sale_price": 49000,
                "specifications": {"Kết nối": "Bluetooth 5.3"},
            },
        ]

        for p_data in products_data:
            cat_slug = p_data.pop("category")
            original_price = p_data["original_price"]
            price = p_data["price"]
            discount = int((1 - price / original_price) * 100) if original_price > 0 else 0

            product = Product(
                category_id=categories[cat_slug].id,
                discount=discount,
                images=[f"https://placehold.co/800x800/EE4D2D/white?text={p_data['sku']}"],
                shopee_last_synced_at=datetime.utcnow(),
                flash_sale_end=datetime.utcnow() + timedelta(days=30)
                if p_data.get("is_flash_sale")
                else None,
                description=p_data["name"]
                + ". Chính hãng, giá tốt, giao hàng nhanh, bảo hành 1 đổi 1.",
                **p_data,
            )
            db.add(product)
            db.flush()

            db.add(
                ProductReview(
                    product_id=product.id,
                    user_name="Nguyễn Văn A",
                    rating=5,
                    comment="Sản phẩm rất tốt, giao hàng nhanh. Sẽ ủng hộ shop dài dài!",
                    source="shopee",
                    likes=12,
                )
            )
            db.add(
                ProductReview(
                    product_id=product.id,
                    user_name="Trần Thị B",
                    rating=5,
                    comment="Chất lượng tuyệt vời, đáng đồng tiền bát gạo. 5 sao!",
                    source="shopee",
                    likes=8,
                )
            )

            if p_data["sku"] == "OL-IP15PM-002":
                db.add(
                    ProductVariant(
                        product_id=product.id,
                        name="Màu sắc",
                        value="Trong suốt",
                        sku=f"{p_data['sku']}-TRONG",
                        stock=80,
                    )
                )
                db.add(
                    ProductVariant(
                        product_id=product.id,
                        name="Màu sắc",
                        value="Đen",
                        sku=f"{p_data['sku']}-DEN",
                        stock=60,
                    )
                )

        db.commit()
        print(
            f"Seeded {len(categories_data)} categories and {len(products_data)} products"
        )

    finally:
        db.close()


if __name__ == "__main__":
    seed()
