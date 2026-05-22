# Tài liệu tích hợp Phần mềm Quản lý Kho ↔ Phụ Kiện Hạt Dẻ

Tài liệu này mô tả contract REST API + Webhook giữa **website Phụ Kiện Hạt Dẻ** (sau đây gọi là *website*) và **phần mềm quản lý kho tự viết của bạn** (sau đây gọi là *kho*).

Mục tiêu:
1. Kho **đẩy tồn kho** cho website (đồng bộ định kỳ + realtime qua webhook khi có thay đổi)
2. Website **gửi đơn hàng mới** vào kho ngay khi khách xác nhận
3. Website **trừ kho** khi đơn được confirm và **hoàn kho** khi hủy/trả

```
            ┌─────────────────────────┐
            │     Phần mềm Kho        │
            │     (tự viết)           │
            └────┬────────────────┬───┘
                 │                │
       Webhook ──▼                ▼── HTTP poll/push
       inventory                  (sync stock)
       update                     POST /inventory/sync
       ───────────►               POST /inventory/deduct
                                  POST /inventory/restore
            ┌─────────────────────────┐
            │   Website Backend       │
            │  (api.phukienhatde.vn)  │
            └────┬────────────────────┘
                 │
                 ▼ Export new order
              POST {KHO}/orders   (signed)
```

---

## 1. Xác thực

Tất cả request **giữa 2 hệ thống** đều yêu cầu 3 header:

| Header | Giá trị | Mô tả |
|--------|---------|-------|
| `X-API-Key` | `INVENTORY_API_KEY` | API key dùng chung 2 phía |
| `X-Timestamp` | Unix timestamp giây hiện tại | Chống replay (chấp nhận lệch ±5 phút) |
| `X-Signature` | `HMAC-SHA256(body, INVENTORY_WEBHOOK_SECRET)` | Chữ ký xác minh nội dung |

Ví dụ ký request bằng Python:

```python
import hmac, hashlib, time, json, requests

API_KEY    = "your-api-key"
SECRET     = "your-webhook-secret"
BASE_URL   = "https://api.phukienhatde.vn/api/v1"

body = json.dumps({"sku":"SP-1001","quantity":50}).encode()
signature = hmac.new(SECRET.encode(), body, hashlib.sha256).hexdigest()

r = requests.post(
    f"{BASE_URL}/inventory/webhooks/inventory-update",
    data=body,
    headers={
        "Content-Type": "application/json",
        "X-API-Key": API_KEY,
        "X-Timestamp": str(int(time.time())),
        "X-Signature": signature,
    },
)
```

---

## 2. Kho → Website

### 2.1. Webhook cập nhật tồn kho realtime

Gọi mỗi khi tồn kho của 1 SKU thay đổi trong hệ thống kho.

```
POST /api/v1/inventory/webhooks/inventory-update
Content-Type: application/json
X-Signature: <hmac>

{
  "sku": "SP-1001",
  "quantity": 50,
  "warehouse": "HCM-Q1",
  "updated_at": "2025-05-22T10:00:00Z"
}
```

**Response 200:**
```json
{"success": true, "sku": "SP-1001", "stock": 50}
```

### 2.2. Đồng bộ hàng loạt định kỳ

Khuyến nghị chạy mỗi 5–15 phút, hoặc khi vừa khởi động kho.

```
POST /api/v1/inventory/sync
Headers: X-API-Key, X-Timestamp, X-Signature

{
  "items": [
    {"sku": "SP-1001", "quantity": 50},
    {"sku": "SP-1002", "quantity": 120},
    {"sku": "SP-1003", "quantity": 0}
  ]
}
```

**Response 200:**
```json
{
  "success": true,
  "updated_count": 3,
  "not_found_skus": []
}
```

### 2.3. Trừ kho thủ công

Dùng khi kho có thao tác giảm kho ngoài đơn hàng (ví dụ hàng hỏng, hàng mất).

```
POST /api/v1/inventory/deduct
{
  "order_id": "ADJUST-2025-001",
  "items": [{"sku": "SP-1001", "quantity": 2}]
}
```

### 2.4. Hoàn kho

```
POST /api/v1/inventory/restore
{
  "order_id": "ORD-20250522-ABC123",
  "items": [{"sku": "SP-1001", "quantity": 2}],
  "reason": "customer_returned"
}
```

---

## 3. Website → Kho

Website sẽ tự động export đơn hàng tới endpoint kho ngay khi đơn chuyển sang trạng thái `confirmed`.

Cấu hình endpoint trong file `.env` của website:

```
INVENTORY_EXTERNAL_URL=https://kho.phukienhatde.vn/api
```

### 3.1. Đơn hàng mới (Order Export)

Website sẽ gọi:

```
POST {INVENTORY_EXTERNAL_URL}/orders
Headers: X-API-Key, X-Signature

{
  "order_id": "ORD-20250522-ABC123",
  "customer_name": "Nguyễn Văn A",
  "customer_phone": "0901234567",
  "shipping_address": "123 Đường ABC, Q.1, TP.HCM",
  "items": [
    {
      "sku": "SP-1001",
      "name": "Kính cường lực iPhone 15 Pro Max",
      "variant": "Trong suốt",
      "quantity": 2,
      "price": 45000
    }
  ],
  "subtotal": 90000,
  "shipping_fee": 30000,
  "total": 120000,
  "payment_method": "cod",
  "status": "confirmed",
  "created_at": "2025-05-22T10:30:00Z"
}
```

Kho cần response 200 với body bất kỳ. Nếu trả 4xx/5xx, website sẽ flag `inventory_exported=0` để có thể retry sau.

### 3.2. (Tùy chọn) Cập nhật trạng thái vận chuyển

Khi kho ship hàng, có thể PATCH lại trạng thái đơn:

```
PATCH https://api.phukienhatde.vn/api/v1/orders/ORD-20250522-ABC123/status
{
  "status": "shipping",
  "note": "Đã giao GHN, mã vận đơn: 1234567"
}
```

---

## 4. Trạng thái đơn hàng & ảnh hưởng tới kho

| Trạng thái | Ý nghĩa | Tác động kho |
|------------|---------|--------------|
| `pending` | Mới tạo, chờ xác nhận | Chưa trừ kho |
| `confirmed` | Đã xác nhận | **Tự động trừ kho** + export sang kho |
| `shipping` | Đang giao | Không đổi |
| `delivered` | Đã giao thành công | Không đổi |
| `cancelled` | Khách/shop hủy | **Tự động hoàn kho** |
| `returned` | Khách trả hàng | **Tự động hoàn kho** |

---

## 5. Bảng lỗi

| HTTP | Mã lỗi | Ý nghĩa |
|------|--------|---------|
| 401 | `Invalid API key` | API key sai/thiếu |
| 401 | `Request timestamp expired` | Timestamp lệch quá 5 phút |
| 401 | `Invalid webhook signature` | HMAC signature sai |
| 404 | `SKU not found` | SKU không tồn tại trong DB website |
| 400 | `Insufficient stock` | Đơn hàng vượt tồn (chỉ ở create order) |

---

## 6. Quy ước SKU

- SKU là **identifier duy nhất** giữa kho và website
- Format đề xuất: `SP-<số tự tăng>` hoặc theo schema riêng của kho
- Khi tạo sản phẩm trên website, **phải** dùng đúng SKU đã có trong kho
- Mỗi biến thể (màu/kích thước) → 1 SKU riêng

---

## 7. Test endpoints

Sau khi cấu hình `INVENTORY_API_KEY` và `INVENTORY_WEBHOOK_SECRET` ở 2 phía, test qua Postman collection: [`warehouse-postman.json`](./warehouse-postman.json)

Hoặc test bằng curl:

```bash
# Test query tồn kho
curl https://api.phukienhatde.vn/api/v1/inventory/products/SP-1001

# Test webhook
TS=$(date +%s)
BODY='{"sku":"SP-1001","quantity":99,"warehouse":"HCM-Q1","updated_at":"2025-05-22T10:00:00Z"}'
SIG=$(echo -n "$BODY" | openssl dgst -sha256 -hmac "your-webhook-secret" | cut -d' ' -f2)

curl -X POST https://api.phukienhatde.vn/api/v1/inventory/webhooks/inventory-update \
  -H "Content-Type: application/json" \
  -H "X-Signature: $SIG" \
  -d "$BODY"
```
