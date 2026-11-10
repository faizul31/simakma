<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Microservices Dashboard & API Playground</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4f46e5',
                        secondary: '#06b6d4',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen font-sans antialiased">

    <!-- Header Navigation -->
    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-cyan-400 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/25">
                    <i class="fa-solid fa-network-wired"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg text-white">Microservices Campus Portal</h1>
                    <p class="text-xs text-slate-400">Laravel 13 &bull; MySQL &bull; 1 Gateway + 4 Services</p>
                </div>
            </div>

            <!-- Active User Status -->
            <div id="authStatusBadge" class="flex items-center gap-3">
                <span class="text-xs bg-slate-800 text-slate-400 px-3 py-1.5 rounded-full border border-slate-700">
                    <i class="fa-solid fa-circle text-amber-500 text-[8px] mr-1.5 animate-pulse"></i> Belum Login
                </span>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Architecture Overview Banner -->
        <div class="bg-gradient-to-r from-indigo-900/40 via-purple-900/30 to-slate-900 border border-indigo-500/30 rounded-2xl p-6 mb-8">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-indigo-400 bg-indigo-950/80 px-2.5 py-1 rounded-md border border-indigo-800/50">
                        Arsitektur Microservices Aktif
                    </span>
                    <h2 class="text-xl font-bold text-white mt-2">Portal Interaktif Backend API</h2>
                    <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                        Aplikasi microservices umumnya berjalan sebagai <strong class="text-white">Headless REST API</strong> (menghasilkan JSON). Halaman ini disediakan sebagai dashboard interaktif untuk menguji alur autentikasi, kegiatan, dan pendaftaran secara langsung dari browser.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs">
                    <span class="px-3 py-1.5 bg-slate-800 rounded-lg border border-slate-700 text-slate-300">
                        <strong class="text-emerald-400">Port 8001:</strong> Auth Service
                    </span>
                    <span class="px-3 py-1.5 bg-slate-800 rounded-lg border border-slate-700 text-slate-300">
                        <strong class="text-cyan-400">Port 8002:</strong> Kegiatan Service
                    </span>
                    <span class="px-3 py-1.5 bg-slate-800 rounded-lg border border-slate-700 text-slate-300">
                        <strong class="text-indigo-400">Port 8003:</strong> Pendaftaran Service
                    </span>
                    <span class="px-3 py-1.5 bg-slate-800 rounded-lg border border-slate-700 text-slate-300">
                        <strong class="text-amber-400">Port 8004:</strong> Prestasi Service
                    </span>
                </div>
            </div>
        </div>

        <!-- Grid Layout: 3 Columns -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- Left Column: Auth & User Profile (4 Cols) -->
            <div class="lg:col-span-4 space-y-6">

                <!-- Login Card -->
                <div class="bg-slate-800/70 border border-slate-700 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-key text-amber-400"></i> Service 1: Autentikasi
                        </h3>
                        <span class="text-[10px] bg-slate-700 text-slate-300 px-2 py-0.5 rounded">Sanctum Token</span>
                    </div>

                    <p class="text-xs text-slate-400 mb-4">Pilih salah satu akun dummy untuk uji coba login instan:</p>

                    <!-- Quick Login Buttons -->
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <button onclick="quickLogin('2024001001', 'password123')" class="p-2.5 bg-slate-900/80 hover:bg-indigo-950/80 border border-slate-700 hover:border-indigo-500 rounded-xl text-left transition group">
                            <div class="text-xs font-semibold text-white group-hover:text-indigo-300">Mahasiswa</div>
                            <div class="text-[10px] text-slate-400">Ahmad Fauzi</div>
                        </button>
                        <button onclick="quickLogin('ADM001', 'password123')" class="p-2.5 bg-slate-900/80 hover:bg-emerald-950/80 border border-slate-700 hover:border-emerald-500 rounded-xl text-left transition group">
                            <div class="text-xs font-semibold text-white group-hover:text-emerald-300">Admin</div>
                            <div class="text-[10px] text-slate-400">Siti Rahmawati</div>
                        </button>
                        <button onclick="quickLogin('ORM202401', 'password123')" class="p-2.5 bg-slate-900/80 hover:bg-purple-950/80 border border-slate-700 hover:border-purple-500 rounded-xl text-left transition group">
                            <div class="text-xs font-semibold text-white group-hover:text-purple-300">Ormawa</div>
                            <div class="text-[10px] text-slate-400">BEM Fasilkom</div>
                        </button>
                        <button onclick="quickLogin('0412058501', 'password123')" class="p-2.5 bg-slate-900/80 hover:bg-amber-950/80 border border-slate-700 hover:border-amber-500 rounded-xl text-left transition group">
                            <div class="text-xs font-semibold text-white group-hover:text-amber-300">Dosen</div>
                            <div class="text-[10px] text-slate-400">Dr. Budi Santoso</div>
                        </button>
                    </div>

                    <!-- Manual Login Form -->
                    <form id="loginForm" onsubmit="handleManualLogin(event)" class="space-y-3 pt-3 border-t border-slate-700">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">NIM / NIDN</label>
                            <input type="text" id="inputNim" required placeholder="Contoh: 2024001001" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Password</label>
                            <input type="password" id="inputPassword" required value="password123" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-medium py-2 rounded-lg text-sm transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i> Login ke Auth Service
                        </button>
                    </form>

                    <!-- Active User Info Box -->
                    <div id="userProfileBox" class="hidden mt-4 p-4 bg-slate-900/90 border border-emerald-500/40 rounded-xl space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-user-check"></i> Sesi Aktif
                            </span>
                            <button onclick="handleLogout()" class="text-[11px] text-red-400 hover:text-red-300 underline">Logout</button>
                        </div>
                        <div class="text-sm font-semibold text-white" id="userName">-</div>
                        <div class="text-xs text-slate-400 flex items-center justify-between">
                            <span>Role: <span id="userRoleBadge" class="uppercase font-bold text-indigo-400">-</span></span>
                            <span>ID: <span id="userIdVal" class="text-slate-300">-</span></span>
                        </div>
                    </div>
                </div>

                <!-- API Response Monitor -->
                <div class="bg-slate-800/70 border border-slate-700 rounded-2xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-300 flex items-center gap-2">
                            <i class="fa-solid fa-terminal text-cyan-400"></i> JSON Response Output
                        </span>
                        <span id="responseStatusBadge" class="text-[10px] px-2 py-0.5 rounded bg-slate-700 text-slate-400">Ready</span>
                    </div>
                    <pre id="jsonOutput" class="bg-slate-950 p-3 rounded-lg text-[11px] text-emerald-400 overflow-x-auto max-h-56 font-mono border border-slate-800">Menunggu aksi request API...</pre>
                </div>
            </div>

            <!-- Right Column: Services Interaction (8 Cols) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- Service 2: Kegiatan Catalog -->
                <div class="bg-slate-800/70 border border-slate-700 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-calendar-check text-cyan-400"></i> Service 2: Katalog Kegiatan Kampus
                            </h3>
                            <p class="text-xs text-slate-400">Data live dari tabel <code>kegiatans</code> di <code>db_kegiatan</code> (Port 8002)</p>
                        </div>
                        <button onclick="loadKegiatan()" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 rounded-lg text-xs font-medium text-white transition flex items-center gap-1.5">
                            <i class="fa-solid fa-arrows-rotate"></i> Refresh
                        </button>
                    </div>

                    <!-- Kegiatan Cards Grid -->
                    <div id="kegiatanContainer" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-8 text-center text-slate-500 col-span-2 text-sm">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Memuat data kegiatan...
                        </div>
                    </div>
                </div>

                <!-- Service 3: Pendaftaran & Verifikasi -->
                <div class="bg-slate-800/70 border border-slate-700 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-file-signature text-indigo-400"></i> Service 3: Riwayat & Verifikasi Pendaftaran
                            </h3>
                            <p class="text-xs text-slate-400">Data live dari tabel <code>pendaftarans</code> di <code>db_pendaftaran</code> (Port 8003)</p>
                        </div>
                        <button onclick="loadPendaftarans()" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 rounded-lg text-xs font-medium text-white transition flex items-center gap-1.5">
                            <i class="fa-solid fa-arrows-rotate"></i> Muat Ulang
                        </button>
                    </div>

                    <!-- Pendaftaran Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-900/60 text-slate-400 border-b border-slate-700 uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-2.5 px-3">ID</th>
                                    <th class="py-2.5 px-3">Kegiatan</th>
                                    <th class="py-2.5 px-3">Mahasiswa ID</th>
                                    <th class="py-2.5 px-3">Rekomendasi Dosen</th>
                                    <th class="py-2.5 px-3">Status Akhir</th>
                                    <th class="py-2.5 px-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="pendaftaranTableBody" class="divide-y divide-slate-700 text-slate-300">
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-slate-500">Klik "Muat Ulang" atau lakukan pendaftaran untuk melihat data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Service 4: Prestasi & SKPI Service (Port 8004) -->
                <div class="bg-slate-800/70 border border-slate-700 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-trophy text-amber-400"></i> Service 4: Portofolio Prestasi Mahasiswa (SKPI)
                            </h3>
                            <p class="text-xs text-slate-400">Klaim sertifikat lomba luar kampus di <code>db_prestasi</code> (Port 8004)</p>
                        </div>
                        <button onclick="openModalPrestasi()" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-500 rounded-lg text-xs font-medium text-white transition flex items-center gap-1.5">
                            <i class="fa-solid fa-plus"></i> Klaim Prestasi
                        </button>
                    </div>

                    <!-- Prestasi Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-900/60 text-slate-400 border-b border-slate-700 uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-2.5 px-3">ID</th>
                                    <th class="py-2.5 px-3">Judul Prestasi</th>
                                    <th class="py-2.5 px-3">Tingkat</th>
                                    <th class="py-2.5 px-3">Validasi Dosen</th>
                                    <th class="py-2.5 px-3">Validasi Akhir</th>
                                    <th class="py-2.5 px-3 text-right">Aksi Validasi</th>
                                </tr>
                            </thead>
                            <tbody id="prestasiTableBody" class="divide-y divide-slate-700 text-slate-300">
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-slate-500">Memuat data prestasi...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

    </main>

    <!-- Modal Klaim Prestasi -->
    <div id="prestasiModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-slate-800 border border-slate-700 rounded-2xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-trophy text-amber-400"></i> Form Klaim Prestasi SKPI
                </h4>
                <button onclick="closeModalPrestasi()" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form id="formPrestasi" onsubmit="submitPrestasi(event)" class="space-y-3">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">ID Mahasiswa</label>
                    <input type="number" id="prestasiMahasiswaId" required value="1" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">ID Dosen Pembimbing (Opsional)</label>
                    <input type="number" id="prestasiDosenId" value="4" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" placeholder="Contoh: 4 (Dr. Budi Santoso)">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Judul Prestasi / Lomba</label>
                    <input type="text" id="prestasiJudul" required placeholder="Juara 1 Hackathon Nasional 2026" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Tingkat Kejuaraan</label>
                    <select id="prestasiTingkat" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
                        <option value="Lokal">Lokal</option>
                        <option value="Provinsi">Provinsi</option>
                        <option value="Nasional" selected>Nasional</option>
                        <option value="Internasional">Internasional</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Upload File Sertifikat (PDF/JPG)</label>
                    <input type="file" id="prestasiFile" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-300">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="closeModalPrestasi()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-xs">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white font-medium rounded-lg text-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Unggah Klaim
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div id="daftarModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-slate-800 border border-slate-700 rounded-2xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-bold text-white" id="modalKegiatanJudul">Daftar Kegiatan</h4>
                <button onclick="closeModal()" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form id="formPendaftaran" onsubmit="submitPendaftaran(event)" class="space-y-4">
                <input type="hidden" id="modalKegiatanId">

                <div>
                    <label class="block text-xs text-slate-400 mb-1">ID Mahasiswa Pendaftar</label>
                    <input type="number" id="modalMahasiswaId" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" placeholder="Contoh: 1">
                    <p class="text-[10px] text-slate-500 mt-1">Otomatis terisi dari ID user yang sedang login</p>
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">ID Dosen Wali (Opsional)</label>
                    <input type="number" id="modalDosenWaliId" value="4" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" placeholder="ID Dosen (misal: 4)">
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">File Syarat Pendaftaran (PDF/Gambar)</label>
                    <input type="file" id="modalFileSyarat" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-300">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-xs">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-lg text-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Pendaftaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Frontend Scripting Logic -->
    <script>
        let authToken = localStorage.getItem('token') || '';
        let currentUser = JSON.parse(localStorage.getItem('user') || 'null');

        // Init on page load
        document.addEventListener('DOMContentLoaded', () => {
            renderAuthStatus();
            loadKegiatan();
            loadPendaftarans();
            loadPrestasis();
        });

        function logOutput(data, status = 200) {
            const output = document.getElementById('jsonOutput');
            const badge = document.getElementById('responseStatusBadge');
            output.textContent = typeof data === 'string' ? data : JSON.stringify(data, null, 2);

            badge.textContent = `HTTP ${status}`;
            if (status >= 200 && status < 300) {
                badge.className = 'text-[10px] px-2 py-0.5 rounded bg-emerald-950 text-emerald-400 border border-emerald-800';
            } else {
                badge.className = 'text-[10px] px-2 py-0.5 rounded bg-rose-950 text-rose-400 border border-rose-800';
            }
        }

        function renderAuthStatus() {
            const badge = document.getElementById('authStatusBadge');
            const profileBox = document.getElementById('userProfileBox');

            if (currentUser && authToken) {
                badge.innerHTML = `
                    <div class="flex items-center gap-2 bg-emerald-950/60 border border-emerald-600/40 text-emerald-300 px-3 py-1.5 rounded-full text-xs">
                        <i class="fa-solid fa-circle text-emerald-400 text-[8px] animate-pulse"></i>
                        <span>${currentUser.nama_lengkap} (${currentUser.role})</span>
                    </div>
                `;
                profileBox.classList.remove('hidden');
                document.getElementById('userName').textContent = currentUser.nama_lengkap;
                document.getElementById('userRoleBadge').textContent = currentUser.role;
                document.getElementById('userIdVal').textContent = currentUser.id;
            } else {
                badge.innerHTML = `
                    <span class="text-xs bg-slate-800 text-slate-400 px-3 py-1.5 rounded-full border border-slate-700">
                        <i class="fa-solid fa-circle text-amber-500 text-[8px] mr-1.5 animate-pulse"></i> Belum Login
                    </span>
                `;
                profileBox.classList.add('hidden');
            }
        }

        async function quickLogin(nim, password) {
            document.getElementById('inputNim').value = nim;
            document.getElementById('inputPassword').value = password;
            await doLogin(nim, password);
        }

        async function handleManualLogin(e) {
            e.preventDefault();
            const nim = document.getElementById('inputNim').value;
            const pass = document.getElementById('inputPassword').value;
            await doLogin(nim, pass);
        }

        async function doLogin(nim, password) {
            try {
                const res = await fetch('/api/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ nim_nidn: nim, password: password })
                });

                const json = await res.json();
                logOutput(json, res.status);

                if (res.ok && json.data) {
                    authToken = json.data.access_token;
                    currentUser = json.data.user;
                    localStorage.setItem('token', authToken);
                    localStorage.setItem('user', JSON.stringify(currentUser));
                    renderAuthStatus();
                } else {
                    alert(json.message || 'Login gagal');
                }
            } catch (err) {
                logOutput({ error: err.message }, 500);
            }
        }

        function handleLogout() {
            authToken = '';
            currentUser = null;
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            renderAuthStatus();
            logOutput({ message: "Logout berhasil lokal" }, 200);
        }

        async function loadKegiatan() {
            const container = document.getElementById('kegiatanContainer');
            try {
                const res = await fetch('/api/kegiatan/semua');
                const json = await res.json();

                if (res.ok && json.data) {
                    if (json.data.length === 0) {
                        container.innerHTML = `<div class="p-8 text-center text-slate-500 col-span-2">Belum ada kegiatan yang terdaftar.</div>`;
                        return;
                    }

                    container.innerHTML = json.data.map(k => {
                        const sisa = Math.max(0, k.kuota_maksimal - k.kuota_terisi);
                        const pct = Math.min(100, Math.round((k.kuota_terisi / k.kuota_maksimal) * 100));
                        const isPublished = k.status_publikasi === 'Published';

                        return `
                            <div class="p-4 bg-slate-900/80 border border-slate-700/80 rounded-xl space-y-3">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded ${isPublished ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-amber-950 text-amber-400 border border-amber-800'}">
                                        ${k.status_publikasi}
                                    </span>
                                    <span class="text-[11px] text-slate-400">ID: #${k.id}</span>
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-white">${k.judul_kegiatan}</h4>
                                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">${k.deskripsi}</p>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex justify-between text-[11px] text-slate-400">
                                        <span>Kuota Terisi: <strong class="text-white">${k.kuota_terisi} / ${k.kuota_maksimal}</strong></span>
                                        <span>Sisa: <strong class="text-emerald-400">${sisa}</strong></span>
                                    </div>
                                    <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-indigo-500 h-1.5 rounded-full" style="width: ${pct}%"></div>
                                    </div>
                                </div>
                                <div class="pt-2 flex justify-between items-center border-t border-slate-800">
                                    <button onclick="cekKuotaLive(${k.id})" class="text-xs text-cyan-400 hover:text-cyan-300 underline">
                                        <i class="fa-solid fa-satellite-dish"></i> Cek Kuota API
                                    </button>
                                    <button onclick="openModal(${k.id}, '${k.judul_kegiatan}')" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-medium transition">
                                        Daftar
                                    </button>
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            } catch (err) {
                container.innerHTML = `<div class="p-8 text-center text-red-400 col-span-2">Gagal memuat kegiatan: ${err.message}</div>`;
            }
        }

        async function cekKuotaLive(id) {
            try {
                const res = await fetch(`/api/kegiatan/${id}/cek-kuota`);
                const json = await res.json();
                logOutput(json, res.status);
            } catch (err) {
                logOutput({ error: err.message }, 500);
            }
        }

        function openModal(kegiatanId, judul) {
            document.getElementById('modalKegiatanId').value = kegiatanId;
            document.getElementById('modalKegiatanJudul').textContent = `Daftar: ${judul}`;
            if (currentUser) {
                document.getElementById('modalMahasiswaId').value = currentUser.id;
            }
            document.getElementById('daftarModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('daftarModal').classList.add('hidden');
        }

        async function submitPendaftaran(e) {
            e.preventDefault();
            const kegiatanId = document.getElementById('modalKegiatanId').value;
            const mahasiswaId = document.getElementById('modalMahasiswaId').value;
            const dosenId = document.getElementById('modalDosenWaliId').value;
            const fileInput = document.getElementById('modalFileSyarat');

            const formData = new FormData();
            formData.append('kegiatan_id', kegiatanId);
            formData.append('mahasiswa_id', mahasiswaId);
            if (dosenId) formData.append('dosen_wali_id', dosenId);
            if (fileInput.files[0]) formData.append('file_syarat', fileInput.files[0]);

            try {
                const res = await fetch('/api/daftar', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });

                const json = await res.json();
                logOutput(json, res.status);

                if (res.ok) {
                    alert('Pendaftaran Berhasil! Kuota kegiatan otomatis dipotong.');
                    closeModal();
                    loadKegiatan();
                    loadPendaftarans();
                } else {
                    alert(json.message || 'Pendaftaran Gagal');
                }
            } catch (err) {
                logOutput({ error: err.message }, 500);
            }
        }

        async function loadPendaftarans() {
            const tbody = document.getElementById('pendaftaranTableBody');
            try {
                // Fetch riwayat atau all pendaftaran
                const res = await fetch('/api/pendaftaran/mahasiswa/1'); // default test user 1
                const json = await res.json();

                if (res.ok && json.data) {
                    if (json.data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="6" class="py-6 text-center text-slate-500">Belum ada riwayat pendaftaran. Coba lakukan pendaftaran di atas.</td></tr>`;
                        return;
                    }

                    tbody.innerHTML = json.data.map(p => `
                        <tr class="hover:bg-slate-800/40">
                            <td class="py-2.5 px-3 font-mono text-slate-400">#${p.id}</td>
                            <td class="py-2.5 px-3 font-medium text-white">Kegiatan #${p.kegiatan_id}</td>
                            <td class="py-2.5 px-3 text-slate-300">Mhs #${p.mahasiswa_id}</td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] ${p.status_rekomendasi_dosen === 'Direkomendasikan' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-slate-800 text-slate-400'}">
                                    ${p.status_rekomendasi_dosen}
                                </span>
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] ${p.status_pendaftaran === 'Diterima' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : (p.status_pendaftaran === 'Ditolak' ? 'bg-rose-950 text-rose-400 border border-rose-800' : 'bg-amber-950 text-amber-400 border border-amber-800')}">
                                    ${p.status_pendaftaran}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-right space-x-1">
                                <button onclick="rekomendasikan(${p.id})" title="Dosen: Rekomendasikan" class="px-2 py-1 bg-amber-700/60 hover:bg-amber-600 text-[10px] rounded text-white">
                                    <i class="fa-solid fa-stamp"></i> Rekomendasi
                                </button>
                                <button onclick="verifikasiAkhir(${p.id})" title="Admin: Verifikasi Diterima" class="px-2 py-1 bg-emerald-700/60 hover:bg-emerald-600 text-[10px] rounded text-white">
                                    <i class="fa-solid fa-check-double"></i> Terima
                                </button>
                            </td>
                        </tr>
                    `).join('');
                }
            } catch (err) {
                tbody.innerHTML = `<tr><td colspan="6" class="py-4 text-center text-red-400">Gagal memuat data pendaftaran: ${err.message}</td></tr>`;
            }
        }

        async function rekomendasikan(id) {
            try {
                const res = await fetch(`/api/pendaftaran/${id}/rekomendasi`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ status_rekomendasi_dosen: 'Direkomendasikan' })
                });
                const json = await res.json();
                logOutput(json, res.status);
                loadPendaftarans();
            } catch (err) {
                logOutput({ error: err.message }, 500);
            }
        }

        async function verifikasiAkhir(id) {
            try {
                const res = await fetch(`/api/pendaftaran/${id}/verifikasi-akhir`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ status_pendaftaran: 'Diterima', catatan_verifikator: 'Diverifikasi via Dashboard' })
                });
                const json = await res.json();
                logOutput(json, res.status);
                loadPendaftarans();
            } catch (err) {
                logOutput({ error: err.message }, 500);
            }
        }

        // ================= Prestasi Service Logic (Port 8004) =================
        function openModalPrestasi() {
            if (currentUser) {
                document.getElementById('prestasiMahasiswaId').value = currentUser.id;
            }
            document.getElementById('prestasiModal').classList.remove('hidden');
        }

        function closeModalPrestasi() {
            document.getElementById('prestasiModal').classList.add('hidden');
        }

        async function submitPrestasi(e) {
            e.preventDefault();
            const mhsId = document.getElementById('prestasiMahasiswaId').value;
            const dosenId = document.getElementById('prestasiDosenId').value;
            const judul = document.getElementById('prestasiJudul').value;
            const tingkat = document.getElementById('prestasiTingkat').value;
            const file = document.getElementById('prestasiFile').files[0];

            const formData = new FormData();
            formData.append('mahasiswa_id', mhsId);
            if (dosenId) formData.append('dosen_pembimbing_id', dosenId);
            formData.append('judul_prestasi', judul);
            formData.append('tingkat', tingkat);
            if (file) formData.append('file_sertifikat', file);

            try {
                const res = await fetch('/api/prestasi', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });
                const json = await res.json();
                logOutput(json, res.status);

                if (res.ok) {
                    alert('Klaim prestasi lomba berhasil diunggah!');
                    closeModalPrestasi();
                    loadPrestasis();
                } else {
                    alert(json.message || 'Gagal mengunggah klaim prestasi');
                }
            } catch (err) {
                logOutput({ error: err.message }, 500);
            }
        }

        async function loadPrestasis() {
            const tbody = document.getElementById('prestasiTableBody');
            try {
                const res = await fetch('/api/prestasi');
                const json = await res.json();

                if (res.ok && json.data) {
                    if (json.data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="6" class="py-6 text-center text-slate-500">Belum ada klaim prestasi diajukan. Klik "Klaim Prestasi" untuk menambahkan.</td></tr>`;
                        return;
                    }

                    tbody.innerHTML = json.data.map(p => `
                        <tr class="hover:bg-slate-800/40">
                            <td class="py-2.5 px-3 font-mono text-slate-400">#${p.id}</td>
                            <td class="py-2.5 px-3 font-medium text-white">${p.judul_prestasi}</td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-cyan-300 border border-slate-700">${p.tingkat}</span>
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] ${p.status_validasi_dosen === 'Disetujui' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : (p.status_validasi_dosen === 'Ditolak' ? 'bg-rose-950 text-rose-400 border border-rose-800' : 'bg-slate-800 text-slate-400')}">
                                    ${p.status_validasi_dosen}
                                </span>
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] ${p.status_validasi_akhir === 'Disetujui' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : (p.status_validasi_akhir === 'Ditolak' ? 'bg-rose-950 text-rose-400 border border-rose-800' : 'bg-amber-950 text-amber-400 border border-amber-800')}">
                                    ${p.status_validasi_akhir}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-right space-x-1">
                                <button onclick="validasiDosenPrestasi(${p.id}, 'Disetujui')" title="Dosen: Setujui" class="px-2 py-1 bg-amber-700/60 hover:bg-amber-600 text-[10px] rounded text-white">
                                    <i class="fa-solid fa-check"></i> Dosen
                                </button>
                                <button onclick="validasiAdminPrestasi(${p.id}, 'Disetujui')" title="Admin: Setujui Final" class="px-2 py-1 bg-emerald-700/60 hover:bg-emerald-600 text-[10px] rounded text-white">
                                    <i class="fa-solid fa-check-double"></i> Admin
                                </button>
                            </td>
                        </tr>
                    `).join('');
                }
            } catch (err) {
                tbody.innerHTML = `<tr><td colspan="6" class="py-4 text-center text-red-400">Gagal memuat prestasi: ${err.message}</td></tr>`;
            }
        }

        async function validasiDosenPrestasi(id, status) {
            try {
                const res = await fetch(`/api/prestasi/${id}/validasi-dosen`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ status_validasi_dosen: status, catatan: 'Disetujui oleh dosen pembimbing' })
                });
                const json = await res.json();
                logOutput(json, res.status);
                loadPrestasis();
            } catch (err) {
                logOutput({ error: err.message }, 500);
            }
        }

        async function validasiAdminPrestasi(id, status) {
            try {
                const res = await fetch(`/api/prestasi/${id}/validasi-admin`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ status_validasi_akhir: status, catatan: 'Validasi akhir kemahasiswaan lolos' })
                });
                const json = await res.json();
                logOutput(json, res.status);
                loadPrestasis();
            } catch (err) {
                logOutput({ error: err.message }, 500);
            }
        }
    </script>
</body>
</html>
