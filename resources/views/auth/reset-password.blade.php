<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi - SIPAFKA UHO</title>
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
                <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Buat Kata Sandi Baru</h2>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    Tentukan kata sandi baru untuk akun <span class="font-semibold text-slate-800">{{ $email }}</span>
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

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi Baru</label>
                    <input type="password" id="password" name="password" required autofocus
                           placeholder="Minimal 6 karakter"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all placeholder:text-slate-400">
                    @error('password')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           placeholder="Ulangi kata sandi baru"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all placeholder:text-slate-400">
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-lg shadow-blue-600/30 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan Kata Sandi Baru
                </button>
            </form>

            <div class="mt-6 text-center text-xs text-slate-500">
                Batal atur ulang sandi?
                <a href="{{ route('login') }}" class="text-blue-600 font-bold hover:underline ml-1">Kembali ke Masuk</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 px-6 sm:px-12 py-4 text-center text-xs text-slate-400 border-t border-white/10">
        &copy; {{ date('Y') }} SIPAFKA UHO. Program Studi Ilmu Komputer Universitas Halu Oleo.
    </footer>

</body>
</html>
