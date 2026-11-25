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
// MODUL 3: KATALOG FASILITAS, DETAIL & JADWAL PENGGUNAAN
// Pemilik: M3 — Developer 1 (Cinta Aprilianti Hartono H. / F1G125028)
// =========================================================================
Route::prefix('fasilitas')->name('fasilitas.')->group(function () {
    Route::get('/', [\App\Http\Controllers\M3\FacilityCatalogController::class, 'index'])->name('index');
    Route::get('/barang', [\App\Http\Controllers\M3\FacilityCatalogController::class, 'barang'])->name('barang');
    Route::get('/laboratorium', [\App\Http\Controllers\M3\FacilityCatalogController::class, 'laboratorium'])->name('laboratorium');
    Route::get('/{facility}', [\App\Http\Controllers\M3\FacilityCatalogController::class, 'show'])->whereNumber('facility')->name('show');
    Route::get('/{facility}/cek-jadwal', [\App\Http\Controllers\M3\FacilityCatalogController::class, 'checkSchedule'])->whereNumber('facility')->name('cek-jadwal');
});
// END M3

// START M4
// =========================================================================
// MODUL 4: PERSETUJUAN & KEPUTUSAN (DOSEN)
// Rute untuk Modul 4 disiapkan di sini oleh penanggung jawab M4.
// =========================================================================
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
