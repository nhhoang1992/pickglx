"""Shopee sync endpoints — sync sold count and 5-star reviews from Shopee."""

from fastapi import APIRouter, BackgroundTasks, Depends, HTTPException, status
from sqlalchemy.orm import Session

from app.core.config import settings
from app.db.session import get_db
from app.schemas.inventory import ShopeeSyncRequest, ShopeeSyncResponse
from app.services.shopee_sync import ShopeeSyncService

router = APIRouter(prefix="/shopee", tags=["shopee-sync"])


@router.post("/sync", response_model=ShopeeSyncResponse)
async def sync_from_shopee(
    payload: ShopeeSyncRequest | None = None,
    db: Session = Depends(get_db),
):
    """Sync product metrics (sold count, ratings, 5-star reviews) from Shopee."""
    if not settings.SHOPEE_SYNC_ENABLED:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Shopee sync is disabled",
        )

    shop_username = (payload.shop_username if payload else None) or settings.SHOPEE_SHOP_USERNAME
    skus = payload.product_skus if payload else None

    service = ShopeeSyncService(db)
    synced, failed = await service.sync_shop(shop_username, skus)
    db.commit()

    return ShopeeSyncResponse(
        success=True,
        synced_count=len(synced),
        failed_skus=failed,
        metrics=synced,
    )


@router.post("/sync/{sku}")
async def sync_single_product(sku: str, db: Session = Depends(get_db)):
    """Sync a single product's Shopee metrics."""
    from app.models.product import Product

    product = db.query(Product).filter(Product.sku == sku).first()
    if not product:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Product not found")
    if not product.shopee_product_id:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Product has no Shopee mapping (shopee_product_id is missing)",
        )

    service = ShopeeSyncService(db)
    shop_info = await service.fetch_shop_info(settings.SHOPEE_SHOP_USERNAME)
    if not shop_info:
        raise HTTPException(
            status_code=status.HTTP_502_BAD_GATEWAY,
            detail="Cannot fetch shop info from Shopee",
        )

    metrics = await service.sync_product(product, str(shop_info["shopid"]))
    db.commit()

    if not metrics:
        raise HTTPException(
            status_code=status.HTTP_502_BAD_GATEWAY,
            detail="Cannot fetch product metrics from Shopee",
        )

    return metrics
