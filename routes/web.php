<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes - SIPAFKA (Sistem Informasi Peminjaman Fasilitas Kampus)
| Program Studi Ilmu Komputer FMIPA Universitas Halu Oleo
| Tim Pengembang & Scrum Master: Adam Muzakir Eni (NIM: F1G125001)
|--------------------------------------------------------------------------
| Setiap anggota modul (M1 - M6) bekerja di blok penanda masing-masing.
| Jangan mengubah atau mengisi bagian milik anggota lain.
*/

// START M1
// =========================================================================
// MODUL 1: FOUNDATION, AUTENTIKASI & KOMPONEN BERSAMA
// Pemilik: M1 — Foundation / Backend & Shared Components (Adam Muzakir Eni)
// =========================================================================

// Halaman Publik & Katalog Awal
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/fasilitas', [HomeController::class, 'fasilitas'])->name('fasilitas.index');

// Autentikasi Pengguna (Tamu / Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    Route::get('/lupa-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::post('/lupa-password', function () {
        return back()->with('info', 'Tautan pemulihan kata sandi telah dikirim ke email institusi.');
    })->name('password.email');
});

// Sesi Terautentikasi & Profil Pengguna
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profil', [AuthController::class, 'showProfile'])->name('profile.index');
    Route::put('/profil', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profil/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // =========================================================================
    // FITUR ADMIN (M1 — Admin)
    // =========================================================================
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // 1. Dashboard Admin
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // 2. Manajemen Fasilitas Barang & Laboratorium
        Route::get('/fasilitas', [AdminController::class, 'kelolaFasilitas'])->name('kelola-fasilitas');
        Route::post('/fasilitas/barang', [AdminController::class, 'storeBarang'])->name('store-barang');
        Route::put('/fasilitas/barang/{id}', [AdminController::class, 'updateBarang'])->name('update-barang');
        Route::delete('/fasilitas/barang/{id}', [AdminController::class, 'destroyBarang'])->name('destroy-barang');

        Route::post('/fasilitas/ruangan', [AdminController::class, 'storeRuangan'])->name('store-ruangan');
        Route::put('/fasilitas/ruangan/{id}', [AdminController::class, 'updateRuangan'])->name('update-ruangan');
        Route::delete('/fasilitas/ruangan/{id}', [AdminController::class, 'destroyRuangan'])->name('destroy-ruangan');

        // 3. Manajemen Pengguna (Civitas Akademika)
        Route::get('/pengguna', [AdminController::class, 'kelolaPengguna'])->name('kelola-pengguna');
        Route::put('/pengguna/{id}', [AdminController::class, 'updatePengguna'])->name('pengguna.update');
        Route::put('/pengguna/{id}/role', [AdminController::class, 'updateRolePengguna'])->name('pengguna.update-role');
        Route::delete('/pengguna/{id}', [AdminController::class, 'destroyPengguna'])->name('pengguna.destroy');

        // Monitoring Transaksi & Riwayat (Aktivitas Sistem)
        Route::get('/transaksi', [AdminController::class, 'transaksiBerjalan'])->name('transaksi-berjalan');
        Route::get('/riwayat', [AdminController::class, 'riwayatTransaksi'])->name('riwayat-transaksi');
    });
});
// END M1

// START M2
// =========================================================================
// MODUL 2: LANDING PAGE, AUTENTIKASI, RESET PASSWORD, PROFIL & PANDUAN
// Pemilik: M2 — Product Owner + Developer (Marcelo Huggie Robin E.A. / F1G125038)
// =========================================================================

// Halaman Publik: Landing Page & Panduan Penggunaan
Route::get('/', [\App\Http\Controllers\M2\LandingController::class, 'index'])->name('home');
Route::get('/panduan', [\App\Http\Controllers\M2\LandingController::class, 'panduan'])->name('panduan.index');

// Autentikasi & Pemulihan Kata Sandi (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\M2\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\M2\AuthController::class, 'login'])->name('login.post');

    Route::get('/register', [\App\Http\Controllers\M2\AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [\App\Http\Controllers\M2\AuthController::class, 'register'])->name('register.post');

    Route::get('/lupa-password', [\App\Http\Controllers\M2\PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/lupa-password', [\App\Http\Controllers\M2\PasswordResetController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password/{token}', [\App\Http\Controllers\M2\PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\M2\PasswordResetController::class, 'reset'])->name('password.update');
});

// Sesi Terautentikasi, Logout & Manajemen Profil Pengguna
Route::middleware('auth')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\M2\AuthController::class, 'logout'])->name('logout');

    Route::get('/profil', [\App\Http\Controllers\M2\ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profil/edit', [\App\Http\Controllers\M2\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [\App\Http\Controllers\M2\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/password', [\App\Http\Controllers\M2\ProfileController::class, 'updatePassword'])->name('profile.password');
});
// END M2

// START M3
// =========================================================================
// MODUL 3: PEMINJAMAN FASILITAS (MAHASISWA)
// Rute untuk Modul 3 disiapkan di sini oleh penanggung jawab M3.
// =========================================================================
// END M3

// START M4
// =========================================================================
// MODUL 4: DASHBOARD MAHASISWA, PEMINJAMAN BARANG/LAB & RIWAYAT (M4)
// Pemilik: M4 — Developer 2 (Ridha Septiani K / F1G125075)
// =========================================================================
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    // 1. Dashboard Mahasiswa
    Route::get('/dashboard', [\App\Http\Controllers\M4\DashboardController::class, 'index'])
        ->name('dashboard');

    // 2. Form Peminjaman Barang & Laboratorium
    Route::get('/peminjaman/barang/{id?}', [\App\Http\Controllers\M4\PeminjamanController::class, 'createBarang'])
        ->name('form-barang');
    Route::get('/peminjaman/lab/{id?}', [\App\Http\Controllers\M4\PeminjamanController::class, 'createLab'])
        ->name('form-lab');
    Route::get('/pinjam/{jenis?}/{id?}', [\App\Http\Controllers\M4\PeminjamanController::class, 'create'])
        ->name('form-peminjaman');
    Route::post('/pinjam', [\App\Http\Controllers\M4\PeminjamanController::class, 'store'])
        ->name('store-peminjaman');

    // 3. Daftar & Detail Peminjaman Saya
    Route::get('/peminjaman-saya', [\App\Http\Controllers\M4\PeminjamanController::class, 'index'])
        ->name('peminjaman-saya');
    Route::get('/peminjaman-saya/{id}', [\App\Http\Controllers\M4\PeminjamanController::class, 'show'])
        ->whereNumber('id')
        ->name('detail-peminjaman');

    // 4. Status & Riwayat Peminjaman Mahasiswa
    Route::get('/riwayat', [\App\Http\Controllers\M4\PeminjamanController::class, 'riwayat'])
        ->name('riwayat');
});
// END M4

// START M5
// =========================================================================
// MODUL 5: QR CODE TOKEN & VALIDASI TRANSAKSI
// Rute untuk Modul 5 disiapkan di sini oleh penanggung jawab M5.
// =========================================================================
// END M5

// START M6
// =========================================================================
// MODUL 6: MONITORING, LOG JADWAL & PELAPORAN TRANSAKSI
// Rute untuk Modul 6 disiapkan di sini oleh penanggung jawab M6.
// =========================================================================
// END M6
