# Deploy `phukienhatde.vn` lên VPS

Hướng dẫn deploy website Phụ Kiện Hạt Dẻ (frontend Next.js + backend FastAPI + PostgreSQL + Redis + Nginx + Let's Encrypt) lên server Ubuntu 22.04+.

Có 2 cách:

- **Cách A — Docker Compose** (đơn giản, khuyến nghị cho server mới)
- **Cách B — systemd + Nginx native** (nhẹ hơn, không cần Docker)

---

## Yêu cầu chung

1. VPS Ubuntu 22.04+ (≥ 1 vCPU, 2GB RAM, 20GB SSD)
2. Tên miền `phukienhatde.vn` đã trỏ A record vào IP server:
   - `phukienhatde.vn` → IP server
   - `api.phukienhatde.vn` → IP server (subdomain riêng cho backend)
   - `www.phukienhatde.vn` → IP server (redirect về apex)
3. Đã mở port 22, 80, 443 ở firewall
4. SSH user có quyền sudo

---

## Cách A: Docker Compose

### 1. Cài Docker & Compose

```bash
sudo apt update && sudo apt install -y docker.io docker-compose-plugin git nginx certbot python3-certbot-nginx
sudo systemctl enable --now docker
sudo usermod -aG docker $USER && newgrp docker
```

### 2. Clone repo & cấu hình env

```bash
sudo mkdir -p /srv && sudo chown $USER:$USER /srv
cd /srv && git clone https://github.com/nhhoang1992/pickglx.git
cd pickglx/backend
cp .env.example .env
nano .env   # Điền credentials thật (xem mục Cấu hình bên dưới)
```

### 3. Build & chạy

```bash
cd /srv/pickglx
docker compose up -d --build
docker compose exec backend python seed_data.py  # Seed dữ liệu mẫu
docker compose ps
```

### 4. Cấu hình Nginx + SSL

```bash
sudo cp /srv/pickglx/deploy/nginx/phukienhatde.vn.conf /etc/nginx/sites-available/
sudo ln -s /etc/nginx/sites-available/phukienhatde.vn.conf /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default

# Lấy SSL certificate
sudo certbot --nginx -d phukienhatde.vn -d www.phukienhatde.vn -d api.phukienhatde.vn

sudo nginx -t && sudo systemctl reload nginx
```

### 5. Verify

```bash
curl https://api.phukienhatde.vn/health
# {"status": "ok"}

curl https://phukienhatde.vn
# Trả về HTML frontend
```

---

## Cách B: systemd + Nginx native (không dùng Docker)

### 1. Cài dependencies

```bash
sudo apt update && sudo apt install -y python3.12 python3.12-venv python3-pip postgresql redis-server nginx certbot python3-certbot-nginx nodejs npm
sudo systemctl enable --now postgresql redis-server
```

Cài Node.js 24 LTS (nếu apt chưa có):

```bash
curl -fsSL https://deb.nodesource.com/setup_24.x | sudo bash -
sudo apt install -y nodejs
```

### 2. Tạo user & database

```bash
sudo useradd -r -m -d /srv/pickglx -s /bin/bash pickglx
sudo -u postgres psql -c "CREATE USER pickglx WITH PASSWORD 'STRONG_PW_HERE';"
sudo -u postgres psql -c "CREATE DATABASE pickglx OWNER pickglx;"
```

### 3. Clone & setup backend

```bash
sudo -u pickglx -i
cd /srv/pickglx
git clone https://github.com/nhhoang1992/pickglx.git .
cd backend
python3.12 -m venv venv && source venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
nano .env   # Điền credentials
python seed_data.py
exit
```

### 4. Setup frontend (build)

```bash
sudo -u pickglx -i
cd /srv/pickglx/frontend
npm ci
echo "NEXT_PUBLIC_API_URL=https://api.phukienhatde.vn/api/v1" > .env.production
npm run build
exit
```

### 5. Cài systemd services

```bash
sudo cp /srv/pickglx/deploy/systemd/pickglx-backend.service /etc/systemd/system/
sudo cp /srv/pickglx/deploy/systemd/pickglx-frontend.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now pickglx-backend pickglx-frontend
sudo systemctl status pickglx-backend pickglx-frontend
```

### 6. Nginx + SSL (giống Cách A bước 4)

---

## Cấu hình `.env`

Backend `.env` cần điền:

| Biến | Mô tả |
|------|-------|
| `DATABASE_URL` | `postgresql+psycopg2://pickglx:PASSWORD@localhost:5432/pickglx` |
| `INVENTORY_API_KEY` | Sinh ngẫu nhiên: `openssl rand -hex 32` |
| `INVENTORY_WEBHOOK_SECRET` | Sinh ngẫu nhiên: `openssl rand -hex 32` |
| `INVENTORY_EXTERNAL_URL` | URL kho của bạn, ví dụ `https://kho.phukienhatde.vn/api` |
| `MOMO_PARTNER_CODE` / `MOMO_ACCESS_KEY` / `MOMO_SECRET_KEY` | Lấy từ https://business.momo.vn (sandbox trước, production sau khi duyệt) |
| `ZALOPAY_APP_ID` / `ZALOPAY_KEY1` / `ZALOPAY_KEY2` | Lấy từ https://merchant.zalopay.vn |
| `MOMO_ENDPOINT` | `https://test-payment.momo.vn/v2/gateway/api/create` (sandbox) hoặc `https://payment.momo.vn/v2/gateway/api/create` (prod) |
| `ZALOPAY_ENDPOINT` | `https://sb-openapi.zalopay.vn/v2/create` (sandbox) hoặc `https://openapi.zalopay.vn/v2/create` (prod) |

⚠️ **Không commit `.env` lên git.** File `.env.example` đã được commit làm template.

---

## Workflow update code

Khi có commit mới trên branch chính:

**Docker:**
```bash
cd /srv/pickglx
git pull
docker compose up -d --build
docker compose exec backend python seed_data.py  # Nếu seed thay đổi
```

**Native:**
```bash
cd /srv/pickglx && git pull
sudo -u pickglx bash -c 'cd /srv/pickglx/backend && source venv/bin/activate && pip install -r requirements.txt'
sudo -u pickglx bash -c 'cd /srv/pickglx/frontend && npm ci && npm run build'
sudo systemctl restart pickglx-backend pickglx-frontend
```

---

## Monitoring

- Backend logs: `docker compose logs -f backend` hoặc `sudo journalctl -u pickglx-backend -f`
- Frontend logs: `docker compose logs -f frontend` hoặc `sudo journalctl -u pickglx-frontend -f`
- Nginx access log: `/var/log/nginx/access.log`
- API health check: `https://api.phukienhatde.vn/health`
- Swagger UI: `https://api.phukienhatde.vn/docs`

---

## Backup

Backup database hàng ngày, lưu 7 bản:

```bash
sudo crontab -e
# Thêm:
0 2 * * * docker compose -f /srv/pickglx/docker-compose.yml exec -T postgres pg_dump -U pickglx pickglx | gzip > /srv/backups/pickglx-$(date +\%Y\%m\%d).sql.gz
0 3 * * * find /srv/backups -name "pickglx-*.sql.gz" -mtime +7 -delete
```
