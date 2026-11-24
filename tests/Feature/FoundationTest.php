<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Facility;
use App\Models\BorrowRequest;
use App\Models\BorrowItem;
use App\Models\QrToken;
use App\Models\ReturnRecord;
use App\Models\ScheduleLog;
use App\Http\Middleware\RoleMiddleware;
use App\Services\QRCodeService;
use App\Services\ScheduleCheckService;
use App\Services\StockService;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
use Carbon\Carbon;

class FoundationTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    /**
     * 1. Pengujian Tabel Database (Migration)
     */
    public function test_seluruh_tabel_database_fondasi_tersedia(): void
    {
        $tables = [
            'users',
            'facilities',
            'borrow_requests',
            'borrow_items',
            'qr_tokens',
            'return_records',
            'schedule_logs',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} harus ada di database.");
        }

        // Cek kolom penting di tabel users
        $this->assertTrue(Schema::hasColumns('users', ['id', 'name', 'email', 'nim_nip', 'role', 'password']));

        // Cek kolom penting di facilities
        $this->assertTrue(Schema::hasColumns('facilities', ['id', 'code', 'name', 'type', 'total_stock', 'available_stock', 'capacity']));

        // Cek kolom penting di borrow_requests
        $this->assertTrue(Schema::hasColumns('borrow_requests', ['id', 'code', 'user_id', 'type', 'borrow_date', 'start_time', 'end_time', 'status']));
    }

    /**
     * 2. Pengujian Seeder Akun Demo
     */
    public function test_seeder_akun_demo_sesuai_spesifikasi(): void
    {
        // Admin
        $admin = User::where('email', 'admin@uho.ac.id')->first();
        $this->assertNotNull($admin, 'Akun admin harus ditemukan.');
        $this->assertEquals('admin', $admin->role);
        $this->assertTrue(Hash::check('password', $admin->password));
        $this->assertTrue($admin->isAdmin());

        // Dosen
        $dosen = User::where('email', 'dosen@uho.ac.id')->first();
        $this->assertNotNull($dosen, 'Akun dosen harus ditemukan.');
        $this->assertEquals('dosen', $dosen->role);
        $this->assertEquals('198501012010121001', $dosen->nim_nip);
        $this->assertEquals('198501012010121001', $dosen->nip);
        $this->assertTrue(Hash::check('password', $dosen->password));
        $this->assertTrue($dosen->isDosen());

        // Mahasiswa
        $mahasiswa = User::where('email', 'mahasiswa@uho.ac.id')->first();
        $this->assertNotNull($mahasiswa, 'Akun mahasiswa harus ditemukan.');
        $this->assertEquals('mahasiswa', $mahasiswa->role);
        $this->assertEquals('F1G125001', $mahasiswa->nim_nip);
        $this->assertEquals('F1G125001', $mahasiswa->nim);
        $this->assertTrue(Hash::check('password', $mahasiswa->password));
        $this->assertTrue($mahasiswa->isMahasiswa());
    }

    /**
     * 3. Pengujian Data Fasilitas Awal
     */
    public function test_data_fasilitas_awal_sesuai_spesifikasi(): void
    {
        // Barang
        $proyektor = Facility::where('name', 'Proyektor')->first();
        $this->assertNotNull($proyektor);
        $this->assertEquals(2, $proyektor->total_stock);
        $this->assertEquals('barang', $proyektor->type);

        $hdmi = Facility::where('name', 'Kabel HDMI')->first();
        $this->assertNotNull($hdmi);
        $this->assertEquals(2, $hdmi->total_stock);

        $terminal = Facility::where('name', 'Kabel Terminal Cabang')->first();
        $this->assertNotNull($terminal);
        $this->assertEquals(5, $terminal->total_stock);

        // Lab
        $labBasdat = Facility::where('name', 'Lab Komputer Basis Data')->first();
        $this->assertNotNull($labBasdat);
        $this->assertEquals(50, $labBasdat->capacity);
        $this->assertTrue($labBasdat->isRuangan());
        $this->assertTrue($labBasdat->isLab());

        $labTIK = Facility::where('name', 'Lab Komputer Unit Teknologi Informasi')->first();
        $this->assertNotNull($labTIK);
        $this->assertEquals(50, $labTIK->capacity);
    }

    /**
     * 4. Pengujian Relasi Eloquent Antar Model
     */
    public function test_relasi_eloquent_antar_model(): void
    {
        $mahasiswa = User::where('role', 'mahasiswa')->first();
        $dosen = User::where('role', 'dosen')->first();
        $facility = Facility::where('code', 'BRG-01')->first();

        // Buat BorrowRequest baru untuk menguji relasi
        $request = BorrowRequest::create([
            'code' => 'REQ-TEST-M1',
            'user_id' => $mahasiswa->id,
            'type' => 'barang',
            'facility_id' => $facility->id,
            'borrow_date' => Carbon::now()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'duration_hours' => 2,
            'purpose' => 'Uji Relasi Model M1',
            'status' => 'disetujui',
            'approved_by' => $dosen->id,
        ]);

        $item = BorrowItem::create([
            'borrow_request_id' => $request->id,
            'facility_id' => $facility->id,
            'quantity' => 1,
        ]);

        $qr = QrToken::create([
            'borrow_request_id' => $request->id,
            'token' => 'QR-TEST-M1-TOKEN',
            'type' => 'penyerahan',
            'expires_at' => Carbon::now()->addMinutes(15),
            'is_used' => false,
        ]);

        $return = ReturnRecord::create([
            'borrow_request_id' => $request->id,
            'lecturer_id' => $dosen->id,
            'return_date' => Carbon::now(),
            'returned_quantity' => 1,
            'condition' => 'Baik',
        ]);

        $schedule = ScheduleLog::create([
            'facility_id' => $facility->id,
            'borrow_request_id' => $request->id,
            'date' => Carbon::now()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'status' => 'terjadwal',
        ]);

        // Assert Relasi
        $this->assertEquals($mahasiswa->id, $request->user->id);
        $this->assertEquals($dosen->id, $request->approver->id);
        $this->assertEquals($facility->id, $request->facility->id);
        $this->assertCount(1, $request->items);
        $this->assertCount(1, $request->borrowItems);
        $this->assertCount(1, $request->qrTokens);
        $this->assertNotNull($request->returnRecord);
        $this->assertCount(1, $request->scheduleLogs);

        // Relasi balik
        $this->assertEquals($request->id, $item->borrowRequest->id);
        $this->assertEquals($facility->id, $item->facility->id);
        $this->assertEquals($request->id, $qr->borrowRequest->id);
        $this->assertEquals($request->id, $return->borrowRequest->id);
        $this->assertEquals($dosen->id, $return->lecturer->id);
        $this->assertEquals($dosen->id, $return->verifiedBy->id);
        $this->assertEquals($request->id, $schedule->borrowRequest->id);
    }

    /**
     * 5. Pengujian RoleMiddleware
     */
    public function test_role_middleware_membatasi_akses(): void
    {
        $middleware = new RoleMiddleware();

        $admin = User::where('role', 'admin')->first();
        $mahasiswa = User::where('role', 'mahasiswa')->first();

        // 1. Unauthenticated request
        $guestRequest = Request::create('/test-admin', 'GET');
        $response = $middleware->handle($guestRequest, function () {
            return response('OK');
        }, 'admin');
        $this->assertEquals(302, $response->getStatusCode());

        // 2. Authenticated as Mahasiswa trying to access 'admin'
        $this->actingAs($mahasiswa);
        $forbiddenRequest = Request::create('/test-admin', 'GET');
        $forbiddenRequest->headers->set('Accept', 'application/json');
        $forbiddenResponse = $middleware->handle($forbiddenRequest, function () {
            return response('OK');
        }, 'admin');
        $this->assertEquals(403, $forbiddenResponse->getStatusCode());

        // 3. Authenticated as Admin accessing 'admin'
        $this->actingAs($admin);
        $allowedRequest = Request::create('/test-admin', 'GET');
        $allowedResponse = $middleware->handle($allowedRequest, function () {
            return response('OK');
        }, 'admin');
        $this->assertEquals(200, $allowedResponse->getStatusCode());
    }

    /**
     * 6. Pengujian Kontrak Method Service Bersama
     */
    public function test_kontrak_schedule_check_service(): void
    {
        $lab = Facility::where('code', 'LAB-01')->first();
        $date = Carbon::now()->addDays(10)->toDateString();

        // Saat tidak ada jadwal, ruangan harus available
        $isAvailable = ScheduleCheckService::isAvailable($lab->id, $date, '08:00:00', '10:00:00');
        $this->assertTrue($isAvailable);

        // Buat peminjaman aktif
        $mahasiswa = User::where('role', 'mahasiswa')->first();
        $request = BorrowRequest::create([
            'code' => 'REQ-SCHEDULE-TEST',
            'user_id' => $mahasiswa->id,
            'type' => 'ruangan',
            'facility_id' => $lab->id,
            'borrow_date' => $date,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'duration_hours' => 2,
            'purpose' => 'Praktikum',
            'status' => 'disetujui',
        ]);

        // Cek bentrok waktu yang sama persis
        $conflict = ScheduleCheckService::isAvailable($lab->id, $date, '08:00:00', '10:00:00');
        $this->assertFalse($conflict, 'Harus mendeteksi bentrok jadwal.');

        // Cek bentrok overlap sebagian (09:00 - 11:00)
        $overlap = ScheduleCheckService::isAvailable($lab->id, $date, '09:00:00', '11:00:00');
        $this->assertFalse($overlap, 'Harus mendeteksi overlap jadwal.');

        // Waktu setelahnya tidak boleh bentrok (10:00 - 12:00)
        $freeTime = ScheduleCheckService::isAvailable($lab->id, $date, '10:00:00', '12:00:00');
        $this->assertTrue($freeTime, 'Waktu setelah jadwal selesai harus tersedia.');
    }

    public function test_kontrak_stock_service(): void
    {
        $facility = Facility::where('code', 'BRG-01')->first();
        $initialStock = $facility->available_stock;

        // 1. Kurangi stok berhasil
        $reduceSuccess = StockService::reduce($facility->id, 1);
        $this->assertTrue($reduceSuccess);
        $facility->refresh();
        $this->assertEquals($initialStock - 1, $facility->available_stock);

        // 2. Kurangi stok melebihi batas yang tersedia -> gagal
        $reduceFail = StockService::reduce($facility->id, 999);
        $this->assertFalse($reduceFail);

        // 3. Kembalikan stok dalam kondisi baik
        StockService::restore($facility->id, 1);
        $facility->refresh();
        $this->assertEquals($initialStock, $facility->available_stock);
    }

    public function test_kontrak_notifikasi_service(): void
    {
        $admin = User::where('role', 'admin')->first();

        // Tidak boleh melempar error dan harus mencatat ke log
        NotifikasiService::kirim(
            $admin,
            'Peminjaman Baru',
            'Ada pengajuan peminjaman baru yang perlu diperiksa.',
            '/admin/transaksi'
        );

        $this->assertTrue(true, 'NotifikasiService::kirim dieksekusi tanpa exception.');
    }

    public function test_kontrak_qr_code_service(): void
    {
        $qrService = new QRCodeService();
        $request = BorrowRequest::first();

        $qrToken = $qrService->generateToken($request, 'penyerahan');
        $this->assertNotNull($qrToken);
        $this->assertStringStartsWith('QR-SERAH-', $qrToken->token);
        $this->assertFalse($qrToken->is_used);

        // Masa berlaku 15 menit
        $this->assertTrue($qrToken->expires_at->isFuture());
        $remainingSeconds = $qrService->getRemainingSeconds($qrToken);
        $this->assertGreaterThan(800, $remainingSeconds);
        $this->assertLessThanOrEqual(900, $remainingSeconds);

        // Validasi token
        $validation = $qrService->validateToken($qrToken->token);
        $this->assertTrue($validation['valid']);
        $this->assertEquals('valid', $validation['status']);

        // Tandai digunakan
        $marked = $qrService->markAsUsed($qrToken);
        $this->assertTrue($marked);
        $qrToken->refresh();
        $this->assertTrue($qrToken->is_used);

        // Validasi setelah digunakan -> already_used
        $validationAfter = $qrService->validateToken($qrToken->token);
        $this->assertFalse($validationAfter['valid']);
        $this->assertEquals('already_used', $validationAfter['status']);
    }

    /**
     * 7. Pengujian Komponen UI Bersama
     */
    public function test_blade_komponen_ui_bersama_berhasil_dirender(): void
    {
        // 1. Button
        $btnHtml = Blade::render('<x-button variant="primary">Klik Saya</x-button>');
        $this->assertStringContainsString('Klik Saya', $btnHtml);
        $this->assertStringContainsString('#2563EB', $btnHtml);

        // 2. Alert
        $alertHtml = Blade::render('<x-alert type="success">Operasi Berhasil</x-alert>');
        $this->assertStringContainsString('Operasi Berhasil', $alertHtml);

        // 3. Badge
        $badgeHtml = Blade::render('<x-badge status="disetujui">Disetujui</x-badge>');
        $this->assertStringContainsString('Disetujui', $badgeHtml);

        // 4. Card
        $cardHtml = Blade::render('<x-card title="Judul Card">Konten Card</x-card>');
        $this->assertStringContainsString('Judul Card', $cardHtml);
        $this->assertStringContainsString('Konten Card', $cardHtml);

        // 5. Input
        $inputHtml = Blade::render('<x-input label="Nama Lengkap" name="nama" placeholder="Masukkan nama" />');
        $this->assertStringContainsString('Nama Lengkap', $inputHtml);
        $this->assertStringContainsString('name="nama"', $inputHtml);

        // 6. Breadcrumb
        $breadcrumbHtml = Blade::render('<x-breadcrumb :items="[[\'label\' => \'Fasilitas\']]" />');
        $this->assertStringContainsString('Beranda', $breadcrumbHtml);
        $this->assertStringContainsString('Fasilitas', $breadcrumbHtml);
    }
}
