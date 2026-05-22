"""ZaloPay v2 payment gateway integration.

Docs: https://docs.zalopay.vn/v2/general/overview.html
Endpoints:
    Sandbox: https://sb-openapi.zalopay.vn/v2/create
    Prod:    https://openapi.zalopay.vn/v2/create

Flow:
    1. POST /v2/create → ZaloPay returns order_url for redirect
    2. User pays on ZaloPay app or browser
    3. ZaloPay POSTs callback to your callback_url with signed data
    4. Verify mac, mark order paid
"""

from __future__ import annotations

import hashlib
import hmac
import json
import time
import uuid
from datetime import datetime
from typing import Any

import httpx

from app.core.config import settings


class ZaloPayProvider:
    name = "zalopay"

    def __init__(self) -> None:
        self.app_id = settings.ZALOPAY_APP_ID
        self.key1 = settings.ZALOPAY_KEY1
        self.key2 = settings.ZALOPAY_KEY2
        self.endpoint = settings.ZALOPAY_ENDPOINT
        self.callback_url = settings.ZALOPAY_CALLBACK_URL
        self.redirect_url = settings.ZALOPAY_REDIRECT_URL

    def _sign(self, raw: str, key: str) -> str:
        return hmac.new(key.encode(), raw.encode(), hashlib.sha256).hexdigest()

    async def create_payment(
        self,
        order_number: str,
        amount: int,
        extra: dict[str, Any] | None = None,
    ) -> dict[str, Any]:
        """Create a ZaloPay order. Returns order_url for redirect."""
        if not self.app_id or not self.key1:
            return {
                "provider": self.name,
                "order_number": order_number,
                "amount": amount,
                "payment_url": None,
                "payment_status": "config_missing",
                "message": "ZALOPAY_* credentials chưa được cấu hình. Đang chạy ở chế độ dry-run.",
            }

        # ZaloPay app_trans_id format: yyMMdd_<unique>
        app_trans_id = f"{datetime.now().strftime('%y%m%d')}_{uuid.uuid4().hex[:12]}"
        app_user = (extra or {}).get("customer_phone") or "guest"
        app_time = int(time.time() * 1000)
        item = json.dumps((extra or {}).get("items") or [])
        embed_data = json.dumps({
            "redirecturl": self.redirect_url,
            "order_number": order_number,
        })

        # mac = sha256(app_id|app_trans_id|app_user|amount|app_time|embed_data|item, key1)
        raw_mac = (
            f"{self.app_id}|{app_trans_id}|{app_user}|{amount}|"
            f"{app_time}|{embed_data}|{item}"
        )
        mac = self._sign(raw_mac, self.key1)

        payload = {
            "app_id": self.app_id,
            "app_user": app_user,
            "app_trans_id": app_trans_id,
            "app_time": app_time,
            "amount": amount,
            "item": item,
            "embed_data": embed_data,
            "description": f"Thanh toán đơn {order_number} - Phụ Kiện Hạt Dẻ",
            "bank_code": "",
            "callback_url": self.callback_url,
            "mac": mac,
        }

        async with httpx.AsyncClient(timeout=15.0) as client:
            resp = await client.post(self.endpoint, data=payload)
            data = resp.json()

        return {
            "provider": self.name,
            "order_number": order_number,
            "app_trans_id": app_trans_id,
            "amount": amount,
            "payment_url": data.get("order_url"),
            "zp_trans_token": data.get("zp_trans_token"),
            "order_token": data.get("order_token"),
            "payment_status": "pending" if data.get("return_code") == 1 else "failed",
            "return_code": data.get("return_code"),
            "message": data.get("return_message") or data.get("sub_return_message"),
        }

    def verify_callback(self, payload: dict[str, Any], signature: str | None = None) -> bool:
        """Verify a ZaloPay callback. Body is `{data: '...', mac: '...', type: ...}`."""
        data = payload.get("data") or ""
        mac = signature or payload.get("mac") or ""
        if not data or not mac:
            return False
        expected = self._sign(data, self.key2)
        return hmac.compare_digest(expected, mac)
