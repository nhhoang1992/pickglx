"""MoMo AIO (All-In-One) payment gateway integration.

Docs: https://developers.momo.vn/v3/docs/payment/api/payment-method/
Endpoints:
    Sandbox: https://test-payment.momo.vn/v2/gateway/api/create
    Prod:    https://payment.momo.vn/v2/gateway/api/create

Flow:
    1. POST /v2/gateway/api/create → MoMo returns a payUrl (or QR code / deeplink)
    2. User pays on MoMo app or browser
    3. MoMo IPNs your callback URL (POST JSON) with signed result
    4. Verify signature, mark order paid
"""

from __future__ import annotations

import hashlib
import hmac
import json
import uuid
from typing import Any

import httpx

from app.core.config import settings


class MoMoProvider:
    name = "momo"

    def __init__(self) -> None:
        self.partner_code = settings.MOMO_PARTNER_CODE
        self.access_key = settings.MOMO_ACCESS_KEY
        self.secret_key = settings.MOMO_SECRET_KEY
        self.endpoint = settings.MOMO_ENDPOINT
        self.return_url = settings.MOMO_RETURN_URL
        self.notify_url = settings.MOMO_NOTIFY_URL

    def _sign(self, raw: str) -> str:
        return hmac.new(self.secret_key.encode(), raw.encode(), hashlib.sha256).hexdigest()

    async def create_payment(
        self,
        order_number: str,
        amount: int,
        extra: dict[str, Any] | None = None,
    ) -> dict[str, Any]:
        """Create a MoMo payment session. Returns payUrl for redirect."""
        if not self.partner_code or not self.access_key or not self.secret_key:
            return {
                "provider": self.name,
                "order_number": order_number,
                "amount": amount,
                "payment_url": None,
                "payment_status": "config_missing",
                "message": "MOMO_* credentials chưa được cấu hình. Đang chạy ở chế độ dry-run.",
            }

        request_id = str(uuid.uuid4())
        order_info = (extra or {}).get("order_info") or f"Thanh toán đơn hàng {order_number}"
        extra_data = ""
        request_type = "captureWallet"

        raw_signature = (
            f"accessKey={self.access_key}"
            f"&amount={amount}"
            f"&extraData={extra_data}"
            f"&ipnUrl={self.notify_url}"
            f"&orderId={order_number}"
            f"&orderInfo={order_info}"
            f"&partnerCode={self.partner_code}"
            f"&redirectUrl={self.return_url}"
            f"&requestId={request_id}"
            f"&requestType={request_type}"
        )
        signature = self._sign(raw_signature)

        payload = {
            "partnerCode": self.partner_code,
            "partnerName": "Phu Kien Hat De",
            "storeId": "PhuKienHatDeStore",
            "requestId": request_id,
            "amount": amount,
            "orderId": order_number,
            "orderInfo": order_info,
            "redirectUrl": self.return_url,
            "ipnUrl": self.notify_url,
            "lang": "vi",
            "extraData": extra_data,
            "requestType": request_type,
            "signature": signature,
        }

        async with httpx.AsyncClient(timeout=15.0) as client:
            resp = await client.post(
                self.endpoint,
                content=json.dumps(payload),
                headers={"Content-Type": "application/json"},
            )
            data = resp.json()

        return {
            "provider": self.name,
            "order_number": order_number,
            "amount": amount,
            "payment_url": data.get("payUrl"),
            "qr_code_url": data.get("qrCodeUrl"),
            "deeplink": data.get("deeplink"),
            "payment_status": "pending" if data.get("resultCode") == 0 else "failed",
            "result_code": data.get("resultCode"),
            "message": data.get("message"),
        }

    def verify_callback(self, payload: dict[str, Any], signature: str | None = None) -> bool:
        """Verify a MoMo IPN callback signature. Returns True if valid."""
        sig = signature or payload.get("signature")
        if not sig:
            return False

        raw = (
            f"accessKey={self.access_key}"
            f"&amount={payload.get('amount')}"
            f"&extraData={payload.get('extraData', '')}"
            f"&message={payload.get('message')}"
            f"&orderId={payload.get('orderId')}"
            f"&orderInfo={payload.get('orderInfo')}"
            f"&orderType={payload.get('orderType')}"
            f"&partnerCode={payload.get('partnerCode')}"
            f"&payType={payload.get('payType')}"
            f"&requestId={payload.get('requestId')}"
            f"&responseTime={payload.get('responseTime')}"
            f"&resultCode={payload.get('resultCode')}"
            f"&transId={payload.get('transId')}"
        )
        expected = self._sign(raw)
        return hmac.compare_digest(expected, sig)
