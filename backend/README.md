# Phụ Kiện Hạt Dẻ - Backend API

FastAPI backend cho website bán phụ kiện điện thoại. Cung cấp API cho:

- Quản lý sản phẩm, danh mục, đánh giá
- Quản lý đơn hàng & checkout
- Đồng bộ tồn kho 2 chiều với phần mềm quản lý kho ngoài
- Đồng bộ lượt bán và đánh giá 5 sao từ Shopee
- Webhook nhận realtime cập nhật từ kho

## Cài đặt

```bash
cd backend
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
cp .env.example .env  # chỉnh sửa cấu hình
python seed_data.py   # tạo dữ liệu mẫu
uvicorn app.main:app --reload
```

Mở http://localhost:8000/docs để xem Swagger UI.

## Endpoints chính

### Public (cho frontend)

- `GET /api/v1/products` — danh sách sản phẩm (filter, sort, search, paginate)
- `GET /api/v1/products/{slug}` — chi tiết sản phẩm
- `GET /api/v1/products/categories` — danh sách danh mục
- `POST /api/v1/orders` — tạo đơn hàng
- `GET /api/v1/orders/{order_number}` — xem chi tiết đơn

### Admin

- `POST /api/v1/products` — tạo sản phẩm
- `PATCH /api/v1/products/{id}` — sửa sản phẩm
- `PATCH /api/v1/orders/{order_number}/status` — đổi trạng thái đơn (tự động trừ/hoàn kho)

### Tích hợp phần mềm quản lý kho (yêu cầu API Key + HMAC)

- `POST /api/v1/inventory/sync` — đồng bộ hàng loạt tồn kho
- `POST /api/v1/inventory/deduct` — trừ kho theo đơn
- `POST /api/v1/inventory/restore` — hoàn kho khi hủy/trả
- `POST /api/v1/inventory/webhooks/inventory-update` — webhook nhận cập nhật từ kho
- `GET /api/v1/inventory/products/{sku}` — tồn kho realtime 1 sản phẩm
- `GET /api/v1/inventory/products?skus=A,B,C` — tồn kho nhiều sản phẩm

### Đồng bộ từ Shopee

- `POST /api/v1/shopee/sync` — đồng bộ toàn bộ shop
- `POST /api/v1/shopee/sync/{sku}` — đồng bộ 1 sản phẩm
  (tự động cập nhật lượt bán, rating, và lấy review 5* về website)

## Xác thực API tích hợp kho

Tất cả endpoint `/api/v1/inventory/sync|deduct|restore` đều yêu cầu headers:

```
X-API-Key: <INVENTORY_API_KEY>
X-Timestamp: <unix_timestamp>
X-Signature: <hmac_sha256(body, INVENTORY_WEBHOOK_SECRET)>
```

Webhook tới `/inventory-update` cần header `X-Signature` (HMAC-SHA256 của body).

## Cấu trúc

```
backend/
├── app/
│   ├── main.py                 # FastAPI entrypoint
│   ├── core/                   # config, security
│   ├── db/                     # database session
│   ├── models/                 # SQLAlchemy models
│   ├── schemas/                # Pydantic schemas
│   ├── api/v1/                 # API endpoints
│   │   ├── products.py
│   │   ├── orders.py
│   │   ├── inventory.py
│   │   └── shopee.py
│   └── services/               # business logic
│       ├── inventory_service.py
│       └── shopee_sync.py
├── seed_data.py
├── requirements.txt
└── .env.example
```
