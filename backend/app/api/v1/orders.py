"""Order endpoints."""

import uuid
from datetime import datetime

from fastapi import APIRouter, BackgroundTasks, Depends, HTTPException, status
from sqlalchemy.orm import Session, selectinload

from app.db.session import get_db
from app.models.order import Order, OrderItem
from app.models.product import Product
from app.schemas.inventory import InventoryDeductItem
from app.schemas.order import OrderCreate, OrderRead, OrderUpdateStatus
from app.services.inventory_service import InventoryService

router = APIRouter(prefix="/orders", tags=["orders"])

SHIPPING_FEE_FREE_THRESHOLD = 150000
STANDARD_SHIPPING_FEE = 30000


def _generate_order_number() -> str:
    """Generate a unique order number: ORD-YYYYMMDD-XXXXXX."""
    today = datetime.utcnow().strftime("%Y%m%d")
    suffix = uuid.uuid4().hex[:6].upper()
    return f"ORD-{today}-{suffix}"


@router.post("", response_model=OrderRead, status_code=status.HTTP_201_CREATED)
def create_order(data: OrderCreate, db: Session = Depends(get_db)):
    """Create a new order from the cart."""
    subtotal = sum(item.quantity * item.price for item in data.items)
    shipping_fee = 0 if subtotal >= SHIPPING_FEE_FREE_THRESHOLD else STANDARD_SHIPPING_FEE
    total = subtotal + shipping_fee

    for item in data.items:
        product = db.get(Product, item.product_id)
        if not product or not product.is_active:
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail=f"Product {item.product_id} not available",
            )
        if product.stock < item.quantity:
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail=f"Insufficient stock for {product.name} (available: {product.stock})",
            )

    order = Order(
        order_number=_generate_order_number(),
        customer_name=data.customer_name,
        customer_phone=data.customer_phone,
        customer_email=data.customer_email,
        shipping_address=data.shipping_address,
        shipping_province=data.shipping_province,
        shipping_district=data.shipping_district,
        shipping_ward=data.shipping_ward,
        subtotal=subtotal,
        shipping_fee=shipping_fee,
        total=total,
        payment_method=data.payment_method,
        shipping_method=data.shipping_method,
        status="pending",
        note=data.note,
    )
    db.add(order)
    db.flush()

    for item in data.items:
        db.add(
            OrderItem(
                order_id=order.id,
                product_id=item.product_id,
                sku=item.sku,
                product_name=item.product_name,
                product_image=item.product_image,
                variant=item.variant,
                quantity=item.quantity,
                price=item.price,
                subtotal=item.quantity * item.price,
            )
        )

    db.commit()
    db.refresh(order)
    return order


@router.get("", response_model=list[OrderRead])
def list_orders(
    customer_phone: str | None = None,
    order_status: str | None = None,
    db: Session = Depends(get_db),
):
    query = db.query(Order).options(selectinload(Order.items))
    if customer_phone:
        query = query.filter(Order.customer_phone == customer_phone)
    if order_status:
        query = query.filter(Order.status == order_status)
    return query.order_by(Order.created_at.desc()).limit(100).all()


@router.get("/{order_number}", response_model=OrderRead)
def get_order(order_number: str, db: Session = Depends(get_db)):
    order = (
        db.query(Order)
        .options(selectinload(Order.items))
        .filter(Order.order_number == order_number)
        .first()
    )
    if not order:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Order not found")
    return order


@router.patch("/{order_number}/status", response_model=OrderRead)
async def update_order_status(
    order_number: str,
    payload: OrderUpdateStatus,
    background: BackgroundTasks,
    db: Session = Depends(get_db),
):
    """Update order status. Triggers inventory deduction on confirm, restore on cancel."""
    order = (
        db.query(Order)
        .options(selectinload(Order.items))
        .filter(Order.order_number == order_number)
        .first()
    )
    if not order:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Order not found")

    old_status = order.status
    order.status = payload.status
    if payload.note:
        order.note = payload.note

    service = InventoryService(db)
    items = [InventoryDeductItem(sku=item.sku, quantity=item.quantity) for item in order.items]

    if payload.status == "confirmed" and old_status == "pending":
        service.deduct_stock(items, order.order_number)
        background.add_task(_export_order_to_warehouse, order.id)
    elif payload.status in {"cancelled", "returned"} and old_status in {"confirmed", "shipping", "delivered"}:
        service.restore_stock(items, order.order_number, reason=f"status_changed_to_{payload.status}")

    db.commit()
    db.refresh(order)
    return order


async def _export_order_to_warehouse(order_id: int) -> None:
    """Background task to export an order to the warehouse software."""
    from app.db.session import SessionLocal

    db = SessionLocal()
    try:
        order = (
            db.query(Order)
            .options(selectinload(Order.items))
            .filter(Order.id == order_id)
            .first()
        )
        if order:
            service = InventoryService(db)
            await service.export_order_to_warehouse(order)
            db.commit()
    finally:
        db.close()
