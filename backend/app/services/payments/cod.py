"""COD (Cash on Delivery) — no external gateway, mark order awaiting delivery."""

from typing import Any


class CODProvider:
    name = "cod"

    async def create_payment(self, order_number: str, amount: int, extra: dict | None = None) -> dict[str, Any]:
        return {
            "provider": self.name,
            "order_number": order_number,
            "amount": amount,
            "payment_url": None,
            "payment_status": "pending",
            "message": "Đơn hàng đã ghi nhận. Quý khách vui lòng thanh toán cho shipper khi nhận hàng.",
        }

    def verify_callback(self, payload: dict, signature: str | None = None) -> bool:
        return False
