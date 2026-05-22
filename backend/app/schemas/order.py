"""Pydantic schemas for orders."""

from datetime import datetime
from typing import Optional

from pydantic import BaseModel, ConfigDict, EmailStr, Field


class OrderItemCreate(BaseModel):
    product_id: int
    sku: str
    product_name: str
    product_image: Optional[str] = None
    variant: Optional[str] = None
    quantity: int = Field(..., ge=1)
    price: float


class OrderItemRead(OrderItemCreate):
    model_config = ConfigDict(from_attributes=True)

    id: int
    subtotal: float


class OrderCreate(BaseModel):
    customer_name: str
    customer_phone: str
    customer_email: Optional[EmailStr] = None
    shipping_address: str
    shipping_province: Optional[str] = None
    shipping_district: Optional[str] = None
    shipping_ward: Optional[str] = None
    payment_method: str = Field(..., description="cod | banking | momo | vnpay")
    shipping_method: str = "standard"
    items: list[OrderItemCreate]
    note: Optional[str] = None


class OrderRead(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    order_number: str
    customer_name: str
    customer_phone: str
    customer_email: Optional[str] = None
    shipping_address: str
    subtotal: float
    shipping_fee: float
    discount: float
    total: float
    payment_method: str
    payment_status: str
    shipping_method: str
    status: str
    note: Optional[str] = None
    inventory_exported: int
    created_at: datetime
    updated_at: datetime
    items: list[OrderItemRead] = []


class OrderUpdateStatus(BaseModel):
    status: str = Field(..., description="pending | confirmed | shipping | delivered | cancelled | returned")
    note: Optional[str] = None
