<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panduan Pengguna (User Guide) - Sistem Kas Sekolah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased" x-data="userGuideApp()" x-init="initGuide()">

    <!-- Header Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm backdrop-blur-md bg-white/90">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="/" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white flex items-center justify-center shadow-md group-hover:scale-105 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 block">Buku Petunjuk Resmi</span>
                        <h1 class="text-base font-extrabold text-slate-900 leading-tight">User Guide Kas Sekolah</h1>
                    </div>
                </a>
            </div>

            <div class="flex items-center space-x-3">
                <a href="/" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-500/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span>Kembali ke Aplikasi</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white py-12 px-4 sm:px-6 lg:px-8 border-b border-indigo-950">
        <div class="max-w-5xl mx-auto text-center">
            <span class="inline-block px-3 py-1 bg-indigo-500/20 border border-indigo-400/30 rounded-full text-indigo-300 text-xs font-semibold uppercase tracking-wider mb-3">
                Dokumentasi & Panduan Operasional
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Panduan Penggunaan Sistem Kas Sekolah</h2>
            <p class="mt-3 text-slate-300 text-sm sm:text-base max-w-2xl mx-auto">
                Pelajari cara pengelolaan kas sekolah, penerbitan tagihan iuran, pencatatan mutasi kas, hingga transparansi laporan keuangan sesuai dengan peran hak akses Anda.
            </p>

            <!-- Search / Filter bar -->
            <div class="mt-6 max-w-md mx-auto">
                <div class="relative">
                    <input type="text" x-model="searchQuery" placeholder="Cari panduan (contoh: buat tagihan, catat kas, korlas)..." class="w-full pl-10 pr-4 py-3 rounded-2xl bg-white/10 border border-white/20 text-white placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white/20 backdrop-blur-md">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Container with Role Filter Tabs -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Role Tabs -->
        <div class="flex items-center justify-start sm:justify-center overflow-x-auto pb-4 gap-2 no-scrollbar">
            <button @click="activeRole = 'ALL'" :class="activeRole === 'ALL' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2.5 rounded-2xl font-bold text-xs whitespace-nowrap transition flex items-center gap-1.5">
                <span>📚 Semua Peran</span>
            </button>
            <button @click="activeRole = 'SUPER_ADMIN'" :class="activeRole === 'SUPER_ADMIN' ? 'bg-purple-600 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2.5 rounded-2xl font-bold text-xs whitespace-nowrap transition flex items-center gap-1.5">
                <span>👑 System Admin</span>
            </button>
            <button @click="activeRole = 'ADMIN'" :class="activeRole === 'ADMIN' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2.5 rounded-2xl font-bold text-xs whitespace-nowrap transition flex items-center gap-1.5">
                <span>🏢 Admin Sekolah</span>
            </button>
            <button @click="activeRole = 'TREASURER'" :class="activeRole === 'TREASURER' ? 'bg-emerald-600 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2.5 rounded-2xl font-bold text-xs whitespace-nowrap transition flex items-center gap-1.5">
                <span>💰 Bendahara Kelas</span>
            </button>
            <button @click="activeRole = 'KORLAS'" :class="activeRole === 'KORLAS' ? 'bg-violet-600 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2.5 rounded-2xl font-bold text-xs whitespace-nowrap transition flex items-center gap-1.5">
                <span>👥 Korlas (Koordinator Kelas)</span>
            </button>
            <button @click="activeRole = 'STUDENT_PARENT'" :class="activeRole === 'STUDENT_PARENT' ? 'bg-sky-600 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2.5 rounded-2xl font-bold text-xs whitespace-nowrap transition flex items-center gap-1.5">
                <span>🎓 Siswa & Wali Murid</span>
            </button>
        </div>

        <!-- Guide Content Sections -->
        <div class="space-y-8 mt-4">

            <!-- 1. OVERVIEW & ARSITEKTUR SISTEM -->
            <section x-show="matchesRole('ALL') && matchesQuery('arsitektur alur sistem pengenalan')" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">1</span>
                    <h3 class="text-xl font-bold text-slate-900">Pengenalan & Alur Utama Sistem Kas Sekolah</h3>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed mb-6">
                    Aplikasi <strong>Sistem Kas Sekolah</strong> dirancang untuk mewujudkan akuntabilitas dan transparansi mutlak dalam pengelolaan keuangan kelas dan sekolah. Sistem ini menjembatani pihak sekolah, bendahara kelas, korlas, dan orang tua murid secara real-time.
                </p>

                <!-- Flow Diagram Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100">
                        <span class="text-xs font-bold text-indigo-700 uppercase block mb-1">Tahap 1: Setup</span>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Inisialisasi Kelas</h4>
                        <p class="text-xs text-slate-500">Admin menyiapkan Tahun Ajaran, Kelas, Siswa, dan Bendahara. Rekening Buku Kas kelas otomatis tercipta.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-100">
                        <span class="text-xs font-bold text-emerald-700 uppercase block mb-1">Tahap 2: Tagihan</span>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Penerbitan Iuran</h4>
                        <p class="text-xs text-slate-500">Bendahara membuat tagihan (bulanan, kas mingguan, atau kegiatan) serentak ke seluruh siswa kelas.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-sky-50/60 border border-sky-100">
                        <span class="text-xs font-bold text-sky-700 uppercase block mb-1">Tahap 3: Pembayaran</span>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Pencatatan Masuk</h4>
                        <p class="text-xs text-slate-500">Iuran yang dibayarkan siswa dicatat oleh bendahara, otomatis menambah saldo buku kas kelas saat itu juga.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-100">
                        <span class="text-xs font-bold text-amber-700 uppercase block mb-1">Tahap 4: Transparansi</span>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Laporan & Bukti</h4>
                        <p class="text-xs text-slate-500">Korlas dan Wali Murid dapat melihat mutasi kas masuk/keluar, lampiran kuitansi, dan mencetak rekap keuangan.</p>
                    </div>
                </div>
            </section>

            <!-- 2. PANDUAN SYSTEM ADMIN (SUPER ADMIN) -->
            <section x-show="matchesRole('SUPER_ADMIN') && matchesQuery('super admin system multi sekolah tambah kode')" class="bg-white rounded-3xl p-6 sm:p-8 border border-purple-200 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-2.5 py-1 rounded-xl bg-purple-100 text-purple-800 font-bold text-xs uppercase">👑 Peran: SUPER ADMIN</span>
                    <h3 class="text-xl font-bold text-slate-900">Panduan Administrator Sistem (Multi-Tenant)</h3>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed mb-6">
                    Super Admin memiliki kewenangan tertinggi untuk mengelola seluruh entitas sekolah dalam platform multi-sekolah.
                </p>

                <div class="space-y-4">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <h4 class="font-bold text-sm text-slate-800 mb-1 flex items-center gap-2">
                            <span class="text-purple-600 font-extrabold">A.</span> Berpindah Antar-Sekolah (School Switcher)
                        </h4>
                        <p class="text-xs text-slate-600">
                            Di bagian atas navbar header terdapat dropdown <strong>"Pilih Sekolah"</strong>. Super Admin dapat berpindah melihat data SD, SMP, ataupun SMA secara instan tanpa perlu logout.
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <h4 class="font-bold text-sm text-slate-800 mb-1 flex items-center gap-2">
                            <span class="text-purple-600 font-extrabold">B.</span> Menambahkan Sekolah Baru
                        </h4>
                        <ol class="list-decimal list-inside text-xs text-slate-600 space-y-1">
                            <li>Klik tombol ungu <strong>"+ Sekolah"</strong> pada header.</li>
                            <li>Isi Nama Sekolah (misal: <em>SMA Negeri 55 Jakarta</em>) dan Kode Sekolah unik (misal: <em>SMAN55JKT</em>).</li>
                            <li>Sistem otomatis membuat tenant terisolasi yang siap digunakan oleh Admin sekolah tersebut.</li>
                        </ol>
                    </div>
                </div>
            </section>

            <!-- 3. PANDUAN ADMIN SEKOLAH -->
            <section x-show="matchesRole('ADMIN') && matchesQuery('admin sekolah tahun ajaran kelas siswa guru staf')" class="bg-white rounded-3xl p-6 sm:p-8 border border-indigo-200 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-2.5 py-1 rounded-xl bg-indigo-100 text-indigo-800 font-bold text-xs uppercase">🏢 Peran: ADMIN SEKOLAH</span>
                    <h3 class="text-xl font-bold text-slate-900">Panduan Administrator Sekolah</h3>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed mb-6">
                    Admin Sekolah bertugas mengatur data master seperti Tahun Pelajaran, Data Kelas, Pendaftaran Siswa, dan Akun Bendahara/Korlas.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <h4 class="font-bold text-sm text-slate-800 mb-1">1. Mengatur Tahun Pelajaran</h4>
                        <p class="text-xs text-slate-600 mb-2">Semua transaksi dan tagihan terikat pada Tahun Ajaran yang aktif.</p>
                        <p class="text-xs text-slate-500">Klik tombol <strong>"+ Tahun Ajaran"</strong> di tab Kelas/Akademik, masukkan format tahun seperti <em>2026/2027</em>, dan centang "Jadikan Tahun Aktif".</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <h4 class="font-bold text-sm text-slate-800 mb-1">2. Membuat Rombel / Kelas</h4>
                        <p class="text-xs text-slate-600 mb-2">Setiap kelas baru otomatis memiliki Rekening Kas tersendiri.</p>
                        <p class="text-xs text-slate-500">Klik tombol <strong>"+ Kelas Baru"</strong>, beri nama kelas (contoh: <em>Kelas 5-A</em>). Rekening kas utama kelas akan langsung dibuat dengan saldo awal Rp 0.</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <h4 class="font-bold text-sm text-slate-800 mb-1">3. Mendaftarkan Siswa Baru</h4>
                        <p class="text-xs text-slate-600 mb-2">Mendaftarkan siswa lengkap dengan identitas sekolah.</p>
                        <p class="text-xs text-slate-500">Buka tab <strong>"Siswa & Anggota"</strong> $\rightarrow$ klik <strong>"+ Tambah Siswa"</strong>. Isi Nama Lengkap, NIS, NISN, Jenis Kelamin, dan pilih Kelas yang dituju.</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <h4 class="font-bold text-sm text-slate-800 mb-1">4. Menugaskan Bendahara & Korlas</h4>
                        <p class="text-xs text-slate-600 mb-2">Membuat akun login bagi petugas kelas.</p>
                        <p class="text-xs text-slate-500">Klik tombol <strong>"+ Akun Petugas"</strong>, masukkan Nama, Email, dan pilih peran <em>Bendahara (TREASURER)</em> atau <em>Korlas (KORLAS)</em>.</p>
                    </div>
                </div>
            </section>

            <!-- 4. PANDUAN BENDAHARA KELAS -->
            <section x-show="matchesRole('TREASURER') && matchesQuery('bendahara tagihan iuran kas bayar catat pengeluaran mutasi')" class="bg-white rounded-3xl p-6 sm:p-8 border border-emerald-200 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-800 font-bold text-xs uppercase">💰 Peran: BENDAHARA KELAS</span>
                    <h3 class="text-xl font-bold text-slate-900">Panduan Operasional Bendahara Kelas</h3>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed mb-6">
                    Bendahara memegang peranan kunci dalam mengelola arus uang kas: menerbitkan tagihan, mencatat pembayaran iuran siswa, mencatat kas masuk non-iuran, dan membukukan pengeluaran kas kelas.
                </p>

                <div class="space-y-4">
                    <!-- Step 1: Tagihan -->
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">1</span>
                            <h4 class="font-bold text-sm text-slate-800">Cara Membuat Tagihan Iuran Baru</h4>
                        </div>
                        <ol class="list-decimal list-inside text-xs text-slate-600 space-y-1.5 ml-1">
                            <li>Buka tab <strong>"Tagihan Iuran"</strong> di dashboard.</li>
                            <li>Klik tombol hijau <strong>"+ Buat Tagihan Iuran"</strong>.</li>
                            <li>Isi <strong>Judul Tagihan</strong> (misal: <em>Iuran Kas Bulan Oktober</em> atau <em>Iuran Renang Kelas 5</em>).</li>
                            <li>Tentukan <strong>Nominal Iuran</strong> (contoh: <em>25000</em>).</li>
                            <li>Pilih <strong>Batas Waktu Pembayaran (Jatuh Tempo)</strong>.</li>
                            <li>Klik <strong>"Simpan & Terbitkan Tagihan"</strong>. Sistem otomatis mendistribusikan tagihan ke seluruh siswa di kelas tersebut.</li>
                        </ol>
                    </div>

                    <!-- Step 2: Bayar Iuran -->
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">2</span>
                            <h4 class="font-bold text-sm text-slate-800">Cara Mencatat Pembayaran Iuran Siswa</h4>
                        </div>
                        <ol class="list-decimal list-inside text-xs text-slate-600 space-y-1.5 ml-1">
                            <li>Di tabel daftar tagihan iuran, cari nama siswa yang membayar.</li>
                            <li>Siswa yang belum lunas memiliki badge merah <strong>"Menunggu Pembayaran"</strong> dan tombol <strong>"Bayar"</strong>.</li>
                            <li>Klik tombol <strong>"Bayar"</strong> $\rightarrow$ Pilih metode bayar (Tunai / Transfer Bank).</li>
                            <li>Klik <strong>"Konfirmasi Pembayaran"</strong>.</li>
                            <li>Status tagihan otomatis berubah menjadi <span class="text-emerald-700 font-bold">LUNAS</span> dan <strong>saldo kas kelas otomatis bertambah seketika</strong>!</li>
                        </ol>
                    </div>

                    <!-- Step 3: Kas Masuk Langsung -->
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">3</span>
                            <h4 class="font-bold text-sm text-slate-800">Mencatat Kas Masuk Non-Iuran (Donasi / Sisa Kas Lalu)</h4>
                        </div>
                        <p class="text-xs text-slate-600 mb-2">Untuk uang kas masuk di luar iuran siswa:</p>
                        <p class="text-xs text-slate-600">Klik tombol <strong>"+ Catat Kas Masuk"</strong> di tab Buku Kas/Transaksi $\rightarrow$ Isi Nominal $\rightarrow$ Tuliskan Keterangan (contoh: <em>Donasi orang tua murid untuk lomba kebersihan</em>) $\rightarrow$ Simpan.</p>
                    </div>

                    <!-- Step 4: Pengeluaran -->
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs font-bold">4</span>
                            <h4 class="font-bold text-sm text-slate-800">Mencatat Pengeluaran Uang Kas</h4>
                        </div>
                        <ol class="list-decimal list-inside text-xs text-slate-600 space-y-1.5 ml-1">
                            <li>Klik tombol merah <strong>"- Catat Pengeluaran"</strong>.</li>
                            <li>Masukkan nominal uang yang dibelanjakan.</li>
                            <li>Ketik deskripsi pengeluaran dengan rinci (misal: <em>Beli spidol whiteboard & penghapus kelas</em>).</li>
                            <li>Masukkan nomor nota/kuitansi pembelian sebagai bukti fisik pertanggungjawaban.</li>
                            <li>Klik <strong>"Simpan Pengeluaran"</strong>. Saldo kas kelas akan otomatis berkurang secara akurat.</li>
                        </ol>
                    </div>
                </div>
            </section>

            <!-- 5. PANDUAN KORLAS (KOORDINATOR KELAS) -->
            <section x-show="matchesRole('KORLAS') && matchesQuery('korlas koordinator verifikasi pengawasan paguyuban')" class="bg-white rounded-3xl p-6 sm:p-8 border border-violet-200 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-2.5 py-1 rounded-xl bg-violet-100 text-violet-800 font-bold text-xs uppercase">👥 Peran: KOORDINATOR KELAS (KORLAS)</span>
                    <h3 class="text-xl font-bold text-slate-900">Panduan Koordinator Kelas (Korlas) & Paguyuban</h3>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed mb-6">
                    Korlas berperan sebagai pengawas transparansi perwakilan orang tua murid untuk memastikan uang kas dikelola dengan amanah dan tepat sasaran.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <h4 class="font-bold text-sm text-slate-800 mb-1">🔍 Monitoring Kas Kelas Real-Time</h4>
                        <p class="text-xs text-slate-600">
                            Korlas dapat memantau secara langsung total saldo yang tersisa, total pemasukan, dan total pengeluaran tanpa perlu menunggu laporan manual bulanan dari bendahara.
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <h4 class="font-bold text-sm text-slate-800 mb-1">📊 Cetak Rekapitulasi Kas</h4>
                        <p class="text-xs text-slate-600">
                            Gunakan tombol <strong>"Cetak Laporan Kas"</strong> untuk menghasilkan format PDF atau cetak fisik yang siap dibagikan saat rapat paguyuban kelas.
                        </p>
                    </div>
                </div>
            </section>

            <!-- 6. PANDUAN SISWA & WALI MURID -->
            <section x-show="matchesRole('STUDENT_PARENT') && matchesQuery('siswa wali murid ortu tagihan iuran kuitansi riwayat')" class="bg-white rounded-3xl p-6 sm:p-8 border border-sky-200 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-2.5 py-1 rounded-xl bg-sky-100 text-sky-800 font-bold text-xs uppercase">🎓 Peran: SISWA & WALI MURID</span>
                    <h3 class="text-xl font-bold text-slate-900">Panduan Siswa & Orang Tua / Wali Murid</h3>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed mb-6">
                    Orang tua dan siswa memiliki akses penuh untuk memeriksa status iuran pribadi dan transparansi alokasi dana kas kelas.
                </p>

                <div class="space-y-4">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <h4 class="font-bold text-sm text-slate-800 mb-1">1. Memeriksa Tagihan Iuran Pribadi</h4>
                        <p class="text-xs text-slate-600">
                            Saat masuk ke aplikasi, dashboard akan langsung menampilkan daftar tagihan atas nama anak Anda. Anda dapat melihat tagihan mana yang sudah <strong>LUNAS</strong> dan tagihan yang masih <strong>Belum Dibayar</strong> beserta batas jatuh temponya.
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <h4 class="font-bold text-sm text-slate-800 mb-1">2. Transparansi Pengeluaran Kas Kelas</h4>
                        <p class="text-xs text-slate-600">
                            Orang tua murid dapat melihat tab <strong>"Buku Kas Kelas"</strong> untuk mengetahui dengan jelas uang kas kelas dipakai untuk apa saja (pembelian perlengkapan, jenguk siswa sakit, fotokopi lembar kerja, dll).
                        </p>
                    </div>
                </div>
            </section>

            <!-- 7. FREQUENTLY ASKED QUESTIONS (FAQ) -->
            <section x-show="matchesQuery('faq tanya jawab lupa password kendala')" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm">?</span>
                    <h3 class="text-xl font-bold text-slate-900">Pertanyaan yang Sering Diajukan (FAQ)</h3>
                </div>

                <div class="divide-y divide-slate-100">
                    <div class="py-3">
                        <h4 class="font-bold text-xs sm:text-sm text-slate-800 mb-1">Bagaimana cara login jika lupa email atau password?</h4>
                        <p class="text-xs text-slate-500">Siswa dapat login menggunakan NIS (Nomor Induk Siswa). Jika lupa kata sandi, silakan hubungi Admin Sekolah untuk mereset kata sandi akun Anda.</p>
                    </div>
                    <div class="py-3">
                        <h4 class="font-bold text-xs sm:text-sm text-slate-800 mb-1">Apakah saldo kas kelas bisa bernilai minus (negatif)?</h4>
                        <p class="text-xs text-slate-500">Sistem memvalidasi pengeluaran kas. Bendahara disarankan hanya membukukan pengeluaran yang tidak melampaui saldo kas yang ada demi akuntabilitas pembukuan.</p>
                    </div>
                    <div class="py-3">
                        <h4 class="font-bold text-xs sm:text-sm text-slate-800 mb-1">Bagaimana mencetak laporan keuangan kas?</h4>
                        <p class="text-xs text-slate-500">Buka menu <strong>Buku Kas</strong>, klik tombol <strong>"Cetak Laporan Kas"</strong> di pojok kanan atas tabel transaksi. Sistem akan memunculkan dialog cetak browser (dapat disimpan sebagai PDF atau dicetak langsung ke printer).</p>
                    </div>
                </div>
            </section>

        </div>

        <!-- Footer Note -->
        <footer class="text-center text-xs text-slate-400 mt-12 mb-8">
            &copy; 2026 Sistem Kas Sekolah. Aplikasi Manajemen Kas & Iuran Kelas Terpadu.
        </footer>
    </main>

    <script>
        function userGuideApp() {
            return {
                activeRole: 'ALL',
                searchQuery: '',

                initGuide() {
                    // Read URL query parameter ?role=...
                    const params = new URLSearchParams(window.location.search);
                    const roleParam = params.get('role');
                    if (roleParam) {
                        this.activeRole = roleParam.toUpperCase();
                    }
                },

                matchesRole(roleKey) {
                    if (this.activeRole === 'ALL') return true;
                    if (this.activeRole === roleKey) return true;
                    if (this.activeRole === 'STUDENT' && roleKey === 'STUDENT_PARENT') return true;
                    if (this.activeRole === 'PARENT' && roleKey === 'STUDENT_PARENT') return true;
                    return false;
                },

                matchesQuery(keywords) {
                    if (!this.searchQuery.trim()) return true;
                    const query = this.searchQuery.toLowerCase();
                    return keywords.toLowerCase().includes(query) || 'semua panduan faq bantuan'.includes(query);
                }
            };
        }
    </script>
</body>
</html>
