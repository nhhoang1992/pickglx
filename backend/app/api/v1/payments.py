"""Payment gateway endpoints: MoMo, ZaloPay, COD."""

import json
from datetime import datetime
from typing import Any

from fastapi import APIRouter, Depends, HTTPException, Request, status
from sqlalchemy.orm import Session

from app.db.session import get_db
from app.models.order import Order
from app.services.payments import CODProvider, MoMoProvider, ZaloPayProvider

router = APIRouter(prefix="/payments", tags=["payments"])


def _get_provider(name: str):
    name = (name or "").lower()
    if name == "momo":
        return MoMoProvider()
    if name == "zalopay":
        return ZaloPayProvider()
    if name == "cod":
        return CODProvider()
    raise HTTPException(
        status_code=status.HTTP_400_BAD_REQUEST,
        detail=f"Unsupported payment provider: {name}",
    )


@router.post("/{provider}/create/{order_number}")
async def create_payment_session(
    provider: str,
    order_number: str,
    db: Session = Depends(get_db),
):
    """Create a payment session for an existing order and return the gateway URL."""
    order = db.query(Order).filter(Order.order_number == order_number).first()
    if not order:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Order not found")
    if order.payment_status == "paid":
        raise HTTPException(status_code=status.HTTP_400_BAD_REQUEST, detail="Order already paid")

    svc = _get_provider(provider)
    result = await svc.create_payment(
        order_number=order_number,
        amount=int(order.total),
        extra={
            "customer_phone": order.customer_phone,
            "order_info": f"Thanh toán đơn {order_number}",
            "items": [
                {"itemid": str(it.product_id), "itemname": it.product_name, "itemprice": int(it.price), "itemquantity": it.quantity}
                for it in order.items
            ],
        },
    )

    if result.get("payment_status") == "pending":
        order.payment_method = provider
        db.commit()

    return result


@router.post("/momo/ipn")
async def momo_ipn(request: Request, db: Session = Depends(get_db)) -> dict[str, Any]:
    """MoMo Instant Payment Notification. MoMo POSTs JSON body with signature."""
    payload = await request.json()
    svc = MoMoProvider()
    if not svc.verify_callback(payload):
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid MoMo signature")

    order_number = payload.get("orderId")
    result_code = payload.get("resultCode")
    order = db.query(Order).filter(Order.order_number == order_number).first()
    if not order:
        return {"received": True, "found": False}

    if result_code == 0:
        order.payment_status = "paid"
        order.status = "confirmed"
        order.updated_at = datetime.utcnow()
    else:
        order.payment_status = "failed"
    db.commit()

    return {"received": True, "order_number": order_number, "payment_status": order.payment_status}


@router.post("/zalopay/callback")
async def zalopay_callback(request: Request, db: Session = Depends(get_db)) -> dict[str, Any]:
    """ZaloPay callback. Body is JSON {data, mac, type}."""
    body = await request.json()
    svc = ZaloPayProvider()
    if not svc.verify_callback(body):
        return {"return_code": -1, "return_message": "Invalid mac"}

    try:
        data = json.loads(body.get("data", "{}"))
    except json.JSONDecodeError:
        return {"return_code": -1, "return_message": "Invalid data"}

    embed = json.loads(data.get("embed_data", "{}"))
    order_number = embed.get("order_number")
    order = db.query(Order).filter(Order.order_number == order_number).first()
    if not order:
        return {"return_code": 1, "return_message": "Order not found but ack"}

    order.payment_status = "paid"
    order.status = "confirmed"
    order.updated_at = datetime.utcnow()
    db.commit()

    return {"return_code": 1, "return_message": "success"}


@router.get("/{provider}/methods")
def list_supported_providers() -> list[dict[str, Any]]:
    """List all supported payment providers and their UI metadata."""
    return [
        {"id": "cod", "name": "Thanh toán khi nhận hàng (COD)", "icon": "💵", "instant": False},
        {"id": "momo", "name": "Ví MoMo", "icon": "📱", "instant": True},
        {"id": "zalopay", "name": "ZaloPay", "icon": "💳", "instant": True},
    ]
