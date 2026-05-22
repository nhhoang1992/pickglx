"""Security utilities: API key validation and webhook signature verification."""

import hashlib
import hmac
import time
from typing import Optional

from fastapi import Header, HTTPException, status

from app.core.config import settings


async def verify_api_key(
    x_api_key: Optional[str] = Header(None),
    x_timestamp: Optional[str] = Header(None),
    x_signature: Optional[str] = Header(None),
) -> bool:
    """Verify inventory/external API requests using HMAC signature."""
    if not x_api_key or x_api_key != settings.INVENTORY_API_KEY:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid API key",
        )

    if not x_timestamp:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Missing timestamp",
        )

    try:
        request_time = int(x_timestamp)
    except ValueError:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid timestamp format",
        )

    now = int(time.time())
    if abs(now - request_time) > 300:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Request timestamp expired",
        )

    return True


def verify_webhook_signature(payload: bytes, signature: str) -> bool:
    """Verify HMAC-SHA256 signature for incoming webhooks."""
    expected = hmac.new(
        settings.INVENTORY_WEBHOOK_SECRET.encode(),
        payload,
        hashlib.sha256,
    ).hexdigest()
    return hmac.compare_digest(expected, signature)


def generate_signature(payload: bytes) -> str:
    """Generate HMAC-SHA256 signature for outgoing webhooks."""
    return hmac.new(
        settings.INVENTORY_WEBHOOK_SECRET.encode(),
        payload,
        hashlib.sha256,
    ).hexdigest()
