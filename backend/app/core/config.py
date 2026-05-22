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

    REDIS_URL: str = "redis://localhost:6379/0"

    BACKEND_CORS_ORIGINS: list[str] = [
        "http://localhost:3000",
        "http://127.0.0.1:3000",
    ]


settings = Settings()
