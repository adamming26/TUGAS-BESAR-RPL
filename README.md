# SIPAFKA — Sistem Informasi Peminjaman Fasilitas Kampus

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-%5E8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Vite](https://img.shields.io/badge/Vite-8.x-646CFF?style=for-the-badge&logo=vite&logoColor=white)](https://vite.dev)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-12.x-3C9CD7?style=for-the-badge&logo=php&logoColor=white)](https://phpunit.de)

**SIPAFKA** adalah aplikasi web berbasis Laravel untuk mengelola dan mendigitalisasi seluruh alur peminjaman fasilitas kampus (peralatan/barang inventaris dan ruangan laboratorium) di lingkungan **Program Studi Ilmu Komputer, Fakultas Matematika dan Ilmu Pengetahuan Alam (FMIPA), Universitas Halu Oleo**.

---

## Daftar Isi

- [Latar Belakang & Tujuan](#-latar-belakang--tujuan)
- [Fitur Utama per Peran](#-fitur-utama-per-peran)
- [Tim Pengembang & Pembagian Peran](#-tim-pengembang--pembagian-peran)
- [Alur Kerja Sistem](#-alur-kerja-sistem)
- [Skema Basis Data](#-skema-basis-data)
- [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
- [Panduan Instalasi & Menjalankan Proyek](#-panduan-instalasi--menjalankan-proyek)
- [Akun Demo & Data Awal](#-akun-demo--data-awal)
- [Pengujian (Testing)](#-pengujian-testing)
- [Struktur Direktori](#-struktur-direktori)

---

## Latar Belakang & Tujuan

Pengelolaan peminjaman fasilitas akademik seperti proyektor, kabel pendukung, maupun ruang laboratorium secara manual rentan terhadap pencatatan yang tercecer, bentrok jadwal penggunaan ruangan, ketidaksesuaian stok tersedia, serta kurangnya bukti serah terima dan pencatatan kondisi barang saat pengembalian.

SIPAFKA dibangun untuk menjawab kebutuhan tersebut melalui:
1. **Sentralisasi Katalog & Jadwal** — Menampilkan stok barang dan jadwal penggunaan laboratorium secara terstruktur.
2. **Validasi Otomatis** — Mencegah pengajuan melebihi stok (`StockService`) dan mencegah bentrok waktu penggunaan ruangan (`ScheduleCheckService`).
3. **Verifikasi QR Code Terbatas Waktu** — Menggunakan token QR dengan masa berlaku **15 menit** (`QRCodeService`) untuk memvalidasi serah terima dan pengembalian secara langsung.
4. **Audit Kondisi Pengembalian** — Mencatat kondisi aset yang dikembalikan (`Baik`, `Rusak Ringan`, `Rusak Berat`) pada tabel `return_records` dan memulihkan stok secara selektif.

---

## Fitur Utama per Peran

Sistem menerapkan kontrol akses berbasis peran melalui `RoleMiddleware` (`admin`, `dosen`, `mahasiswa`):

### 1. Halaman Publik & Autentikasi
- **Landing Page & Panduan:** Ringkasan statistik ketersediaan laboratorium dan barang serta halaman panduan penggunaan (`/`, `/panduan`).
- **Katalog Fasilitas:** Daftar fasilitas keseluruhan, filter barang (`/fasilitas/barang`), filter laboratorium (`/fasilitas/laboratorium`), detail fasilitas, serta pengecekan jadwal per tanggal (`/fasilitas/{facility}/cek-jadwal`).
- **Autentikasi & Profil:** Login menggunakan Email atau NIM/NIP, registrasi akun mahasiswa, lupa/reset kata sandi, serta pembaruan profil dan kata sandi.

### 2. Mahasiswa (`role:mahasiswa`)
- **Dashboard Mahasiswa:** Ringkasan status pengajuan (`menunggu`, `disetujui`, `dipinjam`, `selesai`, `ditolak`), pengajuan terbaru, serta akses cepat ke fasilitas aktif.
- **Pengajuan Peminjaman:** Form pengajuan peminjaman barang dan laboratorium dengan validasi ketersediaan stok dan pengecekan bentrok jadwal.
- **Tracking & QR Code:** Melihat daftar peminjaman saya, detail transaksi, riwayat peminjaman, menampilkan/membuat QR Code (`QR-SERAH-*` / `QR-KEMBALI-*`), serta mengajukan pengembalian.

### 3. Dosen (`role:dosen`)
- **Dashboard & Keputusan Pengajuan:** Meninjau daftar pengajuan masuk, melihat detail pengajuan, menyetujui atau menolak pengajuan (dengan alasan penolakan), serta melihat riwayat keputusan.
- **Pemindai QR Code (Web Camera & Input Manual):** Memindai QR Code melalui kamera peramban (`html5-qrcode.min.js`) atau input token manual dengan validasi backend berbasis AJAX.
- **Serah Terima & Konfirmasi Pengembalian:** Memproses serah terima fasilitas (mengurangi stok barang dan mengubah status menjadi `dipinjam`) serta mengonfirmasi pengembalian dengan pencatatan jumlah kembali, kondisi, dan catatan audit.
- **Audit Pengembalian:** Riwayat serah terima dan hasil pemeriksaan pengembalian lengkap dengan filter kondisi, jenis fasilitas, rentang tanggal, dan pencarian.

### 4. Admin (`role:admin`)
- **Dashboard Admin:** Statistik pengguna (`admin`, `dosen`, `mahasiswa`), ringkasan fasilitas barang dan laboratorium, kondisi aset, serta pemantauan aktivitas sistem.
- **Manajemen Fasilitas (CRUD):** Tambah, ubah, dan hapus data barang maupun ruangan/laboratorium beserta stok, kapasitas, kondisi, dan status aktif.
- **Manajemen Pengguna (CRUD & Role):** Pengelolaan akun civitas akademika serta pengaturan peran pengguna.
- **Monitoring Transaksi:** Memantau transaksi berjalan dan riwayat seluruh transaksi peminjaman.

---

## Tim Pengembang & Pembagian Peran

Proyek Tugas Besar Mata Kuliah Rekayasa Perangkat Lunak (RPL) ini dikerjakan secara kolaboratif dengan pembagian peran tim dan tanggung jawab teknis sebagai berikut:

| Nama Anggota | NIM | Peran Tim (Scrum) | Modul & Fokus Pengembangan |
| :--- | :---: | :--- | :--- |
| **Amal** | `F1G125024` | **Product Owner (PO) & Penyusun README** | Penyusunan dokumen README & kebutuhan produk, serta kontribusi alur persetujuan & QR Token (`M5`). |
| **Adam Muzakir Eni** | `F1G125001` | **Scrum Master** | **Modul 1 (Foundation & Admin):** `RoleMiddleware`, `AdminController`, `StockService`, `ScheduleCheckService`, `NotifikasiService`, migrasi & seeder basis data. |
| **Marcelo Huggie Robin E.A.** | `F1G125038` | **Development 1** | **Modul 2 (Landing, Auth & Profil):** `LandingController`, `M2\AuthController`, `PasswordResetController`, `ProfileController`, halaman landing & panduan. |
| **Cinta Aprilianti Hartono H.** | `F1G125028` | **Development 2** | **Modul 3 (Katalog Fasilitas):** `FacilityCatalogController`, halaman katalog barang & laboratorium, detail fasilitas, dan pengecekan jadwal penggunaan. |
| **Ridha Septiani K** | `F1G125075` | **Development 3** | **Modul 4 (Peminjaman Mahasiswa):** `M4\DashboardController`, `PeminjamanController`, form peminjaman barang/lab, tracking status & tampilan QR Code mahasiswa. |
| **La Ode Ihwal Rafael** | `F1G125037` | **Development 4** | **Modul 6 (Scanner QR & Audit):** `QrScannerController`, `HandoverReturnController`, `ReturnAuditController`, validasi kamera QR, serah terima, pengembalian & audit. |

---

## Alur Kerja Sistem

```text
[Mahasiswa] Mengajukan Peminjaman (Barang / Lab)
     │
     ▼  Validasi StockService / ScheduleCheckService
[Status: menunggu]
     │
     ▼  Review oleh Dosen
[Dosen] ──┬── Tolak (disertai reject_reason) ──► [Status: ditolak]
          │
          └── Setujui ──► Generate QR Token Penyerahan (berlaku 15 menit)
                                │
                                ▼  [Status: disetujui]
                          Dosen Memindai QR Mahasiswa
                                │
                                ▼  Validasi token (ada, belum dipakai, belum kedaluwarsa)
                          Konfirmasi Penyerahan
                                │
                                ├──► Status: dipinjam
                                └──► Stok barang dikurangi (StockService::reduce)
                                │
                                ▼  Saat Pengembalian
                          Dosen Memeriksa & Mencatat ReturnRecord
                                │
                                ├──► Status: dikembalikan / selesai
                                └──► Stok dipulihkan untuk unit kondisi Baik (StockService::restore)
```

---

## Skema Basis Data

Aplikasi menggunakan skema migrasi Laravel di `database/migrations/` dengan tabel domain utama sebagai berikut:

| Tabel | Model | Fungsi Utama |
| :--- | :--- | :--- |
| `users` | `User` | Data akun pengguna (`admin`, `dosen`, `mahasiswa`), `nim_nip`, kontak, dan kredensial. |
| `facilities` | `Facility` | Master data fasilitas (`type`: `barang` / `ruangan`), stok total, stok tersedia, kapasitas, kondisi, status aktif. |
| `borrow_requests` | `BorrowRequest` | Pengajuan peminjaman, jadwal (`borrow_date`, `start_time`, `end_time`), tujuan, status, dan persetujuan dosen. |
| `borrow_items` | `BorrowItem` | Detail item dan kuantitas barang yang dipinjam dalam satu transaksi. |
| `qr_tokens` | `QrToken` | Token QR dinamis (`penyerahan` / `pengembalian`), masa berlaku (`expires_at`), dan status pemakaian (`is_used`). |
| `return_records` | `ReturnRecord` | Catatan audit pengembalian oleh dosen (`return_date`, `returned_quantity`, `condition`, `notes`). |
| `schedule_logs` | `ScheduleLog` | Log penjadwalan fasilitas/ruangan (`terjadwal`, `berlangsung`, `selesai`, `dibatalkan`). |

---

## Teknologi yang Digunakan

- **Backend:** PHP `^8.3`, Laravel Framework `^13.17`
- **Frontend:** Blade Templates, Tailwind CSS `^4.0`, Vite `^8.0` (`laravel-vite-plugin`, `@tailwindcss/vite`)
- **Database:** MySQL (konfigurasi default pada `.env.example`) & SQLite `:memory:` (konfigurasi pengujian pada `phpunit.xml`)
- **QR Code Client Library:** `public/js/qrcode.min.js` & `public/js/html5-qrcode.min.js`
- **Testing & Tooling:** PHPUnit `^12.5.12`, Laravel Pint, Laravel Pail, Mockery, Faker

---

## Panduan Instalasi & Menjalankan Proyek

### Prasyarat
- **PHP** >= 8.3 (beserta ekstensi standar Laravel seperti `pdo_mysql` / `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`)
- **Composer** >= 2.x
- **Node.js** & **NPM**
- **MySQL / MariaDB** (atau SQLite jika digunakan secara lokal)

### Langkah Instalasi

1. **Masuk ke direktori proyek**
   ```bash
   cd TUGAS-BESAR-RPL
   ```

2. **Instal dependensi PHP**
   ```bash
   composer install
   ```

3. **Konfigurasi file `.env`**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Sesuaikan konfigurasi database pada `.env` (default di `.env.example` menggunakan MySQL dengan nama database `sipafka`):
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sipafka
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Jalankan migrasi dan seeder**
   ```bash
   php artisan migrate --seed
   ```

5. **Instal dependensi frontend dan build aset**
   ```bash
   npm install
   npm run build
   ```
   *(Atau jalankan `npm run dev` selama mode pengembangan)*

6. **Jalankan server pengembangan lokal**
   ```bash
   php artisan serve
   ```
   Akses aplikasi melalui browser pada `http://127.0.0.1:8000` (atau sesuai `APP_URL`).

---

## Akun Demo & Data Awal

Seeder bawaan (`UserSeeder` dan `FacilitySeeder` via `DatabaseSeeder`) menyediakan akun uji dan data awal fasilitas:

### Kredensial Akun (`UserSeeder`)

| Role | Email | NIM / NIP | Password |
| :--- | :--- | :--- | :---: |
| **Admin** | `admin@uho.ac.id` | `ADM-ILKOM-01` | `password` |
| **Dosen** | `dosen@uho.ac.id` | `198501012010121001` | `password` |
| **Mahasiswa** | `mahasiswa@uho.ac.id` | `F1G125001` | `password` |

### Data Fasilitas Awal (`FacilitySeeder`)
- **Barang:** `BRG-01` (Proyektor), `BRG-02` (Kabel HDMI), `BRG-03` (Kabel Terminal Cabang)
- **Laboratorium:** `LAB-01` (Lab Komputer Basis Data), `LAB-02` (Lab Komputer Unit Teknologi Informasi)

---

## Pengujian (Testing)

Pengujian otomatis dikonfigurasi menggunakan SQLite in-memory pada `phpunit.xml`. Setelah dependensi Composer terpasang (`composer install`), jalankan:

```bash
php artisan test
```
atau melalui script Composer:
```bash
composer test
```

### Daftar Berkas Pengujian
- `tests/Feature/FoundationTest.php` — Pengujian fondasi sistem, model, dan service inti
- `tests/Feature/AdminTest.php` — Pengujian fitur manajemen admin (fasilitas, pengguna, transaksi)
- `tests/Feature/M2AuthProfileLandingTest.php` — Pengujian landing page, autentikasi, reset password, dan profil
- `tests/Feature/M3FacilityCatalogTest.php` — Pengujian katalog fasilitas dan pengecekan jadwal
- `tests/Feature/M4PeminjamanMahasiswaTest.php` — Pengujian dashboard dan pengajuan peminjaman mahasiswa
- `tests/Feature/M6ScannerReturnAuditTest.php` — Pengujian pemindai QR, serah terima, pengembalian, dan audit
- `tests/Feature/SipafkaWorkflowTest.php` — Pengujian alur kerja end-to-end SIPAFKA

---

## Struktur Direktori

```text
TUGAS-BESAR-RPL/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── M2/                 # AuthController, LandingController, PasswordResetController, ProfileController
│   │   │   ├── M3/                 # FacilityCatalogController
│   │   │   ├── M4/                 # DashboardController, PeminjamanController
│   │   │   ├── M6/                 # HandoverReturnController, QrScannerController, ReturnAuditController
│   │   │   ├── AdminController.php
│   │   │   ├── AuthController.php
│   │   │   ├── DosenController.php
│   │   │   ├── HomeController.php
│   │   │   └── MahasiswaController.php
│   │   └── Middleware/
│   │       └── RoleMiddleware.php
│   ├── Models/                     # User, Facility, BorrowRequest, BorrowItem, QrToken, ReturnRecord, ScheduleLog
│   └── Services/                   # QRCodeService, StockService, ScheduleCheckService, NotifikasiService
├── database/
│   ├── migrations/                 # Migrasi tabel sistem & 7 domain SIPAFKA
│   └── seeders/                    # DatabaseSeeder, UserSeeder, FacilitySeeder
├── public/
│   ├── images/                     # Aset gambar kampus
│   └── js/                         # html5-qrcode.min.js, qrcode.min.js
├── resources/
│   ├── css/app.css                 # Konfigurasi Tailwind CSS v4 & token desain
│   ├── js/app.js
│   └── views/                      # Blade templates (admin, auth, components, dosen, fasilitas, layouts, m3, mahasiswa, panduan, partials, profile)
├── routes/web.php                  # Definisi seluruh rute aplikasi
├── tests/                          # Unit & Feature tests
├── .env.example
├── composer.json
├── package.json
└── phpunit.xml
```

---

## Lisensi & Informasi Akademik

Dikembangkan untuk memenuhi **Tugas Besar Mata Kuliah Rekayasa Perangkat Lunak (RPL)**  
Program Studi S1 Ilmu Komputer, FMIPA — **Universitas Halu Oleo**, Kendari.
