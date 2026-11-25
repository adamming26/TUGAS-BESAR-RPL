<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - SIPAFKA UHO</title>

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
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-800 min-h-screen flex flex-row antialiased overflow-x-hidden">

    <!-- Mobile Sidebar Backdrop -->
    <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden transition-opacity"></div>

    <!-- Sidebar Wrapper -->
    <div id="sidebar-container" class="fixed inset-y-0 left-0 z-50 transform -translate-x-full lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen transition duration-200 ease-in-out shrink-0">
        @if(auth()->user()->role === 'admin')
            @include('partials.sidebar.admin')
        @elseif(auth()->user()->role === 'dosen')
            @include('partials.sidebar.dosen')
        @else
            @include('partials.sidebar.mahasiswa')
        @endif
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen overflow-y-auto">
        <!-- Top Navbar Header (Figma Accurately Mirrored) -->
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 px-6 lg:px-10 py-4 flex items-center justify-between">
            <!-- Mobile Toggle Button -->
            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="p-2 rounded-xl text-slate-600 hover:bg-slate-100 lg:hidden focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div class="hidden lg:block text-slate-400">
                    <button class="p-2 rounded-xl hover:bg-slate-100 text-slate-500 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Profile Info Persis Figma (Right Corner) -->
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm font-semibold text-slate-900 leading-tight">
                        {{ auth()->user()->name ?? '[Nama Pengguna]' }}
                    </p>
                    <p class="text-xs text-slate-500 capitalize leading-tight">
                        {{ auth()->user()->role ?? 'Mahasiswa' }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-full bg-[#2563EB] flex items-center justify-center text-white shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
        </header>

        <!-- Flash Session Messages -->
        <div class="px-6 lg:px-10 pt-4">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center justify-between mb-4 shadow-sm animate-fade-in">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex items-center justify-between mb-4 shadow-sm animate-fade-in">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900">&times;</button>
                </div>
            @endif

            @if(session('warning'))
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-2xl flex items-center justify-between mb-4 shadow-sm animate-fade-in">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <span class="text-sm font-medium">{{ session('warning') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-amber-600 hover:text-amber-900">&times;</button>
                </div>
            @endif
        </div>

        <!-- Page Content -->
        <main class="flex-1 px-6 lg:px-10 py-6 pb-12">
            @yield('content')
        </main>
    </div>

    <!-- Modal Konfirmasi Universal (UX Modal Dialog Persis Permintaan) -->
    <div id="confirm-modal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl transform transition-all">
            <div class="flex items-center space-x-3 mb-4">
                <div id="modal-icon-bg" class="w-12 h-12 rounded-2xl bg-blue-100 text-[#2563EB] flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 id="modal-title" class="text-lg font-bold text-slate-900">Konfirmasi Aksi</h3>
                    <p id="modal-subtitle" class="text-xs text-slate-500">Pastikan keputusan Anda sudah sesuai</p>
                </div>
            </div>
            <p id="modal-message" class="text-sm text-slate-600 mb-6 leading-relaxed">
                Apakah Anda yakin ingin melanjutkan tindakan ini?
            </p>
            <div class="flex items-center justify-end space-x-3">
                <button type="button" onclick="closeConfirmModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="button" id="modal-confirm-btn" class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-sm font-medium text-white transition shadow-sm">
                    Lanjutkan
                </button>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar-container');
            const backdrop = document.getElementById('sidebar-backdrop');
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }

        let onModalConfirmCallback = null;

        function openConfirmModal(title, message, confirmBtnText = 'Lanjutkan', isDanger = false, callback = null) {
            document.getElementById('modal-title').textContent = title;
            document.getElementById('modal-message').textContent = message;
            const confirmBtn = document.getElementById('modal-confirm-btn');
            confirmBtn.textContent = confirmBtnText;

            if (isDanger) {
                confirmBtn.className = "px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-sm font-medium text-white transition shadow-sm";
            } else {
                confirmBtn.className = "px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-sm font-medium text-white transition shadow-sm";
            }

            onModalConfirmCallback = callback;
            document.getElementById('confirm-modal').classList.remove('hidden');
        }

        function closeConfirmModal() {
            document.getElementById('confirm-modal').classList.add('hidden');
            onModalConfirmCallback = null;
        }

        document.getElementById('modal-confirm-btn').addEventListener('click', function() {
            if (typeof onModalConfirmCallback === 'function') {
                onModalConfirmCallback();
            }
            closeConfirmModal();
        });
    </script>
    @stack('scripts')
</body>
</html>
