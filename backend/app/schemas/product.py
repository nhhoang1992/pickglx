"""Pydantic schemas for products."""

from datetime import datetime
from typing import Any, Optional

from pydantic import BaseModel, ConfigDict, Field


class CategoryBase(BaseModel):
    name: str
    slug: str
    icon: Optional[str] = None
    image: Optional[str] = None
    parent_id: Optional[int] = None
    sort_order: int = 0


class CategoryCreate(CategoryBase):
    pass


class CategoryRead(CategoryBase):
    model_config = ConfigDict(from_attributes=True)

    id: int
    is_active: bool
    created_at: datetime


class ProductVariantSchema(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: Optional[int] = None
    name: str
    value: str
    sku: Optional[str] = None
    image: Optional[str] = None
    price_diff: float = 0
    stock: int = 0


class ProductReviewSchema(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: Optional[int] = None
    user_name: str
    avatar: Optional[str] = None
    rating: int = Field(..., ge=1, le=5)
    comment: Optional[str] = None
    images: list[str] = []
    variant: Optional[str] = None
    likes: int = 0
    source: str = "website"
    created_at: Optional[datetime] = None


class ProductBase(BaseModel):
    sku: str
    name: str
    slug: str
    description: Optional[str] = None
    specifications: dict[str, Any] = {}
    images: list[str] = []
    compatible_models: list[str] = []
    price: float
    original_price: float
    cost_price: float = 0
    category_id: int


class ProductCreate(ProductBase):
    stock: int = 0
    variants: list[ProductVariantSchema] = []


class ProductUpdate(BaseModel):
    name: Optional[str] = None
    description: Optional[str] = None
    price: Optional[float] = None
    original_price: Optional[float] = None
    stock: Optional[int] = None
    is_active: Optional[bool] = None
    is_flash_sale: Optional[bool] = None
    flash_sale_price: Optional[float] = None
    flash_sale_end: Optional[datetime] = None


class ProductRead(ProductBase):
    model_config = ConfigDict(from_attributes=True)

    id: int
    discount: int
    stock: int
    sold: int
    rating: float
    review_count: int
    shopee_product_id: Optional[str] = None
    shopee_rating: float
    shopee_sold: int
    shopee_review_count: int
    shopee_last_synced_at: Optional[datetime] = None
    is_flash_sale: bool
    flash_sale_price: Optional[float] = None
    flash_sale_end: Optional[datetime] = None
    is_active: bool
    created_at: datetime
    updated_at: datetime
    variants: list[ProductVariantSchema] = []


class ProductListResponse(BaseModel):
    items: list[ProductRead]
    total: int
    page: int
    page_size: int
