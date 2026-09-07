# Sistem Kas Sekolah (Multi-Tenant School Treasury System)

Platform manajemen keuangan kas sekolah terpadu dan transparan berbasis **Next.js**, **NestJS**, **MySQL**, dan **Redis**.

---

## 📚 Buku Panduan (Documentation)

Silakan merujuk ke panduan resmi berikut sesuai kebutuhan:

1. 🛠️ **[Panduan Teknis & Instalasi (Technical Guide)](file:///e:/WebApps/kas-sekolah/docs/TECHNICAL_GUIDE.md)**
   - Prasyarat sistem (Node.js 20+, MySQL 8.0, Redis 7)
   - Setup variabel lingkungan (`.env`)
   - Pengelolaan database MySQL dengan Prisma ORM
   - Konfigurasi Docker & Docker Compose
   - Panduan Deployment ke server produksi (VPS, Nginx, PM2, SSL)

2. 📖 **[Buku Panduan Pengguna (User Guide)](file:///e:/WebApps/kas-sekolah/docs/USER_GUIDE.md)**
   - Penjelasan hak akses peran (Super Admin, Admin Sekolah, Bendahara, Korlas, Siswa, Wali Murid)
   - Akun demo & cara login pertama kali
   - Alur pembuatan skema iuran & penagihan kas siswa
   - Pencatatan transaksi kas masuk & pengeluaran (expense)
   - Sistem persetujuan (approval) pengeluaran
   - Ekspor dan cetak laporan keuangan resmi berformat PDF

---

## 🚀 Memulai Cepat (Quick Start)

### 1. Instalasi Dependensi
```bash
npm install
```

### 2. Setup Database MySQL
Pastikan server MySQL berjalan di port `3306` dan konfigurasi [apps/api/.env](file:///e:/WebApps/kas-sekolah/apps/api/.env) sudah sesuai:
```env
DATABASE_URL="mysql://root:password@localhost:3306/kas-sekolah"
```

Sinkronkan database dan buat akun administrator awal:
```bash
npm run prisma:generate
npm run prisma:push
npm run seed:admin
```

### 3. Menjalankan Aplikasi
```bash
# Menjalankan Backend API (Port 3001)
npm run dev:api

# Menjalankan Frontend Web (Port 3000)
npm run dev:web
```

Buka peramban ke **http://localhost:3000** untuk mengakses aplikasi.
