# Pickglx — Website bán phụ kiện điện thoại

E-commerce platform chuyên bán phụ kiện điện thoại (kính cường lực, ốp lưng, sạc, cáp, tai nghe...) với giao diện tương tự Shopee, tông màu cam, ưu tiên mobile-first.

## Tính năng chính

- **Giao diện giống Shopee** — orange theme `#EE4D2D`, layout quen thuộc với người dùng Việt Nam
- **Mobile-first responsive** — bottom navigation, sticky checkout bar, tối ưu cho điện thoại
- **Flash Sale** countdown realtime, sản phẩm gợi ý, danh mục
- **Giỏ hàng & Checkout** với COD, MoMo, VNPay, chuyển khoản
- **Đồng bộ tồn kho 2 chiều** với phần mềm quản lý kho qua REST API + Webhook
- **Đồng bộ Shopee** — tự động kéo lượt bán và đánh giá 5★ từ shop Shopee

## Cấu trúc dự án

```
pickglx/
├── frontend/        # Next.js 16 + TypeScript + Tailwind CSS (giao diện Shopee-like)
└── backend/         # FastAPI + SQLAlchemy + Pydantic (API & tích hợp)
```

## Khởi chạy

### Frontend (Next.js)

```bash
cd frontend
npm install
npm run dev
# http://localhost:3000
```

### Backend (FastAPI)

```bash
cd backend
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
python seed_data.py
uvicorn app.main:app --reload
# http://localhost:8000  (docs: http://localhost:8000/docs)
```

## Kiến trúc

```
┌─────────────────┐         ┌─────────────────┐
│  Frontend       │◄───────►│  Backend        │
│  (Next.js)      │  REST   │  (FastAPI)      │
│  Mobile-first   │   API   │                 │
└─────────────────┘         └────────┬────────┘
                                     │
                  ┌──────────────────┼──────────────────┐
                  │                  │                  │
            ┌─────▼─────┐     ┌──────▼──────┐    ┌──────▼──────┐
            │ Phần mềm  │     │   Shopee    │    │ PostgreSQL  │
            │ quản lý   │     │  (sync sold │    │   + Redis   │
            │   kho     │     │  & 5★ rev.) │    │             │
            └───────────┘     └─────────────┘    └─────────────┘
```

Xem [plan_website_phukien.md](./plan_website_phukien.md) (đính kèm) để biết kế hoạch chi tiết.

## Tech stack

| Layer | Tech |
|-------|------|
| Frontend | Next.js 16, TypeScript, Tailwind CSS, Zustand, Lucide icons |
| Backend | FastAPI, SQLAlchemy, Pydantic v2, httpx |
| Database | SQLite (dev) / PostgreSQL (prod) |
| Cache | Redis |
| Tích hợp | REST API + Webhook (HMAC-SHA256) |
