# Kas Sekolah API (Backend Service)

Layanan backend REST API untuk Sistem Kas Sekolah yang dibangun dengan **NestJS**, **Prisma ORM**, **MySQL 8.0**, dan **Redis**.

---

## 🛠️ Konfigurasi Database MySQL
Backend menggunakan MySQL 8.0 sebagai database utama. Konfigurasi tersimpan di [`.env`](file:///e:/WebApps/kas-sekolah/apps/api/.env):
```env
DATABASE_URL="mysql://root:long2go@localhost:3306/kas-sekolah"
```

### Perintah Pengelolaan Prisma
```bash
# Generate Prisma Client
npm run prisma:generate

# Push skema tabel ke database MySQL
npm run prisma:push

# Migrasi skema database
npm run prisma:migrate

# Buka antarmuka grafis Prisma Studio
npm run prisma:studio

# Seeding akun admin awal
npm run seed:admin
```

---

## 🚀 Menjalankan Server API

```bash
# Mode pengembangan (Watch mode)
npm run start:dev

# Mode produksi
npm run build
npm run start:prod
```

Server API berjalan secara default pada port `3001`.

---

## 📖 Dokumentasi Lengkap
- **Panduan Teknis & Deployment**: [docs/TECHNICAL_GUIDE.md](file:///e:/WebApps/kas-sekolah/docs/TECHNICAL_GUIDE.md)
- **Panduan Pengguna (User Guide)**: [docs/USER_GUIDE.md](file:///e:/WebApps/kas-sekolah/docs/USER_GUIDE.md)
