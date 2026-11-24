<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - SIPAFKA UHO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: { 950: '#070f26', 900: '#0c1a40', 800: '#13285c', 700: '#1b3b87' },
                        primary: { 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8' }
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased text-slate-900 min-h-screen relative flex flex-col justify-between overflow-x-hidden">

    <!-- Background Image with Overlay -->
    <div class="fixed inset-0 z-0 bg-cover bg-center" style="background-image: linear-gradient(135deg, rgba(12, 26, 64, 0.92) 0%, rgba(20, 48, 115, 0.88) 100%), url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=1920&q=80');">
    </div>

    <!-- Header Navigation -->
    <header class="relative z-10 px-6 sm:px-12 py-6 flex items-center justify-between max-w-7xl w-full mx-auto">
        <a href="{{ route('home') }}" class="flex items-center space-x-3 text-white">
            <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center font-extrabold text-blue-400 text-lg shadow-lg">
                S
            </div>
            <div>
                <div class="font-bold text-lg tracking-tight text-white flex items-center gap-2">
                    SIPAFKA
                    <span class="text-[10px] bg-blue-500/30 text-blue-300 font-semibold px-2 py-0.5 rounded-full border border-blue-400/30">UHO</span>
                </div>
                <div class="text-xs text-blue-200/80">Prodi Ilmu Komputer</div>
            </div>
        </a>

        <nav class="flex items-center space-x-4 text-sm text-white">
            <a href="{{ route('home') }}" class="text-blue-200 hover:text-white transition-colors">Beranda</a>
            <a href="{{ route('login') }}" class="text-blue-200 hover:text-white transition-colors">Masuk</a>
        </nav>
    </header>

    <!-- Main Section -->
    <main class="relative z-10 px-4 sm:px-8 py-8 max-w-7xl w-full mx-auto flex-1 flex items-center justify-center">
        <div class="w-full max-w-md bg-white rounded-3xl p-8 sm:p-10 shadow-2xl border border-slate-100">
            <div class="mb-6 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-sm">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Lupa Kata Sandi?</h2>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    Masukkan email terdaftar dan Nomor Induk (NIM / NIP) untuk memverifikasi akun Anda.
                </p>
            </div>

            @if(session('error'))
                <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Terdaftar</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="contoh: mahasiswa@sipafka.test"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all placeholder:text-slate-400">
                    @error('email')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nomor_induk" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor Induk (NIM / NIP / ID)</label>
                    <input type="text" id="nomor_induk" name="nomor_induk" value="{{ old('nomor_induk') }}" required
                           placeholder="contoh: F1G125001"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all placeholder:text-slate-400">
                    @error('nomor_induk')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-lg shadow-blue-600/30 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    Verifikasi Akun
                </button>
            </form>

            <div class="mt-6 text-center text-xs text-slate-500">
                Ingat kata sandi Anda?
                <a href="{{ route('login') }}" class="text-blue-600 font-bold hover:underline ml-1">Masuk Sekarang</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 px-6 sm:px-12 py-4 text-center text-xs text-slate-400 border-t border-white/10">
        &copy; {{ date('Y') }} SIPAFKA UHO. Program Studi Ilmu Komputer Universitas Halu Oleo.
    </footer>

</body>
</html>
