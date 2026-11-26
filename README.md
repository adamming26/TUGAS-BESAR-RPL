# SIPAFKA — Sistem Informasi Peminjaman Fasilitas Kampus

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Vite](https://img.shields.io/badge/Vite-8.x-646CFF?style=for-the-badge&logo=vite&logoColor=white)](https://vitejs.dev)
[![PHPUnit](https://img.shields.io/badge/Tests-63%20Passed-brightgreen?style=for-the-badge&logo=php&logoColor=white)](https://phpunit.de)

**SIPAFKA** adalah aplikasi web terintegrasi untuk mendigitalisasi seluruh siklus peminjaman fasilitas (perangkat keras, multimedia, dan laboratorium) di lingkungan Program Studi Ilmu Komputer, Fakultas Matematika dan Ilmu Pengetahuan Alam, Universitas Halu Oleo.

---

## 📌 Daftar Isi
- [Latar Belakang & Masalah](#-latar-belakang--masalah)
- [Solusi & Fitur Utama](#-solusi--fitur-utama)
- [Peran & Modul Tim Pengembang (M1–M6)](#-peran--modul-tim-pengembang-m1m6)
- [Arsitektur & Alur Kerja Sistem](#-arsitektur--alur-kerja-sistem)
- [Spesifikasi Teknologi](#-spesifikasi-teknologi)
- [Panduan Instalasi & Menjalankan Proyek](#-panduan-instalasi--menjalankan-proyek)
- [Kredensial Akun Default (Demo)](#-kredensial-akun-default-demo)
- [Pengujian Otomatis (Automated Testing)](#-pengujian-otomatis-automated-testing)
- [Struktur Direktori Utama](#-struktur-direktori-utama)

---

## 📖 Latar Belakang & Masalah

Sebelum adanya SIPAFKA, proses peminjaman fasilitas prodi menghadapi kendala operasional:
1. **Pencatatan Manual / Tercecer:** Pengajuan surat fisik atau pesan instan berisiko kehilangan riwayat transaksi.
2. **Konflik Jadwal & Ketidakpastian Stok:** Tidak adanya sistem real-time untuk mengecek bentrok jadwal pemakaian laboratorium atau ketersediaan unit barang.
3. **Ketidakjelasan Serah Terima:** Serah terima barang tanpa bukti verifikasi terstandarisasi menimbulkan dispute saat barang hilang atau rusak.
4. **Pencatatan Kondisi Pengembalian:** Kondisi fisik aset (baik/rusak) jarang terdokumentasi terperinci per unit, mengacaukan akurasi stok siap pakai.

---

## ✨ Solusi & Fitur Utama

SIPAFKA menghadirkan solusi menyeluruh dengan menghubungkan **3 Aktor** (Mahasiswa, Dosen, dan Admin):

- **Katalog Fasilitas Terpadu:** Informasi ketersediaan stok barang dan status pemakaian laboratorium secara real-time.
- **Validasi Otomatis (Atomic Services):** Pengecekan kuota stok (`StockService`) dan pencegahan tabrakan jadwal laboratorium (`ScheduleCheckService`) sebelum permohonan tersimpan.
- **Approval Multi-Peran:** Dosen bertindak sebagai verifikator permohonan dengan opsi setuju atau tolak (wajib melampirkan alasan penolakan).
- **QR Code Dinamis 15 Menit:** Penerbitan token QR otomatis dengan enkripsi acak dan batas waktu 15 menit server-side untuk memastikan kehadiran fisik peminjam.
- **Pemindai Kamera Berbasis Web (AJAX):** Dosen dapat memindai token QR langsung melalui peramban tanpa instalasi aplikasi pihak ketiga.
- **Audit Pengembalian & Rekonsiliasi Stok:** Dosen mencatat kondisi setiap unit (Baik / Rusak Ringan / Rusak Berat). Stok otomatis hanya dipulihkan untuk unit berstatus "Baik".
- **Database Transaction Protection:** Menjamin konsistensi data transaksi serah terima dan pengembalian terhadap rollback otomatis jika terjadi kegagalan sistem.

---

## 👥 Peran & Modul Tim Pengembang (M1–M6)

Proyek ini dibangun secara modular oleh 6 anggota tim Pengembang Rekayasa Perangkat Lunak:

| Modul | Nama Pengembang | NIM | Fokus & Tanggung Jawab Teknis |
| :---: | :--- | :---: | :--- |
| **M1** | **Adam Muzakir Eni** | F1G125001 | **Core Architecture & Admin:** `RoleMiddleware`, `AdminController` (CRUD Fasilitas & Pengguna, Monitoring Transaksi), `StockService`, `ScheduleCheckService`, `NotifikasiService`, Migration & Seeder 7 tabel domain. |
| **M2** | **Marcelo Huggie Robin E.A** | F1G125038 | **Autentikasi & Profil:** `AuthController` (Login, Register, Logout), `PasswordResetController`, `ProfileController`, Keamanan (Bcrypt Hash, CSRF Protection), Landing Page & Panduan. |
| **M3** | **Cinta Aprilianti Hartono H.** | F1G125028 | **Katalog Fasilitas:** `FacilityCatalogController`, Model terpadu `Facility` (mendukung `barang` & `ruangan`), visualisasi status stok real-time dan jadwal penggunaan aktif. |
| **M4** | **Ridha Septiani K** | F1G125075 | **Peminjaman Mahasiswa:** `PeminjamanController` (Form permohonan barang & lab), validasi ketersediaan terintegrasi, visualisasi timeline status transaksi, antarmuka tampilan QR Code aktif. |
| **M5** | **Amal** | F1G125024 | **Persetujuan Dosen:** `DosenController` (Verifikasi & keputusan setuju/tolak dengan alasan), `QRCodeService` (Penerbitan token `QR-SERAH` berlaku 15 menit), model `QrToken`. |
| **M6** | **La Ode Ihwal Rafael** | F1G125037 | **QR Scanner & Serah Terima:** `QrScannerController` (Validasi AJAX kamera 5 tahap), `HandoverReturnController` (Penyerahan & Pengembalian), `ReturnAuditController`, pencatatan kondisi barang per unit & `ScheduleLog`. |

---

## 🔄 Arsitektur & Alur Kerja Sistem

```
[ Mahasiswa ]
     │
     ▼ (1. Mengajukan Pinjaman)
[ StockService / ScheduleCheckService ] ──(Otomatis Tolak jika bentrok/stok habis)
     │ (Lolos Validasi)
     ▼
[ Status: Menunggu ]
     │
     ▼ (2. Review & Putuskan)
[ Dosen ] ──┬── Tolak (Wajib isi alasan) ──► [ Status: Ditolak ]
            │
            └── Setujui ──► [ Sistem Generate QR Token (15 Menit) ] ──► [ Status: Disetujui ]
                                      │
                                      ▼ (3. Mahasiswa Tunjukkan QR Code)
                                [ Dosen Scan via Kamera ]
                                      │
                                (Validasi 5 Lapis)
                                      ▼
                                [ Konfirmasi Penyerahan ]
                                      │
                                      ├── Status: Dipinjam
                                      └── Stok Barang Dikurangi
                                      │
                                      ▼ (4. Pengembalian)
                                [ Dosen Catat Kondisi Per Unit ]
                                      │
                                      ├── Status: Selesai
                                      ├── Audit Tersimpan di ReturnRecord
                                      └── Stok Dipulihkan (Hanya Unit "Baik")
```

---

## 🛠 Spesifikasi Teknologi

- **Bahasa Pemrograman:** PHP 8.3+
- **Framework Backend:** Laravel 13.x
- **Frontend / Styling:** Blade Templating, Tailwind CSS v4, Vite 8
- **Basis Data:**
  - SQLite (Lingkungan Pengembangan / Pengujian)
  - MySQL / MariaDB (Lingkungan Produksi)
- **Pengujian:** PHPUnit 12.x
- **Kamera / Pemindai QR:** HTML5-QRCode / JavaScript Camera API

---

## 🚀 Panduan Instalasi & Menjalankan Proyek

Pastikan di lingkungan lokal telah terpasang:
- PHP >= 8.3
- Composer >= 2.x
- Node.js >= 18.x & NPM

### 1. Salin Proyek
Buka terminal dan masuk ke direktori proyek:
```bash
cd "d:/SEMS 3/REKAYASA PERANGKAT LUNAK/TUGAS-BESAR-RPL"
```

### 2. Instal Dependensi Backend
```bash
composer install
```

### 3. Konfigurasi Lingkungan (`.env`)
Salin file contoh konfigurasi dan buat application key:
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Migrasi & Pengisian Data Awal (Seeder)
Jalankan migrasi basis data beserta seeder data demo:
```bash
php artisan migrate --seed
```

### 5. Instal & Kompilasi Frontend Asset
```bash
npm install
npm run build
```
*(Gunakan `npm run dev` jika ingin menjalankan Vite hot-reload selama pengembangan)*

### 6. Jalankan Server Lokal
```bash
php artisan serve
```
Aplikasi dapat diakses melalui browser pada alamat: **`http://127.0.0.1:8000`**

---

## 🔑 Kredensial Akun Default (Demo)

Sistem telah dilengkapi dengan data seeder untuk pengujian langsung setiap role:

| Role | Email Login | Password | Deskripsi Akses |
| :--- | :--- | :---: | :--- |
| **Admin** | `admin@uho.ac.id` | `password` | Kelola master fasilitas, manajemen akun pengguna, audit seluruh transaksi |
| **Dosen** | `dosen@uho.ac.id` | `password` | Review & verifikasi peminjaman, QR Scanner serah terima, pencatatan kondisi pengembalian |
| **Mahasiswa** | `mahasiswa@uho.ac.id` | `password` | Akses katalog, pengajuan peminjaman barang/lab, tracking status, buka QR Code aktif |

---

## 🧪 Pengujian Otomatis (Automated Testing)

Proyek ini dilengkapi dengan pengujian menyeluruh (Unit & Feature Test) menggunakan PHPUnit untuk memastikan integritas logika bisnis:

Untuk menjalankan seluruh rangkaian pengujian:
```bash
php artisan test
```

### Cakupan Pengujian:
- **Autentikasi & Hak Akses:** Login, RoleMiddleware, pencegahan otorisasi silang (403 Forbidden).
- **Layanan Inti (Service Layer):** `StockService` (pengurangan & restorasi kondisi parsial), `ScheduleCheckService` (deteksi tabrakan waktu lab).
- **Siklus QR Code:** Masa kedaluwarsa 15 menit, penolakan token ganda (*replay attack*), invalidasi setelah pemakaian.
- **Konsistensi Transaksi:** Pengujian transaksi atomik rollback pada kegagalan serah terima.

Hasil Pengujian: **`63 tests passed, 0 failed`** ✅

---

## 📂 Struktur Direktori Utama

```
TUGAS-BESAR-RPL/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # 16 Controller (Auth, Admin, Dosen, Peminjaman, QR, dsb.)
│   │   └── Middleware/        # RoleMiddleware pembeda hak akses
│   ├── Models/                # 7 Model Domain (User, Facility, BorrowRequest, QrToken, dsb.)
│   └── Services/              # QRCodeService, StockService, ScheduleCheckService, NotifikasiService
├── database/
│   ├── migrations/            # Skema 7 tabel domain basis data
│   └── seeders/               # Data awal pengguna demo & fasilitas kampus
├── resources/
│   ├── css/                   # Konfigurasi Tailwind CSS v4
│   └── views/                 # Blade views responsif per modul (M1–M6)
├── routes/
│   └── web.php                # Definisi route dengan proteksi middleware
└── tests/
    └── Feature/               # 63 automated test case pengujian fitur menyeluruh
```

---

## 📄 Lisensi & Hak Cipta

Proyek ini dikembangkan untuk pemenuhan **Tugas Besar Mata Kuliah Rekayasa Perangkat Lunak (RPL)**  
Program Studi S1 Ilmu Komputer — Fakultas Matematika dan Ilmu Pengetahuan Alam  
**Universitas Halu Oleo (UHO), Kendari**  
Tahun Ajaran 2025/2026.
