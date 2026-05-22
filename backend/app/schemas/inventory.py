"""Pydantic schemas for inventory sync with external warehouse software."""

from datetime import datetime
from typing import Optional

from pydantic import BaseModel, Field


class InventoryItem(BaseModel):
    sku: str
    quantity: int = Field(..., ge=0)
    warehouse: Optional[str] = None
    updated_at: Optional[datetime] = None


class InventorySyncRequest(BaseModel):
    items: list[InventoryItem]


class InventorySyncResponse(BaseModel):
    success: bool
    updated_count: int
    not_found_skus: list[str] = []


class InventoryDeductItem(BaseModel):
    sku: str
    quantity: int = Field(..., ge=1)


class InventoryDeductRequest(BaseModel):
    order_id: str
    items: list[InventoryDeductItem]


class InventoryRestoreRequest(BaseModel):
    order_id: str
    items: list[InventoryDeductItem]
    reason: str = "order_cancelled"


class InventoryWebhookPayload(BaseModel):
    sku: str
    quantity: int
    warehouse: Optional[str] = None
    updated_at: datetime


class OrderExportItem(BaseModel):
    sku: str
    name: str
    variant: Optional[str] = None
    quantity: int
    price: float


class OrderExportPayload(BaseModel):
    order_id: str
    customer_name: str
    customer_phone: str
    shipping_address: str
    items: list[OrderExportItem]
    subtotal: float
    shipping_fee: float
    total: float
    payment_method: str
    status: str
    created_at: datetime


class ShopeeSyncRequest(BaseModel):
    shop_username: str
    product_skus: Optional[list[str]] = None


class ShopeeProductMetrics(BaseModel):
    sku: str
    shopee_product_id: Optional[str] = None
    sold: int = 0
    rating: float = 0
    review_count: int = 0
    five_star_count: int = 0
    last_synced_at: datetime


class ShopeeSyncResponse(BaseModel):
    success: bool
    synced_count: int
    failed_skus: list[str] = []
    metrics: list[ShopeeProductMetrics] = []
