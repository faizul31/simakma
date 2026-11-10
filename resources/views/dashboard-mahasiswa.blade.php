<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - Portal Kemahasiswaan Terpadu</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Top Navigation Bar -->
    <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-sky-400 flex items-center justify-center text-white font-extrabold text-base shadow-md shadow-indigo-500/20">
                    KM
                </div>
                <div>
                    <h1 class="text-sm font-extrabold text-slate-900 tracking-tight leading-tight flex items-center gap-2">
                        Portal Kemahasiswaan
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">Mahasiswa</span>
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">Sistem Microservices Terintegrasi &bull; Port 8000</p>
                </div>
            </div>

            <!-- Profile Info & Logout -->
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold text-slate-900 leading-tight">{{ $user['nama_lengkap'] ?? 'Mahasiswa' }}</div>
                    <div class="text-[11px] text-indigo-600 font-semibold mt-0.5">NIM: {{ $user['nim_nidn'] ?? '-' }} &bull; {{ $user['prodi'] ?? 'Teknik Informatika' }}</div>
                </div>

                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs border border-indigo-200 hidden sm:flex">
                    {{ strtoupper(substr($user['nama_lengkap'] ?? 'M', 0, 2)) }}
                </div>

                <form method="POST" action="{{ route('web.logout') }}">
                    @csrf
                    <button type="submit" title="Keluar dari akun"
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
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm animate-fade-in">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div>
                        <div class="font-bold">Berhasil!</div>
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
                        <div class="font-bold">Perhatian!</div>
                        <div class="text-xs text-rose-700">{{ session('error') }}</div>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800 text-xl font-bold p-1">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start justify-between shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <div class="font-bold mb-1">Gagal Memproses Data:</div>
                        <ul class="list-disc list-inside text-xs space-y-1 text-rose-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800 text-xl font-bold p-1">&times;</button>
            </div>
        @endif

        @if (!empty($errorNotices))
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-1">
                <div class="font-bold flex items-center gap-1.5 text-amber-800">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Informasi Status Komunikasi Microservices
                </div>
                @foreach ($errorNotices as $notice)
                    <div>&bull; {{ $notice }}</div>
                @endforeach
            </div>
        @endif

        <!-- ============================================================= -->
        <!-- HERO WELCOME BANNER & STATISTIC CARDS                         -->
        <!-- ============================================================= -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 p-6 sm:p-8 text-white shadow-xl">
            <!-- Decorative Background Element -->
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute right-20 top-0 w-32 h-32 bg-sky-400/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2 max-w-xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-xs font-semibold text-indigo-200 border border-white/15">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Portal Aktif &bull; Semester Genap 2026
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Halo, {{ $user['nama_lengkap'] ?? 'Mahasiswa' }}! 👋
                    </h2>
                    <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                        Jelajahi kompetisi, beasiswa, dan seminar kampus terkini. Daftarkan diri Anda dan klaim sertifikat lomba luar kampus untuk pengisian poin SKPI.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" onclick="openModalPrestasi()"
                        class="px-4 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-bold text-xs shadow-lg shadow-emerald-500/25 transition active:scale-95 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>+ Klaim Prestasi SKPI</span>
                    </button>
                </div>
            </div>

            <!-- 4 Modern Stat Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-6 pt-6 border-t border-white/10">
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 border border-white/10 flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/30 flex items-center justify-center text-indigo-300 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-300 font-medium">Kegiatan Terbuka</div>
                        <div class="text-lg font-bold text-white">{{ count($kegiatans) }} Program</div>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 border border-white/10 flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/30 flex items-center justify-center text-sky-300 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-300 font-medium">Pendaftaran Saya</div>
                        <div class="text-lg font-bold text-white">{{ count($pendaftarans) }} Kegiatan</div>
                    </div>
                </div>

                @php
                    $diterimaCount = collect($pendaftarans)->where('status_pendaftaran', 'Diterima')->count();
                    $prestasiDisetujui = collect($prestasis)->where('status_validasi_akhir', 'Disetujui')->count();
                @endphp

                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 border border-white/10 flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/30 flex items-center justify-center text-emerald-300 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-300 font-medium">Lolos / Diterima</div>
                        <div class="text-lg font-bold text-white">{{ $diterimaCount }} Berkas</div>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 border border-white/10 flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/30 flex items-center justify-center text-amber-300 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-300 font-medium">Prestasi SKPI Sah</div>
                        <div class="text-lg font-bold text-white">{{ $prestasiDisetujui }} Sertifikat</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- MAIN SECTION: 2 KOLOM (KATALOG KIRI & RIWAYAT KANAN)          -->
        <!-- ============================================================= -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- BAGIAN KIRI: KATALOG KEGIATAN PUBLISHED (Port 8002) -->
            <section class="lg:col-span-7 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                            Katalog Kegiatan & Acara Kampus
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        </h3>
                        <p class="text-xs text-slate-500">Daftar lomba, seminar, dan beasiswa resmi yang sedang membuka pendaftaran.</p>
                    </div>

                    <!-- Filter Kategori & Search -->
                    <div class="relative w-full sm:w-56">
                        <input type="text" id="searchKegiatan" onkeyup="filterKegiatan()"
                            placeholder="Cari nama kegiatan..."
                            class="w-full px-3.5 py-2 pl-9 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-xs">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>

                @if (empty($kegiatans))
                    <div class="bg-white rounded-3xl border border-dashed border-slate-300 p-12 text-center space-y-3">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 mx-auto flex items-center justify-center text-indigo-500">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800">Belum Ada Kegiatan Tersedia</h4>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">Saat ini belum ada kegiatan yang dipublikasikan oleh Ormawa atau Admin. Silakan periksa kembali nanti.</p>
                    </div>
                @else
                    <div id="kegiatanContainer" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach ($kegiatans as $kegiatan)
                            @php
                                $terisi = (int) ($kegiatan['kuota_terisi'] ?? 0);
                                $maks = (int) ($kegiatan['kuota_maksimal'] ?? 0);
                                $isPenuh = $terisi >= $maks;
                                $sisa = max(0, $maks - $terisi);
                                $persen = $maks > 0 ? min(100, round(($terisi / $maks) * 100)) : 100;
                            @endphp
                            <div class="kegiatan-card bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between space-y-4 group">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            {{ $kegiatan['kategori']['nama_kategori'] ?? 'Lomba & Beasiswa' }}
                                        </span>

                                        @if ($isPenuh)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200">
                                                KUOTA PENUH
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Sisa {{ $sisa }} Kursi
                                            </span>
                                        @endif
                                    </div>

                                    <h4 class="kegiatan-judul text-base font-bold text-slate-900 group-hover:text-indigo-600 transition leading-snug line-clamp-2">
                                        {{ $kegiatan['judul_kegiatan'] ?? '-' }}
                                    </h4>

                                    <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                                        {{ $kegiatan['deskripsi'] ?? 'Tidak ada deskripsi singkat.' }}
                                    </p>

                                    @if (!empty($kegiatan['persyaratan']))
                                        <div class="pt-1.5 flex items-start gap-1 text-[11px] font-semibold text-amber-700 bg-amber-50/80 border border-amber-200/70 px-2 py-1 rounded-lg">
                                            <span class="flex-shrink-0">📋</span>
                                            <span class="truncate">Syarat: {{ $kegiatan['persyaratan'] }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="space-y-3 pt-3 border-t border-slate-100 text-xs">
                                    <div class="flex items-center justify-between text-slate-500">
                                        <span class="flex items-center gap-1 text-[11px]">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            Batas Daftar:
                                        </span>
                                        <span class="font-bold text-slate-800 text-[11px]">
                                            {{ \Carbon\Carbon::parse($kegiatan['batas_pendaftaran'])->isoFormat('D MMM Y, HH:mm') }}
                                        </span>
                                    </div>

                                    <!-- Kuota Progress Bar -->
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between text-[11px]">
                                            <span class="text-slate-500">Kuota Terisi:</span>
                                            <span class="font-bold text-slate-800">{{ $terisi }} / {{ $maks }} Peserta</span>
                                        </div>
                                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full {{ $isPenuh ? 'bg-rose-500' : 'bg-gradient-to-r from-indigo-500 to-sky-400' }}" style="width: {{ $persen }}%"></div>
                                        </div>
                                    </div>

                                    <!-- Tombol Aksi Daftar -->
                                    @if ($isPenuh)
                                        <button disabled
                                            class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-400 font-semibold text-xs cursor-not-allowed">
                                            Pendaftaran Ditutup
                                        </button>
                                    @else
                                        <button type="button"
                                            onclick="openModalDaftar({{ $kegiatan['id'] }}, '{{ addslashes($kegiatan['judul_kegiatan']) }}', {{ json_encode($kegiatan['persyaratan'] ?? '') }})"
                                            class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition flex items-center justify-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            <span>Daftar Sekarang</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <!-- BAGIAN KANAN: RIWAYAT PENDAFTARAN & PRESTASI -->
            <section class="lg:col-span-5 space-y-6">

                <!-- KARTU 1: RIWAYAT PENDAFTARAN KEGIATAN -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                Riwayat Pendaftaran
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ count($pendaftarans) }}
                                </span>
                            </h3>
                            <p class="text-xs text-slate-500">Status verifikasi dosen wali dan admin kampus.</p>
                        </div>
                    </div>

                    @if (empty($pendaftarans))
                        <div class="text-center py-8 text-slate-400 text-xs space-y-1">
                            <p class="font-medium text-slate-600">Belum ada riwayat pendaftaran.</p>
                            <p class="text-[11px] text-slate-400">Pilih salah satu program di sisi kiri untuk mulai mendaftar.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($pendaftarans as $p)
                                @php
                                    $rek = $p['status_rekomendasi_dosen'] ?? 'Menunggu';
                                    $rekBadge = match ($rek) {
                                        'Direkomendasikan' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'Ditolak'          => 'bg-rose-50 text-rose-700 border-rose-200',
                                        default            => 'bg-amber-50 text-amber-700 border-amber-200',
                                    };

                                    $status = $p['status_pendaftaran'] ?? 'Menunggu';
                                    $statusBadge = match ($status) {
                                        'Diterima'     => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        'Lolos Berkas' => 'bg-sky-100 text-sky-800 border-sky-300',
                                        'Revisi'       => 'bg-purple-100 text-purple-800 border-purple-300',
                                        'Ditolak'      => 'bg-rose-100 text-rose-800 border-rose-300',
                                        default        => 'bg-amber-100 text-amber-800 border-amber-300',
                                    };
                                @endphp
                                <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/70 hover:border-indigo-200 transition space-y-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs leading-snug">
                                                {{ $p['kegiatan']['judul_kegiatan'] ?? "Kegiatan #{$p['kegiatan_id']}" }}
                                            </div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">
                                                Didaftar pada: {{ \Carbon\Carbon::parse($p['created_at'])->format('d M Y, H:i') }}
                                            </div>
                                        </div>
                                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge }} flex-shrink-0">
                                            {{ $status }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between text-[11px] pt-2 border-t border-slate-200/60">
                                        <span class="text-slate-500">Rekomendasi Dosen:</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $rekBadge }}">
                                            {{ $rek }}
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
                                        <div class="pt-2 border-t border-slate-200/60 text-xs">
                                            <span class="text-[10px] font-bold text-slate-500 block mb-1">Berkas Persyaratan Diunggah:</span>
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach ($daftarBerkas as $labelDoc => $pathDoc)
                                                    <a href="{{ asset('storage/' . $pathDoc) }}" target="_blank"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 text-[10px] font-semibold transition shadow-2xs">
                                                        <span>📄</span>
                                                        <span class="truncate max-w-[150px]">{{ is_string($labelDoc) ? $labelDoc : 'Dokumen' }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if (!empty($p['catatan_verifikator']))
                                        <div class="text-[11px] p-2 rounded-xl bg-amber-50 text-amber-900 border border-amber-200 italic">
                                            <strong>Catatan Admin:</strong> "{{ $p['catatan_verifikator'] }}"
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- KARTU 2: REKAP PRESTASI LOMBA SKPI (Port 8004) -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                Portofolio Prestasi (SKPI)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ count($prestasis) }}
                                </span>
                            </h3>
                            <p class="text-xs text-slate-500">Klaim sertifikat lomba luar kampus untuk SKPI.</p>
                        </div>
                        <button type="button" onclick="openModalPrestasi()"
                            class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition">
                            + Unggah
                        </button>
                    </div>

                    @if (empty($prestasis))
                        <div class="text-center py-6 text-slate-400 text-xs">
                            Belum ada sertifikat lomba luar kampus yang diajukan.
                        </div>
                    @else
                        <div class="space-y-2.5">
                            @foreach ($prestasis as $prestasi)
                                @php
                                    $valAkhir = $prestasi['status_validasi_akhir'] ?? 'Menunggu';
                                    $valBadge = match ($valAkhir) {
                                        'Disetujui' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        'Ditolak'   => 'bg-rose-100 text-rose-800 border-rose-300',
                                        default     => 'bg-amber-100 text-amber-800 border-amber-300',
                                    };
                                @endphp
                                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2 hover:border-emerald-200 transition">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="space-y-0.5">
                                            <div class="font-bold text-slate-900 text-xs leading-snug">{{ $prestasi['judul_prestasi'] }}</div>
                                            <div class="text-[10px] text-slate-500">
                                                Tingkat: <span class="font-semibold text-slate-700">{{ $prestasi['tingkat'] }}</span> &bull; 
                                                Dosen Pembimbing: <span class="font-semibold {{ ($prestasi['status_validasi_dosen'] ?? '') === 'Disetujui' ? 'text-emerald-700' : 'text-slate-700' }}">{{ $prestasi['status_validasi_dosen'] ?? 'Menunggu' }}</span>
                                            </div>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $valBadge }} flex-shrink-0">
                                            {{ $valAkhir }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 text-[11px]">
                                        @if ($valAkhir === 'Disetujui')
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                                <span>⭐</span> Resmi Masuk Poin SKPI
                                            </span>
                                        @elseif ($valAkhir === 'Ditolak')
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200">
                                                <span>❌</span> Tidak Lolos Verifikasi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                                                <span>⏳</span> Menunggu Verifikasi Akhir
                                            </span>
                                        @endif

                                        @if (!empty($prestasi['file_sertifikat']))
                                            <a href="{{ asset('storage/' . $prestasi['file_sertifikat']) }}" target="_blank"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 text-[10px] font-semibold transition">
                                                <span>📄</span> Lihat Sertifikat
                                            </a>
                                        @endif
                                    </div>

                                    @if (!empty($prestasi['catatan']))
                                        <div class="text-[10px] p-2 rounded-xl bg-amber-50 text-amber-900 border border-amber-200 italic">
                                            <strong>Catatan:</strong> "{{ $prestasi['catatan'] }}"
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </section>
        </div>
    </main>

    <!-- ============================================================= -->
    <!-- MODAL PENDAFTARAN KEGIATAN                                    -->
    <!-- ============================================================= -->
    <div id="modalDaftar" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                        📝
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Form Pendaftaran Kegiatan</h3>
                        <p class="text-[11px] text-slate-500">Isi formulir pendaftaran kegiatan di bawah ini</p>
                    </div>
                </div>
                <button type="button" onclick="closeModalDaftar()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('web.mahasiswa.daftar') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" id="modal_kegiatan_id" name="kegiatan_id">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Kegiatan</label>
                    <input type="text" id="modal_judul_kegiatan" readonly
                        class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs text-slate-800 font-bold">
                </div>

                <!-- Kotak Informasi Persyaratan Kegiatan (Ditetapkan Admin) -->
                <div id="modal_box_persyaratan" class="p-3.5 rounded-2xl bg-amber-50/80 border border-amber-200/80 text-amber-900 space-y-1.5 transition-all">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-amber-900">
                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span id="modal_label_persyaratan">Persyaratan Pendaftaran dari Admin:</span>
                    </div>
                    <div id="modal_teks_persyaratan" class="text-xs text-amber-950/90 whitespace-pre-line pl-5 leading-relaxed font-medium">
                        <!-- Teks persyaratan diisi via JavaScript -->
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="dosen_wali_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Pilih Dosen Wali (Rekomendasi)
                        </label>
                        @if (!empty($user['prodi']))
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-200">
                                <span>🎓</span> Jurusan: {{ $user['prodi'] }}
                            </span>
                        @endif
                    </div>

                    <!-- Filter Toggle Checkbox Sesuai Jurusan -->
                    <div class="flex items-center justify-between p-2.5 mb-2 bg-indigo-50/70 rounded-xl border border-indigo-100 text-[11px]">
                        <span class="text-slate-600 font-medium">Filter Sesuai Jurusan Mahasiswa:</span>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                            <input type="checkbox" id="toggleDosenSejurusanWali" checked onchange="toggleFilterDosen('toggleDosenSejurusanWali', 'optgroupLainnyaWali')"
                                class="w-3.5 h-3.5 text-indigo-600 rounded focus:ring-indigo-500 border-slate-300">
                            <span class="text-xs font-bold text-indigo-900">Hanya Sejurusan</span>
                        </label>
                    </div>

                    <select name="dosen_wali_id" id="dosen_wali_id"
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 font-medium">
                        @if (!empty($dosenGrouped['sejurusan']) || !empty($dosenGrouped['lainnya']))
                            @if (!empty($dosenGrouped['sejurusan']))
                                <optgroup id="optgroupSejurusanWali" label="⭐ Dosen Jurusan Anda ({{ $user['prodi'] ?? 'Rekomendasi' }})">
                                    @foreach ($dosenGrouped['sejurusan'] as $dsn)
                                        <option value="{{ $dsn['id'] }}">
                                            {{ $dsn['nama_lengkap'] }} (NIDN: {{ $dsn['nim_nidn'] ?? '-' }}) &bull; {{ $dsn['prodi'] ?? '' }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif

                            @if (!empty($dosenGrouped['lainnya']))
                                <optgroup id="optgroupLainnyaWali" label="🏛️ Dosen Jurusan Lainnya" hidden disabled style="display: none;">
                                    @foreach ($dosenGrouped['lainnya'] as $dsn)
                                        <option value="{{ $dsn['id'] }}">
                                            {{ $dsn['nama_lengkap'] }} (NIDN: {{ $dsn['nim_nidn'] ?? '-' }}) &bull; {{ $dsn['prodi'] ?? '' }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @elseif (!empty($daftarDosen))
                            @foreach ($daftarDosen as $dsn)
                                <option value="{{ $dsn['id'] }}">
                                    {{ $dsn['nama_lengkap'] }} (NIDN: {{ $dsn['nim_nidn'] ?? '-' }}) &bull; {{ $dsn['prodi'] ?? '' }}
                                </option>
                            @endforeach
                        @else
                            <option value="4">Dr. Budi Santoso, M.Kom (NIDN: 0412058501)</option>
                        @endif
                    </select>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Unggah Berkas Persyaratan (PDF / JPG / PNG, Maks 5MB)
                        </label>
                        <span id="badge_status_berkas" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">
                            Opsional
                        </span>
                    </div>

                    <!-- Dynamic Container untuk Slot Upload Berkas Sesuai Persyaratan -->
                    <div id="container_unggah_berkas" class="space-y-2.5">
                        <!-- Di-generate otomatis via openModalDaftar -->
                    </div>
                </div>

                <div class="p-3 rounded-2xl bg-indigo-50/70 border border-indigo-100 text-indigo-900 text-[11px] leading-relaxed">
                    💡 <strong>Inter-Service Communication:</strong> Pendaftaran Service (8003) akan memverifikasi ketersediaan kuota secara real-time ke Kegiatan Service (8002) sebelum menyimpan pendaftaran Anda.
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeModalDaftar()"
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/25 transition">
                        Kirim Pendaftaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- MODAL PENGAJUAN PRESTASI SKPI                                -->
    <!-- ============================================================= -->
    <div id="modalPrestasi" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                        🏆
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Klaim Sertifikat Prestasi Lomba</h3>
                        <p class="text-[11px] text-slate-500">Ajukan bukti prestasi untuk poin SKPI</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('modalPrestasi').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('web.mahasiswa.prestasi') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Prestasi / Juara Lomba</label>
                    <input type="text" name="judul_prestasi" required placeholder="Contoh: Juara 1 Gemastik 2026"
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-3 flex flex-col justify-between">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tingkat Lomba</label>
                            <select name="tingkat" required
                                class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 font-medium">
                                <option value="Lokal">Lokal</option>
                                <option value="Provinsi">Provinsi</option>
                                <option value="Nasional" selected>Nasional</option>
                                <option value="Internasional">Internasional</option>
                            </select>
                        </div>

                        <div>
                            <label for="display_nip_dosen" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                NIP / NIDN Dosen
                            </label>
                            <div class="relative">
                                <input type="text" id="display_nip_dosen" readonly
                                    placeholder="NIP / NIDN Dosen"
                                    class="w-full pl-3 pr-8 py-2.5 bg-slate-100/90 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-700 focus:outline-none cursor-default shadow-2xs">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Dosen Pembimbing</label>
                            @if (!empty($user['prodi']))
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                    <span>🎓</span> {{ $user['prodi'] }}
                                </span>
                            @endif
                        </div>

                        <!-- Filter Toggle Checkbox Sesuai Jurusan -->
                        <div class="flex items-center justify-between p-2 mb-2 bg-emerald-50/70 rounded-xl border border-emerald-100 text-[11px]">
                            <span class="text-slate-600 font-medium">Filter Sesuai Jurusan:</span>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                                <input type="checkbox" id="toggleDosenSejurusanPembimbing" checked onchange="toggleFilterDosen('toggleDosenSejurusanPembimbing', 'optgroupLainnyaPembimbing'); updateDosenPembimbingNip();"
                                    class="w-3.5 h-3.5 text-emerald-600 rounded focus:ring-emerald-500 border-slate-300">
                                <span class="text-xs font-bold text-emerald-900">Hanya Sejurusan</span>
                            </label>
                        </div>

                        <select name="dosen_pembimbing_id" id="dosen_pembimbing_id" onchange="updateDosenPembimbingNip()"
                            class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 font-medium">
                            @if (!empty($dosenGrouped['sejurusan']) || !empty($dosenGrouped['lainnya']))
                                @if (!empty($dosenGrouped['sejurusan']))
                                    <optgroup id="optgroupSejurusanPembimbing" label="⭐ Dosen Jurusan Anda ({{ $user['prodi'] ?? 'Rekomendasi' }})">
                                        @foreach ($dosenGrouped['sejurusan'] as $dsn)
                                            <option value="{{ $dsn['id'] }}" data-nidn="{{ $dsn['nim_nidn'] ?? '-' }}">
                                                {{ $dsn['nama_lengkap'] }} ({{ $dsn['prodi'] ?? 'Dosen' }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif

                                @if (!empty($dosenGrouped['lainnya']))
                                    <optgroup id="optgroupLainnyaPembimbing" label="🏛️ Dosen Jurusan Lainnya" hidden disabled style="display: none;">
                                        @foreach ($dosenGrouped['lainnya'] as $dsn)
                                            <option value="{{ $dsn['id'] }}" data-nidn="{{ $dsn['nim_nidn'] ?? '-' }}">
                                                {{ $dsn['nama_lengkap'] }} ({{ $dsn['prodi'] ?? 'Dosen' }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @elseif (!empty($daftarDosen))
                                @foreach ($daftarDosen as $dsn)
                                    <option value="{{ $dsn['id'] }}" data-nidn="{{ $dsn['nim_nidn'] ?? '-' }}">
                                        {{ $dsn['nama_lengkap'] }} ({{ $dsn['prodi'] ?? 'Dosen' }})
                                    </option>
                                @endforeach
                            @else
                                <option value="4" data-nidn="0412058501">Dr. Budi Santoso, M.Kom</option>
                            @endif
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Upload Sertifikat (PDF/JPG, Maks 5MB)</label>
                    <input type="file" name="file_sertifikat" required accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modalPrestasi').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-500/25 transition">
                        Unggah Klaim
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function parsePersyaratan(persyaratanText) {
            if (!persyaratanText || typeof persyaratanText !== 'string') return [];
            var lines = persyaratanText.split(/\r?\n/);
            var items = [];
            for (var i = 0; i < lines.length; i++) {
                var line = lines[i].trim();
                if (!line) continue;
                // Bersihkan awalan nomor atau bullet seperti: "1.", "1. ", "1)", "-", "*", "•"
                var cleaned = line.replace(/^(\d+[\.\)]|\-|\*|•)\s*/, '').trim();
                if (cleaned.length > 0) {
                    items.push(cleaned);
                }
            }
            return items;
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function openModalDaftar(id, judul, persyaratan) {
            document.getElementById('modal_kegiatan_id').value = id;
            document.getElementById('modal_judul_kegiatan').value = judul;

            var boxSyarat = document.getElementById('modal_box_persyaratan');
            var labelSyarat = document.getElementById('modal_label_persyaratan');
            var teksSyarat = document.getElementById('modal_teks_persyaratan');
            var badgeStatus = document.getElementById('badge_status_berkas');
            var containerBerkas = document.getElementById('container_unggah_berkas');

            var items = parsePersyaratan(persyaratan);

            if (items.length > 0) {
                if (labelSyarat) labelSyarat.innerText = 'Persyaratan Pendaftaran dari Admin:';
                if (teksSyarat) teksSyarat.innerText = persyaratan;
                if (boxSyarat) {
                    boxSyarat.className = "p-3.5 rounded-2xl bg-amber-50/80 border border-amber-200/80 text-amber-900 space-y-1.5 transition-all";
                }
                if (badgeStatus) {
                    badgeStatus.innerText = items.length + " Berkas Wajib Diunggah";
                    badgeStatus.className = "text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 border border-rose-200";
                }

                // Render satu slot upload file terpisah untuk setiap butir persyaratan
                var html = '';
                for (var i = 0; i < items.length; i++) {
                    var namaSyarat = escapeHtml(items[i]);
                    html += '<div class="p-3 bg-slate-50/90 rounded-2xl border border-slate-200 space-y-1.5 transition hover:border-indigo-300">' +
                                '<div class="flex items-center justify-between">' +
                                    '<label class="block text-xs font-bold text-slate-800 leading-snug">' +
                                        '<span class="inline-block px-1.5 py-0.5 rounded-md bg-indigo-100 text-indigo-700 text-[10px] font-black mr-1.5">Berkas #' + (i + 1) + '</span>' +
                                        namaSyarat +
                                    '</label>' +
                                    '<span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200">' +
                                        '*Wajib' +
                                    '</span>' +
                                '</div>' +
                                '<input type="hidden" name="syarat_labels[]" value="' + namaSyarat + '">' +
                                '<input type="file" name="files_syarat[]" required accept=".pdf,.jpg,.jpeg,.png"' +
                                    ' class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">' +
                                '<p class="text-[10px] text-slate-400">Pilih berkas untuk: <strong>' + namaSyarat + '</strong> (PDF / JPG / PNG, Maks 5MB)</p>' +
                            '</div>';
                }
                containerBerkas.innerHTML = html;
            } else {
                if (labelSyarat) labelSyarat.innerText = 'Persyaratan Pendaftaran:';
                if (teksSyarat) teksSyarat.innerText = "Kegiatan ini tidak memerlukan berkas persyaratan khusus dari admin. Pengunggahan file bersifat opsional.";
                if (boxSyarat) {
                    boxSyarat.className = "p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-600 space-y-1.5 transition-all";
                }
                if (badgeStatus) {
                    badgeStatus.innerText = "Opsional";
                    badgeStatus.className = "text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500";
                }

                containerBerkas.innerHTML = '<div class="p-3 bg-slate-50/90 rounded-2xl border border-slate-200 space-y-1.5">' +
                                                '<div class="flex items-center justify-between">' +
                                                    '<label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">' +
                                                        'Unggah Berkas Pendukung (Opsional)' +
                                                    '</label>' +
                                                    '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">' +
                                                        'Opsional' +
                                                    '</span>' +
                                                '</div>' +
                                                '<input type="file" name="file_syarat" accept=".pdf,.jpg,.jpeg,.png"' +
                                                    ' class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">' +
                                                '<p class="text-[10px] text-slate-400">Pengunggahan berkas bersifat opsional karena tidak ada berkas khusus yang disyaratkan.</p>' +
                                            '</div>';
            }

            document.getElementById('modalDaftar').classList.remove('hidden');
        }

        function closeModalDaftar() {
            document.getElementById('modalDaftar').classList.add('hidden');
        }

        function filterKegiatan() {
            var input = document.getElementById('searchKegiatan').value.toLowerCase();
            var cards = document.getElementsByClassName('kegiatan-card');
            for (var i = 0; i < cards.length; i++) {
                var title = cards[i].querySelector('.kegiatan-judul').innerText.toLowerCase();
                if (title.indexOf(input) > -1) {
                    cards[i].style.display = "";
                } else {
                    cards[i].style.display = "none";
                }
            }
        }

        function openModalPrestasi() {
            document.getElementById('modalPrestasi').classList.remove('hidden');
            updateDosenPembimbingNip();
        }

        function updateDosenPembimbingNip() {
            var select = document.getElementById('dosen_pembimbing_id');
            var inputNip = document.getElementById('display_nip_dosen');
            if (!select || !inputNip) return;

            var selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt) {
                var nidn = selectedOpt.getAttribute('data-nidn');
                inputNip.value = (nidn && nidn !== '-') ? nidn : '-';
            } else {
                inputNip.value = '-';
            }
        }

        function toggleFilterDosen(checkboxId, optgroupId) {
            var checkbox = document.getElementById(checkboxId);
            var optgroup = document.getElementById(optgroupId);
            if (!optgroup) return;

            if (checkbox && checkbox.checked) {
                optgroup.style.display = 'none';
                optgroup.disabled = true;
                optgroup.hidden = true;

                // Jika opsi terpilih saat ini berada di optgroup yang baru di-disable, arahkan ke opsi aktif pertama
                var select = optgroup.closest('select');
                if (select && select.selectedOptions.length > 0 && select.selectedOptions[0].disabled) {
                    for (var i = 0; i < select.options.length; i++) {
                        if (!select.options[i].disabled && !select.options[i].hidden) {
                            select.selectedIndex = i;
                            break;
                        }
                    }
                }
            } else {
                optgroup.style.display = '';
                optgroup.disabled = false;
                optgroup.hidden = false;
            }

            if (checkboxId === 'toggleDosenSejurusanPembimbing') {
                updateDosenPembimbingNip();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            updateDosenPembimbingNip();
        });
    </script>
</body>
</html>
