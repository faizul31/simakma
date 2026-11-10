<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Portal Microservices Kemahasiswaan</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .bg-grid-pattern {
            background-image: radial-gradient(rgba(255, 255, 255, 0.07) 1px, transparent 1px);
            background-size: 28px 28px;
        }
    </style>
</head>
<body class="bg-slate-950 min-h-screen text-slate-100 antialiased selection:bg-indigo-500 selection:text-white relative flex items-center justify-center p-4 sm:p-6 lg:p-8 overflow-x-hidden">

    <!-- Ambient Glowing Gradient Orbs -->
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none -translate-y-1/2"></div>
    <div class="fixed bottom-0 right-1/4 w-[32rem] h-[32rem] bg-cyan-600/15 rounded-full blur-3xl pointer-events-none translate-y-1/3"></div>
    <div class="fixed top-1/3 right-10 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Background Grid Pattern -->
    <div class="fixed inset-0 bg-grid-pattern pointer-events-none"></div>

    <!-- Main Container -->
    <div class="relative z-10 w-full max-w-6xl grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

        <!-- ============================================================= -->
        <!-- KOLOM KIRI: BRANDING, INFO ARSITEKTUR & AKUN DEMO             -->
        <!-- ============================================================= -->
        <div class="lg:col-span-5 space-y-6 lg:space-y-8">
            
            <!-- Logo & Brand Header -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-500 via-sky-500 to-cyan-400 flex items-center justify-center text-white font-extrabold text-xl shadow-lg shadow-indigo-500/30 ring-4 ring-white/10">
                        KM
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            API Gateway &bull; Port 8000
                        </div>
                        <h2 class="text-xs font-semibold text-slate-400">Portal Microservices Kemahasiswaan</h2>
                    </div>
                </div>

                <div class="space-y-2">
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white leading-tight">
                        Satu Pintu Layanan <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-sky-300 to-teal-300">
                            Kemahasiswaan Terpadu
                        </span>
                    </h1>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed">
                        Aplikasi arsitektur microservices berbasis Laravel 13 yang menghubungkan Auth Service (8001), Kegiatan (8002), Pendaftaran (8003), dan Prestasi SKPI (8004).
                    </p>
                </div>
            </div>

            <!-- Key Feature Highlights -->
            <div class="grid grid-cols-1 gap-2.5">
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm">
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <div class="text-xs">
                        <span class="font-bold text-white">Katalog Kegiatan & Kuota Realtime</span>
                        <p class="text-slate-400 text-[11px]">Sinkronisasi pendaftaran lomba, beasiswa, dan seminar.</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm">
                    <div class="w-8 h-8 rounded-xl bg-teal-500/20 text-teal-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <div class="text-xs">
                        <span class="font-bold text-white">Rekomendasi Dosen & Validasi SKPI</span>
                        <p class="text-slate-400 text-[11px]">Verifikasi terstruktur dari dosen wali hingga admin pusat.</p>
                    </div>
                </div>
            </div>

            <!-- Demo Account Quick Selector Card -->
            <div class="bg-slate-900/80 backdrop-blur-md border border-slate-800 rounded-3xl p-5 shadow-2xl space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300">Akun Demo (Klik untuk Isi Otomatis)</h3>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono">pass: <span class="text-amber-300">password123</span></span>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <!-- Mahasiswa -->
                    <button type="button" onclick="fillLogin('2024001001', 'password123', 'Mahasiswa')"
                        class="text-left p-2.5 rounded-2xl bg-slate-950/70 hover:bg-indigo-600/20 border border-slate-800 hover:border-indigo-500/50 transition group active:scale-95">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-white group-hover:text-indigo-300 flex items-center gap-1.5">
                                <span>🎓</span> Mahasiswa
                            </span>
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-mono mt-1">2024001001</div>
                    </button>

                    <!-- Dosen Wali -->
                    <button type="button" onclick="fillLogin('0412058501', 'password123', 'Dosen Wali')"
                        class="text-left p-2.5 rounded-2xl bg-slate-950/70 hover:bg-teal-600/20 border border-slate-800 hover:border-teal-500/50 transition group active:scale-95">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-white group-hover:text-teal-300 flex items-center gap-1.5">
                                <span>👨‍🏫</span> Dosen Wali
                            </span>
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-mono mt-1">0412058501</div>
                    </button>

                    <!-- Admin Kampus -->
                    <button type="button" onclick="fillLogin('ADM001', 'password123', 'Admin Kampus')"
                        class="text-left p-2.5 rounded-2xl bg-slate-950/70 hover:bg-cyan-600/20 border border-slate-800 hover:border-cyan-500/50 transition group active:scale-95">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-white group-hover:text-cyan-300 flex items-center gap-1.5">
                                <span>🏛️</span> Admin Kampus
                            </span>
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-mono mt-1">ADM001</div>
                    </button>

                    <!-- Pengurus Ormawa -->
                    <button type="button" onclick="fillLogin('ORM202401', 'password123', 'Pengurus Ormawa')"
                        class="text-left p-2.5 rounded-2xl bg-slate-950/70 hover:bg-purple-600/20 border border-slate-800 hover:border-purple-500/50 transition group active:scale-95">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-white group-hover:text-purple-300 flex items-center gap-1.5">
                                <span>👥</span> Ormawa
                            </span>
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-mono mt-1">ORM202401</div>
                    </button>
                </div>

                <!-- Live Campus Student (UNUJA) -->
                <button type="button" onclick="fillLogin('1921300014', 'password123', 'MOH NURUL YAQIN (UNUJA)')"
                    class="w-full text-left p-2.5 rounded-2xl bg-gradient-to-r from-emerald-950/70 to-teal-950/70 hover:from-emerald-900/50 hover:to-teal-900/50 border border-emerald-500/30 transition group active:scale-95">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-emerald-300 text-xs flex items-center gap-1.5">
                            <span>⚡</span> Mahasiswa Asli UNUJA (Live API)
                        </span>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-bold">Live Kampus</span>
                    </div>
                    <div class="text-[11px] text-slate-300 font-mono mt-1 flex items-center justify-between">
                        <span>NIM: 1921300014</span>
                        <span class="text-emerald-400 font-sans font-semibold">MOH NURUL YAQIN &bull; Teknik</span>
                    </div>
                </button>

                <!-- Live Campus Lecturer (UNUJA) -->
                <button type="button" onclick="fillLogin('0706119303', 'password123', 'FUAD HASAN, S.T. (UNUJA)')"
                    class="w-full text-left p-2.5 rounded-2xl bg-gradient-to-r from-cyan-950/70 to-blue-950/70 hover:from-cyan-900/50 hover:to-blue-900/50 border border-cyan-500/30 transition group active:scale-95">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-cyan-300 text-xs flex items-center gap-1.5">
                            <span>⚡</span> Dosen Asli UNUJA (Live API)
                        </span>
                        <span class="px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 text-[10px] font-bold">Live Kampus</span>
                    </div>
                    <div class="text-[11px] text-slate-300 font-mono mt-1 flex items-center justify-between">
                        <span>NIDN: 0706119303</span>
                        <span class="text-cyan-400 font-sans font-semibold">FUAD HASAN, S.T. &bull; Teknik Elektro</span>
                    </div>
                </button>
            </div>

            <!-- Microservices Port Status Bar -->
            <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-400 pt-2 border-t border-slate-800/80">
                <span class="inline-flex items-center gap-1.5 bg-slate-900/60 px-2.5 py-1 rounded-lg border border-slate-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span> Auth: 8001
                </span>
                <span class="inline-flex items-center gap-1.5 bg-slate-900/60 px-2.5 py-1 rounded-lg border border-slate-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Kegiatan: 8002
                </span>
                <span class="inline-flex items-center gap-1.5 bg-slate-900/60 px-2.5 py-1 rounded-lg border border-slate-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span> Pendaftaran: 8003
                </span>
                <span class="inline-flex items-center gap-1.5 bg-slate-900/60 px-2.5 py-1 rounded-lg border border-slate-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Prestasi: 8004
                </span>
            </div>

        </div>

        <!-- ============================================================= -->
        <!-- KOLOM KANAN: CARD FORM LOGIN                                  -->
        <!-- ============================================================= -->
        <div class="lg:col-span-7">
            <div class="bg-white/95 backdrop-blur-2xl rounded-3xl p-7 sm:p-10 shadow-2xl shadow-indigo-950/50 border border-white/20 text-slate-900 relative">
                
                <div class="mb-7">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 mb-2">
                        <span>🔐 Autentikasi SSO Mahasiswa & Dosen</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Masuk ke Portal</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Gunakan akun SSO Mahasiswa, Dosen, atau Akun Administrator Anda.</p>
                </div>

                <!-- Notifikasi Flash Success -->
                @if (session('success'))
                    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-3 shadow-xs">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <div class="font-bold">Berhasil!</div>
                            <div>{{ session('success') }}</div>
                        </div>
                    </div>
                @endif

                <!-- Notifikasi Flash Errors -->
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm shadow-xs">
                        <div class="font-bold flex items-center gap-2 mb-1.5">
                            <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Gagal Memproses Autentikasi</span>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-1 text-rose-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Form Login -->
                <form method="POST" action="{{ route('web.login.process') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="nim_nidn" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            NIM / NIDN / ID Pengguna
                        </label>
                        <div class="relative">
                            <input type="text" id="nim_nidn" name="nim_nidn" value="{{ old('nim_nidn') }}" required
                                placeholder="Contoh: 2024001001 atau 0412058501"
                                class="w-full pl-4 pr-11 py-3.5 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white focus:border-indigo-600 transition shadow-2xs">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Kata Sandi
                            </label>
                            <span class="text-xs text-slate-400">Default: password123</span>
                        </div>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                placeholder="Masukkan kata sandi akun Anda"
                                class="w-full pl-4 pr-11 py-3.5 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white focus:border-indigo-600 transition shadow-2xs">
                            
                            <!-- Toggle Password Visibility -->
                            <button type="button" onclick="togglePasswordVisibility()" title="Tampilkan/Sembunyikan sandi"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 transition">
                                <svg id="eyeIconOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg id="eyeIconClosed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" checked class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                            <span>Ingat sesi login saya</span>
                        </label>
                        <span id="selectedRoleIndicator" class="text-indigo-600 font-semibold hidden">
                            Role Terpilih: <span id="roleLabel">Mahasiswa</span>
                        </span>
                    </div>

                    <button type="submit"
                        class="w-full py-4 px-4 rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-700 to-sky-600 hover:from-indigo-500 hover:to-sky-500 text-white font-bold text-sm shadow-xl shadow-indigo-600/30 transition active:scale-[0.99] flex items-center justify-center gap-2.5">
                        <span>Masuk ke Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </form>

                <!-- Bottom Footer Tips -->
                <div class="mt-8 pt-6 border-t border-slate-200 text-center space-y-2">
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Autentikasi terverifikasi oleh <strong class="text-slate-800">Auth Service (Port 8001)</strong> via Bearer Token Sanctum.
                    </p>
                    <div class="inline-flex items-center gap-2 text-[11px] text-indigo-700 bg-indigo-50/90 py-1.5 px-3.5 rounded-xl border border-indigo-200">
                        <span>💡</span>
                        <span>Jalankan file <code class="font-bold text-indigo-900 bg-indigo-100 px-1 py-0.5 rounded">start_microservices.bat</code> untuk menyalakan ke-5 service sekaligus!</span>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Interactive Client Script -->
    <script>
        function fillLogin(nim, password, roleName) {
            const nimInput = document.getElementById('nim_nidn');
            const passInput = document.getElementById('password');
            const roleIndicator = document.getElementById('selectedRoleIndicator');
            const roleLabel = document.getElementById('roleLabel');

            nimInput.value = nim;
            passInput.value = password;

            if (roleName) {
                roleLabel.textContent = roleName;
                roleIndicator.classList.remove('hidden');
            }

            // Highlight animation on input
            nimInput.classList.add('ring-2', 'ring-indigo-500');
            passInput.classList.add('ring-2', 'ring-indigo-500');
            setTimeout(() => {
                nimInput.classList.remove('ring-2', 'ring-indigo-500');
                passInput.classList.remove('ring-2', 'ring-indigo-500');
            }, 600);
        }

        function togglePasswordVisibility() {
            const passInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eyeIconOpen');
            const eyeClosed = document.getElementById('eyeIconClosed');

            if (passInput.type === 'password') {
                passInput.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                passInput.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
