<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen Wali & Pembimbing - Portal Terpadu</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased selection:bg-teal-500 selection:text-white">

    <!-- Top Navigation Bar -->
    <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-teal-600 via-emerald-600 to-emerald-400 flex items-center justify-center text-white font-extrabold text-base shadow-md shadow-emerald-500/20">
                    DW
                </div>
                <div>
                    <h1 class="text-sm font-extrabold text-slate-900 tracking-tight leading-tight flex items-center gap-2">
                        Portal Akademik & Kemahasiswaan
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Dosen</span>
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">Layanan Rekomendasi & Validasi SKPI &bull; Port 8000</p>
                </div>
            </div>

            <!-- Profile Info & Logout -->
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold text-slate-900 leading-tight">{{ $user['nama_lengkap'] ?? 'Bapak/Ibu Dosen' }}</div>
                    <div class="text-[11px] text-teal-700 font-semibold mt-0.5">NIDN: {{ $user['nim_nidn'] ?? '-' }} &bull; {{ $user['prodi'] ?? 'Teknik Informatika' }}</div>
                </div>

                <div class="w-10 h-10 rounded-full bg-teal-100 text-teal-800 font-bold flex items-center justify-center text-xs border border-teal-200 hidden sm:flex">
                    {{ strtoupper(substr($user['nama_lengkap'] ?? 'D', 0, 2)) }}
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
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-teal-950 via-emerald-900 to-slate-900 p-6 sm:p-8 text-white shadow-xl">
            <!-- Decorative Glow Elements -->
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute right-28 top-0 w-40 h-40 bg-teal-400/15 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-xs font-semibold text-emerald-200 border border-white/15">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Portal Pembimbingan &bull; Tahun Ajaran 2025/2026
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Selamat Datang, {{ $user['nama_lengkap'] ?? 'Bapak/Ibu Dosen' }}! 👋
                    </h2>
                    <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                        Evaluasi dan berikan rekomendasi pendaftaran kegiatan bagi mahasiswa perwalian Anda, serta verifikasi keabsahan sertifikat lomba luar kampus untuk penerbitan poin SKPI.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-3.5 py-2 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15 text-xs font-medium text-emerald-200 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        <span>Dosen Pembimbing & Wali</span>
                    </span>
                </div>
            </div>

            @php
                $pendingRekomendasi = collect($pendaftarans)->where('status_rekomendasi_dosen', 'Menunggu')->count();
                $pendingPrestasi = collect($prestasis)->where('status_validasi_dosen', 'Menunggu')->count();
            @endphp

            <!-- 4 Modern Stat Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-6 pt-6 border-t border-white/10">
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-amber-500/30 flex items-center justify-center text-amber-300 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-300 font-medium">Menunggu Rekomendasi</div>
                        <div class="text-xl font-extrabold text-amber-300">{{ $pendingRekomendasi }} Berkas</div>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-teal-500/30 flex items-center justify-center text-teal-300 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-300 font-medium">Total Permohonan</div>
                        <div class="text-xl font-bold text-white">{{ count($pendaftarans) }} Berkas</div>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-purple-500/30 flex items-center justify-center text-purple-300 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-300 font-medium">Validasi SKPI Tertunda</div>
                        <div class="text-xl font-extrabold text-purple-300">{{ $pendingPrestasi }} Sertifikat</div>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-emerald-500/30 flex items-center justify-center text-emerald-300 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-300 font-medium">Total Klaim Masuk</div>
                        <div class="text-xl font-bold text-white">{{ count($prestasis) }} Prestasi</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- NAVIGATION TABS & FILTER                                      -->
        <!-- ============================================================= -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-3">
            <div class="flex items-center gap-2">
                <button type="button" id="tabBtnRekomendasi" onclick="switchDosenTab('rekomendasi')"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-emerald-600 text-white shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Rekomendasi Pendaftaran</span>
                    <span class="px-2 py-0.5 rounded-full bg-white/20 text-white text-[10px]">{{ count($pendaftarans) }}</span>
                </button>

                <button type="button" id="tabBtnPrestasi" onclick="switchDosenTab('prestasi')"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Validasi Prestasi (SKPI)</span>
                    <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 text-[10px]">{{ count($prestasis) }}</span>
                </button>
            </div>

            <div class="text-xs text-slate-500 font-medium">
                Pendaftaran Service: <span class="text-teal-600 font-semibold">:8003</span> &bull; Prestasi Service: <span class="text-indigo-600 font-semibold">:8004</span>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 1: PERMOHONAN REKOMENDASI PENDAFTARAN MAHASISWA (Port 8003)-->
        <!-- ============================================================= -->
        <section id="paneRekomendasi" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-50 text-teal-700 border border-teal-200 mb-1">
                        Pendaftaran Service &bull; Port 8003
                    </div>
                    <h2 class="text-lg font-bold text-slate-900">Permohonan Rekomendasi Pendaftaran</h2>
                    <p class="text-xs text-slate-500">Mahasiswa memerlukan persetujuan dosen wali sebelum dapat diverifikasi akhir oleh Admin Kemahasiswaan.</p>
                </div>

                <!-- Instant Search Filter -->
                <div class="w-full md:w-72 relative">
                    <input type="text" id="cariPendaftaran" onkeyup="filterTabelPendaftaran()" placeholder="Cari nama mahasiswa atau kegiatan..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 focus:bg-white transition">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>

            @if (empty($pendaftarans))
                <div class="text-center py-12 border border-dashed border-slate-200 rounded-2xl p-8 space-y-3">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-700">Semua Permohonan Selesai Direview</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">Saat ini tidak ada pendaftaran mahasiswa yang menunggu rekomendasi dosen wali.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs" id="tabelPendaftaran">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-3">Mahasiswa Bimbingan</th>
                                <th class="py-3 px-3">Kegiatan / Program</th>
                                <th class="py-3 px-3">Berkas Persyaratan</th>
                                <th class="py-3 px-3">Status Saat Ini</th>
                                <th class="py-3 px-3 text-right">Tindakan Rekomendasi</th>
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
                                    $statusRek = $p['status_rekomendasi_dosen'] ?? 'Menunggu';
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition row-pendaftaran">
                                    <td class="py-3.5 px-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-800 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                                {{ strtoupper(substr($namaMhs, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 leading-snug col-nama">{{ $namaMhs }}</div>
                                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">NIM: {{ $nimMhs }} &bull; {{ $prodiMhs }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3">
                                        <div class="font-semibold text-slate-800 leading-snug col-kegiatan">{{ $judulKeg }}</div>
                                        <span class="inline-block mt-1 px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-[10px] font-semibold border border-indigo-100">
                                            {{ $kategoriKeg }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3">
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
                                            <div class="flex flex-col gap-1.5">
                                                @foreach ($daftarBerkas as $labelDoc => $pathDoc)
                                                    <a href="{{ asset('storage/' . $pathDoc) }}" target="_blank"
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 text-[10px] font-semibold transition shadow-2xs"
                                                        title="Lihat Berkas: {{ $labelDoc }}">
                                                        <svg class="w-3 h-3 text-teal-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                        <span class="max-w-[140px] truncate">{{ is_string($labelDoc) ? $labelDoc : 'Lihat Berkas' }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-slate-400 text-xs italic">
                                                <span>&mdash;</span> Tanpa Berkas
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-3">
                                        @if ($statusRek === 'Direkomendasikan')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Direkomendasikan
                                            </span>
                                        @elseif ($statusRek === 'Ditolak')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 inline-flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Ditolak
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 inline-flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Menunggu
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-3 text-right">
                                        <div class="inline-flex items-center gap-2 justify-end">
                                            <form method="POST" action="{{ route('web.dosen.pendaftaran.rekomendasi', $p['id']) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status_rekomendasi_dosen" value="Direkomendasikan">
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-sm hover:shadow transition active:scale-95">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    <span>Setujui</span>
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('web.dosen.pendaftaran.rekomendasi', $p['id']) }}"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menolak permohonan rekomendasi ini?');">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status_rekomendasi_dosen" value="Ditolak">
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-semibold text-xs transition active:scale-95">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    <span>Tolak</span>
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
        <!-- TAB 2: VALIDASI KLAIM PRESTASI LOMBA (Port 8004)              -->
        <!-- ============================================================= -->
        <section id="panePrestasi" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 mb-1">
                        Prestasi Service &bull; Port 8004
                    </div>
                    <h2 class="text-lg font-bold text-slate-900">Validasi Sertifikat Prestasi Lomba (SKPI)</h2>
                    <p class="text-xs text-slate-500">Periksa dokumen bukti sertifikat juara/keikutsertaan lomba luar kampus sebelum diteruskan ke Admin Kemahasiswaan.</p>
                </div>

                <!-- Instant Search Filter -->
                <div class="w-full md:w-72 relative">
                    <input type="text" id="cariPrestasi" onkeyup="filterTabelPrestasi()" placeholder="Cari nama mahasiswa atau judul prestasi..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>

            @if (empty($prestasis))
                <div class="text-center py-12 border border-dashed border-slate-200 rounded-2xl p-8 space-y-3">
                    <div class="w-12 h-12 rounded-full bg-purple-50 text-purple-600 mx-auto flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-700">Semua Klaim Sertifikat Telah Selesai</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">Tidak ada klaim sertifikat prestasi mahasiswa yang memerlukan validasi dosen saat ini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs" id="tabelPrestasi">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-3">Nama Prestasi & Dokumen</th>
                                <th class="py-3 px-3">Mahasiswa Pengaju</th>
                                <th class="py-3 px-3">Tingkat Lomba</th>
                                <th class="py-3 px-3">Status Saat Ini</th>
                                <th class="py-3 px-3 text-right">Form Validasi & Catatan</th>
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
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition row-prestasi">
                                    <td class="py-3.5 px-3">
                                        <div class="font-bold text-slate-900 leading-snug col-prestasi">{{ $item['judul_prestasi'] }}</div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] text-slate-400 font-mono">ID: #{{ $item['id'] }}</span>
                                            @if (!empty($item['file_sertifikat']))
                                                <a href="{{ asset('storage/' . $item['file_sertifikat']) }}" target="_blank"
                                                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-teal-700 hover:text-teal-900 hover:underline">
                                                    <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                    <span>Lihat File Sertifikat</span>
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
                                                <div class="font-bold text-slate-900 leading-snug col-mhs">{{ $namaMhs }}</div>
                                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">NIM: {{ $nimMhs }} &bull; {{ $prodiMhs }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeTingkat }}">
                                            Tingkat {{ $tingkat }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3">
                                        @if ($valDosen === 'Disetujui')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Disetujui
                                            </span>
                                        @elseif ($valDosen === 'Ditolak')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 inline-flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Ditolak
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 inline-flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Menunggu
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-3 text-right">
                                        <form method="POST" action="{{ route('web.dosen.prestasi.validasi', $item['id']) }}" class="inline-flex items-center gap-2 justify-end">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="catatan" placeholder="Catatan (wajib jika tolak)"
                                                value="{{ $item['catatan'] ?? '' }}"
                                                class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs w-48 focus:ring-2 focus:ring-teal-500 focus:bg-white transition">
                                            <button type="submit" name="status_validasi_dosen" value="Disetujui"
                                                class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-sm hover:shadow transition active:scale-95">
                                                Setujui
                                            </button>
                                            <button type="submit" name="status_validasi_dosen" value="Ditolak"
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

    </main>

    <script>
        function switchDosenTab(tab) {
            const paneRek = document.getElementById('paneRekomendasi');
            const panePres = document.getElementById('panePrestasi');
            const btnRek = document.getElementById('tabBtnRekomendasi');
            const btnPres = document.getElementById('tabBtnPrestasi');

            if (tab === 'rekomendasi') {
                paneRek.classList.remove('hidden');
                panePres.classList.add('hidden');

                btnRek.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-emerald-600 text-white shadow-sm';
                btnPres.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-600';
            } else {
                paneRek.classList.add('hidden');
                panePres.classList.remove('hidden');

                btnPres.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-indigo-600 text-white shadow-sm';
                btnRek.className = 'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-600';
            }
        }

        function filterTabelPendaftaran() {
            const q = (document.getElementById('cariPendaftaran').value || '').toLowerCase();
            const rows = document.querySelectorAll('.row-pendaftaran');
            rows.forEach(r => {
                const nama = (r.querySelector('.col-nama')?.textContent || '').toLowerCase();
                const keg = (r.querySelector('.col-kegiatan')?.textContent || '').toLowerCase();
                if (nama.includes(q) || keg.includes(q)) {
                    r.style.display = '';
                } else {
                    r.style.display = 'none';
                }
            });
        }

        function filterTabelPrestasi() {
            const q = (document.getElementById('cariPrestasi').value || '').toLowerCase();
            const rows = document.querySelectorAll('.row-prestasi');
            rows.forEach(r => {
                const pres = (r.querySelector('.col-prestasi')?.textContent || '').toLowerCase();
                const mhs = (r.querySelector('.col-mhs')?.textContent || '').toLowerCase();
                if (pres.includes(q) || mhs.includes(q)) {
                    r.style.display = '';
                } else {
                    r.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
