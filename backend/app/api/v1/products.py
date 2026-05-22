"""Product endpoints (public catalog)."""

from typing import Optional

from fastapi import APIRouter, Depends, HTTPException, Query, status
from sqlalchemy import or_
from sqlalchemy.orm import Session, selectinload

from app.db.session import get_db
from app.models.product import Product, Category, ProductReview
from app.schemas.product import (
    ProductRead,
    ProductCreate,
    ProductUpdate,
    ProductListResponse,
    CategoryRead,
    CategoryCreate,
    ProductReviewSchema,
)

router = APIRouter(prefix="/products", tags=["products"])


@router.get("/categories", response_model=list[CategoryRead])
def list_categories(db: Session = Depends(get_db)):
    """List all active product categories."""
    return db.query(Category).filter(Category.is_active.is_(True)).order_by(Category.sort_order).all()


@router.post("/categories", response_model=CategoryRead, status_code=status.HTTP_201_CREATED)
def create_category(data: CategoryCreate, db: Session = Depends(get_db)):
    category = Category(**data.model_dump())
    db.add(category)
    db.commit()
    db.refresh(category)
    return category


@router.get("", response_model=ProductListResponse)
def list_products(
    q: Optional[str] = None,
    category: Optional[str] = None,
    is_flash_sale: Optional[bool] = None,
    sort: str = Query("popular", description="popular | newest | best_selling | price_asc | price_desc"),
    page: int = Query(1, ge=1),
    page_size: int = Query(20, ge=1, le=100),
    db: Session = Depends(get_db),
):
    """List products with filtering, search, and sorting."""
    query = db.query(Product).options(selectinload(Product.variants)).filter(Product.is_active.is_(True))

    if q:
        query = query.filter(
            or_(
                Product.name.ilike(f"%{q}%"),
                Product.description.ilike(f"%{q}%"),
                Product.sku.ilike(f"%{q}%"),
            )
        )

    if category:
        query = query.join(Category).filter(Category.slug == category)

    if is_flash_sale is not None:
        query = query.filter(Product.is_flash_sale.is_(is_flash_sale))

    if sort == "newest":
        query = query.order_by(Product.created_at.desc())
    elif sort == "best_selling":
        query = query.order_by(Product.shopee_sold.desc())
    elif sort == "price_asc":
        query = query.order_by(Product.price.asc())
    elif sort == "price_desc":
        query = query.order_by(Product.price.desc())
    else:
        query = query.order_by(Product.sold.desc(), Product.shopee_sold.desc())

    total = query.count()
    items = query.offset((page - 1) * page_size).limit(page_size).all()

    return {
        "items": items,
        "total": total,
        "page": page,
        "page_size": page_size,
    }


@router.get("/{slug}", response_model=ProductRead)
def get_product(slug: str, db: Session = Depends(get_db)):
    product = (
        db.query(Product)
        .options(selectinload(Product.variants))
        .filter(Product.slug == slug, Product.is_active.is_(True))
        .first()
    )
    if not product:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Product not found")
    return product


@router.post("", response_model=ProductRead, status_code=status.HTTP_201_CREATED)
def create_product(data: ProductCreate, db: Session = Depends(get_db)):
    payload = data.model_dump(exclude={"variants"})
    payload["discount"] = (
        int((1 - data.price / data.original_price) * 100) if data.original_price > 0 else 0
    )
    product = Product(**payload)
    db.add(product)
    db.flush()

    from app.models.product import ProductVariant

    for variant in data.variants:
        db.add(ProductVariant(product_id=product.id, **variant.model_dump(exclude={"id"})))

    db.commit()
    db.refresh(product)
    return product


@router.patch("/{product_id}", response_model=ProductRead)
def update_product(product_id: int, data: ProductUpdate, db: Session = Depends(get_db)):
    product = db.get(Product, product_id)
    if not product:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Product not found")

    update_data = data.model_dump(exclude_unset=True)
    for key, value in update_data.items():
        setattr(product, key, value)

    if product.original_price > 0:
        product.discount = int((1 - product.price / product.original_price) * 100)

    db.commit()
    db.refresh(product)
    return product


@router.get("/{product_id}/reviews", response_model=list[ProductReviewSchema])
def list_reviews(product_id: int, db: Session = Depends(get_db)):
    return (
        db.query(ProductReview)
        .filter(ProductReview.product_id == product_id)
        .order_by(ProductReview.created_at.desc())
        .limit(50)
        .all()
    )
