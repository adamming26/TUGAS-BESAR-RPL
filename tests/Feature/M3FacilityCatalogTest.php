<?php

namespace Tests\Feature;

use App\Models\BorrowRequest;
use App\Models\Facility;
use App\Models\ScheduleLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class M3FacilityCatalogTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    /**
     * 1. Katalog fasilitas umum dan katalog barang.
     */
    public function test_katalog_fasilitas_dan_katalog_barang_menampilkan_data_dengan_benar(): void
    {
        $indexResponse = $this->get(route('fasilitas.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Katalog Inventaris Prodi');
        $indexResponse->assertSee('Proyektor');
        $indexResponse->assertSee('Lab Komputer Basis Data');

        $barangResponse = $this->get(route('fasilitas.barang'));
        $barangResponse->assertStatus(200);
        $barangResponse->assertSee('Katalog Barang');
        $barangResponse->assertSee('Proyektor');
        $barangResponse->assertSee('Kabel HDMI');
        $barangResponse->assertSee('Kabel Terminal Cabang');
        $barangResponse->assertDontSee('Lab Komputer Basis Data');
    }

    /**
     * 2. Katalog laboratorium.
     */
    public function test_katalog_laboratorium_hanya_menampilkan_ruangan_lab(): void
    {
        $response = $this->get(route('fasilitas.laboratorium'));

        $response->assertStatus(200);
        $response->assertSee('Katalog Laboratorium');
        $response->assertSee('Lab Komputer Basis Data');
        $response->assertSee('Lab Komputer Unit Teknologi Informasi');
        $response->assertSee('50 orang');
        $response->assertDontSee('Proyektor');
        $response->assertDontSee('Kabel HDMI');
    }

    /**
     * 3. Pencarian fasilitas berdasarkan nama, kode, dan deskripsi.
     */
    public function test_pencarian_fasilitas_berdasarkan_nama_dan_kode(): void
    {
        $byName = $this->get(route('fasilitas.index', ['search' => 'Proyektor']));
        $byName->assertStatus(200);
        $byName->assertSee('Proyektor');
        $byName->assertDontSee('Kabel Terminal Cabang');

        $byCode = $this->get(route('fasilitas.index', ['search' => 'LAB-02']));
        $byCode->assertStatus(200);
        $byCode->assertSee('Lab Komputer Unit Teknologi Informasi');
        $byCode->assertDontSee('Lab Komputer Basis Data');
    }

    /**
     * 4. Filter jenis, kondisi, dan status ketersediaan serta pengecualian fasilitas nonaktif.
     */
    public function test_filter_jenis_kondisi_dan_status_ketersediaan(): void
    {
        // Buat fasilitas dengan stok terbatas (1), habis (0), rusak ringan, dan nonaktif
        Facility::create([
            'code' => 'BRG-TERBATAS',
            'name' => 'Kamera Dokumentasi',
            'type' => 'barang',
            'description' => 'Kamera prodi',
            'total_stock' => 3,
            'available_stock' => 1,
            'capacity' => 0,
            'condition' => 'Baik',
            'is_active' => true,
        ]);

        Facility::create([
            'code' => 'BRG-HABIS',
            'name' => 'Pointer Presentasi',
            'type' => 'barang',
            'description' => 'Wireless presenter',
            'total_stock' => 2,
            'available_stock' => 0,
            'capacity' => 0,
            'condition' => 'Rusak Ringan',
            'is_active' => true,
        ]);

        Facility::create([
            'code' => 'BRG-NONAKTIF',
            'name' => 'Perangkat Gudang',
            'type' => 'barang',
            'description' => 'Sedang tidak digunakan',
            'total_stock' => 2,
            'available_stock' => 2,
            'capacity' => 0,
            'condition' => 'Baik',
            'is_active' => false,
        ]);

        // Fasilitas nonaktif tidak boleh tampil di katalog
        $all = $this->get(route('fasilitas.index'));
        $all->assertDontSee('Perangkat Gudang');

        // Filter jenis barang
        $typeBarang = $this->get(route('fasilitas.index', ['type' => 'barang']));
        $typeBarang->assertStatus(200);
        $typeBarang->assertSee('Kamera Dokumentasi');
        $typeBarang->assertDontSee('Lab Komputer Basis Data');

        // Filter status terbatas
        $terbatas = $this->get(route('fasilitas.index', ['status' => 'terbatas']));
        $terbatas->assertStatus(200);
        $terbatas->assertSee('Kamera Dokumentasi');
        $terbatas->assertDontSee('Proyektor');
        $terbatas->assertDontSee('Pointer Presentasi');

        // Filter status habis
        $habis = $this->get(route('fasilitas.index', ['status' => 'habis']));
        $habis->assertStatus(200);
        $habis->assertSee('Pointer Presentasi');
        $habis->assertDontSee('Proyektor');

        // Filter kondisi Rusak Ringan
        $kondisi = $this->get(route('fasilitas.index', ['condition' => 'Rusak Ringan']));
        $kondisi->assertStatus(200);
        $kondisi->assertSee('Pointer Presentasi');
        $kondisi->assertDontSee('Proyektor');
    }

    /**
     * 5 & 6. Detail fasilitas barang: informasi lengkap & stok.
     */
    public function test_detail_fasilitas_barang_menampilkan_informasi_stok_dan_penggunaan(): void
    {
        $proyektor = Facility::where('code', 'BRG-01')->firstOrFail();

        $response = $this->get(route('fasilitas.show', $proyektor->id));

        $response->assertStatus(200);
        $response->assertSee($proyektor->name);
        $response->assertSee($proyektor->code);
        $response->assertSee($proyektor->description);
        $response->assertSee("{$proyektor->available_stock} / {$proyektor->total_stock} unit");
        $response->assertSee('Informasi Penggunaan');
        $response->assertSee('Tersedia');
    }

    /**
     * 5 & 7. Detail fasilitas laboratorium: kapasitas & jadwal relevan.
     */
    public function test_detail_fasilitas_laboratorium_menampilkan_kapasitas_dan_jadwal(): void
    {
        $lab = Facility::where('code', 'LAB-01')->firstOrFail();
        $scheduleDate = now()->addDays(2)->toDateString();

        $response = $this->get(route('fasilitas.show', [
            'facility' => $lab->id,
            'tanggal' => $scheduleDate,
        ]));

        $response->assertStatus(200);
        $response->assertSee($lab->name);
        $response->assertSee("{$lab->capacity} orang");
        $response->assertSee('Pelatihan Basis Data Lanjut Himpunan Mahasiswa');
        $response->assertSee('13:00 - 16:00');
        $response->assertSee('Waktu Tersedia');
    }

    /**
     * 8 & 9. Pemeriksaan bentrok jadwal terintegrasi ScheduleCheckService.
     */
    public function test_pemeriksaan_bentrok_jadwal_mendeteksi_bentrok_dan_waktu_tersedia(): void
    {
        $lab = Facility::where('code', 'LAB-02')->firstOrFail();
        $mahasiswa = User::where('role', 'mahasiswa')->firstOrFail();
        $targetDate = Carbon::today()->addDays(5)->toDateString();

        // Buat peminjaman aktif 10:00 - 12:00
        $borrow = BorrowRequest::create([
            'code' => 'REQ-M3-CONFLICT',
            'user_id' => $mahasiswa->id,
            'type' => 'ruangan',
            'facility_id' => $lab->id,
            'borrow_date' => $targetDate,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'duration_hours' => 2,
            'purpose' => 'Uji Bentrok Jadwal M3',
            'participant_count' => 30,
            'status' => 'disetujui',
        ]);

        ScheduleLog::create([
            'facility_id' => $lab->id,
            'borrow_request_id' => $borrow->id,
            'date' => $targetDate,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'terjadwal',
        ]);

        // 1. Cek waktu bentrok (11:00 selama 2 jam -> 11:00-13:00) via halaman detail
        $conflictPage = $this->get(route('fasilitas.show', [
            'facility' => $lab->id,
            'tanggal' => $targetDate,
            'jam_mulai' => '11:00',
            'durasi' => 2,
            'cek_jadwal' => 1,
        ]));
        $conflictPage->assertStatus(200);
        $conflictPage->assertSee('Jadwal Bentrok');
        $conflictPage->assertSee('10:00 - 12:00');

        // 2. Cek waktu bentrok via endpoint JSON cek-jadwal
        $conflictJson = $this->getJson(route('fasilitas.cek-jadwal', [
            'facility' => $lab->id,
            'tanggal' => $targetDate,
            'jam_mulai' => '10:00',
            'durasi' => 2,
        ]));
        $conflictJson->assertStatus(200)
            ->assertJson([
                'facility_id' => $lab->id,
                'available' => false,
                'has_conflict' => true,
            ]);

        // 3. Cek waktu tersedia (13:00 selama 2 jam -> 13:00-15:00) via JSON dan halaman detail
        $availableJson = $this->getJson(route('fasilitas.cek-jadwal', [
            'facility' => $lab->id,
            'tanggal' => $targetDate,
            'jam_mulai' => '13:00',
            'durasi' => 2,
        ]));
        $availableJson->assertStatus(200)
            ->assertJson([
                'facility_id' => $lab->id,
                'available' => true,
                'has_conflict' => false,
            ]);

        $availablePage = $this->get(route('fasilitas.show', [
            'facility' => $lab->id,
            'tanggal' => $targetDate,
            'jam_mulai' => '13:00',
            'durasi' => 2,
            'cek_jadwal' => 1,
        ]));
        $availablePage->assertStatus(200);
        $availablePage->assertSee('Jadwal Tersedia');
    }

    /**
     * 10. Pengalihan ke form peminjaman M4 menggunakan facility_id.
     */
    public function test_tombol_peminjaman_mengarahkan_mahasiswa_ke_form_m4_dengan_facility_id(): void
    {
        $mahasiswa = User::where('role', 'mahasiswa')->firstOrFail();
        $proyektor = Facility::where('code', 'BRG-01')->firstOrFail();
        $lab = Facility::where('code', 'LAB-01')->firstOrFail();

        // Guest diarahkan ke halaman login
        $guestDetail = $this->get(route('fasilitas.show', $proyektor->id));
        $guestDetail->assertStatus(200);
        $guestDetail->assertSee(route('login'));

        // Simulasikan rute form peminjaman milik M4 terdaftar di router
        Route::get('/mahasiswa/pinjam/{jenis?}/{id?}', fn () => 'M4 Form')
            ->name('mahasiswa.form-peminjaman');
        app('router')->getRoutes()->refreshNameLookups();

        $expectedBarangUrl = route('mahasiswa.form-peminjaman', [
            'jenis' => 'barang',
            'id' => $proyektor->id,
            'facility_id' => $proyektor->id,
        ]);

        $expectedLabUrl = route('mahasiswa.form-peminjaman', [
            'jenis' => 'lab',
            'id' => $lab->id,
            'facility_id' => $lab->id,
        ]);

        // Cek di Katalog
        $catalogResponse = $this->actingAs($mahasiswa)->get(route('fasilitas.index'));
        $catalogResponse->assertStatus(200);
        $catalogResponse->assertSee($expectedBarangUrl, false);
        $catalogResponse->assertSee($expectedLabUrl, false);

        // Cek di Detail Fasilitas Barang (dengan tanggal/jam pemeriksaan)
        $detailBarangResponse = $this->actingAs($mahasiswa)->get(route('fasilitas.show', [
            'facility' => $proyektor->id,
            'tanggal' => Carbon::tomorrow()->toDateString(),
            'jam_mulai' => '09:00',
            'durasi' => 2,
            'cek_jadwal' => 1,
        ]));
        $detailBarangResponse->assertStatus(200);
        $detailBarangResponse->assertSee("facility_id={$proyektor->id}", false);
        $detailBarangResponse->assertSee('Ajukan Peminjaman');
    }
}
