<?php

namespace Tests\Feature;

use App\Models\BorrowItem;
use App\Models\BorrowRequest;
use App\Models\Facility;
use App\Models\QrToken;
use App\Models\ReturnRecord;
use App\Models\ScheduleLog;
use App\Models\User;
use App\Services\QRCodeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class M6ScannerReturnAuditTest extends TestCase
{
    use DatabaseMigrations;

    protected User $dosen;
    protected User $mahasiswa;
    protected User $admin;
    protected Facility $proyektor;
    protected Facility $kabelTerminal;
    protected Facility $labBasdat;
    protected QRCodeService $qrCodeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        $this->dosen = User::where('role', 'dosen')->firstOrFail();
        $this->mahasiswa = User::where('role', 'mahasiswa')->firstOrFail();
        $this->admin = User::where('role', 'admin')->firstOrFail();
        $this->proyektor = Facility::where('code', 'BRG-01')->firstOrFail();
        $this->kabelTerminal = Facility::where('code', 'BRG-03')->firstOrFail();
        $this->labBasdat = Facility::where('code', 'LAB-01')->firstOrFail();
        $this->qrCodeService = new QRCodeService();
    }

    /**
     * Helper membuat pengajuan barang untuk pengujian M6.
     */
    private function createBarangBorrowRequest(
        Facility $facility,
        int $quantity = 2,
        string $status = 'disetujui',
        ?string $code = null
    ): BorrowRequest {
        $borrowRequest = BorrowRequest::create([
            'code' => $code ?? 'REQ-M6-' . strtoupper(uniqid()),
            'user_id' => $this->mahasiswa->id,
            'type' => 'barang',
            'facility_id' => $facility->id,
            'borrow_date' => Carbon::now()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'duration_hours' => 2,
            'purpose' => 'Pengujian M6 Peminjaman Barang',
            'participant_count' => 1,
            'status' => $status,
            'approved_by' => $this->dosen->id,
        ]);

        BorrowItem::create([
            'borrow_request_id' => $borrowRequest->id,
            'facility_id' => $facility->id,
            'quantity' => $quantity,
        ]);

        return $borrowRequest;
    }

    /**
     * Helper membuat pengajuan laboratorium beserta log jadwal untuk pengujian M6.
     */
    private function createLabBorrowRequest(string $status = 'disetujui'): BorrowRequest
    {
        $borrowRequest = BorrowRequest::create([
            'code' => 'REQ-LAB-M6-' . strtoupper(uniqid()),
            'user_id' => $this->mahasiswa->id,
            'type' => 'ruangan',
            'facility_id' => $this->labBasdat->id,
            'borrow_date' => Carbon::now()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'duration_hours' => 2,
            'purpose' => 'Praktikum Sistem Operasi FMIPA UHO',
            'participant_count' => 25,
            'status' => $status,
            'approved_by' => $this->dosen->id,
        ]);

        ScheduleLog::create([
            'facility_id' => $this->labBasdat->id,
            'borrow_request_id' => $borrowRequest->id,
            'date' => $borrowRequest->borrow_date,
            'start_time' => $borrowRequest->start_time,
            'end_time' => $borrowRequest->end_time,
            'status' => $status === 'dipinjam' ? 'berlangsung' : 'terjadwal',
        ]);

        return $borrowRequest;
    }

    public function test_pembatasan_akses_berdasarkan_role_pada_seluruh_endpoint_m6(): void
    {
        $borrowRequest = $this->createBarangBorrowRequest($this->proyektor, 1, 'dipinjam');
        $returnRecord = ReturnRecord::create([
            'borrow_request_id' => $borrowRequest->id,
            'lecturer_id' => $this->dosen->id,
            'return_date' => Carbon::now(),
            'returned_quantity' => 1,
            'condition' => 'Baik',
            'notes' => 'Normal',
        ]);

        // 1. Guest diarahkan ke login
        $this->get(route('dosen.pindai-qr'))->assertRedirect(route('login'));
        $this->get(route('dosen.pengembalian.index'))->assertRedirect(route('login'));
        $this->get(route('dosen.audit-pengembalian.index'))->assertRedirect(route('login'));

        // 2. Mahasiswa ditolak mengakses halaman dan API M6
        $this->actingAs($this->mahasiswa);
        $this->get(route('dosen.pindai-qr'))->assertStatus(302);
        $this->get(route('dosen.pengembalian.index'))->assertStatus(302);
        $this->get(route('dosen.audit-pengembalian.show', $returnRecord->id))->assertStatus(302);
        $this->postJson(route('dosen.pindai-qr.validasi'), ['token' => 'QR-SERAH-DUMMY'])->assertStatus(403);
        $this->postJson(route('dosen.pindai-qr.penyerahan'), ['token' => 'QR-SERAH-DUMMY'])->assertStatus(403);
        $this->postJson(route('dosen.pindai-qr.pengembalian'), [
            'token' => 'QR-KEMBALI-DUMMY',
            'condition' => 'Baik',
        ])->assertStatus(403);

        // 3. Admin ditolak mengakses scanner & pengembalian dosen
        $this->actingAs($this->admin);
        $this->get(route('dosen.pindai-qr'))->assertRedirect(route('admin.dashboard'));
        $this->postJson(route('dosen.pindai-qr.validasi'), ['token' => 'QR-SERAH-DUMMY'])->assertStatus(403);

        // 4. Dosen diizinkan mengakses seluruh halaman M6
        $this->actingAs($this->dosen);
        $scannerRes = $this->get(route('dosen.pindai-qr'));
        $scannerRes->assertStatus(200);
        $scannerRes->assertSee('Pindai QR Code');
        $scannerRes->assertSee('html5-qrcode.min.js');
        $scannerRes->assertSee('NotAllowedError');
        $scannerRes->assertSee('NotFoundError');

        $this->get(route('dosen.pengembalian.index'))->assertStatus(200);
        $this->get(route('dosen.audit-pengembalian.index'))->assertStatus(200);
        $this->get(route('dosen.audit-pengembalian.show', $returnRecord->id))->assertStatus(200);
    }

    public function test_validasi_token_qr_valid_tidak_valid_kedaluwarsa_sudah_dipakai_salah_jenis_dan_salah_peminjaman(): void
    {
        $this->actingAs($this->dosen);

        $requestA = $this->createBarangBorrowRequest($this->proyektor, 1, 'disetujui');
        $requestB = $this->createBarangBorrowRequest($this->kabelTerminal, 1, 'disetujui');

        $validToken = $this->qrCodeService->generateToken($requestA, 'penyerahan');

        // 1. Token valid
        $this->postJson(route('dosen.pindai-qr.validasi'), [
            'token' => $validToken->token,
            'mode' => 'penyerahan',
        ])
            ->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'status' => 'valid',
                'token' => $validToken->token,
                'type' => 'penyerahan',
            ]);

        // 2. Token tidak terdaftar
        $this->postJson(route('dosen.pindai-qr.validasi'), [
            'token' => 'QR-SERAH-TIDAKADA999',
        ])
            ->assertStatus(422)
            ->assertJson([
                'valid' => false,
                'status' => 'not_found',
            ]);

        // 3. Token salah mode (token penyerahan dipakai pada mode pengembalian)
        $this->postJson(route('dosen.pindai-qr.validasi'), [
            'token' => $validToken->token,
            'mode' => 'pengembalian',
        ])
            ->assertStatus(422)
            ->assertJson([
                'valid' => false,
                'status' => 'wrong_type',
            ]);

        // 4. Token salah peminjaman (token request A dipakai untuk request B)
        $this->postJson(route('dosen.pindai-qr.validasi'), [
            'token' => $validToken->token,
            'borrow_request_id' => $requestB->id,
        ])
            ->assertStatus(422)
            ->assertJson([
                'valid' => false,
                'status' => 'wrong_borrow_request',
            ]);

        // 5. Token kedaluwarsa (lewat 15 menit)
        $validToken->update(['expires_at' => Carbon::now()->subMinutes(2)]);
        $this->postJson(route('dosen.pindai-qr.validasi'), [
            'token' => $validToken->token,
        ])
            ->assertStatus(422)
            ->assertJson([
                'valid' => false,
                'status' => 'expired',
            ]);

        // 6. Token sudah digunakan
        $validToken->update([
            'expires_at' => Carbon::now()->addMinutes(10),
            'is_used' => true,
            'used_at' => Carbon::now(),
        ]);
        $this->postJson(route('dosen.pindai-qr.validasi'), [
            'token' => $validToken->token,
        ])
            ->assertStatus(422)
            ->assertJson([
                'valid' => false,
                'status' => 'already_used',
            ]);
    }

    public function test_serah_terima_barang_mengurangi_stok_dan_mencegah_penggunaan_berulang(): void
    {
        $this->actingAs($this->dosen);

        $initialStock = $this->proyektor->available_stock; // 2 unit
        $borrowRequest = $this->createBarangBorrowRequest($this->proyektor, 1, 'disetujui');
        $handoverToken = $this->qrCodeService->generateToken($borrowRequest, 'penyerahan');

        // Proses serah terima pertama -> berhasil
        $response = $this->post(route('dosen.pindai-qr.penyerahan'), [
            'token' => $handoverToken->token,
            'handover_notes' => 'Diserahkan lengkap dengan tas dan kabel HDMI',
        ]);

        $response->assertRedirect(route('dosen.pindai-qr', ['mode' => 'penyerahan']));
        $response->assertSessionHas('success');

        $borrowRequest->refresh();
        $handoverToken->refresh();
        $this->proyektor->refresh();

        $this->assertEquals('dipinjam', $borrowRequest->status);
        $this->assertTrue($handoverToken->is_used);
        $this->assertNotNull($handoverToken->used_at);
        $this->assertEquals($initialStock - 1, $this->proyektor->available_stock);

        // Pencegahan penggunaan ulang token penyerahan yang sama
        $reuseResponse = $this->post(route('dosen.pindai-qr.penyerahan'), [
            'token' => $handoverToken->token,
        ]);

        $reuseResponse->assertSessionHas('error');
        $this->proyektor->refresh();
        $this->assertEquals($initialStock - 1, $this->proyektor->available_stock);
    }

    public function test_serah_terima_dan_pengembalian_laboratorium_mengubah_status_dan_schedule_log(): void
    {
        $this->actingAs($this->dosen);

        $labRequest = $this->createLabBorrowRequest('disetujui');
        $handoverToken = $this->qrCodeService->generateToken($labRequest, 'penyerahan');

        // Serah terima lab
        $this->post(route('dosen.pindai-qr.penyerahan'), [
            'token' => $handoverToken->token,
        ])->assertSessionHas('success');

        $labRequest->refresh();
        $this->assertEquals('dipinjam', $labRequest->status);
        $this->assertEquals('berlangsung', ScheduleLog::where('borrow_request_id', $labRequest->id)->value('status'));

        // Halaman form konfirmasi pengembalian lab
        $this->get(route('dosen.pengembalian.show', $labRequest->id))
            ->assertStatus(200)
            ->assertSee($labRequest->code);

        // Pengembalian lab
        $returnToken = $this->qrCodeService->generateToken($labRequest, 'pengembalian');
        $returnResponse = $this->post(route('dosen.pindai-qr.pengembalian'), [
            'token' => $returnToken->token,
            'borrow_request_id' => $labRequest->id,
            'condition' => 'Baik',
            'good_quantity' => 1,
            'damaged_quantity' => 0,
            'lost_quantity' => 0,
            'notes' => 'Ruangan bersih dan seluruh komputer mati rapi',
        ]);

        $record = ReturnRecord::where('borrow_request_id', $labRequest->id)->first();
        $this->assertNotNull($record);
        $returnResponse->assertRedirect(route('dosen.audit-pengembalian.show', $record->id));

        $labRequest->refresh();
        $this->assertEquals('dikembalikan', $labRequest->status);
        $this->assertEquals('selesai', ScheduleLog::where('borrow_request_id', $labRequest->id)->value('status'));
    }

    public function test_pengembalian_barang_kondisi_baik_memulihkan_stok_dan_mencegah_pengembalian_ganda(): void
    {
        $this->actingAs($this->dosen);

        $initialStock = $this->kabelTerminal->available_stock; // 5 unit
        $borrowRequest = $this->createBarangBorrowRequest($this->kabelTerminal, 3, 'disetujui');

        $handoverToken = $this->qrCodeService->generateToken($borrowRequest, 'penyerahan');
        $this->post(route('dosen.pindai-qr.penyerahan'), ['token' => $handoverToken->token]);

        $this->kabelTerminal->refresh();
        $this->assertEquals($initialStock - 3, $this->kabelTerminal->available_stock);

        $returnToken = $this->qrCodeService->generateToken($borrowRequest, 'pengembalian');
        $response = $this->post(route('dosen.pindai-qr.pengembalian'), [
            'token' => $returnToken->token,
            'borrow_request_id' => $borrowRequest->id,
            'condition' => 'Baik',
            'good_quantity' => 3,
            'damaged_quantity' => 0,
            'lost_quantity' => 0,
        ]);

        $record = ReturnRecord::where('borrow_request_id', $borrowRequest->id)->first();
        $this->assertNotNull($record);
        $response->assertRedirect(route('dosen.audit-pengembalian.show', $record->id));

        $this->kabelTerminal->refresh();
        $borrowRequest->refresh();
        $returnToken->refresh();

        $this->assertEquals($initialStock, $this->kabelTerminal->available_stock);
        $this->assertEquals('dikembalikan', $borrowRequest->status);
        $this->assertTrue($returnToken->is_used);
        $this->assertEquals(3, $record->returned_quantity);

        // Upaya pengembalian ganda memakai token sama ataupun token baru harus ditolak dan stok tidak bertambah lagi
        $secondToken = $this->qrCodeService->generateToken($borrowRequest, 'pengembalian');
        $doubleAttempt = $this->post(route('dosen.pindai-qr.pengembalian'), [
            'token' => $secondToken->token,
            'borrow_request_id' => $borrowRequest->id,
            'condition' => 'Baik',
            'good_quantity' => 3,
            'damaged_quantity' => 0,
            'lost_quantity' => 0,
        ]);

        $doubleAttempt->assertSessionHas('error');
        $this->kabelTerminal->refresh();
        $this->assertEquals($initialStock, $this->kabelTerminal->available_stock);
        $this->assertEquals(1, ReturnRecord::where('borrow_request_id', $borrowRequest->id)->count());
    }

    public function test_pemeriksaan_kondisi_rusak_dan_hilang_wajib_catatan_dan_hanya_pulihkan_unit_baik(): void
    {
        $this->actingAs($this->dosen);

        $initialStock = $this->kabelTerminal->available_stock; // 5 unit
        $borrowRequest = $this->createBarangBorrowRequest($this->kabelTerminal, 4, 'disetujui');

        $handoverToken = $this->qrCodeService->generateToken($borrowRequest, 'penyerahan');
        $this->post(route('dosen.pindai-qr.penyerahan'), ['token' => $handoverToken->token]);

        $this->kabelTerminal->refresh();
        $this->assertEquals($initialStock - 4, $this->kabelTerminal->available_stock); // 1 unit tersisa

        $returnToken = $this->qrCodeService->generateToken($borrowRequest, 'pengembalian');

        // 1. Gagal jika ada rusak tapi catatan kerusakan kosong
        $missingNotes = $this->post(route('dosen.pindai-qr.pengembalian'), [
            'token' => $returnToken->token,
            'borrow_request_id' => $borrowRequest->id,
            'condition' => 'Rusak Ringan',
            'good_quantity' => 2,
            'damaged_quantity' => 1,
            'lost_quantity' => 1,
            'notes' => '',
        ]);
        $missingNotes->assertSessionHas('error');
        $this->assertNull(ReturnRecord::where('borrow_request_id', $borrowRequest->id)->first());

        // 2. Gagal jika jumlah total tidak sama dengan jumlah dipinjam (4)
        $mismatchQty = $this->post(route('dosen.pindai-qr.pengembalian'), [
            'token' => $returnToken->token,
            'borrow_request_id' => $borrowRequest->id,
            'condition' => 'Rusak Ringan',
            'good_quantity' => 1,
            'damaged_quantity' => 1,
            'lost_quantity' => 0,
            'notes' => '1 stop kontak retak',
        ]);
        $mismatchQty->assertSessionHas('error');

        // 3. Berhasil jika 2 Baik + 1 Rusak + 1 Hilang = 4 unit dengan catatan lengkap
        $validPartial = $this->post(route('dosen.pindai-qr.pengembalian'), [
            'token' => $returnToken->token,
            'borrow_request_id' => $borrowRequest->id,
            'condition' => 'Rusak Ringan',
            'good_quantity' => 2,
            'damaged_quantity' => 1,
            'lost_quantity' => 1,
            'notes' => '1 unit retak di casing, 1 unit hilang di lab',
        ]);

        $record = ReturnRecord::where('borrow_request_id', $borrowRequest->id)->first();
        $this->assertNotNull($record);
        $validPartial->assertRedirect(route('dosen.audit-pengembalian.show', $record->id));

        // Stok hanya dipulihkan +2 (unit baik), dari 1 menjadi 3 (bukan 5)
        $this->kabelTerminal->refresh();
        $this->assertEquals($initialStock - 4 + 2, $this->kabelTerminal->available_stock);
        $this->assertEquals(2, $record->returned_quantity);
        $this->assertEquals('Rusak Ringan', $record->condition);

        // Verifikasi halaman Audit Pengembalian menampilkan data audit lengkap
        $auditRes = $this->get(route('dosen.audit-pengembalian.show', $record->id));
        $auditRes->assertStatus(200);
        $auditRes->assertSee($borrowRequest->code);
        $auditRes->assertSee($this->kabelTerminal->name);
        $auditRes->assertSee($this->mahasiswa->name);
        $auditRes->assertSee($this->dosen->name);
        $auditRes->assertSee('1 unit retak di casing, 1 unit hilang di lab');
        $auditRes->assertSee('2 unit');
    }

    public function test_pengembalian_barang_hilang_semua_tidak_menambah_stok(): void
    {
        $this->actingAs($this->dosen);

        $initialStock = $this->proyektor->available_stock; // 2 unit
        $borrowRequest = $this->createBarangBorrowRequest($this->proyektor, 2, 'disetujui');

        $handoverToken = $this->qrCodeService->generateToken($borrowRequest, 'penyerahan');
        $this->post(route('dosen.pindai-qr.penyerahan'), ['token' => $handoverToken->token]);

        $this->proyektor->refresh();
        $this->assertEquals(0, $this->proyektor->available_stock);

        $returnToken = $this->qrCodeService->generateToken($borrowRequest, 'pengembalian');
        $this->post(route('dosen.pindai-qr.pengembalian'), [
            'token' => $returnToken->token,
            'borrow_request_id' => $borrowRequest->id,
            'condition' => 'Hilang',
            'good_quantity' => 0,
            'damaged_quantity' => 0,
            'lost_quantity' => 2,
            'notes' => 'Kedua unit proyektor hilang setelah kegiatan seminar',
        ])->assertSessionHas('success');

        $this->proyektor->refresh();
        $this->assertEquals(0, $this->proyektor->available_stock);
        $this->assertEquals($initialStock - 2, $this->proyektor->available_stock);
    }
}
