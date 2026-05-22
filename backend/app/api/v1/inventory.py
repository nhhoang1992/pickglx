"""Inventory endpoints — public catalog stock queries and external warehouse sync."""

from fastapi import APIRouter, Depends, HTTPException, Request, status
from sqlalchemy.orm import Session

from app.core.security import verify_api_key, verify_webhook_signature
from app.db.session import get_db
from app.models.product import Product
from app.schemas.inventory import (
    InventoryDeductRequest,
    InventoryRestoreRequest,
    InventorySyncRequest,
    InventorySyncResponse,
    InventoryWebhookPayload,
)
from app.services.inventory_service import InventoryService

router = APIRouter(prefix="/inventory", tags=["inventory"])


@router.get("/products/{sku}")
def get_inventory_by_sku(sku: str, db: Session = Depends(get_db)):
    """Realtime stock query for a single SKU."""
    product = db.query(Product).filter(Product.sku == sku).first()
    if not product:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="SKU not found")
    return {
        "sku": product.sku,
        "stock": product.stock,
        "sold": product.sold,
        "is_active": product.is_active,
    }


@router.get("/products")
def get_inventory_bulk(skus: str, db: Session = Depends(get_db)):
    """Realtime stock query for multiple SKUs (comma-separated)."""
    sku_list = [s.strip() for s in skus.split(",") if s.strip()]
    products = db.query(Product).filter(Product.sku.in_(sku_list)).all()
    return [
        {
            "sku": p.sku,
            "stock": p.stock,
            "sold": p.sold,
            "is_active": p.is_active,
        }
        for p in products
    ]


@router.post("/sync", response_model=InventorySyncResponse, dependencies=[Depends(verify_api_key)])
def sync_inventory(payload: InventorySyncRequest, db: Session = Depends(get_db)):
    """Bulk sync inventory from external warehouse software (authenticated)."""
    service = InventoryService(db)
    updated, not_found = service.bulk_sync(payload.items)
    db.commit()
    return InventorySyncResponse(
        success=True,
        updated_count=updated,
        not_found_skus=not_found,
    )


@router.post("/deduct", dependencies=[Depends(verify_api_key)])
def deduct_inventory(payload: InventoryDeductRequest, db: Session = Depends(get_db)):
    """Deduct inventory when an order is confirmed (authenticated)."""
    service = InventoryService(db)
    updated, not_found = service.deduct_stock(payload.items, payload.order_id)
    db.commit()
    return {
        "success": True,
        "updated_count": updated,
        "not_found_skus": not_found,
    }


@router.post("/restore", dependencies=[Depends(verify_api_key)])
def restore_inventory(payload: InventoryRestoreRequest, db: Session = Depends(get_db)):
    """Restore inventory when an order is cancelled/returned (authenticated)."""
    service = InventoryService(db)
    updated, not_found = service.restore_stock(payload.items, payload.order_id, payload.reason)
    db.commit()
    return {
        "success": True,
        "updated_count": updated,
        "not_found_skus": not_found,
    }


@router.post("/webhooks/inventory-update")
async def inventory_webhook(request: Request, db: Session = Depends(get_db)):
    """Receive realtime inventory updates from external warehouse software."""
    signature = request.headers.get("X-Signature", "")
    body = await request.body()

    if not verify_webhook_signature(body, signature):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid webhook signature",
        )

    payload = InventoryWebhookPayload.model_validate_json(body)
    service = InventoryService(db)
    product = service.update_stock(
        payload.sku,
        payload.quantity,
        source="webhook",
    )
    if product is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="SKU not found")
    db.commit()

    return {"success": True, "sku": payload.sku, "stock": payload.quantity}
