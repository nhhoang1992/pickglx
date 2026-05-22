# KẾ HOẠCH XÂY DỰNG WEBSITE BÁN PHỤ KIỆN ĐIỆN THOẠI

## 📋 Tổng quan dự án

**Tên dự án:** Website bán phụ kiện điện thoại - Phụ Kiện Hạt Dẻ  
**Mục tiêu:** Xây dựng website thương mại điện tử chuyên bán phụ kiện điện thoại, giao diện tương tự Shopee.vn, tông màu cam hiện đại, tích hợp API quản lý kho hàng.  
**Sản phẩm chính:** Kính cường lực, Ốp lưng, Sạc, Cáp, Tai nghe, Phụ kiện điện thoại di động

---

## 1. KIẾN TRÚC HỆ THỐNG

### 1.1. Kiến trúc tổng thể

```
┌─────────────────────────────────────────────────────────────────┐
│                        FRONTEND (Next.js)                        │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────────┐│
│  │ Trang chủ│  │ Sản phẩm │  │ Giỏ hàng │  │ Admin Dashboard  ││
│  └──────────┘  └──────────┘  └──────────┘  └──────────────────┘│
└────────────────────────────┬────────────────────────────────────┘
                             │ REST API / WebSocket
┌────────────────────────────┴────────────────────────────────────┐
│                        BACKEND (FastAPI)                          │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────────┐│
│  │ Auth     │  │ Products │  │ Orders   │  │ Inventory API    ││
│  │ Service  │  │ Service  │  │ Service  │  │ (External Sync)  ││
│  └──────────┘  └──────────┘  └──────────┘  └──────────────────┘│
└────────────────────────────┬────────────────────────────────────┘
                             │
┌────────────────────────────┴────────────────────────────────────┐
│                        DATABASE LAYER                             │
│  ┌──────────────┐  ┌──────────────┐  ┌────────────────────────┐│
│  │ PostgreSQL   │  │ Redis Cache  │  │ File Storage (S3/Min.) ││
│  │ (Main DB)    │  │ (Sessions,   │  │ (Product Images)       ││
│  │              │  │  Cart, Cache) │  │                        ││
│  └──────────────┘  └──────────────┘  └────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
                             │
                    ┌────────┴────────┐
                    │  EXTERNAL APIs   │
                    │ ┌──────────────┐ │
                    │ │ Phần mềm    │ │
                    │ │ quản lý kho  │ │
                    │ └──────────────┘ │
                    │ ┌──────────────┐ │
                    │ │ Payment      │ │
                    │ │ Gateway      │ │
                    │ └──────────────┘ │
                    │ ┌──────────────┐ │
                    │ │ Shipping     │ │
                    │ │ Service      │ │
                    │ └──────────────┘ │
                    └─────────────────┘
```

### 1.2. Công nghệ đề xuất

| Thành phần | Công nghệ | Lý do chọn |
|-----------|-----------|-------------|
| **Frontend** | Next.js 14 (React) + TypeScript | SEO tốt (SSR), hiệu suất cao, ecosystem phong phú |
| **UI Library** | Tailwind CSS + Shadcn/ui | Tùy biến cao, phù hợp thiết kế Shopee-like |
| **Backend** | FastAPI (Python) | Hiệu suất cao, async, tài liệu API tự động (Swagger) |
| **Database** | PostgreSQL | Ổn định, hỗ trợ JSON, full-text search |
| **Cache** | Redis | Session management, giỏ hàng tạm, cache sản phẩm |
| **File Storage** | MinIO / AWS S3 | Lưu trữ hình ảnh sản phẩm |
| **Authentication** | JWT + OAuth2 | Bảo mật, stateless |
| **Deployment** | Docker + Docker Compose | Dễ deploy, nhất quán môi trường |
| **CI/CD** | GitHub Actions | Tự động test và deploy |

---

## 2. DANH SÁCH TÍNH NĂNG CHI TIẾT

### 2.1. Giao diện người dùng (Customer-facing)

#### A. Header & Navigation (Tương tự Shopee)
- [x] Header cố định với logo, thanh tìm kiếm, giỏ hàng, thông báo
- [x] Thanh danh mục sản phẩm (dropdown mega menu)
- [x] Nút đăng nhập/đăng ký
- [x] Icon giỏ hàng với badge số lượng sản phẩm
- [x] Responsive cho mobile/tablet/desktop

#### B. Trang chủ (Homepage)
- [x] Banner carousel (slider quảng cáo, khuyến mãi)
- [x] Danh mục sản phẩm nổi bật (icon + tên)
- [x] Flash Sale countdown timer
- [x] Sản phẩm bán chạy (Best Sellers)
- [x] Sản phẩm mới nhất
- [x] Sản phẩm gợi ý (recommendation grid - infinite scroll)
- [x] Footer với thông tin liên hệ, chính sách

#### C. Trang danh sách sản phẩm (Product Listing)
- [x] Grid/List view sản phẩm
- [x] Bộ lọc: Giá, Thương hiệu, Đánh giá, Tương thích (iPhone/Samsung/...)
- [x] Sắp xếp: Phổ biến, Mới nhất, Bán chạy, Giá thấp→cao, Giá cao→thấp
- [x] Phân trang / Infinite scroll
- [x] Hiển thị: Ảnh, Tên, Giá gốc, Giá sale, % giảm, Đánh giá sao, Đã bán

#### D. Trang chi tiết sản phẩm (Product Detail)
- [x] Gallery ảnh sản phẩm (zoom, multi-image)
- [x] Tên sản phẩm, giá, giá gốc, % giảm giá
- [x] Chọn biến thể: Màu sắc, Kích thước, Model điện thoại tương thích
- [x] Số lượng tồn kho (realtime từ phần mềm quản lý kho)
- [x] Nút "Thêm vào giỏ hàng" và "Mua ngay"
- [x] Mô tả sản phẩm (rich text/HTML)
- [x] Thông số kỹ thuật
- [x] Đánh giá & nhận xét từ khách hàng (sao + ảnh + bình luận)
- [x] Sản phẩm liên quan / Sản phẩm tương tự
- [x] Thông tin vận chuyển (ước tính phí ship, thời gian giao)

#### E. Giỏ hàng (Shopping Cart)
- [x] Thêm/xóa/cập nhật số lượng sản phẩm
- [x] Hiển thị tổng tiền, phí vận chuyển
- [x] Áp dụng mã giảm giá / voucher
- [x] Chọn tất cả / chọn từng sản phẩm để thanh toán
- [x] Lưu giỏ hàng (persistent - Redis)

#### F. Thanh toán (Checkout)
- [x] Chọn/thêm địa chỉ giao hàng
- [x] Chọn phương thức vận chuyển
- [x] Chọn phương thức thanh toán:
  - COD (Thanh toán khi nhận hàng)
  - Chuyển khoản ngân hàng
  - Ví điện tử (MoMo, ZaloPay, VNPay)
  - Thẻ tín dụng/ghi nợ
- [x] Ghi chú đơn hàng
- [x] Xác nhận đơn hàng
- [x] Trang thanh toán thành công

#### G. Tài khoản người dùng
- [x] Đăng ký / Đăng nhập (Email, SĐT, Google, Facebook)
- [x] Quản lý thông tin cá nhân
- [x] Quản lý địa chỉ giao hàng (nhiều địa chỉ)
- [x] Lịch sử đơn hàng & trạng thái
- [x] Danh sách yêu thích (Wishlist)
- [x] Đánh giá sản phẩm đã mua
- [x] Thông báo (đơn hàng, khuyến mãi)

#### H. Tìm kiếm
- [x] Tìm kiếm realtime (autocomplete/suggestions)
- [x] Tìm kiếm theo từ khóa, danh mục
- [x] Lịch sử tìm kiếm
- [x] Từ khóa hot / trending

### 2.2. Trang quản trị (Admin Panel)

#### A. Dashboard
- [x] Tổng quan doanh thu (ngày/tuần/tháng)
- [x] Số đơn hàng mới
- [x] Sản phẩm bán chạy
- [x] Biểu đồ doanh thu, đơn hàng
- [x] Cảnh báo tồn kho thấp

#### B. Quản lý sản phẩm
- [x] CRUD sản phẩm (Thêm, Sửa, Xóa, Ẩn/Hiện)
- [x] Upload nhiều ảnh sản phẩm
- [x] Quản lý biến thể (variant: màu, size, model tương thích)
- [x] Quản lý danh mục sản phẩm
- [x] Import/Export sản phẩm (CSV/Excel)
- [x] Quản lý giá: Giá gốc, giá bán, giá khuyến mãi
- [x] SEO: Meta title, meta description cho từng sản phẩm

#### C. Quản lý đơn hàng
- [x] Danh sách đơn hàng (filter theo trạng thái)
- [x] Trạng thái đơn: Chờ xác nhận → Đã xác nhận → Đang giao → Hoàn thành → Đã hủy
- [x] Xử lý đơn hàng (xác nhận, giao cho shipper)
- [x] In phiếu giao hàng
- [x] Quản lý trả hàng/hoàn tiền

#### D. Quản lý khách hàng
- [x] Danh sách khách hàng
- [x] Lịch sử mua hàng của từng khách
- [x] Phân nhóm khách hàng (VIP, thường, mới)

#### E. Quản lý khuyến mãi
- [x] Tạo/quản lý mã giảm giá (voucher)
- [x] Flash Sale (thiết lập thời gian, sản phẩm, giá sale)
- [x] Chương trình khuyến mãi (combo, mua 2 giảm X%)

#### F. Quản lý nội dung
- [x] Banner quảng cáo (trang chủ, trang danh mục)
- [x] Trang tĩnh (Giới thiệu, Chính sách, Hướng dẫn mua hàng)
- [x] Thông báo hệ thống

#### G. Báo cáo & Thống kê
- [x] Báo cáo doanh thu (theo ngày/tuần/tháng/năm)
- [x] Báo cáo sản phẩm bán chạy
- [x] Báo cáo tồn kho
- [x] Báo cáo khách hàng

### 2.3. Tính năng bổ sung

- [x] **Chat/Hỗ trợ trực tuyến** - Chat với khách hàng (tương tự Shopee Chat)
- [x] **Đánh giá & Review** - Hệ thống đánh giá sao + ảnh + bình luận
- [x] **Thông báo đẩy** - Push notification cho đơn hàng, khuyến mãi
- [x] **SEO tối ưu** - URL thân thiện, sitemap, structured data
- [x] **PWA** - Progressive Web App cho trải nghiệm mobile tốt hơn
- [x] **Đa ngôn ngữ** - Tiếng Việt (mặc định), có thể mở rộng Tiếng Anh
- [x] **Responsive Design** - Tối ưu cho Mobile, Tablet, Desktop

---

## 3. THIẾT KẾ API KẾT NỐI PHẦN MỀM QUẢN LÝ KHO

### 3.1. Mô hình tích hợp

```
┌──────────────────┐         ┌──────────────────┐
│                  │         │                  │
│  Website bán     │◄───────►│  Phần mềm quản  │
│  hàng (PickGLX)  │  REST   │  lý kho hàng    │
│                  │  API    │  (External)      │
│                  │         │                  │
└──────────────────┘         └──────────────────┘
         │                            │
         │    Webhook / Polling        │
         │◄────────────────────────────│
         │                            │
```

### 3.2. API Endpoints cho phần mềm quản lý kho

#### A. Inventory Sync (Đồng bộ tồn kho)

```
# Website → Phần mềm quản lý kho
POST /api/v1/inventory/sync
  → Đồng bộ toàn bộ tồn kho từ phần mềm quản lý kho

GET /api/v1/inventory/products/{sku}
  → Lấy tồn kho realtime của 1 sản phẩm

GET /api/v1/inventory/products?skus=SKU1,SKU2,...
  → Lấy tồn kho nhiều sản phẩm cùng lúc

# Phần mềm quản lý kho → Website (Webhook)
POST /api/v1/webhooks/inventory-update
  → Nhận thông báo cập nhật tồn kho từ phần mềm quản lý kho
  Body: { "sku": "...", "quantity": 50, "warehouse": "...", "updated_at": "..." }
```

#### B. Order Sync (Đồng bộ đơn hàng)

```
# Website → Phần mềm quản lý kho (Khi có đơn hàng mới)
POST /api/v1/orders/export
  → Gửi thông tin đơn hàng mới sang phần mềm quản lý kho
  Body: {
    "order_id": "ORD-20240101-001",
    "customer": { "name": "...", "phone": "...", "address": "..." },
    "items": [
      { "sku": "KC-IP15PM-001", "name": "Kính cường lực iPhone 15 Pro Max", "quantity": 2, "price": 89000 }
    ],
    "total": 178000,
    "shipping_fee": 30000,
    "payment_method": "COD",
    "status": "confirmed",
    "created_at": "2024-01-01T10:00:00Z"
  }

# Tự động trừ tồn kho khi đơn hàng được xác nhận
POST /api/v1/inventory/deduct
  → Trừ tồn kho khi xác nhận đơn hàng
  Body: {
    "order_id": "ORD-20240101-001",
    "items": [
      { "sku": "KC-IP15PM-001", "quantity": 2 }
    ]
  }

# Hoàn lại tồn kho khi hủy đơn hàng
POST /api/v1/inventory/restore
  → Hoàn tồn kho khi đơn hàng bị hủy/trả hàng
  Body: {
    "order_id": "ORD-20240101-001",
    "items": [
      { "sku": "KC-IP15PM-001", "quantity": 2 }
    ],
    "reason": "customer_cancelled"
  }
```

#### C. Product Sync (Đồng bộ sản phẩm)

```
# Đồng bộ danh sách sản phẩm từ phần mềm quản lý kho
GET /api/v1/products/sync
  → Lấy danh sách sản phẩm mới/cập nhật từ phần mềm quản lý kho

POST /api/v1/products/import
  → Import sản phẩm hàng loạt từ phần mềm quản lý kho
  Body: {
    "products": [
      {
        "sku": "KC-IP15PM-001",
        "name": "Kính cường lực iPhone 15 Pro Max",
        "category": "kinh-cuong-luc",
        "price": 89000,
        "cost_price": 30000,
        "quantity": 100,
        "variants": [...]
      }
    ]
  }
```

#### D. Authentication & Security

```
# API Key Authentication
Headers: {
  "X-API-Key": "your-api-key-here",
  "X-Timestamp": "1704067200",
  "X-Signature": "HMAC-SHA256 signature"
}

# Rate Limiting: 100 requests/minute
# Webhook Verification: HMAC signature trên payload
```

### 3.3. Event-Driven Flow (Luồng xử lý sự kiện)

```
1. Khách đặt hàng trên website
   → Website tạo đơn hàng (status: pending)
   → Kiểm tra tồn kho realtime từ phần mềm quản lý kho
   → Nếu đủ hàng: Reserve (giữ chỗ) tồn kho

2. Admin xác nhận đơn hàng
   → POST /api/v1/orders/export → Gửi đơn hàng sang phần mềm quản lý kho
   → POST /api/v1/inventory/deduct → Trừ tồn kho chính thức
   → Phần mềm quản lý kho cập nhật → Webhook → Website cập nhật tồn kho hiển thị

3. Đơn hàng bị hủy
   → POST /api/v1/inventory/restore → Hoàn lại tồn kho
   → Thông báo cho phần mềm quản lý kho

4. Phần mềm quản lý kho cập nhật tồn kho (nhập hàng mới)
   → Webhook POST /api/v1/webhooks/inventory-update
   → Website cập nhật số lượng tồn kho hiển thị
```

---

## 4. THIẾT KẾ GIAO DIỆN (UI/UX)

### 4.1. Tông màu & Phong cách

| Element | Màu sắc | Mã màu |
|---------|---------|--------|
| **Primary (Cam chủ đạo)** | Cam Shopee-like | `#EE4D2D` |
| **Primary Hover** | Cam đậm | `#D73211` |
| **Secondary** | Trắng | `#FFFFFF` |
| **Background** | Xám nhạt | `#F5F5F5` |
| **Text chính** | Đen/xám đậm | `#222222` |
| **Text phụ** | Xám | `#757575` |
| **Success** | Xanh lá | `#26AA99` |
| **Warning** | Vàng | `#FFBF00` |
| **Error/Sale** | Đỏ | `#EE4D2D` |

### 4.2. Layout trang chủ (theo phong cách Shopee)

```
┌─────────────────────────────────────────────────┐
│ [Top Bar: Free ship, Download app, Social links] │
├─────────────────────────────────────────────────┤
│ [Logo]  [═══ Thanh tìm kiếm ═══]  [🛒 Cart]   │
├─────────────────────────────────────────────────┤
│ [Danh mục ▼] [Từ khóa hot: iPhone, Samsung...] │
├─────────────────────────────────────────────────┤
│ ┌─────────┐ ┌─────────────────────────────────┐ │
│ │ Danh mục│ │     Banner Carousel             │ │
│ │ ├ Kính  │ │   ┌─────────────────────────┐   │ │
│ │ ├ Ốp    │ │   │   FLASH SALE 50%        │   │ │
│ │ ├ Sạc   │ │   │   Phụ kiện chính hãng   │   │ │
│ │ ├ Cáp   │ │   └─────────────────────────┘   │ │
│ │ ├ Tai   │ │                                 │ │
│ │ └ Khác  │ └─────────────────────────────────┘ │
│ └─────────┘                                     │
├─────────────────────────────────────────────────┤
│ ⚡ FLASH SALE - Kết thúc sau: 02:30:45          │
│ [SP1] [SP2] [SP3] [SP4] [SP5] [SP6] [>>>]      │
├─────────────────────────────────────────────────┤
│ 📱 DANH MỤC                                     │
│ [🔲Kính CL] [📱Ốp lưng] [🔌Sạc] [🔗Cáp]       │
│ [🎧Tai nghe] [🔋Pin DP] [📦Phụ kiện khác]       │
├─────────────────────────────────────────────────┤
│ 🔥 SẢN PHẨM BÁN CHẠY                           │
│ ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐     │
│ │ SP │ │ SP │ │ SP │ │ SP │ │ SP │ │ SP │     │
│ │Ảnh │ │Ảnh │ │Ảnh │ │Ảnh │ │Ảnh │ │Ảnh │     │
│ │Tên │ │Tên │ │Tên │ │Tên │ │Tên │ │Tên │     │
│ │Giá │ │Giá │ │Giá │ │Giá │ │Giá │ │Giá │     │
│ │⭐4.8│ │⭐4.9│ │⭐4.7│ │⭐4.8│ │⭐4.9│ │⭐4.6│     │
│ └────┘ └────┘ └────┘ └────┘ └────┘ └────┘     │
├─────────────────────────────────────────────────┤
│ GỢI Ý HÔM NAY (Infinite scroll grid)           │
│ ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐     │
│ │    │ │    │ │    │ │    │ │    │ │    │     │
│ └────┘ └────┘ └────┘ └────┘ └────┘ └────┘     │
│ ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐     │
│ │    │ │    │ │    │ │    │ │    │ │    │     │
│ └────┘ └────┘ └────┘ └────┘ └────┘ └────┘     │
│              ... (load more) ...                 │
├─────────────────────────────────────────────────┤
│ FOOTER                                          │
│ [Về chúng tôi] [Chính sách] [Hỗ trợ] [Liên hệ]│
│ [Hotline] [Địa chỉ] [Social media links]       │
└─────────────────────────────────────────────────┘
```

---

## 5. DANH MỤC SẢN PHẨM ĐỀ XUẤT

Dựa trên nghiên cứu shop "Phụ Kiện Hạt Dẻ" trên Shopee:

| STT | Danh mục | Ví dụ sản phẩm |
|-----|----------|----------------|
| 1 | Kính cường lực | Kính CL iPhone 15/16, Samsung Galaxy S24, Full màn, Privacy |
| 2 | Ốp lưng / Case | Ốp silicon, ốp chống sốc, ốp trong suốt, ốp da |
| 3 | Sạc & Adapter | Sạc nhanh 20W/33W/67W, sạc không dây, adapter |
| 4 | Cáp sạc | Cáp Lightning, Type-C, Micro USB, cáp sạc nhanh |
| 5 | Tai nghe | Tai nghe Bluetooth, tai nghe có dây, AirPods |
| 6 | Pin dự phòng | Power bank 10000/20000mAh |
| 7 | Giá đỡ điện thoại | Giá đỡ bàn, giá đỡ xe hơi, PopSocket |
| 8 | Phụ kiện khác | Bao đựng AirPods, dán camera, ring holder |

---

## 6. KẾ HOẠCH TRIỂN KHAI (ROADMAP)

### Phase 1: MVP (4-6 tuần)
- Setup project structure (Next.js + FastAPI)
- Database schema & migrations
- Authentication (đăng ký/đăng nhập)
- CRUD sản phẩm (Admin)
- Trang chủ, trang danh sách sản phẩm, trang chi tiết sản phẩm
- Giỏ hàng & Checkout cơ bản (COD)
- API kết nối phần mềm quản lý kho (inventory sync, order export)

### Phase 2: Tính năng nâng cao (2-4 tuần)
- Thanh toán online (VNPay/MoMo)
- Flash Sale & Khuyến mãi
- Đánh giá & review sản phẩm
- Admin Dashboard (báo cáo, thống kê)
- Tìm kiếm nâng cao (full-text search)
- Email thông báo đơn hàng

### Phase 3: Tối ưu & Mở rộng (2-4 tuần)
- Chat hỗ trợ trực tuyến
- Push notification
- PWA (Progressive Web App)
- SEO optimization
- Performance optimization (lazy loading, image optimization)
- A/B testing, analytics

---

## 7. CÂU HỎI CẦN XÁC NHẬN TỪ KHÁCH HÀNG

1. **Phần mềm quản lý kho**: Bạn đang sử dụng phần mềm quản lý kho nào? (KiotViet, Sapo, Nhanh.vn, tự phát triển, hoặc phần mềm khác?) → Để tôi thiết kế API tương thích.

2. **Tên miền**: Bạn đã có tên miền chưa? Nếu có, tên miền là gì?

3. **Thanh toán**: Bạn muốn tích hợp cổng thanh toán nào? (VNPay, MoMo, ZaloPay, hoặc chỉ COD trước?)

4. **Hosting**: Bạn muốn host ở đâu? (VPS Việt Nam, AWS, hoặc tôi đề xuất?)

5. **Ưu tiên tính năng**: Trong danh sách tính năng trên, có tính năng nào bạn muốn ưu tiên làm trước hoặc bỏ qua?

6. **Nội dung sản phẩm**: Bạn có sẵn dữ liệu sản phẩm (ảnh, mô tả, giá) hay cần import từ Shopee?

---

## 8. ƯỚC TÍNH KỸ THUẬT

| Metric | Ước tính |
|--------|----------|
| **Số trang frontend** | ~15-20 trang |
| **Số API endpoints** | ~50-60 endpoints |
| **Database tables** | ~15-20 tables |
| **Thời gian phát triển** | 8-14 tuần (MVP + Enhancements) |

---

*Tài liệu này là bản kế hoạch đề xuất ban đầu. Sau khi nhận được phản hồi từ khách hàng, tôi sẽ bắt đầu triển khai theo từng phase.*
