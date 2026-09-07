# Panduan Teknis (Technical Guide) - Sistem Kas Sekolah

Dokumentasi teknis lengkap mengenai arsitektur sistem, instalasi lokal, konfigurasi database MySQL, Docker, proses build, serta panduan deployment ke server produksi.

---

## Daftar Isi
1. [Arsitektur Sistem](#1-arsitektur-sistem)
2. [Prasyarat Sistem (Prerequisites)](#2-prasyarat-sistem-prerequisites)
3. [Struktur Repositori (Monorepo)](#3-struktur-repositori-monorepo)
4. [Variabel Lingkungan (.env)](#4-variabel-lingkungan-env)
5. [Instalasi dan Setup Lokal](#5-instalasi-dan-setup-lokal)
6. [Pengelolaan Database MySQL (Prisma ORM)](#6-pengelolaan-database-mysql-prisma-orm)
7. [Menjalankan Aplikasi dengan Docker](#7-menjalankan-aplikasi-dengan-docker)
8. [Panduan Deployment ke Server Produksi (VPS/Linux)](#8-panduan-deployment-ke-server-produksi-vpslinux)
9. [Troubleshooting & Pemeliharaan](#9-troubleshooting--pemeliharaan)

---

## 1. Arsitektur Sistem

Aplikasi **Kas Sekolah** dibangun menggunakan pola arsitektur modern berbasis monorepo (NPM Workspaces):

```mermaid
graph TD
    Client[Browser / Klien] -->|Next.js App / Port 3000| Web[Frontend: Next.js 16 + React 19]
    Web -->|REST API / Port 3001| API[Backend: NestJS 11 + Fastify/Express]
    API -->|Prisma ORM| MySQL[(Database: MySQL 8.0 / Port 3306)]
    API -->|BullMQ Queue| Redis[(Cache & Queue: Redis 7 / Port 6379)]
    API -->|Nodemailer| SMTP[Email Service / Gmail SMTP]
```

- **Frontend (`apps/web`)**: Next.js 16 (App Router), Tailwind CSS, Lucide Icons, jsPDF & autoTable.
- **Backend (`apps/api`)**: NestJS 11, Prisma ORM 6, Passport JWT Auth, Argon2, BullMQ, Swagger API Docs.
- **Database**: MySQL 8.0 (sebelumnya PostgreSQL, kini telah sepenuhnya dimigrasikan ke MySQL).
- **In-Memory Cache & Message Queue**: Redis 7.0 (untuk antrean background job & notifikasi email).

---

## 2. Prasyarat Sistem (Prerequisites)

Pastikan server atau mesin pengembangan Anda telah terinstal:
- **Node.js**: Versi `18.x` atau `20.x` LTS (disarankan v20+)
- **NPM**: Versi `9.x` atau `10.x` (bawaan Node.js)
- **MySQL Server**: Versi `8.0+` atau MariaDB `10.5+`
- **Redis Server**: Versi `6.x` atau `7.x`
- *(Opsional)* **Docker & Docker Compose**: Jika ingin menjalankan MySQL & Redis dalam container.

---

## 3. Struktur Repositori (Monorepo)

```text
kas-sekolah/
├── apps/
│   ├── api/                     # Backend NestJS
│   │   ├── prisma/
│   │   │   └── schema.prisma    # Skema Prisma ORM (MySQL provider)
│   │   ├── src/
│   │   │   ├── core/            # Database module, Seed service, Auth guards
│   │   │   └── modules/         # Modul fitur: school, user, class, billing, dll.
│   │   ├── seed-admin.js        # Script seeding akun admin utama
│   │   ├── .env                 # Konfigurasi environment backend
│   │   └── package.json
│   └── web/                     # Frontend Next.js
│       ├── src/
│       │   └── app/             # Halaman utama, dashboard, dan komponen UI
│       └── package.json
├── docker/
│   └── docker-compose.yml       # Definisi container MySQL 8.0 & Redis 7
├── docs/                        # Dokumentasi teknis & panduan pengguna
├── package.json                 # Root monorepo package.json
└── README.md
```

---

## 4. Variabel Lingkungan (.env)

Salin atau buat file `.env` di dalam folder `apps/api/.env`:

```env
# Server Konfigurasi
PORT=3001
NODE_ENV=development

# Koneksi Database MySQL
# Format: mysql://<USER>:<PASSWORD>@<HOST>:<PORT>/<DATABASE_NAME>
DATABASE_URL="mysql://root:password@localhost:3306/kas-sekolah"

# Keamanan Token JWT
JWT_SECRET="your-super-secret-jwt-key"
JWT_EXPIRES_IN="15m"
REFRESH_TOKEN_SECRET="your-super-secret-refresh-key"
REFRESH_TOKEN_EXPIRES_IN="7d"

# Antrean & Redis Cache
REDIS_HOST="localhost"
REDIS_PORT=6379

# Konfigurasi Notifikasi Email (SMTP)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
SMTP_FROM=your-email@gmail.com
```

Untuk frontend (`apps/web`), Anda dapat menambahkan file `apps/web/.env.local` (opsional):
```env
NEXT_PUBLIC_API_URL=http://localhost:3001
```

---

## 5. Instalasi dan Setup Lokal

### Langkah 1: Kloning Repositori & Instal Dependensi
Jalankan di terminal root project:
```bash
# Instal semua dependensi monorepo sekaligus
npm install
```

### Langkah 2: Menyiapkan Database MySQL
Pastikan server MySQL berjalan di `localhost:3306` dan buat database `kas-sekolah` jika belum ada:
```sql
CREATE DATABASE `kas-sekolah` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Langkah 3: Sinkronisasi Skema Database & Seeding
Sinkronkan model database menggunakan Prisma:
```bash
# Generate Prisma Client MySQL
npm run prisma:generate

# Push skema tabel langsung ke database MySQL
npm run prisma:push

# Jalankan seed akun admin utama (SysAdmin & School Admin)
npm run seed:admin
```

Akun default yang terbentuk:
- **System Admin**: `sysadmin@sistemkas.com` | Password: `Password123!`
- **School Admin**: `admin@sdn08pagi.sch.id` | Password: `Password123!`

### Langkah 4: Menjalankan Aplikasi dalam Mode Pengembangan
Buka dua terminal atau gunakan perintah workspace berikut:

**Menjalankan Backend API:**
```bash
npm run dev:api
# API berjalan di http://localhost:3001
# Dokumentasi Swagger dapat diakses di http://localhost:3001/api/docs (jika diaktifkan)
```

**Menjalankan Frontend Web:**
```bash
npm run dev:web
# Frontend berjalan di http://localhost:3000
```

---

## 6. Pengelolaan Database MySQL (Prisma ORM)

Skema Prisma berada di [`apps/api/prisma/schema.prisma`](file:///e:/WebApps/kas-sekolah/apps/api/prisma/schema.prisma).

### Perintah Penting Database:
| Kebutuhan | Perintah Root | Keterangan |
| :--- | :--- | :--- |
| **Generate Client** | `npm run prisma:generate` | Memperbarui TypeScript types Prisma |
| **Push Skema** | `npm run prisma:push` | Sinkronisasi skema ke MySQL tanpa migration file |
| **Migrasi Baru** | `npm run prisma:migrate` | Membuat riwayat file migrasi SQL |
| **Prisma Studio** | `npm run prisma:studio` | Membuka UI web untuk melihat/mengedit isi tabel |
| **Seed Data** | `npm run seed:admin` | Menambahkan data awal administrator |

---

## 7. Menjalankan Aplikasi dengan Docker

Jika ingin menjalankan service database MySQL dan Redis secara otomatis via Docker:

1. Masuk ke direktori docker:
   ```bash
   cd docker
   ```
2. Jalankan container:
   ```bash
   docker-compose up -d
   ```
3. Cek status container:
   ```bash
   docker-compose ps
   ```
4. Menghentikan container:
   ```bash
   docker-compose down
   ```

Data database akan tersimpan persisten di volume docker `mysql_data`.

---

## 8. Panduan Deployment ke Server Produksi (VPS/Linux)

Berikut adalah panduan standar deployment ke server Linux Ubuntu 22.04 / 24.04:

### 1. Persiapan Server
```bash
# Update paket sistem
sudo apt update && sudo apt upgrade -y

# Instal Node.js 20 LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs git nginx

# Instal Process Manager PM2
sudo npm install -g pm2
```

### 2. Kloning & Build Proyek
```bash
# Tempatkan kode di /var/www/kas-sekolah
cd /var/www
sudo git clone <URL_REPO_ANDA> kas-sekolah
cd kas-sekolah

# Instal dependencies
npm install

# Setup environment backend
cp apps/api/.env.example apps/api/.env
nano apps/api/.env   # Sesuaikan DATABASE_URL dan JWT_SECRET produksi

# Push skema & generate client
npm run prisma:generate
npm run prisma:push
npm run seed:admin

# Build aplikasi Backend dan Frontend
npm run build:api
npm run build:web
```

### 3. Konfigurasi Process Manager (PM2)
Buat file `ecosystem.config.js` di root folder:
```javascript
module.exports = {
  apps: [
    {
      name: 'kas-sekolah-api',
      cwd: './apps/api',
      script: 'dist/main.js',
      env: {
        NODE_ENV: 'production',
        PORT: 3001
      }
    },
    {
      name: 'kas-sekolah-web',
      cwd: './apps/web',
      script: 'node_modules/next/dist/bin/next',
      args: 'start -p 3000',
      env: {
        NODE_ENV: 'production'
      }
    }
  ]
};
```

Jalankan service:
```bash
pm2 start ecosystem.config.js
pm2 save
pm2 startup
```

### 4. Konfigurasi Nginx Reverse Proxy
Buat file virtual host di `/etc/nginx/sites-available/kas-sekolah`:
```nginx
server {
    listen 80;
    server_name kas.sekolahanda.sch.id;

    # Frontend Next.js
    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }

    # Backend API NestJS
    location /api/ {
        proxy_pass http://localhost:3001/;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Aktifkan konfigurasi dan reload Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/kas-sekolah /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 5. Pasang SSL Gratis (Let's Encrypt / Certbot)
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d kas.sekolahanda.sch.id
```

---

## 9. Troubleshooting & Pemeliharaan

1. **Error: `Can't reach database server at localhost:3306`**
   - Pastikan service MySQL sedang aktif: `sudo systemctl status mysql` (Linux) atau via Services Windows.
   - Pastikan user `root` memiliki akses lokal dan password sesuai pada `.env`.

2. **Error: `mode: 'insensitive'` pada Prisma Query**
   - MySQL secara bawaan sudah case-insensitive untuk collation `utf8mb4_unicode_ci` / `utf8mb4_general_ci`.
   - Jangan gunakan parameter `mode: 'insensitive'` di Prisma model MySQL.

3. **Backup Database MySQL Harian**
   Jalankan perintah backup berkala via cron job:
   ```bash
   mysqldump -u root -p kas-sekolah > /backup/kas_sekolah_$(date +\%F).sql
   ```
