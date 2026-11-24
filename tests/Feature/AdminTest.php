<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Facility;
use App\Models\BorrowRequest;
use App\Models\BorrowItem;
use App\Models\ScheduleLog;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminTest extends TestCase
{
    use DatabaseMigrations;

    protected User $admin;
    protected User $dosen;
    protected User $mahasiswa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        $this->admin = User::where('email', 'admin@uho.ac.id')->first();
        $this->dosen = User::where('email', 'dosen@uho.ac.id')->first();
        $this->mahasiswa = User::where('email', 'mahasiswa@uho.ac.id')->first();
    }

    /**
     * 1. HAK AKSES & OTORISASI ADMIN
     */
    public function test_guest_tidak_dapat_mengakses_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_mahasiswa_tidak_dapat_mengakses_admin_dashboard(): void
    {
        $response = $this->actingAs($this->mahasiswa)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_dosen_tidak_dapat_mengakses_admin_dashboard(): void
    {
        $response = $this->actingAs($this->dosen)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_admin_dapat_mengakses_dashboard_dan_melihat_statistik(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Dashboard Admin');
        $response->assertSee('Total Pengguna');
        $response->assertSee('Ringkasan Barang');
        $response->assertSee('Laboratorium');
    }

    /**
     * 2. CRUD FASILITAS BARANG
     */
    public function test_admin_dapat_mengakses_halaman_kelola_fasilitas(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kelola-fasilitas'));
        $response->assertStatus(200);
        $response->assertSee('Kelola Fasilitas Kampus');
    }

    public function test_admin_berhasil_menambahkan_fasilitas_barang(): void
    {
        $payload = [
            'code' => 'BRG-TEST-01',
            'name' => 'Kamera DSLR Canon',
            'total_stock' => 5,
            'condition' => 'Baik',
            'is_active' => 1,
            'description' => 'Kamera untuk dokumentasi kegiatan kampus',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.store-barang'), $payload);

        $response->assertRedirect(route('admin.kelola-fasilitas', ['type' => 'barang']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('facilities', [
            'code' => 'BRG-TEST-01',
            'name' => 'Kamera DSLR Canon',
            'type' => 'barang',
            'total_stock' => 5,
            'available_stock' => 5,
        ]);
    }

    public function test_admin_berhasil_mengedit_fasilitas_barang(): void
    {
        $barang = Facility::where('type', 'barang')->first();

        $payload = [
            'code' => $barang->code,
            'name' => 'Nama Barang Terupdate',
            'total_stock' => 10,
            'available_stock' => 8,
            'condition' => 'Baik',
            'is_active' => 1,
            'description' => 'Deskripsi baru barang',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.update-barang', $barang->id), $payload);

        $response->assertRedirect(route('admin.kelola-fasilitas', ['type' => 'barang']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('facilities', [
            'id' => $barang->id,
            'name' => 'Nama Barang Terupdate',
            'total_stock' => 10,
            'available_stock' => 8,
        ]);
    }

    public function test_edit_barang_ditolak_jika_stok_tersedia_melebihi_total_stok(): void
    {
        $barang = Facility::where('type', 'barang')->first();

        $payload = [
            'code' => $barang->code,
            'name' => $barang->name,
            'total_stock' => 5,
            'available_stock' => 10, // Invalid: exceeds total_stock
            'condition' => 'Baik',
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.update-barang', $barang->id), $payload);

        $response->assertSessionHasErrors(['available_stock']);
    }

    public function test_admin_berhasil_menghapus_barang_tanpa_transaksi_aktif(): void
    {
        $barang = Facility::create([
            'code' => 'BRG-DEL-01',
            'name' => 'Barang Siap Hapus',
            'type' => 'barang',
            'total_stock' => 2,
            'available_stock' => 2,
            'condition' => 'Baik',
            'is_active' => 1,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.destroy-barang', $barang->id));

        $response->assertRedirect(route('admin.kelola-fasilitas', ['type' => 'barang']));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('facilities', ['id' => $barang->id]);
    }

    public function test_admin_ditolak_menghapus_barang_yang_sedang_dipinjam(): void
    {
        $barang = Facility::where('type', 'barang')->first();

        // Buat peminjaman aktif untuk barang ini
        BorrowRequest::create([
            'code' => 'REQ-ACTIVE-01',
            'user_id' => $this->mahasiswa->id,
            'facility_id' => $barang->id,
            'type' => 'barang',
            'borrow_date' => now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '12:00',
            'purpose' => 'Praktikum Lapangan',
            'status' => 'dipinjam',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.destroy-barang', $barang->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('facilities', ['id' => $barang->id]);
    }

    /**
     * 3. CRUD FASILITAS RUANGAN / LABORATORIUM
     */
    public function test_admin_berhasil_menambahkan_fasilitas_laboratorium(): void
    {
        $payload = [
            'code' => 'LAB-TEST-01',
            'name' => 'Laboratorium Kecerdasan Buatan',
            'capacity' => 45,
            'condition' => 'Baik',
            'is_active' => 1,
            'description' => 'Dilengkapi GPU workstation untuk riset AI',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.store-ruangan'), $payload);

        $response->assertRedirect(route('admin.kelola-fasilitas', ['type' => 'ruangan']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('facilities', [
            'code' => 'LAB-TEST-01',
            'name' => 'Laboratorium Kecerdasan Buatan',
            'type' => 'ruangan',
            'capacity' => 45,
        ]);
    }

    public function test_admin_berhasil_mengedit_fasilitas_laboratorium(): void
    {
        $lab = Facility::where('type', 'ruangan')->first();

        $payload = [
            'code' => $lab->code,
            'name' => 'Lab RPL Terupdate',
            'capacity' => 60,
            'condition' => 'Baik',
            'is_active' => 1,
            'description' => 'Kapasitas telah diperluas',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.update-ruangan', $lab->id), $payload);

        $response->assertRedirect(route('admin.kelola-fasilitas', ['type' => 'ruangan']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('facilities', [
            'id' => $lab->id,
            'name' => 'Lab RPL Terupdate',
            'capacity' => 60,
        ]);
    }

    public function test_admin_berhasil_menghapus_laboratorium_tanpa_booking_aktif(): void
    {
        $lab = Facility::create([
            'code' => 'LAB-DEL-01',
            'name' => 'Lab Siap Hapus',
            'type' => 'ruangan',
            'capacity' => 20,
            'total_stock' => 1,
            'available_stock' => 1,
            'condition' => 'Baik',
            'is_active' => 1,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.destroy-ruangan', $lab->id));

        $response->assertRedirect(route('admin.kelola-fasilitas', ['type' => 'ruangan']));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('facilities', ['id' => $lab->id]);
    }

    public function test_admin_ditolak_menghapus_laboratorium_dengan_jadwal_aktif(): void
    {
        $lab = Facility::where('type', 'ruangan')->first();

        $borrowReq = BorrowRequest::create([
            'code' => 'REQ-ACTIVE-LAB-01',
            'user_id' => $this->dosen->id,
            'facility_id' => $lab->id,
            'type' => 'ruangan',
            'borrow_date' => now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '10:00',
            'purpose' => 'Praktikum Rekayasa Perangkat Lunak',
            'status' => 'disetujui',
        ]);

        ScheduleLog::create([
            'borrow_request_id' => $borrowReq->id,
            'facility_id' => $lab->id,
            'date' => now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '10:00',
            'status' => 'berlangsung',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.destroy-ruangan', $lab->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('facilities', ['id' => $lab->id]);
    }

    /**
     * 4. MANAJEMEN PENGGUNA
     */
    public function test_admin_dapat_mengakses_halaman_kelola_pengguna(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kelola-pengguna'));
        $response->assertStatus(200);
        $response->assertSee('Kelola Pengguna');
        $response->assertSee($this->mahasiswa->name);
        $response->assertSee($this->dosen->name);
    }

    public function test_admin_berhasil_memperbarui_profil_pengguna(): void
    {
        $targetUser = User::create([
            'name' => 'Pengguna Percobaan',
            'email' => 'percobaan@uho.ac.id',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
            'nim_nip' => 'F1G199999',
            'phone' => '0812000000',
        ]);

        $payload = [
            'name' => 'Pengguna Percobaan Diperbarui',
            'email' => 'percobaan.update@uho.ac.id',
            'nim_nip' => 'F1G188888',
            'phone' => '0812999999',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.pengguna.update', $targetUser->id), $payload);

        $response->assertRedirect(route('admin.kelola-pengguna'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Pengguna Percobaan Diperbarui',
            'email' => 'percobaan.update@uho.ac.id',
            'nim_nip' => 'F1G188888',
            'phone' => '0812999999',
        ]);
    }

    public function test_admin_berhasil_mengubah_role_pengguna(): void
    {
        $targetUser = User::create([
            'name' => 'Calon Dosen',
            'email' => 'calon.dosen@uho.ac.id',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.pengguna.update-role', $targetUser->id), [
            'role' => 'dosen',
        ]);

        $response->assertRedirect(route('admin.kelola-pengguna'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'role' => 'dosen',
        ]);
    }

    public function test_admin_tidak_dapat_menurunkan_role_akunnya_sendiri(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.pengguna.update-role', $this->admin->id), [
            'role' => 'mahasiswa',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('admin', $this->admin->fresh()->role);
    }

    public function test_admin_tidak_dapat_menghapus_akunnya_sendiri(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.pengguna.destroy', $this->admin->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_berhasil_menghapus_pengguna_tanpa_transaksi_aktif(): void
    {
        $targetUser = User::create([
            'name' => 'User Hapus',
            'email' => 'user.hapus@uho.ac.id',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.pengguna.destroy', $targetUser->id));

        $response->assertRedirect(route('admin.kelola-pengguna'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    public function test_admin_ditolak_menghapus_pengguna_dengan_peminjaman_aktif(): void
    {
        $barang = Facility::where('type', 'barang')->first();

        $activeUser = User::create([
            'name' => 'Peminjam Aktif',
            'email' => 'peminjam.aktif@uho.ac.id',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
        ]);

        BorrowRequest::create([
            'code' => 'REQ-ACTIVE-USER-01',
            'user_id' => $activeUser->id,
            'facility_id' => $barang->id,
            'type' => 'barang',
            'borrow_date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'purpose' => 'Keperluan Riset',
            'status' => 'dipinjam',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.pengguna.destroy', $activeUser->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $activeUser->id]);
    }
}
