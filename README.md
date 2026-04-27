# Pickglx

> Nền tảng web dành cho cộng đồng CLB Pickleball Việt Nam.

Pickglx giúp các CLB Pickleball:

- **Tổ chức giải đấu** (vòng tròn / loại trực tiếp / Mexicano) với bảng đấu & BXH realtime.
- **Chia tiền sinh hoạt** theo đầu người, số trận, hoặc số giờ chơi — tự sinh QR VietQR.
- **Lịch xé vé sân**: chủ sân đăng khung giờ, người chơi đăng ký slot và upload bằng chứng chuyển khoản.
- **Quản lý CLB**: đội trưởng duyệt thành viên gia nhập, quản lý buổi chơi và quỹ.

## Tech stack

- **Next.js 14** (App Router) + **TypeScript** + **TailwindCSS**
- **Prisma 6** + **PostgreSQL**
- **Auth.js (NextAuth v5)** — Credentials (email/password), sẵn sàng cắm thêm Google
- **Hosting**: Vercel + Supabase / Neon (production)
- **Local dev**: PostgreSQL trong Docker

## Bắt đầu

### 1. Cài đặt dependencies

```bash
npm install
```

### 2. Khởi chạy Postgres local

```bash
docker compose up -d
# hoặc
docker run -d --name pickglx-pg \
  -e POSTGRES_USER=pickglx -e POSTGRES_PASSWORD=pickglx -e POSTGRES_DB=pickglx \
  -p 5432:5432 postgres:16-alpine
```

### 3. Tạo file `.env`

Copy từ `.env.example`:

```bash
cp .env.example .env
```

Cập nhật `AUTH_SECRET` cho production:

```bash
openssl rand -base64 32
```

### 4. Chạy migration & dev server

```bash
npx prisma migrate dev
npm run dev
```

Mở http://localhost:3000

## Cấu trúc thư mục

```
src/
  app/                  # App Router (pages, route handlers)
    (public)            # Trang công khai
    auth/               # Đăng ký / đăng nhập
    dashboard/          # Khu vực có auth
    api/                # REST endpoints
  components/           # UI components dùng chung
  lib/                  # Helpers (prisma client, utils)
  auth.ts               # Cấu hình NextAuth
prisma/
  schema.prisma         # Schema database
  migrations/           # Lịch sử migration
```

## Roadmap

- [x] Foundation: scaffold + theme + Prisma schema + Auth (PR #1)
- [ ] Module CLB: tạo CLB, gia nhập, đội trưởng duyệt thành viên
- [ ] Module Chia tiền: tạo session, chia tiền, QR VietQR, upload bill
- [ ] Module Lịch xé vé: sân đăng lịch, đăng ký slot, duyệt
- [ ] Module Giải đấu: round-robin, BXH, lịch trận
- [ ] Notifications (email + web push)
- [ ] PWA + mobile native (Expo)

## License

MIT
