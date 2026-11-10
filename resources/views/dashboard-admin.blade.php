<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin Kemahasiswaan & Ormawa - Portal Terpadu</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased selection:bg-cyan-500 selection:text-white">
    @php
        $roleUser = strtolower($user['role'] ?? 'admin');
        $isAdmin  = ($roleUser === 'admin');
        $isOrmawa = ($roleUser === 'ormawa');
    @endphp

    <!-- Top Navigation Bar -->
    <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr {{ $isOrmawa ? 'from-amber-600 via-orange-600 to-amber-700' : 'from-cyan-600 via-sky-600 to-blue-600' }} flex items-center justify-center text-white font-extrabold text-base shadow-md {{ $isOrmawa ? 'shadow-amber-500/20' : 'shadow-cyan-500/20' }}">
                    {{ $isOrmawa ? 'OR' : 'AK' }}
                </div>
                <div>
                    <h1 class="text-sm font-extrabold text-slate-900 tracking-tight leading-tight flex items-center gap-2">
                        {{ $isOrmawa ? 'Portal Pengurus Ormawa' : 'Portal Kemahasiswaan & Ormawa' }}
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $isOrmawa ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-cyan-50 text-cyan-700 border border-cyan-200' }}">
                            {{ $isOrmawa ? 'Pengurus Ormawa' : 'Admin Kampus' }}
                        </span>
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">
                        {{ $isOrmawa ? 'Pusat Manajemen Kegiatan & Monitoring Pendaftar' : 'Pusat Kendali Master Data & Verifikasi Kemahasiswaan' }} &bull; Port 8000
                    </p>
                </div>
            </div>

            <!-- Profile Info & Logout -->
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold text-slate-900 leading-tight">{{ $user['nama_lengkap'] ?? 'Admin Kemahasiswaan' }}</div>
                    <div class="text-[11px] {{ $isOrmawa ? 'text-amber-700' : 'text-cyan-700' }} font-semibold mt-0.5">Role: {{ strtoupper($user['role'] ?? 'ADMIN') }} &bull; {{ $user['nim_nidn'] ?? 'ADM001' }}</div>
                </div>

                <div class="w-10 h-10 rounded-full {{ $isOrmawa ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-cyan-100 text-cyan-800 border-cyan-200' }} font-bold flex items-center justify-center text-xs border hidden sm:flex">
                    {{ strtoupper(substr($user['nama_lengkap'] ?? 'A', 0, 2)) }}
                </div>

                <form method="POST" action="{{ route('web.logout') }}">
                    @csrf
                    <button type="submit" title="Keluar dari sistem"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-700 hover:text-rose-600 border border-slate-200 hover:border-rose-200 text-xs font-semibold transition active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <!-- Flash Messages & Alerts -->
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div>
                        <div class="font-bold">Aksi Berhasil!</div>
                        <div class="text-xs text-emerald-700">{{ session('success') }}</div>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 text-xl font-bold p-1">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <div class="font-bold">Peringatan!</div>
                        <div class="text-xs text-rose-700">{{ session('error') }}</div>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800 text-xl font-bold p-1">&times;</button>
            </div>
        @endif

        @if (!empty($errorNotices))
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-1">
                <div class="font-bold flex items-center gap-1.5 text-amber-800">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Status Komunikasi Antar-Microservices
                </div>
                @foreach ($errorNotices as $notice)
                    <div>&bull; {{ $notice }}</div>
                @endforeach
            </div>
        @endif

        <!-- ============================================================= -->
        <!-- HERO WELCOME BANNER & STATISTIC CARDS                         -->
        <!-- ============================================================= -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-950 via-cyan-950 to-blue-950 p-6 sm:p-8 text-white shadow-xl">
            <!-- Decorative Glow Elements -->
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-cyan-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute right-32 top-0 w-44 h-44 bg-blue-400/15 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-xs font-semibold {{ $isOrmawa ? 'text-amber-200' : 'text-cyan-200' }} border border-white/15">
                        <span class="w-2 h-2 rounded-full {{ $isOrmawa ? 'bg-amber-400' : 'bg-cyan-400' }} animate-pulse"></span>
                        {{ $isOrmawa ? 'Pengurus Organisasi Mahasiswa (Ormawa)' : 'Admin Kemahasiswaan & Ormawa • Pusat Kontrol' }}
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        {{ $isOrmawa ? 'Pusat Kegiatan Ormawa 🏛️' : 'Pusat Kendali Kemahasiswaan 🏛️' }}
                    </h2>
                    <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                        @if ($isOrmawa)
                            Kelola kegiatan organisasi, tentukan berkas persyaratan pendaftaran, dan pantau mahasiswa yang mendaftar dari seluruh jurusan secara real-time.
                        @else
                            Kelola master kegiatan kampus, terbitkan pengumuman lomba dan beasiswa, verifikasi akhir berkas pendaftaran mahasiswa, serta validasi sertifikat prestasi untuk poin SKPI.
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <button type="button" onclick="document.getElementById('modalTambahKategori').classList.remove('hidden')"
                        class="px-3.5 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 text-white font-semibold text-xs transition active:scale-95 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        <span>+ Kategori</span>
                    </button>

                    <button type="button" onclick="document.getElementById('modalTambahKegiatan').classList.remove('hidden')"
                        class="px-4 py-2.5 rounded-2xl bg-gradient-to-r {{ $isOrmawa ? 'from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 shadow-amber-500/25' : 'from-cyan-500 to-blue-500 hover:from-cyan-400 hover:to-blue-400 shadow-cyan-500/25' }} text-white font-bold text-xs shadow-lg transition active:scale-95 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>+ Tambah Kegiatan Baru</span>
                    </button>
                </div>
            </div>

            @php
                $pubCount = collect($kegiatans)->where('status_publikasi', 'Published')->count();
                $draftCount = collect($kegiatans)->where('status_publikasi', 'Draft')->count();
                $pendingPendaftaran = collect($pendaftarans)->where('status_pendaftaran', 'Menunggu')->count();
                $pendingPrestasiFinal = collect($prestasis)->where('status_validasi_akhir', 'Menunggu')->count();
            @endphp

            <!-- Modern Stat Cards -->
            <div class="grid grid-cols-2 {{ $isAdmin ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }} gap-3 sm:gap-4 mt-6 pt-6 border-t border-white/10">
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-cyan-500/30 flex items-center justify-center text-cyan-300 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-300 font-medium">Master Kegiatan</div>
                        <div class="text-xl font-extrabold text-white">{{ count($kegiatans) }} Program</div>
                        <div class="text-[10px] text-cyan-300 font-semibold">{{ $pubCount }} Terbit &bull; {{ $draftCount }} Draft</div>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-sky-500/30 flex items-center justify-center text-sky-300 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-300 font-medium">Total Pendaftaran</div>
                        <div class="text-xl font-bold text-white">{{ count($pendaftarans) }} Peserta</div>
                        <div class="text-[10px] text-amber-300 font-semibold">{{ $pendingPendaftaran }} Butuh Verifikasi</div>
                    </div>
                </div>

                @if ($isAdmin)
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-indigo-500/30 flex items-center justify-center text-indigo-300 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <div class="text-[11px] text-slate-300 font-medium">Klaim Sertifikat SKPI</div>
                            <div class="text-xl font-bold text-white">{{ count($prestasis) }} Berkas</div>
                            <div class="text-[10px] text-indigo-300 font-semibold">{{ $pendingPrestasiFinal }} Menunggu Final</div>
                        </div>
                    </div>
                @endif

                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-emerald-500/30 flex items-center justify-center text-emerald-300 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-300 font-medium">Kategori Tersedia</div>
                        <div class="text-xl font-bold text-white">{{ count($kategoris) }} Kategori</div>
                        <div class="text-[10px] text-emerald-300 font-semibold">Tersinkron di Port 8002</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- NAVIGATION TABS                                               -->
        <!-- ============================================================= -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-3">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="tabBtnKegiatan" onclick="switchAdminTab('kegiatan')"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-cyan-600 text-white shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span>Master Kegiatan</span>
                    <span class="px-2 py-0.5 rounded-full bg-white/20 text-white text-[10px]">{{ count($kegiatans) }}</span>
                </button>

                <button type="button" id="tabBtnPendaftaran" onclick="switchAdminTab('pendaftaran')"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ $isAdmin ? 'Verifikasi Pendaftaran' : 'Data Pendaftar Kegiatan' }}</span>
                    <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 text-[10px]">{{ count($pendaftarans) }}</span>
                </button>

                @if ($isAdmin)
                    <button type="button" id="tabBtnPrestasi" onclick="switchAdminTab('prestasi')"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Validasi Akhir SKPI</span>
                        <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 text-[10px]">{{ count($prestasis) }}</span>
                    </button>
                @endif
            </div>

            <div class="text-xs text-slate-500 font-medium">
                Kegiatan: <span class="text-cyan-600 font-semibold">:8002</span> &bull; Pendaftaran: <span class="text-teal-600 font-semibold">:8003</span> &bull; Prestasi: <span class="text-indigo-600 font-semibold">:8004</span>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 1: MASTER DATA KEGIATAN & KATEGORI (Port 8002)            -->
        <!-- ============================================================= -->
        <section id="paneKegiatan" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200 mb-1">
                        Kegiatan Service &bull; Port 8002 &bull; db_kegiatan
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-900">Master Data Kegiatan & Acara Kampus</h2>
                    <p class="text-xs text-slate-500">Kelola master kegiatan (lomba, beasiswa, seminar), kuota peserta, dan status publikasi.</p>
                </div>

                <!-- Instant Search Filter -->
                <div class="w-full md:w-72 relative">
                    <input type="text" id="cariKegiatanAdmin" onkeyup="filterTabelKegiatanAdmin()" placeholder="Cari judul kegiatan atau kategori..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>

            @if (empty($kegiatans))
                <div class="text-center py-12 border border-dashed border-slate-200 rounded-2xl p-8 space-y-3">
                    <div class="w-12 h-12 rounded-full bg-slate-100 mx-auto flex items-center justify-center text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-700">Belum Ada Master Data Kegiatan</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">Klik tombol <strong>+ Tambah Kegiatan Baru</strong> di atas untuk membuat kegiatan pertama Anda.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs" id="tabelKegiatanAdmin">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-3">Kegiatan / Judul</th>
                                <th class="py-3 px-3">Kategori</th>
                                <th class="py-3 px-3">Batas Pendaftaran</th>
                                <th class="py-3 px-3">Kuota (Terisi / Maks)</th>
                                <th class="py-3 px-3">Status</th>
                                <th class="py-3 px-3 text-right">Aksi Manajemen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($kegiatans as $k)
                                @php
                                    $terisi = (int) ($k['kuota_terisi'] ?? 0);
                                    $maks = (int) ($k['kuota_maksimal'] ?? 0);
                                    $persen = $maks > 0 ? min(100, round(($terisi / $maks) * 100)) : 100;
                                    $isPublished = ($k['status_publikasi'] ?? 'Draft') === 'Published';
                                    $katNama = $k['kategori']['nama_kategori'] ?? 'Umum';
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition row-kegiatan-admin">
                                    <td class="py-3.5 px-3">
                                        <div class="font-bold text-slate-900 leading-snug col-judul-admin">{{ $k['judul_kegiatan'] }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono mt-0.5">ID: #{{ $k['id'] }} &bull; Pembuat ID: {{ $k['pembuat_id'] ?? '-' }}</div>
                                        @if (!empty($k['persyaratan']))
                                            <div class="text-[10px] text-amber-700 bg-amber-50 rounded px-1.5 py-0.5 inline-block mt-1 font-medium border border-amber-200" title="{{ $k['persyaratan'] }}">
                                                📋 Syarat: {{ \Illuminate\Support\Str::limit($k['persyaratan'], 35) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 col-kat-admin">
                                            {{ $katNama }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 font-medium text-slate-700">
                                        {{ \Carbon\Carbon::parse($k['batas_pendaftaran'])->isoFormat('D MMM Y, HH:mm') }}
                                    </td>
                                    <td class="py-3.5 px-3">
                                        <div class="w-32 space-y-1">
                                            <div class="flex items-center justify-between text-[10px]">
                                                <span class="font-bold text-slate-800">{{ $terisi }} / {{ $maks }}</span>
                                                <span class="text-slate-400 font-medium">{{ $persen }}%</span>
                                            </div>
                                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full {{ $terisi >= $maks ? 'bg-rose-500' : ($persen > 75 ? 'bg-amber-500' : 'bg-cyan-600') }}" style="width: {{ $persen }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3">
                                        @if ($isPublished)
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Published
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200 inline-flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Draft
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-3 text-right">
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            <!-- Toggle Publish/Draft -->
                                            <form method="POST" action="{{ route('web.admin.kegiatan.toggle', $k['id']) }}">
                                                @csrf
                                                <input type="hidden" name="status_publikasi" value="{{ $k['status_publikasi'] }}">
                                                <button type="submit" title="{{ $isPublished ? 'Ubah ke Draft' : 'Terbitkan ke Mahasiswa' }}"
                                                    class="px-2.5 py-1.5 rounded-xl text-xs font-semibold transition active:scale-95 {{ $isPublished ? 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' }}">
                                                    {{ $isPublished ? 'Draft' : 'Terbitkan' }}
                                                </button>
                                            </form>

                                            <!-- Edit Kegiatan -->
                                            <button type="button" onclick="openModalEditKegiatan({{ json_encode($k) }})"
                                                class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 text-xs font-semibold transition active:scale-95">
                                                Edit
                                            </button>

                                            <!-- Hapus Kegiatan -->
                                            <form method="POST" action="{{ route('web.admin.kegiatan.destroy', $k['id']) }}"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus kegiatan #{{ $k['id'] }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-semibold transition active:scale-95">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <!-- ============================================================= -->
        <!-- TAB 2: VERIFIKASI AKHIR PENDAFTARAN MAHASISWA (Port 8003)     -->
        <!-- ============================================================= -->
        <section id="panePendaftaran" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-5 hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-50 text-teal-700 border border-teal-200 mb-1">
                        Pendaftaran Service &bull; Port 8003 &bull; db_pendaftaran
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-900">
                        {{ $isAdmin ? 'Verifikasi Akhir Pendaftaran Mahasiswa' : 'Data Pendaftar Kegiatan Mahasiswa' }}
                    </h2>
                    <p class="text-xs text-slate-500">
                        @if ($isAdmin)
                            Admin hanya dapat menyetujui jika dosen wali telah memberikan status 'Direkomendasikan'.
                        @else
                            Pantau mahasiswa yang mendaftar kegiatan ormawa. Verifikasi resmi kelayakan dilakukan oleh Dosen Wali dan Admin Kampus.
                        @endif
                    </p>
                </div>

                <!-- Instant Search Filter -->
                <div class="w-full md:w-72 relative">
                    <input type="text" id="cariPendaftaranAdmin" onkeyup="filterTabelPendaftaranAdmin()" placeholder="Cari nama mahasiswa atau kegiatan..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 focus:bg-white transition">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>

            @if ($isOrmawa)
                <div class="p-3.5 rounded-2xl bg-amber-50/80 border border-amber-200 text-amber-900 text-xs flex items-center gap-2.5">
                    <span class="text-base flex-shrink-0">ℹ️</span>
                    <span class="leading-relaxed">
                        <strong>Perhatian Pengurus Ormawa:</strong> Anda memiliki akses monitoring data peserta yang mendaftar kegiatan. Hak verifikasi dan kelolosan berkas merupakan kewenangan resmi <strong>Dosen Wali</strong> dan <strong>Admin Kemahasiswaan</strong>.
                    </span>
                </div>
            @endif

            @if (empty($pendaftarans))
                <div class="text-center py-12 border border-dashed border-slate-200 rounded-2xl p-8 space-y-3">
                    <div class="w-12 h-12 rounded-full bg-slate-100 mx-auto flex items-center justify-center text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-700">Belum Ada Pendaftaran Masuk</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">Ketika mahasiswa mendaftarkan diri pada kegiatan kampus, data akan muncul di sini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs" id="tabelPendaftaranAdmin">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-3">ID & Mahasiswa</th>
                                <th class="py-3 px-3">Kegiatan / Program</th>
                                <th class="py-3 px-3">Rekomendasi Dosen</th>
                                <th class="py-3 px-3">Status Saat Ini</th>
                                <th class="py-3 px-3 text-right">{{ $isAdmin ? 'Verifikasi Akhir Admin' : 'Status Verifikasi' }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($pendaftarans as $p)
                                @php
                                    $namaMhs = $p['mahasiswa']['nama_lengkap'] ?? "Mahasiswa #{$p['mahasiswa_id']}";
                                    $nimMhs = $p['mahasiswa']['nim_nidn'] ?? '-';
                                    $prodiMhs = $p['mahasiswa']['prodi'] ?? '-';
                                    $judulKeg = $p['kegiatan']['judul_kegiatan'] ?? "Kegiatan #{$p['kegiatan_id']}";
                                    $kategoriKeg = $p['kegiatan']['kategori']['nama_kategori'] ?? 'Umum';

                                    $rek = $p['status_rekomendasi_dosen'] ?? 'Menunggu';
                                    $rekBadge = match ($rek) {
                                        'Direkomendasikan' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'Ditolak'          => 'bg-rose-50 text-rose-700 border-rose-200',
                                        default            => 'bg-amber-50 text-amber-700 border-amber-200',
                                    };

                                    $status = $p['status_pendaftaran'] ?? 'Menunggu';
                                    $statusBadge = match ($status) {
                                        'Diterima'     => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        'Lolos Berkas' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                                        'Revisi'       => 'bg-purple-100 text-purple-800 border-purple-300',
                                        'Ditolak'      => 'bg-rose-100 text-rose-800 border-rose-300',
                                        default        => 'bg-amber-100 text-amber-800 border-amber-300',
                                    };
                                    $bolehTerima = ($rek === 'Direkomendasikan' || empty($p['dosen_wali_id']));
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition row-pendaftaran-admin">
                                    <td class="py-3.5 px-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-cyan-100 text-cyan-800 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                                {{ strtoupper(substr($namaMhs, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 leading-snug col-nama-admin">{{ $namaMhs }}</div>
                                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">#{{ $p['id'] }} &bull; NIM: {{ $nimMhs }} &bull; {{ $prodiMhs }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3">
                                        <div class="font-semibold text-slate-800 leading-snug col-kegiatan-admin">{{ $judulKeg }}</div>
                                        <div class="flex flex-col gap-1 mt-1">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded-full bg-cyan-50 text-cyan-700 text-[10px] font-semibold border border-cyan-100">
                                                    {{ $kategoriKeg }}
                                                </span>
                                            </div>

                                            @php
                                                $daftarBerkas = $p['daftar_berkas'] ?? [];
                                                if (empty($daftarBerkas) && !empty($p['file_syarat'])) {
                                                    $raw = trim($p['file_syarat']);
                                                    if (str_starts_with($raw, '{') || str_starts_with($raw, '[')) {
                                                        $decoded = json_decode($raw, true);
                                                        $daftarBerkas = is_array($decoded) ? $decoded : ['Dokumen Syarat' => $p['file_syarat']];
                                                    } else {
                                                        $daftarBerkas = ['Dokumen Syarat' => $p['file_syarat']];
                                                    }
                                                }
                                            @endphp

                                            @if (!empty($daftarBerkas))
                                                <div class="flex flex-wrap gap-1 mt-0.5">
                                                    @foreach ($daftarBerkas as $labelDoc => $pathDoc)
                                                        <a href="{{ asset('storage/' . $pathDoc) }}" target="_blank"
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-teal-50 hover:bg-teal-100 text-teal-700 text-[10px] font-semibold border border-teal-200 transition"
                                                            title="Buka Dokumen: {{ $labelDoc }}">
                                                            <span>📄</span>
                                                            <span class="max-w-[130px] truncate">{{ is_string($labelDoc) ? $labelDoc : 'Dokumen' }}</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $rekBadge }} inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $rek === 'Direkomendasikan' ? 'bg-emerald-500' : ($rek === 'Ditolak' ? 'bg-rose-500' : 'bg-amber-500 animate-pulse') }}"></span>
                                            {{ $rek }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge }}">
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 text-right">
                                        @if ($isAdmin)
                                            <form method="POST" action="{{ route('web.admin.pendaftaran.verifikasi', $p['id']) }}" class="inline-flex items-center gap-2 justify-end">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="catatan_verifikator" placeholder="Catatan admin..."
                                                    value="{{ $p['catatan_verifikator'] ?? '' }}"
                                                    class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs w-40 focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">

                                                @if ($bolehTerima)
                                                    <button type="submit" name="status_pendaftaran" value="Diterima"
                                                        class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-sm hover:shadow transition active:scale-95">
                                                        Diterima
                                                    </button>
                                                @else
                                                    <button type="button" disabled title="Dosen belum merekomendasikan"
                                                        class="px-3 py-1.5 rounded-xl bg-slate-200 text-slate-400 font-semibold text-xs cursor-not-allowed">
                                                        Diterima (Terkunci)
                                                    </button>
                                                @endif

                                                <button type="submit" name="status_pendaftaran" value="Ditolak"
                                                    class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-semibold text-xs transition active:scale-95">
                                                    Tolak
                                                </button>
                                            </form>
                                        @else
                                            <div class="text-right">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-slate-100 text-slate-600 text-[10px] font-semibold border border-slate-200" title="Verifikasi resmi dilakukan oleh Dosen Wali dan Admin Kampus">
                                                    🔒 Verifikasi oleh Dosen & Admin
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        @if ($isAdmin)
        <!-- ============================================================= -->
        <!-- TAB 3: VALIDASI AKHIR PRESTASI SKPI (Port 8004)               -->
        <!-- ============================================================= -->
        <section id="panePrestasi" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-5 hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 mb-1">
                        Prestasi Service &bull; Port 8004 &bull; db_prestasi
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-900">Validasi Akhir Prestasi Lomba (SKPI)</h2>
                    <p class="text-xs text-slate-500">Validasi final kemahasiswaan untuk penerbitan sertifikat dan poin pada Surat Keterangan Pendamping Ijazah.</p>
                </div>

                <!-- Instant Search Filter -->
                <div class="w-full md:w-72 relative">
                    <input type="text" id="cariPrestasiAdmin" onkeyup="filterTabelPrestasiAdmin()" placeholder="Cari nama mahasiswa atau judul prestasi..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>

            @if (empty($prestasis))
                <div class="text-center py-12 border border-dashed border-slate-200 rounded-2xl p-8 space-y-3">
                    <div class="w-12 h-12 rounded-full bg-slate-100 mx-auto flex items-center justify-center text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-700">Belum Ada Klaim Prestasi SKPI</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">Sertifikat lomba luar kampus yang diunggah mahasiswa akan masuk ke verifikasi ini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs" id="tabelPrestasiAdmin">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-3">Judul Prestasi & Dokumen</th>
                                <th class="py-3 px-3">Mahasiswa Pengaju</th>
                                <th class="py-3 px-3">Validasi Dosen</th>
                                <th class="py-3 px-3">Status Akhir</th>
                                <th class="py-3 px-3 text-right">Aksi Verifikasi Admin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($prestasis as $item)
                                @php
                                    $namaMhs = $item['mahasiswa']['nama_lengkap'] ?? "Mahasiswa #{$item['mahasiswa_id']}";
                                    $nimMhs = $item['mahasiswa']['nim_nidn'] ?? '-';
                                    $prodiMhs = $item['mahasiswa']['prodi'] ?? '-';
                                    $tingkat = $item['tingkat'] ?? 'Lokal';
                                    $badgeTingkat = match($tingkat) {
                                        'Internasional' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'Nasional'      => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        'Provinsi'      => 'bg-sky-50 text-sky-700 border-sky-200',
                                        default         => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };

                                    $valDosen = $item['status_validasi_dosen'] ?? 'Menunggu';
                                    $bolehSetuju = empty($item['dosen_pembimbing_id']) || $valDosen === 'Disetujui';
                                    $statusAkhir = $item['status_validasi_akhir'] ?? 'Menunggu';
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition row-prestasi-admin">
                                    <td class="py-3.5 px-3">
                                        <div class="font-bold text-slate-900 leading-snug col-prestasi-admin">{{ $item['judul_prestasi'] }}</div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeTingkat }}">
                                                Tingkat {{ $tingkat }}
                                            </span>
                                            @if (!empty($item['file_sertifikat']))
                                                <a href="{{ asset('storage/' . $item['file_sertifikat']) }}" target="_blank"
                                                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 hover:text-emerald-900 hover:underline">
                                                    📜 Dokumen Sertifikat
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-800 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                                {{ strtoupper(substr($namaMhs, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 leading-snug col-mhs-admin">{{ $namaMhs }}</div>
                                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">NIM: {{ $nimMhs }} &bull; {{ $prodiMhs }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $valDosen === 'Disetujui' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }} inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $valDosen === 'Disetujui' ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse' }}"></span>
                                            {{ $valDosen }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusAkhir === 'Disetujui' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-amber-100 text-amber-800 border-amber-300' }}">
                                            {{ $statusAkhir }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 text-right">
                                        <form method="POST" action="{{ route('web.admin.prestasi.validasi', $item['id']) }}" class="inline-flex items-center gap-2 justify-end">
                                            @csrf
                                            @method('PUT')
                                            @if ($bolehSetuju)
                                                <button type="submit" name="status_validasi_akhir" value="Disetujui"
                                                    class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-sm hover:shadow transition active:scale-95">
                                                    Setujui Final
                                                </button>
                                            @else
                                                <button type="button" disabled title="Dosen pembimbing belum menyetujui"
                                                    class="px-3 py-1.5 rounded-xl bg-slate-200 text-slate-400 font-semibold text-xs cursor-not-allowed">
                                                    Setujui (Terkunci)
                                                </button>
                                            @endif
                                            <button type="submit" name="status_validasi_akhir" value="Ditolak"
                                                class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-semibold text-xs transition active:scale-95">
                                                Tolak
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
        @endif

    </main>

    <!-- ============================================================= -->
    <!-- MODAL TAMBAH KEGIATAN BARU                                    -->
    <!-- ============================================================= -->
    <div id="modalTambahKegiatan" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100 space-y-5 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Tambah Master Kegiatan Baru</h3>
                        <p class="text-[11px] text-slate-400">Kegiatan Service &bull; Port 8002</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('modalTambahKegiatan').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold p-1">&times;</button>
            </div>

            <form method="POST" action="{{ route('web.admin.kegiatan.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Judul Kegiatan</label>
                    <input type="text" name="judul_kegiatan" required placeholder="Contoh: Kompetisi Robotik Nasional 2026"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kategori</label>
                        <select name="kategori_id" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                            @foreach ($kategoris as $kat)
                                <option value="{{ $kat['id'] }}">{{ $kat['nama_kategori'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status Publikasi</label>
                        <select name="status_publikasi" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                            <option value="Published" selected>Published (Buka Sekarang)</option>
                            <option value="Draft">Draft (Simpan Sementara)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Batas Pendaftaran</label>
                        <input type="datetime-local" name="batas_pendaftaran" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kuota Maksimal</label>
                        <input type="number" name="kuota_maksimal" min="1" value="100" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Deskripsi Lengkap</label>
                    <textarea name="deskripsi" rows="3" required placeholder="Jelaskan detail pengantar, tanggal kegiatan, dan ketentuan lainnya..."
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 focus:bg-white transition"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Persyaratan Berkas Pendaftaran</span>
                        <span class="text-[10px] text-slate-400 font-normal lowercase">(opsional / kosongkan jika tanpa syarat)</span>
                    </label>
                    <textarea name="persyaratan" rows="3" placeholder="Contoh:&#10;1. Scan Kartu Tanda Mahasiswa (KTM)&#10;2. Transkrip Nilai Terakhir&#10;3. Proposal / Berkas Pendukung"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 focus:bg-white transition"></textarea>
                    <p class="text-[10px] text-cyan-700 bg-cyan-50 border border-cyan-200/60 p-2 rounded-xl mt-1.5 leading-relaxed">
                        💡 <strong>Tips Multi-Berkas:</strong> Tuliskan 1 baris untuk setiap berkas yang disyaratkan (tekan Enter untuk baris baru). Sistem otomatis membuatkan slot unggah file terpisah untuk masing-masing berkas di form pendaftaran mahasiswa.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modalTambahKegiatan').classList.add('hidden')"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold shadow-md shadow-cyan-500/20 transition active:scale-95">
                        Simpan Master Kegiatan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- MODAL EDIT KEGIATAN                                           -->
    <!-- ============================================================= -->
    <div id="modalEditKegiatan" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100 space-y-5 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Edit Master Kegiatan</h3>
                        <p class="text-[11px] text-slate-400">Perbarui detail, kuota, atau tanggal batas</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('modalEditKegiatan').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold p-1">&times;</button>
            </div>

            <form id="formEditKegiatan" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Judul Kegiatan</label>
                    <input type="text" id="edit_judul_kegiatan" name="judul_kegiatan" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kategori</label>
                        <select id="edit_kategori_id" name="kategori_id" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                            @foreach ($kategoris as $kat)
                                <option value="{{ $kat['id'] }}">{{ $kat['nama_kategori'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status Publikasi</label>
                        <select id="edit_status_publikasi" name="status_publikasi" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                            <option value="Published">Published</option>
                            <option value="Draft">Draft</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Batas Pendaftaran</label>
                        <input type="datetime-local" id="edit_batas_pendaftaran" name="batas_pendaftaran" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kuota Maksimal</label>
                        <input type="number" id="edit_kuota_maksimal" name="kuota_maksimal" min="1" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Deskripsi Lengkap</label>
                    <textarea id="edit_deskripsi" name="deskripsi" rows="3" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 focus:bg-white transition"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Persyaratan Berkas Pendaftaran</span>
                        <span class="text-[10px] text-slate-400 font-normal lowercase">(opsional / kosongkan jika tanpa syarat)</span>
                    </label>
                    <textarea id="edit_persyaratan" name="persyaratan" rows="3" placeholder="Contoh:&#10;1. Scan Kartu Tanda Mahasiswa (KTM)&#10;2. Transkrip Nilai"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 focus:bg-white transition"></textarea>
                    <p class="text-[10px] text-cyan-700 bg-cyan-50 border border-cyan-200/60 p-2 rounded-xl mt-1.5 leading-relaxed">
                        💡 <strong>Tips Multi-Berkas:</strong> Tuliskan 1 baris untuk setiap berkas yang disyaratkan (tekan Enter untuk baris baru). Sistem otomatis membuatkan slot unggah file terpisah untuk masing-masing berkas di form pendaftaran mahasiswa.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modalEditKegiatan').classList.add('hidden')"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold shadow-md shadow-cyan-500/20 transition active:scale-95">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- MODAL TAMBAH KATEGORI BARU                                    -->
    <!-- ============================================================= -->
    <div id="modalTambahKategori" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Tambah Master Kategori</h3>
                </div>
                <button type="button" onclick="document.getElementById('modalTambahKategori').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold p-1">&times;</button>
            </div>

            <form method="POST" action="{{ route('web.admin.kategori.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Kategori</label>
                    <input type="text" name="nama_kategori" required placeholder="Contoh: Pengabdian Masyarakat"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('modalTambahKategori').classList.add('hidden')"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-md transition active:scale-95">
                        Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function switchAdminTab(tab) {
            const paneKeg = document.getElementById('paneKegiatan');
            const panePend = document.getElementById('panePendaftaran');
            const panePres = document.getElementById('panePrestasi');

            const btnKeg = document.getElementById('tabBtnKegiatan');
            const btnPend = document.getElementById('tabBtnPendaftaran');
            const btnPres = document.getElementById('tabBtnPrestasi');

            // Hide all panes
            paneKeg.classList.add('hidden');
            panePend.classList.add('hidden');
            panePres.classList.add('hidden');

            // Reset all buttons
            btnKeg.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-600';
            btnPend.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-600';
            btnPres.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-600';

            if (tab === 'kegiatan') {
                paneKeg.classList.remove('hidden');
                btnKeg.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-cyan-600 text-white shadow-sm';
            } else if (tab === 'pendaftaran') {
                panePend.classList.remove('hidden');
                btnPend.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-teal-600 text-white shadow-sm';
            } else if (tab === 'prestasi') {
                panePres.classList.remove('hidden');
                btnPres.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-indigo-600 text-white shadow-sm';
            }
        }

        function filterTabelKegiatanAdmin() {
            const q = (document.getElementById('cariKegiatanAdmin').value || '').toLowerCase();
            const rows = document.querySelectorAll('.row-kegiatan-admin');
            rows.forEach(r => {
                const judul = (r.querySelector('.col-judul-admin')?.textContent || '').toLowerCase();
                const kat = (r.querySelector('.col-kat-admin')?.textContent || '').toLowerCase();
                if (judul.includes(q) || kat.includes(q)) {
                    r.style.display = '';
                } else {
                    r.style.display = 'none';
                }
            });
        }

        function filterTabelPendaftaranAdmin() {
            const q = (document.getElementById('cariPendaftaranAdmin').value || '').toLowerCase();
            const rows = document.querySelectorAll('.row-pendaftaran-admin');
            rows.forEach(r => {
                const mhs = (r.querySelector('.col-nama-admin')?.textContent || '').toLowerCase();
                const keg = (r.querySelector('.col-kegiatan-admin')?.textContent || '').toLowerCase();
                if (mhs.includes(q) || keg.includes(q)) {
                    r.style.display = '';
                } else {
                    r.style.display = 'none';
                }
            });
        }

        function filterTabelPrestasiAdmin() {
            const q = (document.getElementById('cariPrestasiAdmin').value || '').toLowerCase();
            const rows = document.querySelectorAll('.row-prestasi-admin');
            rows.forEach(r => {
                const pres = (r.querySelector('.col-prestasi-admin')?.textContent || '').toLowerCase();
                const mhs = (r.querySelector('.col-mhs-admin')?.textContent || '').toLowerCase();
                if (pres.includes(q) || mhs.includes(q)) {
                    r.style.display = '';
                } else {
                    r.style.display = 'none';
                }
            });
        }

        function openModalEditKegiatan(item) {
            document.getElementById('formEditKegiatan').action = '/admin/kegiatan/' + item.id;
            document.getElementById('edit_judul_kegiatan').value = item.judul_kegiatan || '';
            document.getElementById('edit_kategori_id').value = item.kategori_id || '';
            document.getElementById('edit_status_publikasi').value = item.status_publikasi || 'Draft';
            document.getElementById('edit_kuota_maksimal').value = item.kuota_maksimal || 100;
            document.getElementById('edit_deskripsi').value = item.deskripsi || '';
            document.getElementById('edit_persyaratan').value = item.persyaratan || '';

            if (item.batas_pendaftaran) {
                var dt = new Date(item.batas_pendaftaran);
                var formatted = dt.toISOString().slice(0, 16);
                document.getElementById('edit_batas_pendaftaran').value = formatted;
            }

            document.getElementById('modalEditKegiatan').classList.remove('hidden');
        }
    </script>
</body>
</html>
