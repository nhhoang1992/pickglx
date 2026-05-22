from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    """Application settings."""

    model_config = SettingsConfigDict(
        env_file=".env",
        env_file_encoding="utf-8",
        case_sensitive=False,
        extra="ignore",
    )

    APP_NAME: str = "Phụ Kiện Hạt Dẻ API"
    APP_VERSION: str = "1.0.0"
    DEBUG: bool = True

    DATABASE_URL: str = "sqlite:///./app.db"

    SECRET_KEY: str = "change-me-in-production"
    ALGORITHM: str = "HS256"
    ACCESS_TOKEN_EXPIRE_MINUTES: int = 60 * 24 * 7

    INVENTORY_API_KEY: str = "demo-inventory-api-key"
    INVENTORY_WEBHOOK_SECRET: str = "demo-webhook-secret"
    INVENTORY_EXTERNAL_URL: str = "https://example.com/inventory/api"

    SHOPEE_SHOP_USERNAME: str = "phukienhatde"
    SHOPEE_SHOP_ID: str = ""
    SHOPEE_SYNC_ENABLED: bool = True

    # MoMo AIO
    MOMO_PARTNER_CODE: str = ""
    MOMO_ACCESS_KEY: str = ""
    MOMO_SECRET_KEY: str = ""
    MOMO_ENDPOINT: str = "https://test-payment.momo.vn/v2/gateway/api/create"
    MOMO_RETURN_URL: str = "https://phukienhatde.vn/checkout/payment-result"
    MOMO_NOTIFY_URL: str = "https://phukienhatde.vn/api/v1/payments/momo/ipn"

    # ZaloPay v2
    ZALOPAY_APP_ID: str = ""
    ZALOPAY_KEY1: str = ""
    ZALOPAY_KEY2: str = ""
    ZALOPAY_ENDPOINT: str = "https://sb-openapi.zalopay.vn/v2/create"
    ZALOPAY_CALLBACK_URL: str = "https://phukienhatde.vn/api/v1/payments/zalopay/callback"
    ZALOPAY_REDIRECT_URL: str = "https://phukienhatde.vn/checkout/payment-result"

    REDIS_URL: str = "redis://localhost:6379/0"

    BACKEND_CORS_ORIGINS: list[str] = [
        "http://localhost:3000",
        "http://127.0.0.1:3000",
    ]


settings = Settings()
