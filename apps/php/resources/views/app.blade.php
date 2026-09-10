<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kas Sekolah - Portal Keuangan Terpadu</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
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
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        @media print {
            body * { visibility: hidden; }
            #printable-report, #printable-report * { visibility: visible; }
            #printable-report { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
        }
        .gradient-button-school {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #ffffff;
        }
        .gradient-button-school:hover {
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
        }
        .badge-school-purple {
            background-color: #f3e8ff;
            color: #6b21a8;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased selection:bg-indigo-500 selection:text-white" x-data="kasApp()" x-init="initApp()" x-cloak>

    <!-- ==================== HALAMAN LOGIN (2-COLUMN AESTHETIC SPLIT) ==================== -->
    <template x-if="!isLoggedIn">
        <div class="min-h-screen bg-slate-900 flex items-center justify-center p-0 lg:p-6 font-sans">
            <div class="w-full max-w-7xl bg-white lg:rounded-3xl border border-slate-200 shadow-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[90vh]">

                <!-- LEFT COLUMN: 60% Aesthetic School Media Showcase -->
                <div class="lg:col-span-7 relative bg-slate-950 flex flex-col justify-between p-8 lg:p-12 text-white overflow-hidden min-h-[420px]">
                    <!-- Background Media (Image or Video) -->
                    <template x-if="heroMediaType === 'VIDEO'">
                        <video autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover opacity-60 scale-105" :src="heroMediaUrl"></video>
                    </template>
                    <template x-if="heroMediaType !== 'VIDEO'">
                        <img :src="heroMediaUrl" alt="School Showcase" class="absolute inset-0 w-full h-full object-cover opacity-65 scale-105 transition-all duration-700 hover:scale-100" />
                    </template>

                    <!-- Dark Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-indigo-950/30"></div>

                    <!-- Top Brand Header -->
                    <div class="relative z-10 flex items-center space-x-3">
                        <div class="h-12 w-12 rounded-2xl bg-indigo-600/90 backdrop-blur-md border border-white/20 flex items-center justify-center font-black text-2xl shadow-xl">
                            🎒
                        </div>
                        <div>
                            <span class="text-xs font-bold uppercase tracking-widest text-indigo-300">Portal Keuangan</span>
                            <h2 class="text-xl font-black text-white">Multi-School Kas Terpadu</h2>
                        </div>
                    </div>

                    <!-- Middle Welcome Hero Content -->
                    <div class="relative z-10 space-y-4 my-auto py-10">
                        <span class="inline-block px-4 py-1.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 backdrop-blur-md">
                            ✨ Transparan, Akuntabel & Terpercaya
                        </span>
                        <h1 class="text-3xl lg:text-4xl font-black leading-tight text-white">
                            Sistem Keuangan Kas Sekolah & Portal Siswa Cerdas 📚
                        </h1>
                        <p class="text-sm text-slate-300 max-w-lg leading-relaxed font-medium">
                            Solusi pencatatan iuran kas, mutasi terkategori, penjadwalan otomatis per kelas, dan transparansi laporan bukti setoran secara real-time.
                        </p>
                    </div>

                    <!-- Bottom Feature Badges -->
                    <div class="relative z-10 grid grid-cols-3 gap-3 pt-6 border-t border-white/10 text-xs">
                        <div class="bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/10">
                            <span class="text-lg">📌</span>
                            <p class="font-bold text-white mt-1">Multi-Tenancy Korlas</p>
                            <p class="text-[10px] text-slate-300">Isolasi data per kelas</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/10">
                            <span class="text-lg">📊</span>
                            <p class="font-bold text-white mt-1">Buku Kas & Mutasi</p>
                            <p class="text-[10px] text-slate-300">Audit trail tercatat rapi</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/10">
                            <span class="text-lg">🔔</span>
                            <p class="font-bold text-white mt-1">Notifikasi Tagihan</p>
                            <p class="text-[10px] text-slate-300">Broadcast jadwal iuran</p>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: 40% Manual Credentials Login Form -->
                <div class="lg:col-span-5 bg-white p-8 lg:p-12 flex flex-col justify-center">
                    <div class="w-full max-w-md mx-auto">
                        <div class="mb-8">
                            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Masuk ke Akun</h2>
                            <p class="text-xs text-slate-500 mt-1.5 font-medium">Masukkan email, username, atau NIS beserta password Anda untuk mengakses portal.</p>
                        </div>

                        <!-- Manual Login Form -->
                        <form @submit.prevent="handleLogin()" class="space-y-4">
                            <div x-show="loginError" class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2" x-cloak>
                                <svg class="w-4 h-4 flex-shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span x-text="loginError"></span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Username / Email / NIS</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </span>
                                    <input type="text" x-model="loginEmail" required autofocus class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-200 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none text-sm transition bg-slate-50/50" placeholder="nama@sekolah.sch.id / NIS">
                                </div>
                            </div>

                            <div x-data="{ showPassword: false }">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </span>
                                    <input :type="showPassword ? 'text' : 'password'" x-model="loginPassword" required class="w-full pl-10 pr-10 py-3 rounded-2xl border border-slate-200 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none text-sm transition bg-slate-50/50" placeholder="••••••••">
                                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 transition">
                                        <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Random City Security Filter (Anti-Bot & Access Challenge) -->
                            <div class="p-3.5 rounded-2xl bg-indigo-50/70 border border-indigo-100 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-indigo-900 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        Verifikasi Keamanan
                                    </span>
                                    <button type="button" @click="loadSecurityChallenge()" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1 transition" title="Ganti Nama Kota">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        Acak Kota
                                    </button>
                                </div>
                                <p class="text-[11px] text-slate-600">
                                    Ketik nama kota berikut untuk melanjutkan:
                                    <span class="inline-block px-2.5 py-0.5 rounded-lg bg-indigo-600 text-white font-black tracking-wide text-xs select-none shadow-xs" x-text="securityCity || 'Memuat...'"></span>
                                </p>
                                <input type="text" x-model="inputSecurityCity" required class="w-full px-3.5 py-2.5 rounded-xl border border-indigo-200 bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none text-xs font-semibold placeholder:text-slate-400" :placeholder="'Ketik nama kota: ' + (securityCity || '')">
                            </div>

                            <button type="submit" :disabled="isSubmitting" class="w-full py-3.5 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:scale-[0.99] text-white font-bold text-sm shadow-xl shadow-indigo-600/25 transition disabled:opacity-50 flex items-center justify-center gap-2">
                                <svg x-show="isSubmitting" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" x-cloak><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span x-show="!isSubmitting">Masuk ke Portal</span>
                                <span x-show="isSubmitting">Memverifikasi...</span>
                            </button>
                        </form>

                        <p class="text-center text-xs text-slate-400 mt-8">
                            &copy; 2026 Sistem Kas Sekolah. Seluruh hak cipta dilindungi.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </template>


    <!-- ==================== DASHBOARD UTAMA ==================== -->
    <template x-if="isLoggedIn">
        <div class="min-h-screen flex flex-col bg-slate-50">
            <!-- Top Navbar -->
            <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs no-print">
                <div class="w-full px-4 sm:px-6 lg:px-10 h-16 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white flex items-center justify-center shadow-md">
                            <span class="text-lg">🎒</span>
                        </div>
                        <div>
                            <h1 class="text-base font-bold text-slate-900 leading-tight" x-text="currentSchool?.name || 'Sekolah Terpadu'"></h1>
                            <p class="text-[11px] text-slate-400 font-medium">Portal Kas Sekolah & Transparansi Iuran</p>
                        </div>

                        <!-- Multi-School Switcher & School Management for Super Admin -->
                        <template x-if="currentUser?.role === 'SUPER_ADMIN'">
                            <div class="ml-4 flex items-center space-x-2">
                                <div class="flex items-center bg-indigo-50 border border-indigo-200 rounded-xl px-3 py-1.5 shadow-xs">
                                    <span class="text-xs font-bold text-indigo-800 mr-2">Sekolah:</span>
                                    <select x-model="activeSchoolId" @change="changeSchool()" class="bg-transparent text-indigo-900 text-sm font-bold focus:outline-none cursor-pointer">
                                        <template x-for="s in schools" :key="s.id">
                                            <option :value="s.id" x-text="s.name" :selected="s.id === activeSchoolId"></option>
                                        </template>
                                    </select>
                                </div>
                                <button type="button" @click="openSchoolListModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm transition">
                                    Manajemen Sekolah
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Right Controls: User Guide, DB Badge & Profile -->
                    <div class="flex items-center space-x-3">
                        <!-- USER GUIDE BUTTON (Inside Application Only) -->
                        <a :href="'/user-guide?role=' + (currentUser?.role || 'ALL')" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 transition shadow-xs" title="Buka Panduan Penggunaan Aplikasi (User Guide)">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <span>USER GUIDE</span>
                        </a>

                        <div class="hidden lg:flex items-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Sistem Online
                            </span>
                        </div>

                        <!-- User Profile Card & Logout -->
                        <div class="flex items-center space-x-2 bg-white px-3 py-1 rounded-xl border border-slate-200 shadow-xs">
                            <div class="text-right">
                                <p class="text-xs font-bold text-slate-900 leading-tight" x-text="currentUser?.name"></p>
                                <span class="inline-block px-2 py-0.2 rounded-md text-[9px] font-bold badge-school-purple" x-text="formatRole(currentUser?.role) + (currentUser?.managedClass ? ` (${korlasClassName})` : '')"></span>
                            </div>
                            <button type="button" @click="handleLogout()" class="text-xs text-rose-600 hover:text-rose-700 font-bold px-2.5 py-1 bg-rose-50 hover:bg-rose-100 rounded-xl transition" title="Keluar">
                                Keluar
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Container -->
            <main class="w-full px-4 sm:px-6 lg:px-10 pt-6 space-y-6 flex-1">

                <!-- Dynamic Dashboard Banner -->
                <div class="bg-white rounded-3xl p-6 lg:p-8 relative overflow-hidden border border-slate-200 shadow-sm no-print">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                        <div>
                            <span class="text-xs font-bold tracking-wider text-purple-700 uppercase badge-school-purple px-3 py-1 rounded-full">
                                🏫 <span x-text="currentSchool?.name || 'Sekolah'"></span> • <span x-text="currentUser?.role === 'KORLAS' ? 'Cakupan Kas ' + korlasClassName : 'Semua Kelas'"></span>
                            </span>
                            <h2 class="text-2xl font-black text-slate-900 mt-2" x-text="currentUser?.role === 'KORLAS' ? 'Dashboard Korlas (' + korlasClassName + ') 📌' : 'Manajemen Keuangan Kas & Multi-Tenancy 🎓'"></h2>
                            <p class="text-xs text-slate-500 mt-1 max-w-xl font-medium" x-text="currentUser?.role === 'KORLAS' ? 'Ringkasan kas, mutasi, dan tagihan diisolasi secara ketat khusus untuk ' + korlasClassName + '.' : 'Akses penuh administrator untuk melihat dan mengelola keuangan seluruh kelas.'"></p>
                        </div>

                        <!-- Banner Action Buttons -->
                        <div class="flex flex-wrap gap-2">
                            <!-- Super Admin: Foto/Video Header Login -->
                            <template x-if="currentUser?.role === 'SUPER_ADMIN'">
                                <button type="button" @click="openMediaModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-2xl font-bold text-xs shadow-md transition flex items-center gap-1.5">
                                    <span>🖼️ Ganti Foto/Video Header Login</span>
                                </button>
                            </template>

                            <!-- Bendahara, Admin & Korlas Actions: Penjadwalan Iuran -->
                            <template x-if="['SUPER_ADMIN', 'ADMIN', 'TREASURER', 'KORLAS'].includes(currentUser?.role)">
                                <button type="button" @click="openScheduleModal()" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-3 rounded-2xl font-bold text-xs shadow-md transition flex items-center gap-1.5">
                                    <span>📅 Penjadwalan Iuran</span>
                                </button>
                            </template>

                            <template x-if="['SUPER_ADMIN', 'ADMIN', 'TREASURER', 'KORLAS'].includes(currentUser?.role)">
                                <button type="button" @click="openIncomeModal()" class="gradient-button-school px-4 py-3 rounded-2xl font-bold text-xs shadow-md transition flex items-center gap-1.5">
                                    <span>+ Catat Mutasi Kas</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="flex space-x-2 border-b border-slate-200 pb-3 overflow-x-auto no-scrollbar no-print">
                    <button type="button" @click="activeTab = 'OVERVIEW'" :class="activeTab === 'OVERVIEW' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100'" class="px-4 py-2.5 rounded-2xl text-xs font-bold transition whitespace-nowrap">
                        📈 Ringkasan Kas
                    </button>
                    <button type="button" @click="activeTab = 'LEDGER'" :class="activeTab === 'LEDGER' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100'" class="px-4 py-2.5 rounded-2xl text-xs font-bold transition whitespace-nowrap">
                        📊 Buku Besar Mutasi (<span x-text="scopedTransactions.length"></span>)
                    </button>
                    <button type="button" @click="activeTab = 'STUDENTS'" :class="activeTab === 'STUDENTS' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100'" class="px-4 py-2.5 rounded-2xl text-xs font-bold transition whitespace-nowrap">
                        🎓 Data Siswa (<span x-text="scopedStudents.length"></span>)
                    </button>
                    <button type="button" @click="activeTab = 'REPORTS'" :class="activeTab === 'REPORTS' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100'" class="px-4 py-2.5 rounded-2xl text-xs font-bold transition whitespace-nowrap">
                        📋 Iuran Siswa
                    </button>
                    <button type="button" @click="activeTab = 'CLASS_MATRIX_REPORTS'" :class="activeTab === 'CLASS_MATRIX_REPORTS' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100'" class="px-4 py-2.5 rounded-2xl text-xs font-bold transition whitespace-nowrap">
                        📑 Laporan Iuran Siswa
                    </button>
                    <template x-if="['SUPER_ADMIN', 'ADMIN'].includes(currentUser?.role)">
                        <button type="button" @click="activeTab = 'USERS'" :class="activeTab === 'USERS' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100'" class="px-4 py-2.5 rounded-2xl text-xs font-bold transition whitespace-nowrap">
                            🧑‍💻 Account Management (<span x-text="users.filter(u => u.school_id === activeSchoolId || (currentUser?.role === 'SUPER_ADMIN' && u.role === 'SUPER_ADMIN')).length"></span>)
                        </button>
                    </template>
                    <template x-if="['SUPER_ADMIN', 'ADMIN'].includes(currentUser?.role)">
                        <button type="button" @click="activeTab = 'CLASSES'" :class="activeTab === 'CLASSES' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100'" class="px-4 py-2.5 rounded-2xl text-xs font-bold transition whitespace-nowrap">
                            🏫 Data Kelas
                        </button>
                    </template>
                </div>


                <!-- ==================== TAB 1: OVERVIEW ==================== -->
                <div x-show="activeTab === 'OVERVIEW'" class="space-y-6">
                    <!-- 4 Financial Stat Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                            <p class="text-xs font-bold text-slate-500 uppercase">Saldo Kas (<span x-text="currentUser?.role === 'KORLAS' ? korlasClassName : 'Utama'"></span>)</p>
                            <h3 class="text-2xl font-black mt-2 text-slate-900" x-text="formatCurrency(computedBalance)"></h3>
                            <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1 mt-1">● Saldo Riil Terhitung</span>
                        </div>
                        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                            <p class="text-xs font-bold text-slate-500 uppercase">Capaian Pembayaran</p>
                            <h3 class="text-2xl font-black mt-2 text-indigo-600" x-text="computedCollectionRate + '%'"></h3>
                            <span class="text-[11px] text-slate-400 font-medium mt-1 block">Dari Target Iuran</span>
                        </div>
                        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                            <p class="text-xs font-bold text-slate-500 uppercase">Total Terkumpul</p>
                            <h3 class="text-2xl font-black mt-2 text-emerald-600" x-text="formatCurrency(totalDuesCollected)"></h3>
                            <span class="text-[11px] text-slate-400 font-medium mt-1 block">Setoran Masuk Kas</span>
                        </div>
                        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                            <p class="text-xs font-bold text-slate-500 uppercase">Tunggakan Pending</p>
                            <h3 class="text-2xl font-black mt-2 text-amber-600" x-text="formatCurrency(totalPendingDues)"></h3>
                            <span class="text-[11px] text-slate-400 font-medium mt-1 block" x-text="pendingBillingsCount + ' Tagihan Belum Lunas'"></span>
                        </div>
                    </div>

                    <!-- Quick Financial Summary Table -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-bold text-slate-900">Aktivitas Kas Terbaru</h3>
                            <button type="button" @click="activeTab = 'LEDGER'" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Lihat Semua Mutasi →</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px]">
                                    <tr>
                                        <th class="p-3">Waktu</th>
                                        <th class="p-3">Tipe</th>
                                        <th class="p-3">Deskripsi</th>
                                        <th class="p-3 text-right">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="t in scopedTransactions.slice(0, 5)" :key="t.id">
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="p-3 font-mono text-slate-500" x-text="formatDate(t.created_at)"></td>
                                            <td class="p-3">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                                      :class="t.type === 'INCOME' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                                                      x-text="t.type === 'INCOME' ? 'Pemasukan' : 'Pengeluaran'"></span>
                                            </td>
                                            <td class="p-3 font-semibold text-slate-800" x-text="t.description"></td>
                                            <td class="p-3 font-bold text-right"
                                                :class="t.type === 'INCOME' ? 'text-emerald-600' : 'text-rose-600'"
                                                x-text="(t.type === 'INCOME' ? '+ ' : '- ') + formatCurrency(t.amount)"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="scopedTransactions.length === 0">
                                        <td colspan="4" class="p-6 text-center text-slate-400 italic">Belum ada aktivitas mutasi kas tercatat.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- ==================== TAB 2: LEDGER (BUKU BESAR MUTASI) ==================== -->
                <div x-show="activeTab === 'LEDGER'" class="space-y-6">
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Buku Besar Mutasi Kas</h3>
                                <p class="text-xs text-slate-500">Histori kas masuk dan keluar secara lengkap</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" @click="openPdfEmailModal('LEDGER')" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold border border-slate-300 transition flex items-center gap-1.5 shadow-xs">
                                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span>Cetak PDF & Kirim Email</span>
                                </button>
                                <template x-if="['SUPER_ADMIN', 'ADMIN', 'TREASURER', 'KORLAS'].includes(currentUser?.role)">
                                    <button type="button" @click="openIncomeModal()" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">
                                        + Kas Masuk
                                    </button>
                                </template>
                                <template x-if="['SUPER_ADMIN', 'ADMIN', 'TREASURER', 'KORLAS'].includes(currentUser?.role)">
                                    <button type="button" @click="openExpenseModal()" class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-xs">
                                        - Catat Pengeluaran
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Mutasi Table -->
                        <div class="overflow-x-auto mt-4">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px]">
                                    <tr>
                                        <th class="p-3">Waktu</th>
                                        <th class="p-3">Tipe</th>
                                        <th class="p-3">Kategori</th>
                                        <th class="p-3">Keterangan</th>
                                        <th class="p-3 text-right">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="t in scopedTransactions" :key="t.id">
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="p-3 font-mono text-slate-500" x-text="formatDate(t.created_at)"></td>
                                            <td class="p-3">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                                      :class="t.type === 'INCOME' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                                                      x-text="t.type === 'INCOME' ? 'Pemasukan' : 'Pengeluaran'"></span>
                                            </td>
                                            <td class="p-3 font-semibold text-slate-600" x-text="t.category || 'Kas Umum'"></td>
                                            <td class="p-3 text-slate-800 font-medium" x-text="t.description"></td>
                                            <td class="p-3 font-bold text-right"
                                                :class="t.type === 'INCOME' ? 'text-emerald-600' : 'text-rose-600'"
                                                x-text="(t.type === 'INCOME' ? '+ ' : '- ') + formatCurrency(t.amount)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- ==================== TAB 3: STUDENTS (DATA SISWA) ==================== -->
                <div x-show="activeTab === 'STUDENTS'" class="space-y-6">
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Daftar Siswa Terdaftar</h3>
                                <p class="text-xs text-slate-500">Data siswa aktif terikat rombel kelas</p>
                            </div>
                            <template x-if="['SUPER_ADMIN', 'ADMIN'].includes(currentUser?.role)">
                                <button type="button" @click="openAddStudentModal()" class="px-4 py-2 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-xs">
                                    + Tambah Siswa
                                </button>
                            </template>
                        </div>

                        <div class="overflow-x-auto mt-4">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px]">
                                    <tr>
                                        <th class="p-3">Nama Siswa</th>
                                        <th class="p-3">NIS</th>
                                        <th class="p-3">NISN</th>
                                        <th class="p-3">Jenis Kelamin</th>
                                        <th class="p-3">Kelas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="s in scopedStudents" :key="s.id">
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="p-3 font-bold text-slate-900" x-text="s.name"></td>
                                            <td class="p-3 font-mono text-slate-500" x-text="s.nis || '-'"></td>
                                            <td class="p-3 font-mono text-slate-500" x-text="s.nisn || '-'"></td>
                                            <td class="p-3" x-text="s.gender === 'MALE' ? 'Laki-laki' : 'Perempuan'"></td>
                                            <td class="p-3 font-semibold text-indigo-700" x-text="s.enrollments?.[0]?.class?.name || 'Kelas 5-A'"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- ==================== TAB 4: REPORTS (LAPORAN IURAN SISWA) ==================== -->
                <div x-show="activeTab === 'REPORTS'" class="space-y-6">
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="text-base font-bold text-slate-900" x-text="'Laporan Kelunasan Iuran Siswa (' + (currentUser?.role === 'KORLAS' ? (classes.find(c => c.id === currentUser?.managedClass)?.name || 'Kelas Anda') : 'Semua Kelas') + ')'"></h3>
                                <p class="text-xs text-slate-500">Rekapitulasi tagihan per periode bulan dan history setoran siswa</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <select x-model="reportPeriodFilter" class="bg-slate-100 border border-slate-200 rounded-2xl px-3 py-2 text-xs font-bold outline-none">
                                    <option value="ALL">Semua Periode Bulan</option>
                                    <template x-for="p in availablePeriods" :key="p">
                                        <option :value="p" x-text="'Periode: ' + p"></option>
                                    </template>
                                </select>

                                <div class="flex bg-slate-100 p-1 rounded-2xl text-xs font-bold">
                                    <button type="button" @click="reportStatusFilter = 'ALL'" :class="reportStatusFilter === 'ALL' ? 'bg-white font-black shadow-xs' : 'text-slate-600'" class="px-3 py-1.5 rounded-xl transition">Semua</button>
                                    <button type="button" @click="reportStatusFilter = 'PAID'" :class="reportStatusFilter === 'PAID' ? 'bg-emerald-600 text-white font-black shadow-xs' : 'text-slate-600'" class="px-3 py-1.5 rounded-xl transition">Lunas</button>
                                    <button type="button" @click="reportStatusFilter = 'PENDING'" :class="reportStatusFilter === 'PENDING' ? 'bg-amber-600 text-white font-black shadow-xs' : 'text-slate-600'" class="px-3 py-1.5 rounded-xl transition">Belum Lunas</button>
                                </div>

                                <button type="button" @click="openPdfEmailModal('REPORTS')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-2xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span>📄 Export PDF & Kirim Email</span>
                                </button>
                                <template x-if="['SUPER_ADMIN', 'ADMIN', 'TREASURER', 'KORLAS'].includes(currentUser?.role)">
                                    <button type="button" @click="openSchemeModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-2xl text-xs font-bold shadow-md transition">
                                        + Buat Tagihan
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Billings Table -->
                        <div class="overflow-x-auto mt-4">
                            <table class="w-full text-left text-xs text-slate-700">
                                <thead class="bg-slate-100 uppercase font-bold text-[10px] text-slate-500">
                                    <tr>
                                        <th class="p-3">Nama Siswa</th>
                                        <th class="p-3">Kelas</th>
                                        <th class="p-3">Periode</th>
                                        <th class="p-3 text-right">Tagihan</th>
                                        <th class="p-3 text-center">Status</th>
                                        <th class="p-3">Waktu Bayar</th>
                                        <th class="p-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="b in filteredReportBillings" :key="b.id">
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="p-3 font-bold text-slate-900" x-text="b.studentName || b.student?.name || 'Siswa'"></td>
                                            <td class="p-3 font-bold text-indigo-600" x-text="b.className || 'Kelas 5-A'"></td>
                                            <td class="p-3 font-semibold text-slate-700" x-text="b.period || 'Periode'"></td>
                                            <td class="p-3 text-right font-black" x-text="formatCurrency(b.amount_due)"></td>
                                            <td class="p-3 text-center">
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                                      :class="b.status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                                      x-text="b.status === 'PAID' ? 'LUNAS' : 'BELUM LUNAS'"></span>
                                            </td>
                                            <td class="p-3 text-slate-500" x-text="b.paidAt || 'Belum ada setoran'"></td>
                                            <td class="p-3 text-center">
                                                <template x-if="b.status !== 'PAID' && ['SUPER_ADMIN', 'ADMIN', 'TREASURER'].includes(currentUser?.role)">
                                                    <button type="button" @click="openPayModal(b)" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-xl text-[11px] font-bold shadow-xs transition">
                                                        Catat Pelunasan
                                                    </button>
                                                </template>
                                                <template x-if="b.status === 'PAID'">
                                                    <span class="text-emerald-600 font-bold text-[11px]">✓ Lunas</span>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>

                            <template x-if="filteredReportBillings.length === 0">
                                <div class="py-12 text-center text-slate-400 text-xs">
                                    <p class="text-3xl mb-2">📋</p>
                                    <p class="font-bold text-slate-700">Belum ada data tagihan iuran pada filter ini.</p>
                                    <p class="text-[11px] text-slate-400 mt-1">Klik tombol "+ Buat Tagihan" di atas untuk menerbitkan tagihan bagi setiap siswa.</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>


                <!-- ==================== TAB: CLASS MATRIX REPORTS (LAPORAN IURAN SISWA 12 BULAN) ==================== -->
                <div x-show="activeTab === 'CLASS_MATRIX_REPORTS'" class="space-y-6">
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                        <!-- Top Toolbar / Header -->
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                    <span>📑 Laporan Iuran Siswa (Januari - Desember)</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi matriks pembayaran iuran 12 bulan per siswa berdasarkan kelas</p>
                            </div>

                            <!-- Filter & Actions Bar -->
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Pilih Kelas Dropdown -->
                                <div class="flex items-center gap-1.5">
                                    <label class="text-[11px] font-bold text-slate-500">Kelas:</label>
                                    <select x-model="selectedMatrixClassId" class="bg-slate-100 border border-slate-200 rounded-2xl px-3 py-2 text-xs font-bold text-slate-800 outline-none hover:bg-slate-200/60 transition">
                                        <template x-if="currentUser?.role !== 'KORLAS'">
                                            <option value="ALL">🏫 Semua Kelas</option>
                                        </template>
                                        <template x-for="c in classes" :key="c.id">
                                            <option :value="c.id" x-text="c.name"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Pilih Tahun Dropdown -->
                                <div class="flex items-center gap-1.5">
                                    <label class="text-[11px] font-bold text-slate-500">Tahun:</label>
                                    <select x-model.number="selectedMatrixYear" class="bg-slate-100 border border-slate-200 rounded-2xl px-3 py-2 text-xs font-bold text-slate-800 outline-none hover:bg-slate-200/60 transition">
                                        <option value="2024">2024</option>
                                        <option value="2025">2025</option>
                                        <option value="2026">2026</option>
                                        <option value="2027">2027</option>
                                    </select>
                                </div>

                                <!-- Export PDF & Kirim Email Button -->
                                <button type="button" @click="openClassMatrixPdfModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-2xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span>📄 Export PDF & Kirim Email</span>
                                </button>

                                <!-- Cetak Matriks Button -->
                                <button type="button" @click="window.print()" class="px-3.5 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold border border-slate-300 transition flex items-center gap-1.5">
                                    <span>🖨️ Cetak Matriks</span>
                                </button>
                            </div>
                        </div>

                        <!-- KPI Summary Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 my-4">
                            <div class="bg-purple-50/60 border border-purple-200 rounded-2xl p-3.5">
                                <span class="text-[10px] font-bold text-purple-700 uppercase tracking-wider">Total Siswa Terdaftar</span>
                                <div class="text-xl font-black text-purple-900 mt-1" x-text="classMatrixTotals.totalStudents + ' Siswa'"></div>
                                <div class="text-[10px] text-purple-600 mt-0.5" x-text="selectedMatrixClassId === 'ALL' ? 'Semua Kelas' : (classes.find(c => c.id === selectedMatrixClassId)?.name || 'Kelas Terpilih')"></div>
                            </div>
                            <div class="bg-emerald-50/60 border border-emerald-200 rounded-2xl p-3.5">
                                <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Total Iuran Terbayar</span>
                                <div class="text-xl font-black text-emerald-700 mt-1" x-text="formatCurrency(classMatrixTotals.totalPaid)"></div>
                                <div class="text-[10px] text-emerald-600 mt-0.5">Akumulasi penerimaan lunas tahun ini</div>
                            </div>
                            <div class="bg-rose-50/60 border border-rose-200 rounded-2xl p-3.5">
                                <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider">Total Sisa Tunggakan</span>
                                <div class="text-xl font-black text-rose-700 mt-1" x-text="formatCurrency(classMatrixTotals.totalPending)"></div>
                                <div class="text-[10px] text-rose-600 mt-0.5">Kekurangan iuran yang belum diselesaikan</div>
                            </div>
                        </div>

                        <!-- 12-Month Matrix Table -->
                        <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-xs">
                            <table class="w-full text-left text-xs border-collapse min-w-[1000px]">
                                <thead class="bg-slate-800 text-white font-bold text-[10px] uppercase">
                                    <tr>
                                        <th class="p-2.5 text-center w-8 border border-slate-700">No</th>
                                        <th class="p-2.5 min-w-[150px] border border-slate-700">Nama Siswa</th>
                                        <th class="p-2.5 text-center w-20 border border-slate-700">Kelas</th>
                                        <th class="p-2 text-center w-14 border border-slate-700">Jan</th>
                                        <th class="p-2 text-center w-14 border border-slate-700">Feb</th>
                                        <th class="p-2 text-center w-14 border border-slate-700">Mar</th>
                                        <th class="p-2 text-center w-14 border border-slate-700">Apr</th>
                                        <th class="p-2 text-center w-14 border border-slate-700">Mei</th>
                                        <th class="p-2 text-center w-14 border border-slate-700">Jun</th>
                                        <th class="p-2 text-center w-14 border border-slate-700">Jul</th>
                                        <th class="p-2 text-center w-14 border border-slate-700">Ags</th>
                                        <th class="p-2 text-center w-14 border border-slate-700">Sep</th>
                                        <th class="p-2 text-center w-14 border border-slate-700">Okt</th>
                                        <th class="p-2 text-center w-14 border border-slate-700">Nov</th>
                                        <th class="p-2 text-center w-14 border border-slate-700">Des</th>
                                        <th class="p-2.5 text-right w-24 border border-slate-700">Terbayar</th>
                                        <th class="p-2.5 text-right w-24 border border-slate-700">Tunggakan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    <template x-for="(row, idx) in classMatrixData" :key="row.student_id">
                                        <tr class="hover:bg-purple-50/30 transition text-[11px]">
                                            <td class="p-2 text-center text-slate-500 font-mono border-r border-slate-200" x-text="idx + 1"></td>
                                            <td class="p-2 font-bold text-slate-900 border-r border-slate-200" x-text="row.student_name"></td>
                                            <td class="p-2 text-center font-medium text-slate-600 border-r border-slate-200" x-text="row.class_name"></td>

                                            <!-- Month 1..12 Columns -->
                                            <template x-for="m in [1,2,3,4,5,6,7,8,9,10,11,12]" :key="m">
                                                <td class="p-1 text-center border-r border-slate-200">
                                                    <template x-if="row.months[m]?.status === 'PAID'">
                                                        <span class="inline-block px-1 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"
                                                              x-text="row.months[m]?.text"
                                                              :title="'Lunas: ' + formatCurrency(row.months[m]?.paid)"></span>
                                                    </template>
                                                    <template x-if="row.months[m]?.status === 'UNPAID'">
                                                        <span class="inline-block px-1 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200"
                                                              x-text="row.months[m]?.text"
                                                              :title="'Tunggakan: ' + formatCurrency(row.months[m]?.pending)"></span>
                                                    </template>
                                                    <template x-if="row.months[m]?.status === 'NONE'">
                                                        <span class="text-slate-300 font-medium">-</span>
                                                    </template>
                                                </td>
                                            </template>

                                            <!-- Total Terbayar -->
                                            <td class="p-2 text-right font-bold text-emerald-600 border-r border-slate-200" x-text="formatCurrency(row.total_paid)"></td>
                                            <!-- Sisa Tunggakan -->
                                            <td class="p-2 text-right font-bold"
                                                :class="row.total_pending > 0 ? 'text-rose-600' : 'text-slate-400'"
                                                x-text="row.total_pending > 0 ? ('- ' + formatCurrency(row.total_pending)) : '0'"></td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-300 text-slate-800 text-[11px]">
                                    <tr>
                                        <td colspan="3" class="p-2.5 text-right uppercase tracking-wider text-[10px]">Total Keseluruhan:</td>
                                        <td colspan="12" class="p-2 text-center text-slate-500 font-normal italic text-[10px]">
                                            Matriks 12 Bulan Tahun <span x-text="selectedMatrixYear"></span>
                                        </td>
                                        <td class="p-2.5 text-right text-emerald-700 font-black" x-text="formatCurrency(classMatrixTotals.totalPaid)"></td>
                                        <td class="p-2.5 text-right font-black"
                                            :class="classMatrixTotals.totalPending > 0 ? 'text-rose-700' : 'text-slate-500'"
                                            x-text="classMatrixTotals.totalPending > 0 ? ('- ' + formatCurrency(classMatrixTotals.totalPending)) : '0'"></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <!-- Empty State -->
                            <template x-if="classMatrixData.length === 0">
                                <div class="py-12 text-center text-slate-400 text-xs">
                                    <p class="text-3xl mb-2">📑</p>
                                    <p class="font-bold text-slate-700">Tidak ada data siswa untuk kelas atau filter yang dipilih.</p>
                                    <p class="text-[11px] text-slate-400 mt-1">Pilih kelas lain atau pastikan data siswa sudah terdaftar pada kelas tersebut.</p>
                                </div>
                            </template>
                        </div>

                        <!-- Legend -->
                        <div class="flex flex-wrap items-center gap-4 pt-3 text-[11px] text-slate-600">
                            <span class="font-bold text-slate-800">Keterangan:</span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded bg-emerald-100 border border-emerald-300 inline-block"></span>
                                <span><strong>Nilai Hijau:</strong> Iuran Lunas (Nominal Terbayar)</span>
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded bg-rose-100 border border-rose-300 inline-block"></span>
                                <span><strong>Nilai Merah (-) :</strong> Tunggakan (Kekurangan yang belum dibayar)</span>
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded bg-slate-100 border border-slate-300 inline-block text-center text-[9px] leading-3">-</span>
                                <span><strong>Tanda Dash (-):</strong> Tidak Ada Tagihan</span>
                            </span>
                        </div>
                    </div>
                </div>


                <!-- ==================== TAB 5: ACCOUNT MANAGEMENT (USERS) ==================== -->
                <div x-show="activeTab === 'USERS'" class="space-y-6">
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Account Management</h3>
                                <p class="text-xs text-slate-500">Pendaftaran akun Admin, Bendahara, Korlas, dan Orang Tua Siswa</p>
                            </div>
                            <button type="button" @click="openAddAccountModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-2xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
                                <span>+ Tambah Account (Admin / Bendahara / Korlas / Parent)</span>
                            </button>
                        </div>

                        <div class="overflow-x-auto mt-4">
                            <table class="w-full text-left text-xs text-slate-700">
                                <thead class="bg-slate-100 uppercase text-[10px] text-slate-500 font-bold">
                                    <tr>
                                        <th class="p-3">Nama</th>
                                        <th class="p-3">Email</th>
                                        <th class="p-3">Role</th>
                                        <th class="p-3">Cakupan Kelas / Anak</th>
                                        <th class="p-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="u in users.filter(u => u.school_id === activeSchoolId || (currentUser?.role === 'SUPER_ADMIN' && u.role === 'SUPER_ADMIN'))" :key="u.id">
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="p-3 font-bold text-slate-900" x-text="u.name"></td>
                                            <td class="p-3 text-slate-600" x-text="u.email"></td>
                                            <td class="p-3">
                                                <span class="font-bold text-indigo-700" x-text="formatRole(u.role)"></span>
                                            </td>
                                            <td class="p-3 font-semibold text-slate-800">
                                                <template x-if="u.role === 'KORLAS'">
                                                    <span x-text="'📌 Korlas: ' + (classes.find(c => c.id === u.managed_class)?.name || u.managed_class || 'Kelas 5-A')"></span>
                                                </template>
                                                <template x-if="u.role === 'PARENT'">
                                                    <span x-text="'👨‍👦 Anak: ' + (students.find(s => s.id === u.student_id)?.name || 'Siswa')"></span>
                                                </template>
                                                <template x-if="!['KORLAS', 'PARENT'].includes(u.role)">
                                                    <span>-</span>
                                                </template>
                                            </td>
                                            <td class="p-3 text-center">
                                                <button type="button" @click="openEditAccountModal(u)" class="text-indigo-600 hover:text-indigo-800 mr-3 text-[11px] font-bold">Edit</button>
                                                <button type="button" @click="deleteUserAccount(u.id)" class="text-rose-600 hover:text-rose-800 text-[11px] font-bold">Hapus</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- ==================== TAB 6: CLASSES (DATA KELAS & TAHUN AJARAN) ==================== -->
                <div x-show="activeTab === 'CLASSES'" class="space-y-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-6 rounded-3xl shadow-sm border border-slate-200 gap-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Data Kelas & Tahun Ajaran</h3>
                            <p class="text-xs text-slate-500">Kelola master data rombel kelas dan tahun ajaran aktif</p>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="openAddYearModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-2xl text-xs font-bold shadow-md transition">
                                + Tambah Tahun Ajaran
                            </button>
                            <button type="button" @click="openAddClassModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-2xl text-xs font-bold shadow-md transition">
                                + Tambah Kelas
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Classes List -->
                        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                            <h4 class="font-bold text-slate-800 text-sm mb-4">Daftar Rombel Kelas</h4>
                            <div class="divide-y divide-slate-100">
                                <template x-for="c in classes" :key="c.id">
                                    <div class="py-3 flex items-center justify-between">
                                        <div>
                                            <p class="font-bold text-sm text-slate-900" x-text="c.name"></p>
                                            <p class="text-[11px] text-slate-400 font-mono">Buku Kas ID: <span x-text="c.cash_accounts?.[0]?.id || '-'"></span></p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">Aktif</span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Academic Years List -->
                        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                            <h4 class="font-bold text-slate-800 text-sm mb-4">Daftar Tahun Ajaran</h4>
                            <div class="divide-y divide-slate-100">
                                <template x-for="y in academicYears" :key="y.id">
                                    <div class="py-3 flex items-center justify-between">
                                        <p class="font-bold text-sm text-slate-900" x-text="y.year"></p>
                                        <template x-if="y.is_current">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Tahun Aktif</span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

            </main>


            <!-- ==================== MODAL: MANAJEMEN SEKOLAH (SUPER ADMIN) ==================== -->
            <div x-show="showSchoolListModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
                <div class="bg-white rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl flex flex-col max-h-[85vh] border border-slate-200" @click.away="showSchoolListModal = false">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                        <div>
                            <h3 class="text-xl font-black text-slate-900">Manajemen Sekolah</h3>
                            <p class="text-xs text-slate-500 mt-1">Daftar sekolah yang terdaftar di dalam sistem</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button type="button" @click="openAddSchoolModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-xs transition">
                                + Tambah Sekolah
                            </button>
                            <button type="button" @click="showSchoolListModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                        </div>
                    </div>
                    <div class="p-0 overflow-y-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs">
                                <tr>
                                    <th class="p-4 font-bold">Nama Sekolah</th>
                                    <th class="p-4 font-bold">Kode</th>
                                    <th class="p-4 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                <template x-for="s in schools" :key="s.id">
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="p-4 font-bold text-slate-900" x-text="s.name"></td>
                                        <td class="p-4 font-mono text-slate-600" x-text="s.code"></td>
                                        <td class="p-4 text-center">
                                            <button type="button" @click="openEditSchoolModal(s)" class="text-indigo-600 hover:text-indigo-800 mr-3 font-bold">Edit</button>
                                            <button type="button" @click="deleteSchool(s.id)" class="text-rose-600 hover:text-rose-800 font-bold">Hapus</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <!-- ==================== MODAL: TAMBAH / EDIT SEKOLAH ==================== -->
            <div x-show="showSchoolModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
                <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl border border-slate-200" @click.away="showSchoolModal = false">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                        <div>
                            <h3 class="text-lg font-black text-slate-900" x-text="editingSchoolId ? 'Edit Sekolah' : 'Tambah Sekolah Baru'"></h3>
                            <p class="text-xs text-slate-500 mt-1" x-text="editingSchoolId ? 'Ubah informasi sekolah' : 'Daftarkan sekolah baru ke dalam sistem'"></p>
                        </div>
                        <button type="button" @click="showSchoolModal = false" class="text-slate-400 text-lg">✕</button>
                    </div>
                    <form @submit.prevent="saveSchool()" class="p-6 space-y-4 text-xs">
                        <div>
                            <label class="block text-slate-700 font-bold mb-1">Nama Sekolah</label>
                            <input type="text" required placeholder="Contoh: SDN 08 Pagi Jakarta" x-model="newSchoolName" class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3 text-slate-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-slate-700 font-bold mb-1">Kode Sekolah (Unik)</label>
                            <input type="text" required placeholder="Contoh: SDN08-JKT" x-model="newSchoolCode" class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3 text-slate-900 text-sm uppercase">
                        </div>
                        <div>
                            <label class="block text-slate-700 font-bold mb-1">Alamat (Opsional)</label>
                            <input type="text" placeholder="Contoh: Jl. Merdeka No. 8" x-model="newSchoolAddress" class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3 text-slate-900 text-sm">
                        </div>
                        <div class="pt-2 flex gap-3">
                            <button type="button" @click="showSchoolModal = false" class="flex-1 py-3 rounded-2xl bg-slate-100 text-slate-700 font-bold">Batal</button>
                            <button type="submit" class="flex-1 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md" x-text="editingSchoolId ? 'Simpan Perubahan' : 'Simpan Sekolah'"></button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- ==================== MODAL: GANTI FOTO/VIDEO HEADER LOGIN (SUPER ADMIN) ==================== -->
            <div x-show="showMediaModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" x-cloak>
                <div class="bg-white w-full max-w-lg p-6 rounded-3xl border border-slate-200 shadow-2xl space-y-4" @click.away="showMediaModal = false">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <h3 class="text-base font-bold text-slate-900">🖼️ Ganti Foto / Video Header Login Sekolah</h3>
                        <button type="button" @click="showMediaModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                    </div>

                    <form @submit.prevent="handleSaveHeroMedia()" class="space-y-4 text-xs">
                        <div class="p-4 bg-purple-50 border border-purple-200 rounded-2xl space-y-2">
                            <label class="block text-purple-900 font-bold mb-1">
                                📁 Upload File Foto / Video dari Komputer Local
                            </label>
                            <input type="file" accept="image/*,video/*" @change="handleMediaFileUpload($event)" class="w-full bg-white border border-purple-300 rounded-xl p-2.5 text-xs text-slate-800 font-medium file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-600 file:text-white hover:file:bg-purple-700 cursor-pointer">
                            <p class="text-[10px] text-purple-700">
                                Format didukung: JPG, PNG, WEBP, MP4, WEBM (Otomatis mendeteksi foto atau video).
                            </p>
                        </div>

                        <div class="relative text-center my-2">
                            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                            <span class="relative bg-white px-3 text-[10px] uppercase font-bold text-slate-400">Atau Masukkan Link URL</span>
                        </div>

                        <div>
                            <label class="block text-slate-700 font-bold mb-1">Tipe Media Banner</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="inputMediaType = 'IMAGE'" :class="inputMediaType === 'IMAGE' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 text-slate-600'" class="py-2.5 rounded-2xl font-bold border transition">
                                    📷 Foto / Gambar (JPG/PNG)
                                </button>
                                <button type="button" @click="inputMediaType = 'VIDEO'" :class="inputMediaType === 'VIDEO' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 text-slate-600'" class="py-2.5 rounded-2xl font-bold border transition">
                                    🎥 Video MP4 Sekolah
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-slate-700 font-bold mb-1">URL / Data Media Banner Sekolah</label>
                            <input type="text" required placeholder="https://domain-sekolah.sch.id/banner.jpg" x-model="inputMediaUrl" class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3 text-slate-900 text-sm font-mono">
                        </div>

                        <!-- Live Preview -->
                        <div class="p-3 bg-slate-100 rounded-2xl border border-slate-200 space-y-1">
                            <p class="font-bold text-slate-700 text-[11px]">Preview Media Login:</p>
                            <div class="h-36 rounded-xl overflow-hidden bg-slate-900 flex items-center justify-center">
                                <template x-if="inputMediaType === 'VIDEO'">
                                    <video :src="inputMediaUrl" autoplay loop muted class="w-full h-full object-cover"></video>
                                </template>
                                <template x-if="inputMediaType !== 'VIDEO'">
                                    <img :src="inputMediaUrl" alt="Preview" class="w-full h-full object-cover" />
                                </template>
                            </div>
                        </div>

                        <div class="flex space-x-3 pt-2">
                            <button type="button" @click="showMediaModal = false" class="flex-1 py-3 rounded-2xl bg-slate-100 text-slate-700 font-bold">Batal</button>
                            <button type="submit" class="flex-1 py-3 rounded-2xl gradient-button-school text-white font-bold shadow-md">Simpan & Terapkan Banner</button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- ==================== MODAL: PENJADWALAN IURAN OTOMATIS ==================== -->
            <div x-show="showScheduleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" x-cloak>
                <div class="bg-white w-full max-w-md p-6 rounded-3xl border border-slate-200 shadow-2xl space-y-4" @click.away="showScheduleModal = false">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <h3 class="text-base font-bold text-slate-900">📅 Penjadwalan Iuran Otomatis</h3>
                        <button type="button" @click="showScheduleModal = false" class="text-slate-400 text-lg">✕</button>
                    </div>

                    <form @submit.prevent="submitScheduleDues()" class="space-y-4 text-xs">
                        <div>
                            <label class="block text-slate-700 font-bold mb-1">Program Iuran Wajib</label>
                            <input type="text" required x-model="cronTitle" class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3 text-slate-900 text-sm font-medium">
                        </div>

                        <template x-if="currentUser?.role !== 'KORLAS'">
                            <div>
                                <label class="block text-slate-700 font-bold mb-1">Pilih Kelas</label>
                                <select required x-model="cronClassId" class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3 text-slate-900 text-sm font-bold">
                                    <template x-for="cls in classes" :key="cls.id">
                                        <option :value="cls.id" x-text="cls.name"></option>
                                    </template>
                                </select>
                            </div>
                        </template>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-slate-700 font-bold mb-1">Tanggal Eksekusi Rutin</label>
                                <select x-model.number="cronDay" class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3 text-slate-900 text-sm font-bold">
                                    <option :value="1">Setiap Tanggal 1</option>
                                    <option :value="5">Setiap Tanggal 5</option>
                                    <option :value="10">Setiap Tanggal 10</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-slate-700 font-bold mb-1">Nominal (IDR)</label>
                                <input type="number" required x-model.number="cronAmount" class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3 text-slate-900 text-sm font-medium">
                            </div>
                        </div>

                        <div class="p-3.5 bg-purple-50 border border-purple-200 rounded-2xl text-[11px] text-purple-900">
                            🔒 <strong>Isolasi Korlas:</strong> Tagihan otomatis ini hanya akan dihasilkan secara terbatas untuk siswa di <strong><span x-text="currentUser?.role === 'KORLAS' ? korlasClassName : 'Kelas Terpilih'"></span></strong> tanpa mempengaruhi jadwal kelas lain.
                        </div>

                        <div class="flex space-x-3 pt-2">
                            <button type="button" @click="showScheduleModal = false" class="flex-1 py-3 rounded-2xl bg-slate-100 text-slate-700 font-bold">Batal</button>
                            <button type="submit" class="flex-1 py-3 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-bold shadow-md">Simpan Jadwal</button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- ==================== MODAL: KIRIM PDF LAPORAN KE EMAIL ==================== -->
            <div x-show="showPdfEmailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" x-cloak>
                <div class="bg-white w-full max-w-md p-6 rounded-3xl border border-slate-200 shadow-2xl space-y-4" @click.away="showPdfEmailModal = false">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <h3 class="text-base font-bold text-slate-900">📄 Cetak & Kirim Laporan ke Email</h3>
                        <button type="button" @click="showPdfEmailModal = false" class="text-slate-400 text-lg">✕</button>
                    </div>

                    <form @submit.prevent="submitSendPdfEmail()" class="space-y-4 text-xs">
                        <div>
                            <label class="block text-slate-700 font-bold mb-1">Alamat Email Tujuan</label>
                            <input type="email" required placeholder="nama@domain.com" x-model="targetEmail" class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-3 text-slate-900 text-sm">
                            <p class="text-[10px] text-slate-500 mt-1">Salinan rekapitulasi keuangan resmi akan dikirimkan ke email ini.</p>
                        </div>

                        <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-2xl text-[11px] text-indigo-900 space-y-1">
                            <p class="font-bold">Informasi Laporan:</p>
                            <p>Tipe Laporan: <strong x-text="reportPdfType === 'CLASS_MATRIX' ? 'Laporan Matriks Iuran 12 Bulan' : (reportPdfType === 'LEDGER' ? 'Buku Besar Mutasi Kas' : 'Rekapitulasi Iuran Siswa')"></strong></p>
                            <p>Sekolah: <strong x-text="currentSchool?.name"></strong></p>
                        </div>

                        <div class="flex space-x-3 pt-2">
                            <button type="button" @click="showPdfEmailModal = false" class="flex-1 py-3 rounded-2xl bg-slate-100 text-slate-700 font-bold">Batal</button>
                            <button type="submit" class="flex-1 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md">Kirim Email Sekarang</button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- ==================== MODAL: USER ACCOUNT MANAGEMENT (ADD / EDIT) ==================== -->
            <div x-show="showUserModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
                <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl border border-slate-200" @click.away="showUserModal = false">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                        <h3 class="text-base font-bold text-slate-900" x-text="editingUserId ? 'Edit Akun Pengguna' : 'Tambah Akun Pengguna Baru'"></h3>
                        <button type="button" @click="showUserModal = false" class="text-slate-400 text-lg">✕</button>
                    </div>

                    <form @submit.prevent="saveUserAccount()" class="space-y-3 text-xs">
                        <div>
                            <label class="block text-slate-700 font-bold mb-1">Peran (Role)</label>
                            <select x-model="userForm.role" class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-2.5 text-xs font-bold text-slate-900">
                                <template x-if="currentUser?.role === 'SUPER_ADMIN'">
                                    <option value="ADMIN">Admin Sekolah (ADMIN)</option>
                                </template>
                                <option value="TREASURER">Bendahara Kelas (TREASURER)</option>
                                <option value="KORLAS">Koordinator Kelas (KORLAS)</option>
                                <option value="PARENT">Wali Murid / Orang Tua (PARENT)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-slate-700 font-bold mb-1">Nama Lengkap</label>
                            <input type="text" required x-model="userForm.name" class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-2.5 text-xs text-slate-900">
                        </div>

                        <div>
                            <label class="block text-slate-700 font-bold mb-1">Email Akun</label>
                            <input type="email" required x-model="userForm.email" class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-2.5 text-xs text-slate-900">
                        </div>

                        <div>
                            <label class="block text-slate-700 font-bold mb-1" x-text="editingUserId ? 'Password Baru (Kosongkan jika tidak diubah)' : 'Password'"></label>
                            <input type="password" :required="!editingUserId" x-model="userForm.password" class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-2.5 text-xs text-slate-900" placeholder="••••••••">
                        </div>

                        <!-- Class assignment for Korlas -->
                        <div x-show="userForm.role === 'KORLAS'">
                            <label class="block text-slate-700 font-bold mb-1">Pilih Kelas yang Dikoordinasikan</label>
                            <select x-model="userForm.managedClass" class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-2.5 text-xs font-bold text-slate-900">
                                <template x-for="cls in classes" :key="cls.id">
                                    <option :value="cls.id" x-text="cls.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Student child assignment for Parent -->
                        <div x-show="userForm.role === 'PARENT'">
                            <label class="block text-slate-700 font-bold mb-1">Pilih Siswa (Anak dari Orang Tua)</label>
                            <select x-model="userForm.studentId" class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-2.5 text-xs font-bold text-slate-900">
                                <template x-for="st in students" :key="st.id">
                                    <option :value="st.id" x-text="st.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="flex space-x-3 pt-3">
                            <button type="button" @click="showUserModal = false" class="flex-1 py-2.5 rounded-2xl bg-slate-100 text-slate-700 font-bold">Batal</button>
                            <button type="submit" class="flex-1 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md">Simpan Akun</button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- ==================== MODAL CATAT PELUNASAN IURAN ==================== -->
            <div x-show="showPayModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
                <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-slate-100" @click.away="showPayModal = false">
                    <h3 class="text-base font-bold text-slate-800 mb-1">Catat Pembayaran Iuran</h3>
                    <p class="text-xs text-slate-500 mb-4" x-text="'Konfirmasi pembayaran untuk ' + (selectedBilling?.student?.name || 'Siswa')"></p>
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-600 mb-1">Jumlah Bayar</label>
                        <input type="number" x-model.number="payAmount" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm font-bold text-slate-900 outline-none focus:border-emerald-600">
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" @click="showPayModal = false" class="flex-1 py-2.5 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs hover:bg-slate-200 transition">Batal</button>
                        <button type="button" @click="confirmPayBilling()" class="flex-1 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 shadow transition">Konfirmasi Lunas</button>
                    </div>
                </div>
            </div>


            <!-- ==================== MODAL BUAT SKEMA IURAN ==================== -->
            <div x-show="showSchemeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
                <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-100" @click.away="showSchemeModal = false">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-3">
                        <h3 class="text-base font-bold text-slate-800">Terbitkan Tagihan Iuran Siswa</h3>
                        <button type="button" @click="showSchemeModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                    </div>
                    <p class="text-xs text-slate-500 mb-4">Tagihan akan otomatis dibuat untuk setiap siswa pada kelas yang dipilih.</p>
                    <form @submit.prevent="submitCreateScheme()">
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-slate-600 mb-1">Judul / Program Iuran</label>
                            <input type="text" x-model="schemeTitle" required placeholder="Contoh: Iuran Kas September 2026" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-indigo-600">
                        </div>
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-slate-600 mb-1">Pilih Kelas</label>
                            <select x-model="schemeClassId" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-indigo-600 font-bold bg-white">
                                <option value="ALL">Semua Kelas (Seluruh Siswa)</option>
                                <template x-for="c in classes" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Nominal per Siswa (Rp)</label>
                                <input type="number" x-model.number="schemeAmount" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-indigo-600 font-bold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Jatuh Tempo</label>
                                <input type="date" x-model="schemeDueDate" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-indigo-600">
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button type="button" @click="showSchemeModal = false" class="flex-1 py-2.5 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs hover:bg-slate-200 transition">Batal</button>
                            <button type="submit" class="flex-1 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 shadow transition">Terbitkan Tagihan</button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- ==================== MODAL CATAT KAS MASUK LANGSUNG ==================== -->
            <div x-show="showIncomeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
                <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-100" @click.away="showIncomeModal = false">
                    <h3 class="text-base font-bold text-slate-800 mb-1">Catat Kas Masuk Langsung</h3>
                    <p class="text-xs text-slate-500 mb-4">Pemasukan kas di luar tagihan iuran (misal: donasi, sisa kas lama).</p>
                    <form @submit.prevent="submitDirectIncome()">
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-slate-600 mb-1">Sumber / Kategori</label>
                            <select x-model="incomeCategory" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-indigo-600 font-bold">
                                <option value="Donasi">Donasi Orang Tua / Sukarela</option>
                                <option value="Sisa Kas Lalu">Sisa Saldo Kas Periode Lalu</option>
                                <option value="Hadiah / Sponsorship">Hadiah Lomba / Sponsorship</option>
                                <option value="Lainnya">Pemasukan Lainnya</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-slate-600 mb-1">Nominal (Rp)</label>
                            <input type="number" x-model.number="incomeAmount" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-emerald-600 font-bold">
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-600 mb-1">Keterangan</label>
                            <textarea x-model="incomeDescription" rows="2" required placeholder="Keterangan sumber kas masuk..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-indigo-600"></textarea>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button type="button" @click="showIncomeModal = false" class="flex-1 py-2.5 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs hover:bg-slate-200 transition">Batal</button>
                            <button type="submit" class="flex-1 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 shadow transition">Simpan Kas Masuk</button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- ==================== MODAL CATAT PENGELUARAN ==================== -->
            <div x-show="showExpenseModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
                <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-100" @click.away="showExpenseModal = false">
                    <h3 class="text-base font-bold text-slate-800 mb-1">Catat Pengeluaran Uang Kas</h3>
                    <p class="text-xs text-slate-500 mb-4">Pengeluaran kelas harus disertai keterangan & nota pertanggungjawaban.</p>
                    <form @submit.prevent="submitExpense()">
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-slate-600 mb-1">Keperluan Belanja</label>
                            <input type="text" x-model="expenseTitle" required placeholder="Contoh: Beli Spidol & Penghapus Whiteboard" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-indigo-600">
                        </div>
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-slate-600 mb-1">Nominal (Rp)</label>
                            <input type="number" x-model.number="expenseAmount" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-rose-600 font-bold">
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-600 mb-1">Keterangan / Nomor Nota</label>
                            <textarea x-model="expenseDescription" rows="2" placeholder="Nomor kuitansi / rincian barang..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-indigo-600"></textarea>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button type="button" @click="showExpenseModal = false" class="flex-1 py-2.5 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs hover:bg-slate-200 transition">Batal</button>
                            <button type="submit" class="flex-1 py-2.5 rounded-xl bg-rose-600 text-white font-bold text-xs hover:bg-rose-700 shadow transition">Simpan Pengeluaran</button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- ==================== MODAL TAMBAH SISWA ==================== -->
            <div x-show="showAddStudentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
                <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-100" @click.away="showAddStudentModal = false">
                    <h3 class="text-base font-bold text-slate-800 mb-1">Daftarkan Siswa Baru</h3>
                    <p class="text-xs text-slate-500 mb-4">Tambahkan data siswa lengkap dengan rombel kelas.</p>
                    <form @submit.prevent="submitAddStudent()">
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-slate-600 mb-1">Nama Lengkap Siswa</label>
                            <input type="text" x-model="newStudent.name" required placeholder="Contoh: Rian Pratama" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-indigo-600">
                        </div>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">NIS</label>
                                <input type="text" x-model="newStudent.nis" placeholder="20260501" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-indigo-600">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">NISN</label>
                                <input type="text" x-model="newStudent.nisn" placeholder="0051234567" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-indigo-600">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Jenis Kelamin</label>
                                <select x-model="newStudent.gender" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-indigo-600">
                                    <option value="MALE">Laki-laki</option>
                                    <option value="FEMALE">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Kelas</label>
                                <select x-model="newStudent.classId" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-indigo-600">
                                    <template x-for="c in classes" :key="c.id">
                                        <option :value="c.id" x-text="c.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-slate-600 mb-1">Password Akun Siswa (Opsional)</label>
                            <input type="password" x-model="newStudent.password" placeholder="Kosongkan jika ingin dibuat otomatis oleh sistem" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-indigo-600">
                        </div>
                        <div class="flex items-center space-x-2 pt-2">
                            <button type="button" @click="showAddStudentModal = false" class="flex-1 py-2.5 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs hover:bg-slate-200 transition">Batal</button>
                            <button type="submit" class="flex-1 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 shadow transition">Simpan Siswa</button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- ==================== MODAL TAMBAH KELAS ==================== -->
            <div x-show="showAddClassModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
                <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-slate-100" @click.away="showAddClassModal = false">
                    <h3 class="text-base font-bold text-slate-800 mb-1">Tambah Kelas Baru</h3>
                    <p class="text-xs text-slate-500 mb-4">Otomatis membuat buku kas untuk kelas ini.</p>
                    <form @submit.prevent="submitAddClass()">
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-slate-600 mb-1">Nama Kelas</label>
                            <input type="text" x-model="newClassName" required placeholder="Contoh: Kelas 5-B" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-indigo-600">
                        </div>
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-600">Tahun Ajaran</label>
                                <button type="button" @click="showAddClassModal = false; openAddYearModal();" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-bold transition">
                                    + Tambah Tahun Baru
                                </button>
                            </div>
                            <template x-if="academicYears.length > 0">
                                <select x-model="selectedAcademicYearId" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-indigo-600 font-medium">
                                    <template x-for="y in academicYears" :key="y.id">
                                        <option :value="y.id" x-text="y.year + (y.is_current ? ' (Tahun Aktif)' : '')"></option>
                                    </template>
                                </select>
                            </template>
                            <template x-if="academicYears.length === 0">
                                <div class="p-2.5 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-900 text-[11px] leading-relaxed">
                                    ℹ️ Belum ada tahun ajaran. Sistem akan otomatis menetapkan tahun ajaran aktif untuk kelas ini.
                                </div>
                            </template>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button type="button" @click="showAddClassModal = false" class="flex-1 py-2.5 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs hover:bg-slate-200 transition">Batal</button>
                            <button type="submit" class="flex-1 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 shadow transition">Simpan Kelas</button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- ==================== MODAL TAMBAH TAHUN AJARAN ==================== -->
            <div x-show="showAddYearModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
                <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-slate-100" @click.away="showAddYearModal = false">
                    <h3 class="text-base font-bold text-slate-800 mb-1">Tambah Tahun Ajaran</h3>
                    <p class="text-xs text-slate-500 mb-4">Tambahkan kalender akademik sekolah.</p>
                    <form @submit.prevent="submitAddYear()">
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-slate-600 mb-1">Tahun Ajaran</label>
                            <input type="text" x-model="newYearString" required placeholder="Contoh: 2027/2028" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none focus:border-indigo-600">
                        </div>
                        <div class="mb-4 flex items-center">
                            <input type="checkbox" id="isCurrYear" x-model="newYearIsCurrent" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                            <label for="isCurrYear" class="text-xs font-bold text-slate-700">Jadikan Tahun Aktif Saat Ini</label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button type="button" @click="showAddYearModal = false" class="flex-1 py-2.5 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs hover:bg-slate-200 transition">Batal</button>
                            <button type="submit" class="flex-1 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 shadow transition">Simpan Tahun</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Toast Notification -->
            <div x-show="toastMessage" class="fixed bottom-5 right-5 z-50 px-4 py-3 rounded-2xl shadow-xl text-xs font-bold transition flex items-center gap-2"
                 :class="toastType === 'success' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'" x-cloak>
                <span x-text="toastMessage"></span>
            </div>

            <!-- Persistent Error Detail Modal (stays until dismissed, with Copy Error button) -->
            <div x-show="errorMessageDetail" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
                <div class="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-rose-100 space-y-4" @click.away="errorMessageDetail = ''">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl flex-shrink-0 font-bold">
                            ⚠️
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Terjadi Kendala / Error</h3>
                            <p class="text-xs text-slate-500">Pesan error lengkap tertulis di bawah dan dapat disalin.</p>
                        </div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-mono max-h-48 overflow-y-auto whitespace-pre-wrap select-all leading-relaxed" x-text="errorMessageDetail"></div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="navigator.clipboard.writeText(errorMessageDetail); showToast('📋 Pesan error berhasil disalin ke clipboard!');" class="flex-1 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                            Salin Pesan Error
                        </button>
                        <button type="button" @click="errorMessageDetail = ''" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </template>


    <!-- ==================== JAVASCRIPT LOGIC (ALPINE.JS) ==================== -->
    <script>
        function kasApp() {
            return {
                isLoggedIn: false,
                isSubmitting: false,
                loginEmail: '',
                loginPassword: '',
                loginError: '',
                currentUser: null,
                token: '',
                activeTab: 'OVERVIEW',

                // Login Screen Hero Media (Admin Customizable)
                heroMediaUrl: localStorage.getItem('kas_hero_media_url') || 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?q=80&w=1600&auto=format&fit=crop',
                heroMediaType: localStorage.getItem('kas_hero_media_type') || 'IMAGE',
                showMediaModal: false,
                inputMediaUrl: '',
                inputMediaType: 'IMAGE',

                // Data Entities
                schools: [],
                activeSchoolId: '',
                currentSchool: null,
                classes: [],
                academicYears: [],
                users: [],
                billings: [],
                transactions: [],
                expenses: [],

                // Filter States
                reportStatusFilter: 'ALL',
                reportPeriodFilter: 'ALL',
                selectedMatrixClassId: 'ALL',
                selectedMatrixYear: 2026,

                // Modal States
                showSchoolListModal: false,
                showSchoolModal: false,
                editingSchoolId: null,
                newSchoolName: '',
                newSchoolCode: '',
                newSchoolAddress: '',

                showScheduleModal: false,
                cronTitle: 'Iuran Kas Wajib Bulanan',
                cronAmount: 25000,
                cronDay: 1,
                cronClassId: '',

                showPdfEmailModal: false,
                targetEmail: '',
                reportPdfType: 'LEDGER',

                showUserModal: false,
                editingUserId: null,
                userForm: {
                    name: '',
                    email: '',
                    password: '',
                    role: 'KORLAS',
                    managedClass: '',
                    studentId: '',
                },

                showPayModal: false,
                selectedBilling: null,
                payAmount: 0,

                showSchemeModal: false,
                schemeClassId: 'ALL',
                schemeTitle: '',
                schemeAmount: 25000,
                schemeDueDate: '',

                showIncomeModal: false,
                incomeCategory: 'Donasi',
                incomeAmount: 100000,
                incomeDescription: '',

                showExpenseModal: false,
                expenseTitle: '',
                expenseAmount: 0,
                expenseDescription: '',

                // Security Challenge & Error Detail States
                securityCity: '',
                securityToken: '',
                inputSecurityCity: '',
                errorMessageDetail: '',

                showAddStudentModal: false,
                newStudent: {
                    name: '',
                    nis: '',
                    nisn: '',
                    gender: 'MALE',
                    classId: '',
                    password: ''
                },

                showAddClassModal: false,
                newClassName: '',
                selectedAcademicYearId: '',

                showAddYearModal: false,
                newYearString: '',
                newYearIsCurrent: false,

                toastMessage: '',
                toastType: 'success',
                showToast(msg, type = 'success') {
                    if (type === 'error') {
                        this.showErrorModal(msg);
                        return;
                    }
                    this.toastMessage = msg;
                    this.toastType = type;
                    setTimeout(() => {
                        this.toastMessage = '';
                    }, 4500);
                },

                showErrorModal(msg) {
                    this.errorMessageDetail = msg;
                },

                formatRole(role) {
                    switch (role) {
                        case 'SUPER_ADMIN': return 'Super Admin';
                        case 'ADMIN': return 'Admin Sekolah';
                        case 'TREASURER': return 'Bendahara';
                        case 'KORLAS': return 'Koordinator Kelas';
                        case 'PARENT': return 'Wali Murid';
                        case 'STUDENT': return 'Siswa';
                        default: return role || '';
                    }
                },

                get korlasClassName() {
                    if (this.currentUser?.role !== 'KORLAS') return '';
                    const m = this.classes.find(c => c.id === this.currentUser?.managedClass);
                    return m ? m.name : 'Kelas Anda';
                },

                get students() {
                    return this.users.filter(u => u.role === 'STUDENT');
                },

                // Multi-tenancy Scoped Filters
                get scopedTransactions() {
                    return this.transactions.filter(t => {
                        if (this.activeSchoolId && t.school_id && t.school_id !== this.activeSchoolId) return false;
                        if (this.currentUser?.role === 'KORLAS' && this.currentUser?.managedClass) {
                            return t.cash_account?.class_id === this.currentUser.managedClass;
                        }
                        return true;
                    });
                },

                get scopedBillings() {
                    return this.billings.filter(b => {
                        if (this.activeSchoolId && b.school_id && b.school_id !== this.activeSchoolId) return false;
                        if (this.currentUser?.role === 'KORLAS' && this.currentUser?.managedClass) {
                            const cId = b.classId || b.student?.enrollments?.[0]?.class_id;
                            return cId === this.currentUser.managedClass;
                        }
                        if (this.currentUser?.role === 'STUDENT') {
                            return b.student_id === this.currentUser.id;
                        }
                        if (this.currentUser?.role === 'PARENT' && this.currentUser?.student_id) {
                            return b.student_id === this.currentUser.student_id;
                        }
                        return true;
                    });
                },

                get scopedStudents() {
                    return this.students.filter(u => {
                        if (this.activeSchoolId && u.school_id && u.school_id !== this.activeSchoolId) return false;
                        if (this.currentUser?.role === 'KORLAS' && this.currentUser?.managedClass) {
                            return u.enrollments?.[0]?.class_id === this.currentUser.managedClass;
                        }
                        return true;
                    });
                },

                get availablePeriods() {
                    const periods = new Set();
                    this.scopedBillings.forEach(b => {
                        if (b.period) periods.add(b.period);
                    });
                    return Array.from(periods);
                },

                get filteredReportBillings() {
                    let list = this.scopedBillings;
                    if (this.reportPeriodFilter && this.reportPeriodFilter !== 'ALL') {
                        list = list.filter(b => b.period === this.reportPeriodFilter);
                    }
                    if (this.reportStatusFilter !== 'ALL') {
                        list = list.filter(b => b.status === this.reportStatusFilter);
                    }
                    return list;
                },

                get filteredMatrixStudents() {
                    return this.scopedStudents.filter(s => {
                        if (this.selectedMatrixClassId === 'ALL') return true;
                        const cId = s.enrollments?.[0]?.class_id || s.managed_class;
                        return cId === this.selectedMatrixClassId;
                    });
                },

                get classMatrixData() {
                    const year = parseInt(this.selectedMatrixYear) || 2026;
                    const students = this.filteredMatrixStudents;

                    return students.map(s => {
                        const studentClassId = s.enrollments?.[0]?.class_id || s.managed_class;
                        const studentClassName = s.enrollments?.[0]?.class?.name || (this.classes.find(c => c.id === studentClassId)?.name) || '-';

                        const studentBillings = this.billings.filter(b => {
                            if (b.student_id !== s.id) return false;
                            if (!b.due_date) return false;
                            const d = new Date(b.due_date);
                            return d.getFullYear() === year;
                        });

                        let totalPaid = 0;
                        let totalPending = 0;
                        const months = {};

                        for (let m = 1; m <= 12; m++) {
                            const mBillings = studentBillings.filter(b => (new Date(b.due_date).getMonth() + 1) === m);
                            if (mBillings.length === 0) {
                                months[m] = {
                                    status: 'NONE',
                                    paid: 0,
                                    due: 0,
                                    pending: 0,
                                    text: '-'
                                };
                            } else {
                                const due = mBillings.reduce((sum, b) => sum + parseFloat(b.amount_due || 0), 0);
                                const paid = mBillings.reduce((sum, b) => sum + parseFloat(b.amount_paid || 0), 0);
                                const pending = Math.max(0, due - paid);
                                const isPaid = (pending === 0 && due > 0) || mBillings.every(b => b.status === 'PAID');

                                totalPaid += paid;
                                totalPending += pending;

                                if (isPaid) {
                                    months[m] = {
                                        status: 'PAID',
                                        paid: paid,
                                        due: due,
                                        pending: 0,
                                        text: this.formatNumber(paid)
                                    };
                                } else {
                                    months[m] = {
                                        status: 'UNPAID',
                                        paid: paid,
                                        due: due,
                                        pending: pending,
                                        text: '- ' + this.formatNumber(pending)
                                    };
                                }
                            }
                        }

                        return {
                            student_id: s.id,
                            student_name: s.name,
                            class_name: studentClassName,
                            months: months,
                            total_paid: totalPaid,
                            total_pending: totalPending
                        };
                    });
                },

                get classMatrixTotals() {
                    const data = this.classMatrixData;
                    const totalStudents = data.length;
                    const totalPaid = data.reduce((sum, d) => sum + d.total_paid, 0);
                    const totalPending = data.reduce((sum, d) => sum + d.total_pending, 0);
                    return {
                        totalStudents,
                        totalPaid,
                        totalPending
                    };
                },

                // Computed Financial Stats
                get computedBalance() {
                    const inc = this.scopedTransactions.filter(t => t.type === 'INCOME').reduce((s, t) => s + parseFloat(t.amount || 0), 0);
                    const exp = this.scopedTransactions.filter(t => t.type === 'EXPENSE').reduce((s, t) => s + parseFloat(t.amount || 0), 0);
                    return inc - exp;
                },

                get totalDuesExpected() {
                    return this.scopedBillings.reduce((s, b) => s + parseFloat(b.amount_due || 0), 0);
                },

                get totalDuesCollected() {
                    return this.scopedBillings.reduce((s, b) => s + parseFloat(b.amount_paid || 0), 0);
                },

                get totalPendingDues() {
                    const p = this.totalDuesExpected - this.totalDuesCollected;
                    return p > 0 ? p : 0;
                },

                get pendingBillingsCount() {
                    return this.scopedBillings.filter(b => b.status !== 'PAID').length;
                },

                get computedCollectionRate() {
                    if (this.totalDuesExpected <= 0) return '0';
                    return ((this.totalDuesCollected / this.totalDuesExpected) * 100).toFixed(1);
                },

                async apiFetch(url, options = {}) {
                    const headers = {
                        'Accept': 'application/json',
                        ...(options.headers || {})
                    };
                    if (this.token && !headers['Authorization']) {
                        headers['Authorization'] = `Bearer ${this.token}`;
                    }
                    return fetch(url, { ...options, headers });
                },

                async loadSecurityChallenge() {
                    try {
                        const res = await fetch('/api/v1/auth/security-challenge');
                        if (res.ok) {
                            const data = await res.json();
                            this.securityCity = data.city;
                            this.securityToken = data.token;
                        } else {
                            const fallbackCities = ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Yogyakarta', 'Denpasar', 'Makassar', 'Malang', 'Bogor', 'Solo'];
                            this.securityCity = fallbackCities[Math.floor(Math.random() * fallbackCities.length)];
                            this.securityToken = '';
                        }
                    } catch (e) {
                        const fallbackCities = ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Yogyakarta', 'Denpasar', 'Makassar', 'Malang', 'Bogor', 'Solo'];
                        this.securityCity = fallbackCities[Math.floor(Math.random() * fallbackCities.length)];
                        this.securityToken = '';
                    }
                    this.inputSecurityCity = '';
                },

                initApp() {
                    const savedUser = localStorage.getItem('kas_user');
                    const savedToken = localStorage.getItem('kas_token');
                    if (savedUser && savedToken) {
                        try {
                            this.currentUser = JSON.parse(savedUser);
                            this.token = savedToken;
                            this.isLoggedIn = true;
                            this.fetchSchools();
                        } catch (e) {
                            this.handleLogout();
                        }
                    } else {
                        this.loadSecurityChallenge();
                    }
                },

                async handleLogin() {
                    this.isSubmitting = true;
                    this.loginError = '';

                    // Validate random city security challenge
                    if (this.securityCity && this.inputSecurityCity.trim().toLowerCase() !== this.securityCity.toLowerCase()) {
                        this.loginError = `Verifikasi keamanan tidak sesuai. Silakan ketik nama kota "${this.securityCity}" dengan benar.`;
                        this.isSubmitting = false;
                        return;
                    }

                    try {
                        const res = await this.apiFetch('/api/v1/auth/login', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                email: this.loginEmail,
                                password: this.loginPassword,
                                securityCity: this.inputSecurityCity.trim(),
                                securityToken: this.securityToken
                            })
                        });
                        const data = await res.json();
                        if (!res.ok) throw new Error(data.message || 'Login gagal.');

                        this.currentUser = data.user;
                        this.token = data.accessToken || data.token;
                        localStorage.setItem('kas_user', JSON.stringify(data.user));
                        localStorage.setItem('kas_token', this.token);
                        this.isLoggedIn = true;

                        await this.fetchSchools();
                    } catch (err) {
                        this.loginError = err.message;
                        this.loadSecurityChallenge();
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                handleLogout() {
                    this.isLoggedIn = false;
                    this.currentUser = null;
                    this.token = '';
                    localStorage.removeItem('kas_user');
                    localStorage.removeItem('kas_token');
                    this.loadSecurityChallenge();
                },

                async fetchSchools() {
                    try {
                        const res = await this.apiFetch('/api/v1/schools');
                        if (!res.ok) return;
                        this.schools = await res.json();

                        if (this.currentUser?.role === 'SUPER_ADMIN') {
                            if (!this.activeSchoolId) {
                                const defaultSchool = this.schools.find(s => s.code !== 'sistemkas') || this.schools[0];
                                this.activeSchoolId = defaultSchool?.id || '';
                            }
                        } else {
                            this.activeSchoolId = this.currentUser?.schoolId || this.currentUser?.school_id || this.schools[0]?.id || '';
                        }
                        this.changeSchool();
                    } catch (e) {
                        console.error(e);
                    }
                },

                async changeSchool() {
                    this.currentSchool = this.schools.find(s => s.id === this.activeSchoolId) || this.schools[0];
                    if (!this.activeSchoolId) return;

                    await Promise.all([
                        this.fetchClasses(),
                        this.fetchAcademicYears(),
                        this.fetchUsers(),
                        this.fetchBillings(),
                        this.fetchTransactions()
                    ]);
                },

                async fetchClasses() {
                    try {
                        const schoolParam = this.activeSchoolId ? `?schoolId=${this.activeSchoolId}` : '';
                        const res = await this.apiFetch(`/api/v1/classes${schoolParam}`);
                        if (res.ok) {
                            this.classes = await res.json();
                            if (this.classes.length > 0 && !this.cronClassId) {
                                this.cronClassId = this.classes[0].id;
                            }
                            if (this.classes.length > 0 && (!this.schemeClassId || this.schemeClassId === 'ALL')) {
                                this.schemeClassId = 'ALL';
                            }
                        }
                    } catch (e) {}
                },

                async fetchAcademicYears() {
                    try {
                        const res = await this.apiFetch(`/api/v1/academic-years?schoolId=${this.activeSchoolId}`);
                        if (res.ok) {
                            this.academicYears = await res.json();
                            const curr = this.academicYears.find(y => y.is_current);
                            if (curr) this.selectedAcademicYearId = curr.id;
                        }
                    } catch (e) {}
                },

                async fetchUsers() {
                    try {
                        const res = await this.apiFetch(`/api/v1/users?schoolId=${this.activeSchoolId}`);
                        if (res.ok) this.users = await res.json();
                    } catch (e) {}
                },

                async fetchBillings() {
                    try {
                        const schoolParam = this.activeSchoolId ? `?schoolId=${this.activeSchoolId}` : '';
                        const res = await this.apiFetch(`/api/v1/billings${schoolParam}`);
                        if (res.ok) {
                            const raw = await res.json();
                            this.billings = raw.map(b => {
                                const studentName = b.student?.name || 'Unknown Student';
                                const className = b.student?.enrollments?.[0]?.class?.name || (this.classes.find(c => c.id === b.student?.managed_class)?.name) || 'Kelas 5-A';
                                const classId = b.student?.enrollments?.[0]?.class_id || b.student?.managed_class;
                                const scheme = b.dues_scheme || b.duesScheme;
                                const schemeTitle = scheme?.title || 'Iuran Kas';
                                const dueDate = b.due_date || scheme?.due_date;
                                const period = new Date(dueDate || Date.now()).toLocaleString('id-ID', { month: 'long', year: 'numeric' });
                                const amountDue = parseFloat(b.amount_due ?? b.amountDue ?? 0);
                                const amountPaid = parseFloat(b.amount_paid ?? b.amountPaid ?? 0);
                                const isPaid = b.status === 'PAID';
                                const paidAt = isPaid ? (b.updated_at ? new Date(b.updated_at).toLocaleString('id-ID') : 'Sudah Lunas') : 'Belum ada setoran';

                                return {
                                    id: b.id,
                                    school_id: b.student?.school_id || this.activeSchoolId,
                                    student_id: b.student_id,
                                    student: b.student,
                                    studentName: studentName,
                                    className: className,
                                    classId: classId,
                                    dues_scheme: scheme,
                                    schemeTitle: schemeTitle,
                                    due_date: dueDate,
                                    period: period,
                                    amount_due: amountDue,
                                    amount_paid: amountPaid,
                                    status: b.status,
                                    paidAt: paidAt,
                                };
                            });
                        }
                    } catch (e) {
                        console.error('Failed to fetch billings', e);
                    }
                },

                async fetchTransactions() {
                    try {
                        const res = await this.apiFetch(`/api/v1/transactions/school/${this.activeSchoolId}`);
                        if (res.ok) this.transactions = await res.json();
                    } catch (e) {}
                },

                // Hero Media Handlers
                openMediaModal() {
                    this.inputMediaUrl = this.heroMediaUrl;
                    this.inputMediaType = this.heroMediaType;
                    this.showMediaModal = true;
                },

                handleSaveHeroMedia() {
                    if (!this.inputMediaUrl) return this.showToast('URL Media wajib diisi.', 'error');
                    this.heroMediaUrl = this.inputMediaUrl;
                    this.heroMediaType = this.inputMediaType;
                    localStorage.setItem('kas_hero_media_url', this.heroMediaUrl);
                    localStorage.setItem('kas_hero_media_type', this.heroMediaType);
                    this.showMediaModal = false;
                    this.showToast('🖼️ Foto/Video Banner Login Sekolah berhasil diperbarui!');
                },

                handleMediaFileUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const isVideo = file.type.startsWith('video/');
                    this.inputMediaType = isVideo ? 'VIDEO' : 'IMAGE';
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.inputMediaUrl = e.target.result;
                    };
                    reader.readAsDataURL(file);
                },

                // School Management Handlers
                openSchoolListModal() {
                    this.showSchoolListModal = true;
                },

                openAddSchoolModal() {
                    this.editingSchoolId = null;
                    this.newSchoolName = '';
                    this.newSchoolCode = '';
                    this.newSchoolAddress = '';
                    this.showSchoolModal = true;
                },

                openEditSchoolModal(s) {
                    this.editingSchoolId = s.id;
                    this.newSchoolName = s.name;
                    this.newSchoolCode = s.code;
                    this.newSchoolAddress = s.address || '';
                    this.showSchoolModal = true;
                },

                async deleteSchool(id) {
                    if (!confirm('Apakah Anda yakin ingin menghapus sekolah ini?')) return;
                    try {
                        const res = await this.apiFetch(`/api/v1/schools/${id}`, { method: 'DELETE' });
                        if (!res.ok) throw new Error('Gagal menghapus sekolah');
                        this.showToast('Sekolah berhasil dihapus');
                        await this.fetchSchools();
                    } catch (e) {
                        this.showToast(e.message || 'Gagal menghapus sekolah', 'error');
                    }
                },

                async saveSchool() {
                    if (!this.newSchoolName || !this.newSchoolCode) return this.showToast('Nama dan kode wajib diisi', 'error');
                    try {
                        const url = this.editingSchoolId ? `/api/v1/schools/${this.editingSchoolId}` : '/api/v1/schools';
                        const method = this.editingSchoolId ? 'PUT' : 'POST';
                        const res = await this.apiFetch(url, {
                            method,
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                name: this.newSchoolName,
                                code: this.newSchoolCode,
                                address: this.newSchoolAddress
                            })
                        });
                        if (!res.ok) {
                            const data = await res.json();
                            throw new Error(data.message || 'Gagal menyimpan sekolah');
                        }
                        this.showSchoolModal = false;
                        await this.fetchSchools();
                        this.showToast(this.editingSchoolId ? 'Sekolah berhasil diperbarui' : 'Sekolah berhasil ditambahkan');
                    } catch (e) {
                        this.showToast(e.message, 'error');
                    }
                },

                // Scheduled Dues Handler
                openScheduleModal() {
                    if (this.currentUser?.role === 'KORLAS' && this.currentUser?.managedClass) {
                        this.cronClassId = this.currentUser.managedClass;
                    } else if (this.classes.length > 0) {
                        this.cronClassId = this.classes[0].id;
                    }
                    this.showScheduleModal = true;
                },

                async submitScheduleDues() {
                    if (!this.cronClassId) return this.showToast('Pilih kelas terlebih dahulu', 'error');
                    try {
                        const cashRes = await this.apiFetch(`/api/v1/cash-accounts/class/${this.cronClassId}`);
                        if (!cashRes.ok) throw new Error('Gagal mengambil data akun kas');
                        const cashAccs = await cashRes.json();
                        const cashAccountId = Array.isArray(cashAccs) ? cashAccs[0]?.id : cashAccs?.id;
                        if (!cashAccountId) {
                            return this.showToast('Akun kas untuk kelas ini belum tersedia', 'error');
                        }

                        const nextMonth = new Date();
                        nextMonth.setMonth(nextMonth.getMonth() + 1);
                        nextMonth.setDate(this.cronDay);
                        const dueDate = nextMonth.toISOString().split('T')[0];

                        const res = await this.apiFetch('/api/v1/billings/dues-scheme', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                cashAccountId,
                                title: this.cronTitle,
                                amount: this.cronAmount,
                                dueDate
                            })
                        });
                        const data = await res.json();
                        if (!res.ok) throw new Error(data.message || 'Gagal membuat jadwal tagihan');

                        this.showScheduleModal = false;
                        this.showToast(`⏰ Tagihan '${this.cronTitle}' berhasil dibuat untuk ${data.totalBillingsGenerated || 0} siswa!`);
                        await this.fetchBillings();
                    } catch (e) {
                        this.showToast(e.message, 'error');
                    }
                },

                // PDF Email Dialog Handler
                openPdfEmailModal(type = 'LEDGER') {
                    this.reportPdfType = type;
                    this.targetEmail = this.currentUser?.email || '';
                    this.showPdfEmailModal = true;
                },

                openClassMatrixPdfModal() {
                    this.reportPdfType = 'CLASS_MATRIX';
                    this.targetEmail = this.currentUser?.email || '';
                    this.showPdfEmailModal = true;
                },

                async submitSendPdfEmail() {
                    if (!this.targetEmail) return this.showToast('Masukkan alamat email tujuan', 'error');
                    this.showPdfEmailModal = false;
                    this.showToast('⏳ Sedang menyusun PDF dan mengirimkan email...', 'info');
                    try {
                        let items = [];
                        let summary = {};
                        let reportTitle = '';

                        if (this.reportPdfType === 'CLASS_MATRIX') {
                            const selectedClassName = this.selectedMatrixClassId === 'ALL'
                                ? 'Semua Kelas'
                                : (this.classes.find(c => c.id === this.selectedMatrixClassId)?.name || 'Kelas');
                            summary = {
                                totalStudents: this.classMatrixTotals.totalStudents,
                                totalPaid: this.classMatrixTotals.totalPaid,
                                totalPending: this.classMatrixTotals.totalPending
                            };
                            items = this.classMatrixData.map(d => ({
                                student_name: d.student_name,
                                class_name: d.class_name,
                                months: d.months,
                                total_paid: d.total_paid,
                                total_pending: d.total_pending
                            }));
                            reportTitle = `Laporan Matriks Iuran Siswa (${selectedClassName} - Tahun ${this.selectedMatrixYear})`;

                            const res = await this.apiFetch('/api/v1/notifications/email-report', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    email: this.targetEmail,
                                    reportTitle: reportTitle,
                                    reportType: 'CLASS_MATRIX',
                                    schoolName: this.currentSchool?.name || 'Sekolah',
                                    className: selectedClassName,
                                    year: parseInt(this.selectedMatrixYear) || 2026,
                                    summary: summary,
                                    items: items
                                })
                            });
                            const data = await res.json();
                            if (!res.ok) throw new Error(data.message || 'Gagal mengirim email');
                            this.showToast(data.message || `📄 Laporan Matriks PDF berhasil dikirimkan ke email ${this.targetEmail}!`);
                            return;
                        }

                        const isDues = (this.reportPdfType === 'DUES' || this.reportPdfType === 'REPORTS');
                        if (isDues) {
                            items = this.filteredReportBillings.map(b => ({
                                student_name: b.student?.name || 'Siswa',
                                title: b.dues_scheme?.title || 'Iuran Kas',
                                due_date: b.due_date,
                                amount: parseFloat(b.amount_due || 0),
                                status: b.status
                            }));
                            summary = {
                                totalExpected: this.totalDuesExpected,
                                totalCollected: this.totalDuesCollected,
                                totalPending: this.totalPendingDues,
                                rate: this.computedCollectionRate
                            };
                            reportTitle = `Laporan Iuran Siswa - ${this.currentSchool?.name || 'Sekolah'}`;
                        } else {
                            items = this.scopedTransactions.map(t => ({
                                date: t.created_at,
                                type: t.type,
                                category: t.category,
                                description: t.description,
                                amount: parseFloat(t.amount || 0)
                            }));
                            summary = {
                                totalIncome: this.scopedTransactions.filter(t => t.type === 'INCOME').reduce((s, t) => s + parseFloat(t.amount || 0), 0),
                                totalExpense: this.scopedTransactions.filter(t => t.type === 'EXPENSE').reduce((s, t) => s + parseFloat(t.amount || 0), 0),
                                balance: this.computedBalance
                            };
                            reportTitle = `Laporan Buku Besar Kas - ${this.currentSchool?.name || 'Sekolah'}`;
                        }

                        const res = await this.apiFetch('/api/v1/notifications/email-report', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                email: this.targetEmail,
                                reportTitle: reportTitle,
                                reportType: this.reportPdfType,
                                schoolName: this.currentSchool?.name || 'Sekolah',
                                summary: summary,
                                items: items
                            })
                        });
                        const data = await res.json();
                        if (!res.ok) throw new Error(data.message || 'Gagal mengirim email');
                        this.showToast(data.message || `📄 Laporan PDF berhasil dikirimkan ke email ${this.targetEmail}!`);
                    } catch (e) {
                        this.showToast(e.message || 'Gagal mengirim email', 'error');
                    }
                },

                // User Account Management Handlers
                openAddAccountModal() {
                    this.editingUserId = null;
                    this.userForm = {
                        name: '',
                        email: '',
                        password: '',
                        role: this.currentUser?.role === 'SUPER_ADMIN' ? 'ADMIN' : 'KORLAS',
                        managedClass: this.classes[0]?.id || '',
                        studentId: this.students[0]?.id || ''
                    };
                    this.showUserModal = true;
                },

                openEditAccountModal(u) {
                    this.editingUserId = u.id;
                    this.userForm = {
                        name: u.name,
                        email: u.email,
                        password: '',
                        role: u.role,
                        managedClass: u.managed_class || '',
                        studentId: u.student_id || ''
                    };
                    this.showUserModal = true;
                },

                async saveUserAccount() {
                    if (!this.userForm.name || !this.userForm.email) return this.showToast('Nama dan email wajib diisi', 'error');
                    if (!this.editingUserId && !this.userForm.password) return this.showToast('Password wajib diisi untuk akun baru (minimal 6 karakter)', 'error');
                    try {
                        if (this.editingUserId) {
                            const res = await this.apiFetch(`/api/v1/users/${this.editingUserId}`, {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(this.userForm)
                            });
                            if (!res.ok) throw new Error('Gagal memperbarui akun');
                            this.showToast('Akun berhasil diperbarui');
                        } else {
                            const res = await this.apiFetch('/api/v1/auth/register', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    schoolId: this.activeSchoolId,
                                    name: this.userForm.name,
                                    email: this.userForm.email,
                                    password: this.userForm.password,
                                    role: this.userForm.role,
                                    managedClass: this.userForm.managedClass,
                                    studentId: this.userForm.studentId
                                })
                            });
                            if (!res.ok) throw new Error('Gagal mendaftarkan akun');
                            this.showToast('Akun berhasil didaftarkan');
                        }
                        this.showUserModal = false;
                        await this.fetchUsers();
                    } catch (e) {
                        this.showToast(e.message, 'error');
                    }
                },

                async deleteUserAccount(id) {
                    if (!confirm('Apakah Anda yakin ingin menghapus/menonaktifkan akun ini?')) return;
                    try {
                        const res = await this.apiFetch(`/api/v1/users/${id}`, { method: 'DELETE' });
                        if (!res.ok) throw new Error('Gagal memproses akun');
                        this.showToast('Akun berhasil dihapus/dinonaktifkan');
                        await this.fetchUsers();
                    } catch (e) {
                        this.showToast(e.message, 'error');
                    }
                },

                // Action Modals: Pay, Scheme, Income, Expense, Add Student/Class/Year
                openPayModal(billing) {
                    this.selectedBilling = billing;
                    this.payAmount = billing.amount_due - billing.amount_paid;
                    this.showPayModal = true;
                },

                async confirmPayBilling() {
                    if (!this.selectedBilling) return;
                    try {
                        const res = await this.apiFetch(`/api/v1/billings/${this.selectedBilling.id}/pay`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                amount: this.payAmount,
                                createdBy: this.currentUser?.id
                            })
                        });
                        if (!res.ok) throw new Error('Gagal memproses pembayaran');
                        this.showPayModal = false;
                        await Promise.all([this.fetchBillings(), this.fetchTransactions()]);
                        this.showToast('✓ Pembayaran iuran berhasil dicatat!');
                    } catch (e) {
                        this.showToast(e.message, 'error');
                    }
                },

                openSchemeModal() {
                    this.schemeTitle = 'Iuran Kas ' + new Date().toLocaleString('id-ID', { month: 'long', year: 'numeric' });
                    this.schemeAmount = 25000;
                    const nextMonth = new Date();
                    nextMonth.setMonth(nextMonth.getMonth() + 1);
                    this.schemeDueDate = nextMonth.toISOString().split('T')[0];

                    if (this.currentUser?.role === 'KORLAS' && this.currentUser?.managedClass) {
                        this.schemeClassId = this.currentUser.managedClass;
                    } else if (this.classes.length > 0) {
                        this.schemeClassId = this.schemeClassId || 'ALL';
                    } else {
                        this.schemeClassId = 'ALL';
                    }
                    this.showSchemeModal = true;
                },

                async submitCreateScheme() {
                    if (!this.schemeTitle) return this.showToast('Judul tagihan wajib diisi.', 'error');
                    if (!this.schemeAmount || this.schemeAmount <= 0) return this.showToast('Nominal tagihan harus lebih dari 0.', 'error');
                    if (!this.schemeDueDate) return this.showToast('Tanggal jatuh tempo wajib diisi.', 'error');

                    try {
                        const payload = {
                            schoolId: this.activeSchoolId,
                            classId: this.schemeClassId || 'ALL',
                            title: this.schemeTitle,
                            amount: this.schemeAmount,
                            dueDate: this.schemeDueDate
                        };

                        if (this.schemeClassId && this.schemeClassId !== 'ALL') {
                            const cashRes = await this.apiFetch(`/api/v1/cash-accounts/class/${this.schemeClassId}`);
                            if (cashRes.ok) {
                                const cashAccs = await cashRes.json();
                                const caId = Array.isArray(cashAccs) ? cashAccs[0]?.id : cashAccs?.id;
                                if (caId) payload.cashAccountId = caId;
                            }
                        }

                        const res = await this.apiFetch('/api/v1/billings/dues-scheme', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if (!res.ok) throw new Error(data.message || 'Gagal menerbitkan tagihan');

                        this.showSchemeModal = false;
                        await this.fetchBillings();
                        this.showToast(`⏰ Tagihan '${this.schemeTitle}' berhasil dibuat untuk ${data.totalBillingsGenerated || 0} siswa!`);
                    } catch (e) {
                        this.showToast(e.message || 'Gagal menerbitkan tagihan', 'error');
                    }
                },

                openIncomeModal() {
                    this.incomeCategory = 'Donasi';
                    this.incomeAmount = 50000;
                    this.incomeDescription = '';
                    this.showIncomeModal = true;
                },

                async submitDirectIncome() {
                    if (!this.incomeDescription) return this.showToast('Keterangan kas masuk wajib diisi.', 'error');
                    try {
                        const cashRes = await this.apiFetch(`/api/v1/cash-accounts/class/${this.classes[0]?.id}`);
                        const cashAccs = await cashRes.json();
                        const cashAccountId = Array.isArray(cashAccs) ? cashAccs[0]?.id : cashAccs?.id;
                        if (!cashAccountId) return this.showToast('Akun kas kelas belum tersedia.', 'error');

                        const res = await this.apiFetch('/api/v1/transactions', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                cashAccountId: cashAccountId,
                                type: 'INCOME',
                                amount: this.incomeAmount,
                                category: this.incomeCategory,
                                description: this.incomeDescription,
                                recordedBy: this.currentUser?.id
                            })
                        });
                        if (!res.ok) throw new Error('Gagal mencatat kas masuk');
                        this.showIncomeModal = false;
                        await this.fetchTransactions();
                        this.showToast('✓ Kas masuk berhasil dicatat!');
                    } catch (e) {
                        this.showToast(e.message, 'error');
                    }
                },

                openExpenseModal() {
                    this.expenseTitle = '';
                    this.expenseAmount = 25000;
                    this.expenseDescription = '';
                    this.showExpenseModal = true;
                },

                async submitExpense() {
                    if (!this.expenseTitle) return this.showToast('Keperluan belanja wajib diisi.', 'error');
                    try {
                        const cashRes = await this.apiFetch(`/api/v1/cash-accounts/class/${this.classes[0]?.id}`);
                        const cashAccs = await cashRes.json();
                        const cashAccountId = Array.isArray(cashAccs) ? cashAccs[0]?.id : cashAccs?.id;
                        if (!cashAccountId) return this.showToast('Akun kas kelas belum tersedia.', 'error');

                        const res = await this.apiFetch('/api/v1/transactions', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                cashAccountId: cashAccountId,
                                type: 'EXPENSE',
                                amount: this.expenseAmount,
                                category: 'Operasional Kelas',
                                description: this.expenseTitle + (this.expenseDescription ? ' - ' + this.expenseDescription : ''),
                                recordedBy: this.currentUser?.id
                            })
                        });
                        if (!res.ok) throw new Error('Gagal mencatat pengeluaran');
                        this.showExpenseModal = false;
                        await this.fetchTransactions();
                        this.showToast('✓ Pengeluaran kas berhasil dicatat!');
                    } catch (e) {
                        this.showToast(e.message, 'error');
                    }
                },

                openAddStudentModal() {
                    this.newStudent = {
                        name: '',
                        nis: '',
                        nisn: '',
                        gender: 'MALE',
                        classId: this.classes[0]?.id || '',
                        password: ''
                    };
                    this.showAddStudentModal = true;
                },

                async submitAddStudent() {
                    if (!this.newStudent.name) return this.showToast('Nama siswa wajib diisi.', 'error');
                    const generatedEmail = `${this.newStudent.name.toLowerCase().replace(/[^a-z0-9]/g, '')}_${Date.now()}@sekolah.sch.id`;
                    try {
                        const res = await this.apiFetch('/api/v1/auth/register', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                schoolId: this.activeSchoolId,
                                name: this.newStudent.name,
                                email: generatedEmail,
                                password: this.newStudent.password || null,
                                role: 'STUDENT',
                                nis: this.newStudent.nis || null,
                                nisn: this.newStudent.nisn || null,
                                gender: this.newStudent.gender,
                                classId: this.newStudent.classId || (this.classes[0]?.id || null)
                            })
                        });
                        if (!res.ok) throw new Error('Gagal menambahkan siswa');
                        this.showAddStudentModal = false;
                        await this.fetchUsers();
                        this.showToast(`✓ Siswa '${this.newStudent.name}' berhasil didaftarkan!`);
                    } catch (e) {
                        this.showToast(e.message, 'error');
                    }
                },

                openAddClassModal() {
                    this.newClassName = '';
                    const curr = this.academicYears.find(y => y.is_current) || this.academicYears[0];
                    this.selectedAcademicYearId = curr ? curr.id : '';
                    this.showAddClassModal = true;
                },

                async submitAddClass() {
                    if (!this.newClassName) return this.showToast('Nama kelas wajib diisi.', 'error');
                    try {
                        const res = await this.apiFetch('/api/v1/classes', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                academicYearId: this.selectedAcademicYearId || null,
                                schoolId: this.activeSchoolId,
                                name: this.newClassName
                            })
                        });
                        const data = await res.json();
                        if (!res.ok) throw new Error(data.message || 'Gagal membuat kelas');
                        this.showAddClassModal = false;
                        await Promise.all([this.fetchClasses(), this.fetchAcademicYears()]);
                        this.showToast(`✓ Kelas '${this.newClassName}' berhasil dibuat!`);
                    } catch (e) {
                        this.showToast(e.message, 'error');
                    }
                },

                openAddYearModal() {
                    this.newYearString = '';
                    this.newYearIsCurrent = false;
                    this.showAddYearModal = true;
                },

                async submitAddYear() {
                    if (!this.newYearString) return this.showToast('Tahun ajaran wajib diisi.', 'error');
                    try {
                        const res = await this.apiFetch('/api/v1/academic-years', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                schoolId: this.activeSchoolId,
                                year: this.newYearString,
                                isCurrent: this.newYearIsCurrent
                            })
                        });
                        if (!res.ok) throw new Error('Gagal menambahkan tahun ajaran');
                        this.showAddYearModal = false;
                        await this.fetchAcademicYears();
                        this.showToast(`✓ Tahun ajaran '${this.newYearString}' berhasil ditambahkan!`);
                    } catch (e) {
                        this.showToast(e.message, 'error');
                    }
                },

                // Formatting Helpers
                formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(amount || 0);
                },

                formatNumber(amount) {
                    return new Intl.NumberFormat('id-ID').format(amount || 0);
                },

                formatDate(dateStr) {
                    if (!dateStr) return '-';
                    return new Date(dateStr).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric'
                    });
                }
            };
        }
    </script>
</body>
</html>
