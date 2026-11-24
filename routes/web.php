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
// MODUL 2: MANAJEMEN FASILITAS (ADMIN)
// Rute untuk Modul 2 disiapkan di sini oleh penanggung jawab M2.
// =========================================================================
// END M2

// START M3
// =========================================================================
// MODUL 3: PEMINJAMAN FASILITAS (MAHASISWA)
// Rute untuk Modul 3 disiapkan di sini oleh penanggung jawab M3.
// =========================================================================
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
