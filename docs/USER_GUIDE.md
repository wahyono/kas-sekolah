# Buku Panduan Pengguna (User Guide) - Sistem Kas Sekolah

Panduan praktis pengoperasian aplikasi **Sistem Kas Sekolah** untuk setiap peran pengguna (Super Admin, Admin Sekolah, Bendahara Kelas, Korlas, Siswa, dan Wali Murid).

---

## Daftar Isi
1. [Pengenalan Aplikasi](#1-pengenalan-aplikasi)
2. [Tingkatan Hak Akses (User Roles)](#2-tingkatan-hak-akses-user-roles)
3. [Panduan Masuk (Login) & Akun Demo](#3-panduan-masuk-login--akun-demo)
4. [Panduan untuk Super Admin (System Admin)](#4-panduan-untuk-super-admin-system-admin)
5. [Panduan untuk Admin Sekolah](#5-panduan-untuk-admin-sekolah)
6. [Panduan untuk Bendahara Kelas](#6-panduan-untuk-bendahara-kelas)
7. [Panduan untuk Koordinator Kelas (Korlas)](#7-panduan-untuk-koordinator-kelas-korlas)
8. [Panduan untuk Siswa & Wali Murid](#8-panduan-untuk-siswa--wali-murid)
9. [Fitur Ekspor Laporan Keuangan (PDF)](#9-fitur-ekspor-laporan-keuangan-pdf)
10. [Tanya Jawab Umum (FAQ)](#10-tanya-jawab-umum-faq)

---

## 1. Pengenalan Aplikasi

**Sistem Kas Sekolah** adalah platform pencatatan keuangan dan iuran kas sekolah terpadu yang mendukung **multi-sekolah** dan **multi-kelas**. Aplikasi ini dirancang untuk menciptakan transparansi antara pihak sekolah, pengurus kelas (Korlas & Bendahara), serta para siswa dan orang tua / wali murid.

### Fitur Utama:
- **Dashboard Ringkasan Keuangan**: Pantau saldo total, total kas masuk, pengeluaran, dan tagihan tertunda secara real-time.
- **Manajemen Iuran & Tagihan**: Membuat skema iuran bulanan atau insidental kegiatan serta generate tagihan massal untuk siswa.
- **Pencatatan Transaksi & Kuitansi Digital**: Catat kas masuk dan kas keluar lengkap dengan upload bukti kuitansi.
- **Sistem Persetujuan (Approval) Pengeluaran**: Pengeluaran kas harus disetujui terlebih dahulu demi akuntabilitas.
- **Ekspor Laporan PDF Resmi**: Buat laporan kas yang rapi siap cetak untuk rapat orang tua murid atau arsip sekolah.

---

## 2. Tingkatan Hak Akses (User Roles)

Aplikasi memiliki 6 peran pengguna dengan wewenang masing-masing:

| Peran | Nama Peran | Hak Akses Utama |
| :--- | :--- | :--- |
| **SUPER_ADMIN** | System Admin | Mengelola seluruh sekolah, konfigurasi sistem global, dan memantau seluruh transaksi lintas sekolah. |
| **ADMIN** | Admin Sekolah | Mengelola data tahun ajaran, kelas, dan akun seluruh civitas di sekolah yang bersangkutan. |
| **TREASURER** | Bendahara Kelas | Membuat skema iuran, mencatat pembayaran kas, mencatat pengeluaran kas, dan mengelola saldo kas kelas. |
| **KORLAS** | Koordinator Kelas | Memantau kas kelas yang dikelola, meninjau dan menyetujui (approve) pengajuan pengeluaran kas. |
| **STUDENT** | Siswa | Melihat profil, daftar tagihan kas pribadi, riwayat pembayaran, serta bukti pembayaran. |
| **PARENT** | Wali Murid | Memantau tagihan anak, riwayat pembayaran kas anak, dan transparansi arus kas kelas. |

---

## 3. Panduan Masuk (Login) & Akun Demo

1. Buka browser dan arahkan ke alamat web: `http://localhost:3000` (atau domain sekolah Anda).
2. Di halaman login, Anda dapat memasukkan **Email** dan **Password**.
3. Untuk keperluan uji coba atau demo, tersedia tombol pintas login instan:
   - 👑 **0. System Admin**: `sysadmin@sistemkas.com` | Password: `Password123!`
   - 🏢 **1. Admin Sekolah**: `admin@sdn08pagi.sch.id` | Password: `Password123!`
   - 💰 **2. Bendahara Kelas**: `bendahara@sdn08pagi.sch.id` | Password: `Password123!`
   - 👥 **3. Korlas 5-A**: `korlas5a@sdn08pagi.sch.id` | Password: `Password123!`
   - 🎓 **4. Siswa (Andi)**: `andi@sdn08pagi.sch.id` | Password: `Password123!`
   - 👨‍👩‍👧 **5. Wali Murid**: `ortu.andi@sdn08pagi.sch.id` | Password: `Password123!`

---

## 4. Panduan untuk Super Admin (System Admin)

Super Admin bertanggung jawab atas pengelolaan lintas sekolah:
1. **Memilih & Mengganti Sekolah Aktif**:
   - Di navbar bagian atas, gunakan dropdown **"Pilih Sekolah"** untuk beralih antar unit sekolah (contoh: SD Negeri 08 Pagi, SMP Negeri 1 Jakarta, dll.).
2. **Menambah Unit Sekolah Baru**:
   - Buka menu pengaturan sekolah, masukkan nama sekolah, kode unik (contoh: `SDN08PAGI`), alamat, serta nomor telepon.
3. **Audit Log Global**:
   - Pantau aktivitas penting sistem seperti penghapusan akun atau perubahan saldo kas penting.

---

## 5. Panduan untuk Admin Sekolah

Admin Sekolah berwenang mengelola data master pada sekolah yang aktif:
1. **Pengaturan Tahun Ajaran & Kelas**:
   - Menentukan tahun ajaran aktif (misal: `2026/2027`).
   - Mendaftarkan kelas-kelas baru (misal: `Kelas 5-A`, `Kelas 5-B`).
2. **Manajemen Pengguna (User Management)**:
   - Tambah data siswa secara mandiri atau gunakan form input siswa.
   - Tetapkan akun **Korlas** dan tautkan ke kelas yang dibina (*Managed Class*).
   - Tetapkan akun **Bendahara** dan buatkan buku kas kelas default.

---

## 6. Panduan untuk Bendahara Kelas

Bendahara memegang peranan operasional harian kas:

### A. Membuat Skema Iuran Baru
1. Buka tab **"Tagihan & Iuran"**.
2. Klik tombol **"+ Buat Skema Iuran Baru"**.
3. Isi rincian:
   - **Judul Iuran**: contoh *"Iuran Kas Bulanan September 2026"* atau *"Uang Kas Kunjungan Museum"*.
   - **Nominal (Rp)**: contoh `20000`.
   - **Jatuh Tempo**: Tentukan tanggal batas pembayaran.
   - **Target Siswa**: Pilih semua siswa kelas atau pilih siswa tertentu.
4. Klik **"Simpan & Buat Tagihan"**. Tagihan akan langsung muncul di akun masing-masing siswa dan wali murid.

### B. Mencatat Pembayaran Kas dari Siswa
1. Ketika siswa membayar secara tunai atau transfer, buka tab **"Tagihan & Iuran"**.
2. Cari nama siswa pada daftar tagihan.
3. Klik tombol **"Catat Bayar"**.
4. Masukkan nominal yang disetorkan (bisa lunas atau cicilan bertahap).
5. Status tagihan akan otomatis terbarui menjadi `LUNAS` (PAID) atau `SEBAGIAN` (PARTIAL), dan saldo kas kelas akan otomatis bertambah.

### C. Mencatat Pengeluaran Kas (Expense)
1. Buka tab **"Transaksi Kas"** lalu pilih **"Pengeluaran"**.
2. Klik **"+ Catat Pengeluaran"**.
3. Masukkan:
   - Judul pengeluaran (contoh: *Beli Spidol & Penghapus Papan Tulis*).
   - Nominal pengeluaran (contoh: `45000`).
   - Kategori & keterangan pengeluaran.
   - Unggah atau masukkan tautan bukti foto/kuitansi.
4. Klik **"Ajukan Pengeluaran"**. Status awal akan berada dalam posisi `PENDING` menunggu persetujuan Korlas/Admin.

---

## 7. Panduan untuk Koordinator Kelas (Korlas)

Korlas bertindak sebagai pengawas dan perwakilan orang tua di kelas:
1. **Memantau Arus Kas Kelas**:
   - Korlas memiliki akses langsung melihat seluruh kas masuk dan saldo kas terkini di kelas binaannya.
2. **Persetujuan Pengeluaran (Expense Approval)**:
   - Masuk ke tab **"Pengeluaran"**.
   - Pengeluaran yang diajukan oleh Bendahara akan menampilkan tombol **"Setujui" (Approve)** atau **"Tolak" (Reject)**.
   - Setelah disetujui oleh Korlas, dana pengeluaran resmi memotong saldo buku kas kelas.

---

## 8. Panduan untuk Siswa & Wali Murid

Siswa dan orang tua dapat memantau kewajiban keuangan secara transparan:
1. **Memeriksa Status Tagihan**:
   - Di dashboard utama, lihat badge status tagihan:
     - 🟡 **PENDING**: Belum dibayar.
     - 🔵 **PARTIAL**: Telah dibayar sebagian (masih ada sisa).
     - 🟢 **PAID**: Lunas.
     - 🔴 **OVERDUE**: Melewati tanggal jatuh tempo.
2. **Melihat Riwayat Setoran**:
   - Buka tab riwayat untuk melihat kuitansi pembayaran dan tanggal setoran yang telah diverifikasi oleh bendahara.

---

## 9. Fitur Ekspor Laporan Keuangan (PDF)

Aplikasi dilengkapi generator laporan keuangan resmi berformat PDF:
1. Buka tab **"Laporan"** atau klik tombol **"Cetak Laporan PDF"** di dashboard.
2. Pilih filter:
   - Periode bulan atau semester.
   - Filter kelas tertentu.
3. Klik **"Unduh Laporan (PDF)"**.
4. Dokumen PDF akan otomatis tersusun rapi dengan:
   - Kop surat sekolah resmi.
   - Rincian rekapitulasi saldo kas masuk dan keluar.
   - Tabel tagihan siswa dengan persentase kepatuhan pembayaran.
   - Kolom tanda tangan resmi bendahara dan koordinator kelas.

---

## 10. Tanya Jawab Umum (FAQ)

**T: Bagaimana jika siswa membayar iuran kurang dari nominal penuh?**  
J: Bendahara dapat menginput nominal parsial. Status tagihan akan berubah menjadi `PARTIAL` dan menampilkan sisa tagihan yang belum dilunasi.

**T: Apakah wali murid bisa mengedit atau mengubah data keuangan?**  
J: Tidak. Akun Wali Murid dan Siswa bersifat *Read-Only* untuk memastikan keamanan data keuangan kelas.

**T: Jika salah menginput transaksi pengeluaran, apakah bisa dibatalkan?**  
J: Korlas atau Admin dapat menolak (*Reject*) pengeluaran sebelum disetujui, atau Bendahara dapat mencatat transaksi penyesuaian (*Adjustment*).
