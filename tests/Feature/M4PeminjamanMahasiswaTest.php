<?php

namespace Tests\Feature;

use App\Models\BorrowItem;
use App\Models\BorrowRequest;
use App\Models\Facility;
use App\Models\ReturnRecord;
use App\Models\ScheduleLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class M4PeminjamanMahasiswaTest extends TestCase
{
    use DatabaseMigrations;

    protected User $mahasiswa1;
    protected User $mahasiswa2;
    protected User $dosen;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        $this->mahasiswa1 = User::where('email', 'mahasiswa@uho.ac.id')->firstOrFail();
        $this->dosen = User::where('email', 'dosen@uho.ac.id')->firstOrFail();
        $this->admin = User::where('email', 'admin@uho.ac.id')->firstOrFail();

        $this->mahasiswa2 = User::create([
            'name' => 'Ridha Septiani K',
            'email' => 'ridha.m4@uho.ac.id',
            'nim_nip' => 'F1G125075',
            'role' => 'mahasiswa',
            'password' => Hash::make('password'),
            'phone' => '081234567891',
        ]);
    }

    /**
     * 1. Pengujian Akses & Dashboard Mahasiswa.
     */
    public function test_dashboard_mahasiswa_hanya_dapat_diakses_mahasiswa_dan_menampilkan_ringkasan(): void
    {
        // Guest dialihkan ke login
        $this->get(route('mahasiswa.dashboard'))->assertRedirect(route('login'));

        // Admin dialihkan ke dashboard admin; Dosen ditolak (403 sebelum rute M5 ada, atau dialihkan ke dosen.dashboard)
        $this->actingAs($this->admin)->get(route('mahasiswa.dashboard'))->assertRedirect(route('admin.dashboard'));
        $dosenResponse = $this->actingAs($this->dosen)->get(route('mahasiswa.dashboard'));
        if (\Illuminate\Support\Facades\Route::has('dosen.dashboard')) {
            $dosenResponse->assertRedirect(route('dosen.dashboard'));
        } else {
            $dosenResponse->assertStatus(403);
        }

        // Buat pengajuan khusus milik mahasiswa2 agar tidak ikut terhitung di mahasiswa1
        $proyektor = Facility::where('code', 'BRG-01')->firstOrFail();
        BorrowRequest::create([
            'code' => 'REQ-OTHER-MHS2',
            'user_id' => $this->mahasiswa2->id,
            'type' => 'barang',
            'facility_id' => $proyektor->id,
            'borrow_date' => Carbon::now()->addDay()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'duration_hours' => 2,
            'purpose' => 'Pengajuan khusus mahasiswa kedua untuk pengujian isolasi data.',
            'participant_count' => 1,
            'status' => 'menunggu',
        ]);

        // Mahasiswa1 melihat dashboard miliknya beserta akses ke katalog M3 dan daftar peminjaman
        $response = $this->actingAs($this->mahasiswa1)->get(route('mahasiswa.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Dashboard Mahasiswa');
        $response->assertSee($this->mahasiswa1->name);
        $response->assertSee('Katalog Fasilitas');
        $response->assertSee('Daftar Peminjaman Saya');
        $response->assertSee(route('fasilitas.index'));
        $response->assertSee(route('mahasiswa.peminjaman-saya'));
        $response->assertDontSee('REQ-OTHER-MHS2');
    }

    /**
     * 2. Pengujian Form & Pengajuan Peminjaman Barang.
     */
    public function test_mahasiswa_dapat_mengajukan_peminjaman_barang_dengan_input_valid(): void
    {
        $proyektor = Facility::where('code', 'BRG-01')->firstOrFail();

        $formResponse = $this->actingAs($this->mahasiswa2)->get(route('mahasiswa.form-barang'));
        $formResponse->assertStatus(200);
        $formResponse->assertSee('Form Peminjaman Barang');
        $formResponse->assertSee($proyektor->name);

        $borrowDate = Carbon::now()->addDays(3)->toDateString();

        $response = $this->actingAs($this->mahasiswa2)->post(route('mahasiswa.store-peminjaman'), [
            'type' => 'barang',
            'facility_id' => $proyektor->id,
            'quantity' => 2,
            'borrow_date' => $borrowDate,
            'start_time' => '08:30',
            'end_time' => '11:30',
            'purpose' => 'Presentasi proyek mata kuliah Rekayasa Perangkat Lunak',
        ]);

        $response->assertRedirect(route('mahasiswa.peminjaman-saya'));
        $response->assertSessionHas('success');

        $borrowRequest = BorrowRequest::where('user_id', $this->mahasiswa2->id)
            ->where('facility_id', $proyektor->id)
            ->first();

        $this->assertNotNull($borrowRequest);
        $this->assertEquals('barang', $borrowRequest->type);
        $this->assertEquals('menunggu', $borrowRequest->status);
        $this->assertEquals('08:30:00', $borrowRequest->start_time);
        $this->assertEquals('11:30:00', $borrowRequest->end_time);
        $this->assertEquals(3, $borrowRequest->duration_hours);

        $this->assertDatabaseHas('borrow_items', [
            'borrow_request_id' => $borrowRequest->id,
            'facility_id' => $proyektor->id,
            'quantity' => 2,
        ]);
    }

    /**
     * 3. Pengujian Validasi Stok Barang & Input Jadwal Barang.
     */
    public function test_validasi_stok_barang_dan_jadwal_peminjaman_barang(): void
    {
        $proyektor = Facility::where('code', 'BRG-01')->firstOrFail(); // available_stock = 2
        $borrowDate = Carbon::now()->addDays(2)->toDateString();

        // Jumlah melebihi stok tersedia
        $overStock = $this->actingAs($this->mahasiswa2)->post(route('mahasiswa.store-peminjaman'), [
            'type' => 'barang',
            'facility_id' => $proyektor->id,
            'quantity' => $proyektor->available_stock + 5,
            'borrow_date' => $borrowDate,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'purpose' => 'Uji validasi stok barang melebihi batas tersedia.',
        ]);

        $overStock->assertSessionHasErrors(['quantity']);
        $this->assertDatabaseMissing('borrow_requests', [
            'user_id' => $this->mahasiswa2->id,
            'facility_id' => $proyektor->id,
        ]);

        // Waktu selesai lebih awal atau sama dengan waktu mulai
        $invalidTime = $this->actingAs($this->mahasiswa2)->post(route('mahasiswa.store-peminjaman'), [
            'type' => 'barang',
            'facility_id' => $proyektor->id,
            'quantity' => 1,
            'borrow_date' => $borrowDate,
            'start_time' => '14:00',
            'end_time' => '12:00',
            'purpose' => 'Uji validasi waktu selesai tidak valid.',
        ]);
        $invalidTime->assertSessionHasErrors(['end_time']);

        // Tanggal di masa lalu
        $pastDate = $this->actingAs($this->mahasiswa2)->post(route('mahasiswa.store-peminjaman'), [
            'type' => 'barang',
            'facility_id' => $proyektor->id,
            'quantity' => 1,
            'borrow_date' => Carbon::yesterday()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'purpose' => 'Uji validasi tanggal masa lalu.',
        ]);
        $pastDate->assertSessionHasErrors(['borrow_date']);
    }

    /**
     * 4. Pengujian Form & Pengajuan Peminjaman Laboratorium + Validasi Kapasitas & Bentrok Jadwal.
     */
    public function test_peminjaman_laboratorium_validasi_kapasitas_dan_bentrok_jadwal(): void
    {
        $lab = Facility::where('code', 'LAB-01')->firstOrFail(); // capacity = 50
        $date = Carbon::now()->addDays(5)->toDateString();

        $formResponse = $this->actingAs($this->mahasiswa2)->get(route('mahasiswa.form-lab'));
        $formResponse->assertStatus(200);
        $formResponse->assertSee('Form Peminjaman Laboratorium');
        $formResponse->assertSee($lab->name);

        // 1. Melebihi kapasitas lab -> ditolak
        $overCapacity = $this->actingAs($this->mahasiswa2)->post(route('mahasiswa.store-peminjaman'), [
            'type' => 'ruangan',
            'facility_id' => $lab->id,
            'participant_count' => $lab->capacity + 10,
            'borrow_date' => $date,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'purpose' => 'Uji validasi kapasitas laboratorium melebihi kuota.',
        ]);
        $overCapacity->assertSessionHasErrors(['participant_count']);
        $this->assertDatabaseMissing('borrow_requests', [
            'user_id' => $this->mahasiswa2->id,
            'facility_id' => $lab->id,
        ]);

        // 2. Pengajuan lab valid -> tersimpan & schedule_logs tercipta
        $validLab = $this->actingAs($this->mahasiswa2)->post(route('mahasiswa.store-peminjaman'), [
            'type' => 'ruangan',
            'facility_id' => $lab->id,
            'participant_count' => 35,
            'borrow_date' => $date,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'purpose' => 'Praktikum Basis Data Terstruktur Ilmu Komputer UHO',
        ]);
        $validLab->assertRedirect(route('mahasiswa.peminjaman-saya'));
        $validLab->assertSessionHas('success');

        $labRequest = BorrowRequest::where('user_id', $this->mahasiswa2->id)
            ->where('facility_id', $lab->id)
            ->firstOrFail();

        $this->assertEquals(35, $labRequest->participant_count);
        $this->assertEquals('menunggu', $labRequest->status);

        $scheduleLog = ScheduleLog::where('borrow_request_id', $labRequest->id)
            ->where('facility_id', $lab->id)
            ->whereDate('date', $date)
            ->where('start_time', '08:00:00')
            ->where('end_time', '10:00:00')
            ->where('status', 'terjadwal')
            ->first();
        $this->assertNotNull($scheduleLog);

        // 3. Bentrok jadwal (09:00 - 11:00 overlap dengan 08:00 - 10:00) -> ditolak
        $conflictResponse = $this->actingAs($this->mahasiswa1)->post(route('mahasiswa.store-peminjaman'), [
            'type' => 'ruangan',
            'facility_id' => $lab->id,
            'participant_count' => 20,
            'borrow_date' => $date,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'purpose' => 'Kegiatan praktikum pengganti yang bentrok jadwal.',
        ]);
        $conflictResponse->assertSessionHasErrors(['start_time']);

        // 4. Jadwal bersebelahan tanpa overlap (10:00 - 12:00) -> diperbolehkan
        $adjacentResponse = $this->actingAs($this->mahasiswa1)->post(route('mahasiswa.store-peminjaman'), [
            'type' => 'ruangan',
            'facility_id' => $lab->id,
            'participant_count' => 25,
            'borrow_date' => $date,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'purpose' => 'Kegiatan praktikum sesi siang setelah jadwal sebelumnya selesai.',
        ]);
        $adjacentResponse->assertRedirect(route('mahasiswa.peminjaman-saya'));
        $adjacentResponse->assertSessionHas('success');
    }

    /**
     * 5. Pengujian Daftar Peminjaman Saya, Detail, Riwayat & Pembatasan Data Antar Mahasiswa.
     */
    public function test_daftar_detail_riwayat_dan_pembatasan_data_antar_mahasiswa(): void
    {
        $hdmi = Facility::where('code', 'BRG-02')->firstOrFail();
        $proyektor = Facility::where('code', 'BRG-01')->firstOrFail();

        $reqMilikMhs1 = BorrowRequest::create([
            'code' => 'REQ-MHS1-PRIV',
            'user_id' => $this->mahasiswa1->id,
            'type' => 'barang',
            'facility_id' => $hdmi->id,
            'borrow_date' => Carbon::now()->addDay()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'duration_hours' => 2,
            'purpose' => 'Keperluan presentasi internal mahasiswa pertama.',
            'participant_count' => 1,
            'status' => 'menunggu',
        ]);

        $reqMilikMhs2 = BorrowRequest::create([
            'code' => 'REQ-MHS2-PRIV',
            'user_id' => $this->mahasiswa2->id,
            'type' => 'barang',
            'facility_id' => $proyektor->id,
            'borrow_date' => Carbon::now()->addDays(2)->toDateString(),
            'start_time' => '13:00:00',
            'end_time' => '15:00:00',
            'duration_hours' => 2,
            'purpose' => 'Keperluan seminar kelompok mahasiswa kedua.',
            'participant_count' => 1,
            'status' => 'ditolak',
            'reject_reason' => 'Jadwal bentrok dengan agenda jurusan.',
            'approved_by' => $this->dosen->id,
        ]);

        BorrowItem::create([
            'borrow_request_id' => $reqMilikMhs2->id,
            'facility_id' => $proyektor->id,
            'quantity' => 1,
        ]);

        // Daftar Peminjaman Saya mahasiswa2 hanya menampilkan miliknya, bukan REQ-MHS1-PRIV
        $listResponse = $this->actingAs($this->mahasiswa2)->get(route('mahasiswa.peminjaman-saya'));
        $listResponse->assertStatus(200);
        $listResponse->assertSee('REQ-MHS2-PRIV');
        $listResponse->assertDontSee('REQ-MHS1-PRIV');

        // Riwayat mahasiswa2 hanya menampilkan miliknya
        $historyResponse = $this->actingAs($this->mahasiswa2)->get(route('mahasiswa.riwayat'));
        $historyResponse->assertStatus(200);
        $historyResponse->assertSee('REQ-MHS2-PRIV');
        $historyResponse->assertDontSee('REQ-MHS1-PRIV');

        // Mahasiswa2 dapat mengakses detail miliknya sendiri
        $ownDetail = $this->actingAs($this->mahasiswa2)->get(route('mahasiswa.detail-peminjaman', $reqMilikMhs2->id));
        $ownDetail->assertStatus(200);
        $ownDetail->assertSee('REQ-MHS2-PRIV');
        $ownDetail->assertSee('Jadwal bentrok dengan agenda jurusan.');
        $ownDetail->assertSee('Riwayat Status Peminjaman');

        // Pembatasan backend: Mahasiswa2 TIDAK boleh mengakses detail milik Mahasiswa1
        $forbiddenDetail = $this->actingAs($this->mahasiswa2)->get(route('mahasiswa.detail-peminjaman', $reqMilikMhs1->id));
        $forbiddenDetail->assertStatus(403);
    }

    /**
     * 6. Pengujian Integrasi Data Pengajuan M4 untuk Dibaca Modul Persetujuan M5 & Pengembalian M6.
     */
    public function test_integrasi_pengajuan_m4_dapat_dibaca_modul_m5_dan_riwayat_m6(): void
    {
        $proyektor = Facility::where('code', 'BRG-01')->firstOrFail();

        $this->actingAs($this->mahasiswa2)->post(route('mahasiswa.store-peminjaman'), [
            'type' => 'barang',
            'facility_id' => $proyektor->id,
            'quantity' => 1,
            'borrow_date' => Carbon::now()->addDays(4)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'purpose' => 'Integrasi pengajuan M4 agar dapat diperiksa modul persetujuan M5.',
        ])->assertRedirect(route('mahasiswa.peminjaman-saya'));

        // Simulasi pembacaan oleh modul M5 (Dosen)
        $pendingForM5 = BorrowRequest::with(['user', 'facility', 'items.facility'])
            ->where('user_id', $this->mahasiswa2->id)
            ->where('status', 'menunggu')
            ->first();

        $this->assertNotNull($pendingForM5);
        $this->assertEquals($proyektor->id, $pendingForM5->facility->id);
        $this->assertCount(1, $pendingForM5->items);
        $this->assertEquals(1, $pendingForM5->items->first()->quantity);

        // Simulasi keputusan M5 & catatan pengembalian M6
        $pendingForM5->update([
            'status' => 'dikembalikan',
            'approved_by' => $this->dosen->id,
        ]);

        ReturnRecord::create([
            'borrow_request_id' => $pendingForM5->id,
            'lecturer_id' => $this->dosen->id,
            'return_date' => Carbon::now(),
            'returned_quantity' => 1,
            'condition' => 'Baik',
            'notes' => 'Dikembalikan sesuai jadwal.',
        ]);

        // Detail & Riwayat M4 menampilkan status terbaru dan catatan pengembalian M6
        $detailResponse = $this->actingAs($this->mahasiswa2)->get(route('mahasiswa.detail-peminjaman', $pendingForM5->id));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Dikembalikan');
        $detailResponse->assertSee('Dikembalikan sesuai jadwal.');
        $detailResponse->assertSee($this->dosen->name);
    }
}
