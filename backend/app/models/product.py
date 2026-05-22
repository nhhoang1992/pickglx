"""Product and inventory database models."""

from datetime import datetime
from sqlalchemy import (
    Boolean,
    Column,
    DateTime,
    Float,
    ForeignKey,
    Integer,
    JSON,
    String,
    Text,
)
from sqlalchemy.orm import relationship

from app.db.session import Base


class Category(Base):
    __tablename__ = "categories"

    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(200), nullable=False)
    slug = Column(String(200), unique=True, index=True, nullable=False)
    icon = Column(String(20))
    image = Column(String(500))
    parent_id = Column(Integer, ForeignKey("categories.id"), nullable=True)
    sort_order = Column(Integer, default=0)
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime, default=datetime.utcnow)

    products = relationship("Product", back_populates="category")
    children = relationship("Category", remote_side=[parent_id])


class Product(Base):
    __tablename__ = "products"

    id = Column(Integer, primary_key=True, index=True)
    sku = Column(String(100), unique=True, index=True, nullable=False)
    name = Column(String(500), nullable=False)
    slug = Column(String(500), unique=True, index=True, nullable=False)
    description = Column(Text)
    specifications = Column(JSON, default=dict)
    images = Column(JSON, default=list)
    compatible_models = Column(JSON, default=list)

    price = Column(Float, nullable=False)
    original_price = Column(Float, nullable=False)
    cost_price = Column(Float, default=0)
    discount = Column(Integer, default=0)

    category_id = Column(Integer, ForeignKey("categories.id"), index=True)
    category = relationship("Category", back_populates="products")

    stock = Column(Integer, default=0)
    sold = Column(Integer, default=0)
    rating = Column(Float, default=0)
    review_count = Column(Integer, default=0)

    shopee_product_id = Column(String(100), index=True, nullable=True)
    shopee_rating = Column(Float, default=0)
    shopee_sold = Column(Integer, default=0)
    shopee_review_count = Column(Integer, default=0)
    shopee_last_synced_at = Column(DateTime, nullable=True)

    is_flash_sale = Column(Boolean, default=False)
    flash_sale_price = Column(Float, nullable=True)
    flash_sale_start = Column(DateTime, nullable=True)
    flash_sale_end = Column(DateTime, nullable=True)

    is_active = Column(Boolean, default=True)
    seo_title = Column(String(500))
    seo_description = Column(String(1000))

    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    variants = relationship("ProductVariant", back_populates="product", cascade="all, delete-orphan")
    reviews = relationship("ProductReview", back_populates="product", cascade="all, delete-orphan")
    inventory_logs = relationship("InventoryLog", back_populates="product")


class ProductVariant(Base):
    __tablename__ = "product_variants"

    id = Column(Integer, primary_key=True, index=True)
    product_id = Column(Integer, ForeignKey("products.id"), index=True)
    name = Column(String(100), nullable=False)
    value = Column(String(200), nullable=False)
    sku = Column(String(100), index=True)
    image = Column(String(500))
    price_diff = Column(Float, default=0)
    stock = Column(Integer, default=0)
    sort_order = Column(Integer, default=0)

    product = relationship("Product", back_populates="variants")


class ProductReview(Base):
    __tablename__ = "product_reviews"

    id = Column(Integer, primary_key=True, index=True)
    product_id = Column(Integer, ForeignKey("products.id"), index=True)
    user_name = Column(String(200), nullable=False)
    avatar = Column(String(500))
    rating = Column(Integer, nullable=False)
    comment = Column(Text)
    images = Column(JSON, default=list)
    variant = Column(String(200))
    likes = Column(Integer, default=0)

    source = Column(String(50), default="website")
    shopee_review_id = Column(String(100), index=True, nullable=True)

    created_at = Column(DateTime, default=datetime.utcnow)

    product = relationship("Product", back_populates="reviews")


class InventoryLog(Base):
    __tablename__ = "inventory_logs"

    id = Column(Integer, primary_key=True, index=True)
    product_id = Column(Integer, ForeignKey("products.id"), index=True)
    sku = Column(String(100), index=True)
    change_type = Column(String(50), nullable=False)
    quantity_before = Column(Integer)
    quantity_change = Column(Integer)
    quantity_after = Column(Integer)
    reference_id = Column(String(100))
    note = Column(Text)
    source = Column(String(50), default="manual")
    created_at = Column(DateTime, default=datetime.utcnow, index=True)

    product = relationship("Product", back_populates="inventory_logs")
