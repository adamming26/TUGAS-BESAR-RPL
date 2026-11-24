<?php

namespace Database\Seeders;

use App\Models\BorrowItem;
use App\Models\BorrowRequest;
use App\Models\Facility;
use App\Models\QrToken;
use App\Models\ReturnRecord;
use App\Models\ScheduleLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            FacilitySeeder::class,
        ]);

        // Tambahan data demo peminjaman agar tampilan Figma langsung hidup dan terhubung data nyata
        $mahasiswa = User::where('role', 'mahasiswa')->first();
        $dosen = User::where('role', 'dosen')->first();
        $admin = User::where('role', 'admin')->first();

        $proyektor = Facility::where('code', 'BRG-01')->first();
        $hdmi = Facility::where('code', 'BRG-02')->first();
        $terminal = Facility::where('code', 'BRG-03')->first();
        $lab1 = Facility::where('code', 'LAB-01')->first();
        $lab2 = Facility::where('code', 'LAB-02')->first();

        if ($mahasiswa && $dosen && $proyektor && $hdmi && $terminal && $lab1) {
            // 1. Peminjaman Disetujui dengan QR Code aktif (Penyerahan)
            $req1 = BorrowRequest::create([
                'code' => 'REQ-20261124-001',
                'user_id' => $mahasiswa->id,
                'type' => 'barang',
                'facility_id' => $hdmi->id,
                'borrow_date' => now()->toDateString(),
                'start_time' => '08:00:00',
                'end_time' => '10:00:00',
                'duration_hours' => 2,
                'purpose' => 'Presentasi Tugas Akhir Rekayasa Perangkat Lunak',
                'participant_count' => 1,
                'status' => 'disetujui',
                'approved_by' => $dosen->id,
            ]);

            BorrowItem::create([
                'borrow_request_id' => $req1->id,
                'facility_id' => $hdmi->id,
                'quantity' => 1,
            ]);

            QrToken::create([
                'borrow_request_id' => $req1->id,
                'token' => 'QR-PENYERAHAN-' . Str::upper(Str::random(10)),
                'type' => 'penyerahan',
                'expires_at' => now()->addMinutes(15),
                'is_used' => false,
            ]);

            // 2. Peminjaman Dipinjam dengan QR Code aktif (Pengembalian)
            $req2 = BorrowRequest::create([
                'code' => 'REQ-20261124-002',
                'user_id' => $mahasiswa->id,
                'type' => 'barang',
                'facility_id' => $terminal->id,
                'borrow_date' => now()->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
                'duration_hours' => 3,
                'purpose' => 'Kegiatan Praktikum Pemrograman Web',
                'participant_count' => 1,
                'status' => 'dipinjam',
                'approved_by' => $dosen->id,
            ]);

            BorrowItem::create([
                'borrow_request_id' => $req2->id,
                'facility_id' => $terminal->id,
                'quantity' => 1,
            ]);

            QrToken::create([
                'borrow_request_id' => $req2->id,
                'token' => 'QR-PENGEMBALIAN-' . Str::upper(Str::random(10)),
                'type' => 'pengembalian',
                'expires_at' => now()->addMinutes(15),
                'is_used' => false,
            ]);

            // 3. Peminjaman Menunggu Persetujuan
            $req3 = BorrowRequest::create([
                'code' => 'REQ-20261124-003',
                'user_id' => $mahasiswa->id,
                'type' => 'barang',
                'facility_id' => $proyektor->id,
                'borrow_date' => now()->addDay()->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'duration_hours' => 2,
                'purpose' => 'Seminar Proposal Skripsi Mahasiswa',
                'participant_count' => 1,
                'status' => 'menunggu',
            ]);

            BorrowItem::create([
                'borrow_request_id' => $req3->id,
                'facility_id' => $proyektor->id,
                'quantity' => 1,
            ]);

            // 4. Peminjaman Lab Menunggu Izin Dosen
            $req4 = BorrowRequest::create([
                'code' => 'REQ-20261124-004',
                'user_id' => $mahasiswa->id,
                'type' => 'ruangan',
                'facility_id' => $lab1->id,
                'borrow_date' => now()->addDays(2)->toDateString(),
                'start_time' => '13:00:00',
                'end_time' => '16:00:00',
                'duration_hours' => 3,
                'purpose' => 'Pelatihan Basis Data Lanjut Himpunan Mahasiswa',
                'participant_count' => 45,
                'status' => 'menunggu',
            ]);

            ScheduleLog::create([
                'facility_id' => $lab1->id,
                'borrow_request_id' => $req4->id,
                'date' => now()->addDays(2)->toDateString(),
                'start_time' => '13:00:00',
                'end_time' => '16:00:00',
                'status' => 'terjadwal',
            ]);

            // 5. Peminjaman Selesai / Dikembalikan
            $req5 = BorrowRequest::create([
                'code' => 'REQ-20261124-005',
                'user_id' => $mahasiswa->id,
                'type' => 'barang',
                'facility_id' => $proyektor->id,
                'borrow_date' => now()->subDays(2)->toDateString(),
                'start_time' => '08:00:00',
                'end_time' => '11:00:00',
                'duration_hours' => 3,
                'purpose' => 'Perkuliahan Rekayasa Perangkat Lunak',
                'participant_count' => 1,
                'status' => 'dikembalikan',
                'approved_by' => $dosen->id,
            ]);

            ReturnRecord::create([
                'borrow_request_id' => $req5->id,
                'lecturer_id' => $dosen->id,
                'return_date' => now()->subDays(2)->setHour(11)->setMinute(15),
                'returned_quantity' => 1,
                'condition' => 'Baik',
                'notes' => 'Fasilitas dikembalikan lengkap dan dalam kondisi prima.',
            ]);

            // 6. Peminjaman Ditolak
            BorrowRequest::create([
                'code' => 'REQ-20261124-006',
                'user_id' => $mahasiswa->id,
                'type' => 'barang',
                'facility_id' => $hdmi->id,
                'borrow_date' => now()->subDay()->toDateString(),
                'start_time' => '13:00:00',
                'end_time' => '15:00:00',
                'duration_hours' => 2,
                'purpose' => 'Kegiatan pribadi di luar kampus',
                'participant_count' => 1,
                'status' => 'ditolak',
                'reject_reason' => 'Peminjaman inventaris prodi hanya diperkenankan untuk keperluan akademik kampus.',
                'approved_by' => $dosen->id,
            ]);
        }
    }
}
