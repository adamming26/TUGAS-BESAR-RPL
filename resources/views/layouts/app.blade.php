<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SIPAFKA' }} - Peminjaman Fasilitas Prodi Ilmu Komputer UHO</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Vite & CDN Fallback for Zero-Config Setup) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: {
                            900: '#0C1A40',
                            800: '#142555',
                            700: '#1e3573',
                        },
                        brand: {
                            blue: '#2563EB',
                            hover: '#1d4ed8',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col antialiased">
    <!-- Feedback Notifikasi Global (Flash Messages) -->
    <div class="fixed top-5 right-5 z-50 space-y-3 max-w-md w-full px-4 pointer-events-none">
        @if(session('success'))
            <div id="flash-success" class="pointer-events-auto bg-emerald-500 text-white p-4 rounded-2xl shadow-xl flex items-start space-x-3 transition transform duration-300">
                <svg class="w-6 h-6 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1 text-sm font-medium">{{ session('success') }}</div>
                <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div id="flash-error" class="pointer-events-auto bg-rose-500 text-white p-4 rounded-2xl shadow-xl flex items-start space-x-3 transition transform duration-300">
                <svg class="w-6 h-6 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1 text-sm font-medium">{{ session('error') }}</div>
                <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white">&times;</button>
            </div>
        @endif

        @if(session('warning'))
            <div id="flash-warning" class="pointer-events-auto bg-amber-500 text-white p-4 rounded-2xl shadow-xl flex items-start space-x-3 transition transform duration-300">
                <svg class="w-6 h-6 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="flex-1 text-sm font-medium">{{ session('warning') }}</div>
                <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white">&times;</button>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1">
        @yield('content')
    </main>

    <script>
        // Auto dismiss flash messages after 5 seconds
        setTimeout(() => {
            ['flash-success', 'flash-error', 'flash-warning'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.opacity = '0', setTimeout(() => el.remove(), 300);
            });
        }, 5000);
    </script>
    @stack('scripts')
</body>
</html>
