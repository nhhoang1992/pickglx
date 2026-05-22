"""Order and customer database models."""

from datetime import datetime
from sqlalchemy import (
    Column,
    DateTime,
    Float,
    ForeignKey,
    Integer,
    String,
    Text,
)
from sqlalchemy.orm import relationship

from app.db.session import Base


class Customer(Base):
    __tablename__ = "customers"

    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(200), nullable=False)
    phone = Column(String(20), index=True)
    email = Column(String(200), index=True, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)

    orders = relationship("Order", back_populates="customer")


class Address(Base):
    __tablename__ = "addresses"

    id = Column(Integer, primary_key=True, index=True)
    customer_id = Column(Integer, ForeignKey("customers.id"), index=True)
    full_name = Column(String(200), nullable=False)
    phone = Column(String(20), nullable=False)
    province = Column(String(100), nullable=False)
    district = Column(String(100), nullable=False)
    ward = Column(String(100), nullable=False)
    detail = Column(Text, nullable=False)
    is_default = Column(Integer, default=0)
    created_at = Column(DateTime, default=datetime.utcnow)


class Order(Base):
    __tablename__ = "orders"

    id = Column(Integer, primary_key=True, index=True)
    order_number = Column(String(50), unique=True, index=True, nullable=False)
    customer_id = Column(Integer, ForeignKey("customers.id"), index=True, nullable=True)

    customer_name = Column(String(200), nullable=False)
    customer_phone = Column(String(20), nullable=False)
    customer_email = Column(String(200))

    shipping_address = Column(Text, nullable=False)
    shipping_province = Column(String(100))
    shipping_district = Column(String(100))
    shipping_ward = Column(String(100))

    subtotal = Column(Float, nullable=False)
    shipping_fee = Column(Float, default=0)
    discount = Column(Float, default=0)
    total = Column(Float, nullable=False)

    payment_method = Column(String(50), nullable=False)
    payment_status = Column(String(50), default="pending")
    shipping_method = Column(String(50), default="standard")

    status = Column(String(50), default="pending", index=True)
    note = Column(Text)

    inventory_exported = Column(Integer, default=0)
    inventory_exported_at = Column(DateTime, nullable=True)

    created_at = Column(DateTime, default=datetime.utcnow, index=True)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    customer = relationship("Customer", back_populates="orders")
    items = relationship("OrderItem", back_populates="order", cascade="all, delete-orphan")


class OrderItem(Base):
    __tablename__ = "order_items"

    id = Column(Integer, primary_key=True, index=True)
    order_id = Column(Integer, ForeignKey("orders.id"), index=True)
    product_id = Column(Integer, ForeignKey("products.id"), index=True)
    sku = Column(String(100), index=True)
    product_name = Column(String(500), nullable=False)
    product_image = Column(String(500))
    variant = Column(String(200))
    quantity = Column(Integer, nullable=False)
    price = Column(Float, nullable=False)
    subtotal = Column(Float, nullable=False)

    order = relationship("Order", back_populates="items")
