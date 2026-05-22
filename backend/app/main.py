"""FastAPI application entrypoint."""

from contextlib import asynccontextmanager

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from app.api.v1 import inventory, orders, payments, products, shopee
from app.core.config import settings
from app.db.session import Base, engine

# Ensure SQLAlchemy registers all models before create_all
from app.models import order as _order_models  # noqa: F401
from app.models import product as _product_models  # noqa: F401


@asynccontextmanager
async def lifespan(app: FastAPI):
    """Create database tables on startup (dev only — use Alembic in prod)."""
    Base.metadata.create_all(bind=engine)
    yield


app = FastAPI(
    title=settings.APP_NAME,
    version=settings.APP_VERSION,
    description=(
        "API cho website bán phụ kiện điện thoại Phụ Kiện Hạt Dẻ. "
        "Hỗ trợ đồng bộ tồn kho 2 chiều với phần mềm quản lý kho, "
        "đồng bộ lượt bán và đánh giá 5 sao từ Shopee."
    ),
    lifespan=lifespan,
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.BACKEND_CORS_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


@app.get("/", tags=["health"])
def root():
    return {
        "app": settings.APP_NAME,
        "version": settings.APP_VERSION,
        "status": "ok",
    }


@app.get("/health", tags=["health"])
def health_check():
    return {"status": "healthy"}


app.include_router(products.router, prefix="/api/v1")
app.include_router(orders.router, prefix="/api/v1")
app.include_router(inventory.router, prefix="/api/v1")
app.include_router(payments.router, prefix="/api/v1")
app.include_router(shopee.router, prefix="/api/v1")
