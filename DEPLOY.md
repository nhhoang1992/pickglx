# Hướng dẫn Deploy UltimatePOS + Module Shopee trên Localhost

## Yêu cầu hệ thống

| Thành phần | Phiên bản tối thiểu |
|-----------|---------------------|
| PHP | 8.0+ (khuyến nghị 8.1) |
| MySQL | 5.7+ hoặc MariaDB 10.3+ |
| Composer | 2.x |
| Node.js | 14+ (tùy chọn, cho build assets) |
| Git | 2.x |

**Extensions PHP bắt buộc:**
```
php-mbstring php-xml php-zip php-gd php-curl php-mysql php-bcmath php-json php-tokenizer php-ctype php-fileinfo
```

---

## Bước 1: Clone source code

```bash
git clone https://github.com/nhhoang1992/pickglx.git
cd pickglx
```

> **Lưu ý:** Nếu code chưa được merge vào `main`, hãy checkout nhánh có đầy đủ code:
> ```bash
> git checkout devin/1778576270-full-source-with-shopee
> ```

---

## Bước 2: Cài đặt dependencies

```bash
composer install
```

Nếu gặp lỗi memory, chạy:
```bash
COMPOSER_MEMORY_LIMIT=-1 composer install
```

---

## Bước 3: Cấu hình môi trường

```bash
cp .env.example .env
php artisan key:generate
```

Mở file `.env` và sửa các thông tin database:

```env
APP_NAME="Ultimate POS"
APP_URL=http://localhost:8000
APP_TIMEZONE="Asia/Ho_Chi_Minh"
APP_LOCALE=vi

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ultimatepos
DB_USERNAME=root
DB_PASSWORD=your_password
```

---

## Bước 4: Tạo database

```sql
-- Đăng nhập MySQL
mysql -u root -p

-- Tạo database
CREATE DATABASE ultimatepos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- (Tùy chọn) Tạo user riêng
CREATE USER 'posuser'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON ultimatepos.* TO 'posuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## Bước 5: Chạy migration và seed data

```bash
# Chạy migration (tạo bảng)
php artisan migrate

# Seed data mẫu (tạo admin user + dữ liệu cơ bản)
php artisan db:seed
```

> **Lưu ý:** Nếu `db:seed` không có sẵn, bạn cần tạo business và admin user thủ công qua registration page.

---

## Bước 6: Cấp quyền thư mục

```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Hoặc đơn giản hơn (cho development)
chmod -R 777 storage bootstrap/cache
```

---

## Bước 7: Khởi động server

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Truy cập: **http://localhost:8000**

---

## Bước 8: Đăng ký / Đăng nhập

Nếu đã seed data:
- **URL:** http://localhost:8000/login
- **Username:** admin
- **Password:** (xem trong seeder hoặc đăng ký mới)

Nếu chưa có user, truy cập http://localhost:8000/register để tạo tài khoản mới.

---

## Bước 9: Kết nối Shopee (Module Shopee)

### 9.1. Lấy API credentials từ Shopee

1. Truy cập https://open.shopee.com
2. Đăng ký tài khoản Developer → tạo App
3. Lấy **Partner ID** và **Partner Key**

### 9.2. Cấu hình trong phần mềm

1. Đăng nhập → Menu **Shopee** → **Cài đặt Shopee**
2. Nhập **Partner ID** và **Partner Key** → bấm **Lưu cấu hình**
3. Bấm **Kết nối gian hàng mới**

### 9.3. Cấu hình Redirect URL (quan trọng!)

Shopee yêu cầu domain thật cho OAuth callback. **Không chấp nhận `localhost`**.

**Cho development:**
- Dùng [ngrok](https://ngrok.com/) hoặc [Cloudflare Tunnel](https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/) để tạo public URL:
  ```bash
  ngrok http 8000
  ```
- Lấy URL ngrok (ví dụ: `https://abc123.ngrok.io`)

**Trên Shopee Open Platform Console:**
1. Vào **App Management** → **App List** → chọn App → **Edit APP**
2. **Live Redirect URL Domain:** nhập domain ngrok (ví dụ: `https://abc123.ngrok.io`)
3. **IP Address Whitelist:** thêm IP public của máy bạn
4. Bấm **Submit**

**Cập nhật APP_URL trong `.env`:**
```env
APP_URL=https://abc123.ngrok.io
```

### 9.4. Kết nối Shop

1. Quay lại **Cài đặt Shopee** → bấm **Kết nối gian hàng mới**
2. Đăng nhập tài khoản Shopee Seller → Xác nhận quyền
3. Redirect về app → Shop hiện trong danh sách "Đã kết nối"

### 9.5. Đồng bộ dữ liệu

1. **Đơn hàng:** Shopee → Danh sách đơn hàng → bấm **Đồng bộ đơn hàng**
2. **Sản phẩm:** Shopee → Liên kết sản phẩm → bấm **Đồng bộ sản phẩm**
3. **Báo cáo:** Shopee → Báo cáo Shopee (tự động tính từ dữ liệu đã sync)

---

## Cấu trúc Module Shopee

```
Modules/Shopee/
├── Config/shopee.php                    # Cấu hình module
├── Database/Migrations/                 # 8 file migration
├── Http/Controllers/
│   ├── ShopeeAuthController.php         # OAuth flow
│   ├── ShopeeOrderController.php        # Quản lý đơn hàng
│   ├── ShopeeProductMappingController.php # Liên kết sản phẩm
│   ├── ShopeeReportController.php       # 5 trang báo cáo
│   └── ShopeeSettingController.php      # Cài đặt API
├── Models/                              # 8 Eloquent models
├── Providers/ShopeeServiceProvider.php  # Service provider
├── Resources/
│   ├── lang/{vi,en}/lang.php            # Đa ngôn ngữ
│   └── views/shopee/                    # Blade templates
├── Routes/web.php                       # Route definitions
├── Services/                            # API & business logic
└── module.json                          # Module config
```

---

## Tính năng đã phát triển

| Module | Tính năng | Đường dẫn |
|--------|----------|-----------|
| **Đơn hàng** | Tabs trạng thái, bộ lọc shop, hành động hàng loạt | `/shopee/orders` |
| **Liên kết SP** | Mapping SKU Shopee ↔ UltimatePOS, tự động ghép | `/shopee/product-mappings` |
| **Cài đặt** | API config, kết nối shop, OAuth | `/shopee/settings` |
| **Báo cáo - Dashboard** | Doanh thu, đơn hàng, biểu đồ trend | `/shopee/reports` |
| **Báo cáo - Đơn hàng** | Tỷ lệ thành công/hủy/hoàn, thanh toán | `/shopee/reports/order-analysis` |
| **Báo cáo - SP bán chạy** | Top 50 SP/variant theo qty hoặc doanh thu | `/shopee/reports/top-products` |
| **Báo cáo - Vận chuyển** | Carrier, express, khu vực | `/shopee/reports/shipping` |
| **Báo cáo - Đối soát** | Theo ngày/trạng thái/shop | `/shopee/reports/reconciliation` |
| **Nhật ký** | Log đồng bộ | `/shopee/sync-logs` |

---

## Khắc phục sự cố

### Lỗi "Class not found" sau composer install
```bash
composer dump-autoload
php artisan module:enable Shopee
```

### Lỗi migration "Table already exists"
```bash
php artisan migrate:fresh   # ⚠️ XÓA toàn bộ data
# Hoặc chỉ chạy migration Shopee:
php artisan migrate --path=Modules/Shopee/Database/Migrations
```

### Lỗi permission denied trên storage
```bash
chmod -R 777 storage bootstrap/cache
```

### Shopee API trả về "Wrong sign"
- Partner Key có thể bị lưu sai (toàn dấu `*`)
- Vào Cài đặt → xóa ô Partner Key → nhập lại key thật → Lưu

### Không thấy menu Shopee trên sidebar
- Kiểm tra `modules_statuses.json` có `"Shopee": true`
- Chạy `php artisan module:enable Shopee`
- Clear cache: `php artisan cache:clear && php artisan view:clear`

---

## Deploy lên Production (VPS/Hosting)

Khi deploy lên server thật:

1. Đổi `APP_URL` trong `.env` sang domain thật
2. Đổi `APP_ENV=production`, `APP_DEBUG=false`
3. Cập nhật **Redirect URL Domain** trên Shopee Open Platform sang domain thật
4. Cấu hình web server (Nginx/Apache) trỏ document root vào thư mục `public/`
5. Chạy:
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

## Liên hệ

- **Repository:** https://github.com/nhhoang1992/pickglx
- **Nhánh chính:** `main` (hoặc `devin/1778576270-full-source-with-shopee`)
