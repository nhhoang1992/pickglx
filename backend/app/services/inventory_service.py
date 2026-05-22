"""Inventory sync service for integration with external warehouse software."""

from datetime import datetime
from typing import Optional

import httpx
from sqlalchemy.orm import Session

from app.core.config import settings
from app.core.security import generate_signature
from app.models.product import InventoryLog, Product
from app.models.order import Order
from app.schemas.inventory import (
    InventoryDeductItem,
    InventoryItem,
    OrderExportItem,
    OrderExportPayload,
)


class InventoryService:
    """Two-way sync with external warehouse management software."""

    def __init__(self, db: Session):
        self.db = db

    def update_stock(self, sku: str, quantity: int, source: str = "manual", reference_id: Optional[str] = None) -> Optional[Product]:
        """Set absolute stock quantity for a SKU."""
        product = self.db.query(Product).filter(Product.sku == sku).first()
        if not product:
            return None

        old_quantity = product.stock
        product.stock = quantity

        self.db.add(
            InventoryLog(
                product_id=product.id,
                sku=sku,
                change_type="set",
                quantity_before=old_quantity,
                quantity_change=quantity - old_quantity,
                quantity_after=quantity,
                reference_id=reference_id,
                source=source,
            )
        )
        return product

    def deduct_stock(self, items: list[InventoryDeductItem], order_id: str) -> tuple[int, list[str]]:
        """Decrease stock when an order is confirmed."""
        updated = 0
        not_found: list[str] = []

        for item in items:
            product = self.db.query(Product).filter(Product.sku == item.sku).first()
            if not product:
                not_found.append(item.sku)
                continue

            old = product.stock
            product.stock = max(0, product.stock - item.quantity)
            product.sold += item.quantity

            self.db.add(
                InventoryLog(
                    product_id=product.id,
                    sku=item.sku,
                    change_type="deduct",
                    quantity_before=old,
                    quantity_change=-item.quantity,
                    quantity_after=product.stock,
                    reference_id=order_id,
                    source="order",
                )
            )
            updated += 1

        return updated, not_found

    def restore_stock(self, items: list[InventoryDeductItem], order_id: str, reason: str) -> tuple[int, list[str]]:
        """Restore stock when an order is cancelled or returned."""
        updated = 0
        not_found: list[str] = []

        for item in items:
            product = self.db.query(Product).filter(Product.sku == item.sku).first()
            if not product:
                not_found.append(item.sku)
                continue

            old = product.stock
            product.stock += item.quantity
            product.sold = max(0, product.sold - item.quantity)

            self.db.add(
                InventoryLog(
                    product_id=product.id,
                    sku=item.sku,
                    change_type="restore",
                    quantity_before=old,
                    quantity_change=item.quantity,
                    quantity_after=product.stock,
                    reference_id=order_id,
                    note=reason,
                    source="order",
                )
            )
            updated += 1

        return updated, not_found

    def bulk_sync(self, items: list[InventoryItem]) -> tuple[int, list[str]]:
        """Bulk update stock from warehouse software."""
        updated = 0
        not_found: list[str] = []

        for item in items:
            product = self.update_stock(
                item.sku,
                item.quantity,
                source="warehouse_sync",
            )
            if product is None:
                not_found.append(item.sku)
            else:
                updated += 1

        return updated, not_found

    async def export_order_to_warehouse(self, order: Order) -> dict:
        """Send a new order to the external warehouse software."""
        payload = OrderExportPayload(
            order_id=order.order_number,
            customer_name=order.customer_name,
            customer_phone=order.customer_phone,
            shipping_address=order.shipping_address,
            items=[
                OrderExportItem(
                    sku=item.sku,
                    name=item.product_name,
                    variant=item.variant,
                    quantity=item.quantity,
                    price=item.price,
                )
                for item in order.items
            ],
            subtotal=order.subtotal,
            shipping_fee=order.shipping_fee,
            total=order.total,
            payment_method=order.payment_method,
            status=order.status,
            created_at=order.created_at,
        )

        body = payload.model_dump_json().encode()
        signature = generate_signature(body)

        if not settings.INVENTORY_EXTERNAL_URL or "example.com" in settings.INVENTORY_EXTERNAL_URL:
            return {
                "exported": False,
                "reason": "INVENTORY_EXTERNAL_URL not configured (dry-run mode)",
                "payload": payload.model_dump(mode="json"),
            }

        try:
            async with httpx.AsyncClient(timeout=10.0) as client:
                response = await client.post(
                    f"{settings.INVENTORY_EXTERNAL_URL}/orders",
                    content=body,
                    headers={
                        "Content-Type": "application/json",
                        "X-API-Key": settings.INVENTORY_API_KEY,
                        "X-Signature": signature,
                    },
                )
                order.inventory_exported = 1 if response.status_code < 400 else 0
                order.inventory_exported_at = datetime.utcnow()
                return {
                    "exported": response.status_code < 400,
                    "status_code": response.status_code,
                    "response": response.json() if response.headers.get("content-type", "").startswith("application/json") else response.text,
                }
        except httpx.RequestError as exc:
            return {"exported": False, "error": str(exc)}
