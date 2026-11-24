<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Facility;
use App\Models\BorrowRequest;
use App\Models\QrToken;
use App\Services\QRCodeService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;

class SipafkaWorkflowTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_halaman_beranda_dan_katalog_dapat_diakses_publik(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Pinjam Fasilitas Prodi');

        $responseFasilitas = $this->get('/fasilitas');
        $responseFasilitas->assertStatus(200);
        $responseFasilitas->assertSee('Katalog Inventaris Prodi');
    }

    public function test_autentikasi_dan_redireksi_sesuai_role(): void
    {
        if (!\Illuminate\Support\Facades\Route::has('mahasiswa.dashboard')) {
            $this->markTestSkipped('Rute dashboard modul M3/M4/M2 disiapkan pada fase masing-masing modul.');
        }

        // 1. Mahasiswa login
        $mahasiswa = User::where('email', 'mahasiswa@uho.ac.id')->first();
        $this->assertNotNull($mahasiswa);

        $response = $this->post('/login', [
            'identity' => 'mahasiswa@uho.ac.id',
            'password' => 'password',
        ]);
        $response->assertRedirect(route('mahasiswa.dashboard'));

        // 2. Proteksi role: Mahasiswa tidak boleh akses admin/dashboard
        $this->actingAs($mahasiswa);
        $forbidden = $this->get('/admin/dashboard');
        $forbidden->assertRedirect(route('mahasiswa.dashboard'));

        // 3. Dosen login
        $dosen = User::where('email', 'dosen@uho.ac.id')->first();
        $this->assertNotNull($dosen);
        $this->actingAs($dosen);
        $dosenDashboard = $this->get('/dosen/dashboard');
        $dosenDashboard->assertStatus(200);

        // 4. Admin login
        $admin = User::where('email', 'admin@uho.ac.id')->first();
        $this->assertNotNull($admin);
        $this->actingAs($admin);
        $adminDashboard = $this->get('/admin/dashboard');
        $adminDashboard->assertStatus(200);
    }

    public function test_alur_lengkap_peminjaman_barang_dan_persetujuan_dosen(): void
    {
        if (!\Illuminate\Support\Facades\Route::has('mahasiswa.peminjaman-saya')) {
            $this->markTestSkipped('Rute alur peminjaman disiapkan pada fase Modul M3 dan M4.');
        }

        $mahasiswa = User::where('email', 'mahasiswa@uho.ac.id')->first();
        $dosen = User::where('email', 'dosen@uho.ac.id')->first();
        $proyektor = Facility::where('code', 'BRG-01')->first();

        $this->actingAs($mahasiswa);

        // Ajukan Peminjaman
        $response = $this->post('/mahasiswa/pinjam', [
            'type' => 'barang',
            'facility_id' => $proyektor->id,
            'quantity' => 1,
            'borrow_date' => Carbon::now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'duration_hours' => 2,
            'purpose' => 'Presentasi Tugas Akhir Ilmu Komputer FMIPA UHO',
        ]);

        $response->assertRedirect(route('mahasiswa.peminjaman-saya'));

        $borrowRequest = BorrowRequest::where('user_id', $mahasiswa->id)
            ->where('facility_id', $proyektor->id)
            ->where('status', 'menunggu')
            ->first();

        $this->assertNotNull($borrowRequest);

        // Dosen menyetujui pengajuan
        $this->actingAs($dosen);
        $approvalResponse = $this->post(route('dosen.putuskan-pengajuan', $borrowRequest->id), [
            'action' => 'setujui',
            'notes' => 'Disetujui untuk presentasi',
        ]);

        $approvalResponse->assertRedirect(route('dosen.dashboard'));

        $borrowRequest->refresh();
        $this->assertEquals('disetujui', $borrowRequest->status);

        // Pastikan QR penyerahan otomatis terbit
        $qrToken = QrToken::where('borrow_request_id', $borrowRequest->id)
            ->where('type', 'penyerahan')
            ->first();

        $this->assertNotNull($qrToken);
        $this->assertFalse($qrToken->is_used);
    }
}
