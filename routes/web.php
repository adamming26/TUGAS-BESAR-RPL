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

// Halaman Publik
Route::get('/', [HomeController::class, 'index'])->name('home');
// Catatan: Rute '/fasilitas' dikelola secara lengkap pada Modul M3 (FacilityCatalogController) di bawah.

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

    // =========================================================================
    // FITUR MAHASISWA (Pengajuan Peminjaman, Peminjaman Saya & Riwayat)
    // =========================================================================
    Route::middleware('role:mahasiswa')->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        Route::get('/form-peminjaman/{jenis?}/{id?}', [\App\Http\Controllers\MahasiswaController::class, 'formPeminjaman'])->name('form-peminjaman');
        Route::post('/form-peminjaman', [\App\Http\Controllers\MahasiswaController::class, 'storePeminjaman'])->name('store-peminjaman');
        Route::post('/pinjam', [\App\Http\Controllers\MahasiswaController::class, 'storePeminjaman'])->name('pinjam');
        Route::get('/peminjaman-saya', [\App\Http\Controllers\MahasiswaController::class, 'peminjamanSaya'])->name('peminjaman-saya');
        Route::get('/peminjaman/{id}', [\App\Http\Controllers\MahasiswaController::class, 'detailPeminjaman'])->name('detail-peminjaman');
        Route::get('/peminjaman/{id}/qr', [\App\Http\Controllers\MahasiswaController::class, 'showQrCode'])->name('qr-code');
        Route::post('/peminjaman/{id}/qr', [\App\Http\Controllers\MahasiswaController::class, 'generateQr'])->name('generate-qr');
        Route::post('/peminjaman/{id}/kembalikan', [\App\Http\Controllers\MahasiswaController::class, 'ajukanPengembalian'])->name('ajukan-pengembalian');
        Route::get('/riwayat', [\App\Http\Controllers\MahasiswaController::class, 'riwayat'])->name('riwayat');
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
// =========================================================================
Route::middleware(['auth', 'role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
    // Dashboard Terpadu Dosen (Figma Page 9: Pengajuan Masuk & Detail)
    Route::get('/dashboard', [\App\Http\Controllers\DosenController::class, 'dashboard'])->name('dashboard');
    Route::get('/pengajuan-masuk', [\App\Http\Controllers\DosenController::class, 'pengajuanMasuk'])->name('pengajuan-masuk');
    Route::get('/pengajuan/{id}', [\App\Http\Controllers\DosenController::class, 'detailPengajuan'])->name('detail-pengajuan');
    Route::post('/pengajuan/{id}/putuskan', [\App\Http\Controllers\DosenController::class, 'putuskanPengajuan'])->name('putuskan-pengajuan');
    Route::get('/riwayat-keputusan', [\App\Http\Controllers\DosenController::class, 'riwayatKeputusan'])->name('riwayat-keputusan');

    // Integrasi Transaksi Penyerahan & Pengembalian Dosen
    Route::post('/pindai-qr/ajax', [\App\Http\Controllers\DosenController::class, 'validateQrAjax'])->name('validate-qr-ajax');
    Route::post('/proses-penyerahan', [\App\Http\Controllers\DosenController::class, 'prosesPenyerahan'])->name('proses-penyerahan');
    Route::post('/proses-pengembalian', [\App\Http\Controllers\DosenController::class, 'prosesPengembalian'])->name('proses-pengembalian');
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
// MODUL 6: SCANNER QR, SERAH TERIMA, KONFIRMASI PENGEMBALIAN & AUDIT
// Pemilik: M6 — Developer 4 (La Ode Ihwal Rafael / F1G125037)
// =========================================================================
Route::middleware(['auth', 'role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
    // 1. Scanner Kamera QR Code & Validasi Token Backend (Bab 3.7 Halaman 8)
    Route::get('/pindai-qr', [\App\Http\Controllers\M6\QrScannerController::class, 'index'])->name('pindai-qr');
    Route::post('/pindai-qr/validasi', [\App\Http\Controllers\M6\QrScannerController::class, 'validateToken'])->name('pindai-qr.validasi');

    // 2. Proses Serah Terima & Konfirmasi Pengembalian
    Route::post('/pindai-qr/penyerahan', [\App\Http\Controllers\M6\HandoverReturnController::class, 'handover'])->name('pindai-qr.penyerahan');
    Route::post('/pindai-qr/pengembalian', [\App\Http\Controllers\M6\HandoverReturnController::class, 'processReturn'])->name('pindai-qr.pengembalian');

    // 3. Daftar Peminjaman Aktif & Form Konfirmasi Pengembalian (Bab 3.7 Halaman 9)
    Route::get('/pengembalian', [\App\Http\Controllers\M6\HandoverReturnController::class, 'index'])->name('pengembalian.index');
    Route::get('/pengembalian/{borrowRequest}', [\App\Http\Controllers\M6\HandoverReturnController::class, 'show'])->whereNumber('borrowRequest')->name('pengembalian.show');

    // 4. Riwayat Serah Terima & Audit Pengembalian
    Route::get('/audit-pengembalian', [\App\Http\Controllers\M6\ReturnAuditController::class, 'index'])->name('audit-pengembalian.index');
    Route::get('/audit-pengembalian/{returnRecord}', [\App\Http\Controllers\M6\ReturnAuditController::class, 'show'])->whereNumber('returnRecord')->name('audit-pengembalian.show');
});
// END M6
