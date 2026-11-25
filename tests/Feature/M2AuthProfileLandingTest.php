<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class M2AuthProfileLandingTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    /**
     * 1. Landing Page & Panduan Penggunaan dapat diakses publik.
     */
    public function test_landing_page_dan_panduan_dapat_diakses_publik(): void
    {
        $landing = $this->get(route('home'));
        $landing->assertStatus(200);
        $landing->assertSee('Pinjam Fasilitas Prodi dengan Mudah');
        $landing->assertSee('Laboratorium Komputer');
        $landing->assertSee('Fasilitas Barang');
        $landing->assertSee('Masuk');
        $landing->assertSee('Daftar');

        $panduan = $this->get(route('panduan.index'));
        $panduan->assertStatus(200);
        $panduan->assertSee('Panduan Penggunaan SIPAFKA');
    }

    /**
     * 2. Register berhasil dengan role default mahasiswa dan password ter-hash.
     */
    public function test_register_berhasil_dengan_role_default_mahasiswa_dan_password_terhash(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Marcelo Huggie Robin E.A.',
            'nim' => 'F1G125038',
            'email' => 'marcelo@uho.ac.id',
            'jenjang' => 'S1',
            'phone' => '081234567899',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'admin', // Upaya manipulasi role harus diabaikan
        ]);

        $expectedRoute = Route::has('mahasiswa.dashboard') ? 'mahasiswa.dashboard' : 'profile.index';
        $response->assertRedirect(route($expectedRoute));
        $response->assertSessionHas('success');

        $user = User::where('email', 'marcelo@uho.ac.id')->first();
        $this->assertNotNull($user);
        $this->assertEquals('mahasiswa', $user->role);
        $this->assertEquals('F1G125038', $user->nim_nip);
        $this->assertEquals('081234567899', $user->phone);
        $this->assertNotEquals('rahasia123', $user->password);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * 3. Validasi input register (email unik, NIM unik, konfirmasi password, panjang minimal).
     */
    public function test_validasi_input_register(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => '',
            'nim' => 'F1G125001', // Sudah dipakai seeder
            'email' => 'mahasiswa@uho.ac.id', // Sudah dipakai seeder
            'jenjang' => 'INVALID',
            'password' => '123',
            'password_confirmation' => 'berbeda',
        ]);

        $response->assertSessionHasErrors(['name', 'nim', 'email', 'jenjang', 'password']);
        $this->assertGuest();
    }

    /**
     * 4. Login berhasil (dengan Email maupun NIM) dan pengalihan berdasarkan role.
     */
    public function test_login_berhasil_dan_pengalihan_berdasarkan_role(): void
    {
        // Admin -> Dashboard Admin milik M1
        $adminResponse = $this->post(route('login.post'), [
            'identity' => 'admin@uho.ac.id',
            'password' => 'password',
        ]);
        $adminResponse->assertRedirect(route('admin.dashboard'));
        $this->post(route('logout'));

        // Mahasiswa login dengan NIM -> Dashboard Mahasiswa (M4) atau pengalihan aman ke Profil
        $mahasiswaResponse = $this->post(route('login.post'), [
            'identity' => 'F1G125001',
            'password' => 'password',
        ]);
        $expectedMhs = Route::has('mahasiswa.dashboard') ? 'mahasiswa.dashboard' : 'profile.index';
        $mahasiswaResponse->assertRedirect(route($expectedMhs));
        if (!Route::has('mahasiswa.dashboard')) {
            $mahasiswaResponse->assertSessionHas('warning');
        }
        $this->post(route('logout'));

        // Dosen login dengan NIP -> Dashboard Dosen (M5) atau pengalihan aman ke Profil
        $dosenResponse = $this->post(route('login.post'), [
            'identity' => '198501012010121001',
            'password' => 'password',
        ]);
        $expectedDosen = Route::has('dosen.dashboard') ? 'dosen.dashboard' : 'profile.index';
        $dosenResponse->assertRedirect(route($expectedDosen));
        if (!Route::has('dosen.dashboard')) {
            $dosenResponse->assertSessionHas('warning');
        }
    }

    /**
     * 5. Login gagal menampilkan error dan tidak mengautentikasi pengguna.
     */
    public function test_login_gagal_dengan_kredensial_salah(): void
    {
        $response = $this->from(route('login'))->post(route('login.post'), [
            'identity' => 'mahasiswa@uho.ac.id',
            'password' => 'salah-banget',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['identity']);
        $this->assertGuest();
    }

    /**
     * 6. Validasi input login kosong.
     */
    public function test_validasi_input_login_kosong(): void
    {
        $response = $this->post(route('login.post'), [
            'identity' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['identity', 'password']);
        $this->assertGuest();
    }

    /**
     * 7. Forgot Password mengirim notifikasi reset dan menolak email tidak terdaftar.
     */
    public function test_forgot_password_mengirim_tautan_dan_menolak_email_tidak_terdaftar(): void
    {
        Notification::fake();

        $user = User::where('email', 'mahasiswa@uho.ac.id')->firstOrFail();

        $successResponse = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);
        $successResponse->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);

        $failResponse = $this->post(route('password.email'), [
            'email' => 'tidak-ada@uho.ac.id',
        ]);
        $failResponse->assertSessionHasErrors(['email']);
    }

    /**
     * 8. Reset Password memvalidasi token, mengubah kata sandi, dan menolak token tidak valid.
     */
    public function test_reset_password_memvalidasi_token_dan_mengubah_kata_sandi(): void
    {
        $user = User::where('email', 'mahasiswa@uho.ac.id')->firstOrFail();
        $token = Password::broker()->createToken($user);

        // Halaman form reset dapat diakses
        $formResponse = $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]));
        $formResponse->assertStatus(200);
        $formResponse->assertSee('Atur ulang kata sandi');

        // Token palsu ditolak
        $invalidResponse = $this->post(route('password.update'), [
            'token' => 'token-palsu-tidak-valid',
            'email' => $user->email,
            'password' => 'SandiBaru123',
            'password_confirmation' => 'SandiBaru123',
        ]);
        $invalidResponse->assertSessionHasErrors(['email']);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));

        // Token valid berhasil mereset kata sandi
        $validResponse = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'SandiBaru123',
            'password_confirmation' => 'SandiBaru123',
        ]);
        $validResponse->assertRedirect(route('login'));
        $validResponse->assertSessionHas('status');
        $this->assertTrue(Hash::check('SandiBaru123', $user->fresh()->password));

        // Token yang sudah digunakan tidak dapat dipakai kembali
        $reuseResponse = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'SandiLain456',
            'password_confirmation' => 'SandiLain456',
        ]);
        $reuseResponse->assertSessionHasErrors(['email']);
    }

    /**
     * 9. Profil & Edit Profil hanya dapat mengubah data milik pengguna sendiri.
     */
    public function test_pengguna_dapat_melihat_dan_mengubah_profil_serta_kata_sandi_miliknya_sendiri(): void
    {
        $mahasiswa = User::where('email', 'mahasiswa@uho.ac.id')->firstOrFail();
        $dosen = User::where('email', 'dosen@uho.ac.id')->firstOrFail();

        $profilePage = $this->actingAs($mahasiswa)->get(route('profile.index'));
        $profilePage->assertStatus(200);
        $profilePage->assertSee($mahasiswa->name);

        $editPage = $this->actingAs($mahasiswa)->get(route('profile.edit'));
        $editPage->assertStatus(200);
        $editPage->assertSee('Edit Profil');

        // Update profil sendiri (upaya ubah role & user_id lain diabaikan)
        $updateResponse = $this->actingAs($mahasiswa)->put(route('profile.update'), [
            'user_id' => $dosen->id,
            'name' => 'Mahasiswa Diperbarui',
            'email' => 'mahasiswa.baru@uho.ac.id',
            'phone' => '081299998888',
            'role' => 'admin',
            'nim_nip' => 'ADM-HACK',
        ]);

        $updateResponse->assertRedirect(route('profile.index'));
        $updateResponse->assertSessionHas('success');

        $mahasiswa->refresh();
        $dosen->refresh();

        $this->assertEquals('Mahasiswa Diperbarui', $mahasiswa->name);
        $this->assertEquals('mahasiswa.baru@uho.ac.id', $mahasiswa->email);
        $this->assertEquals('081299998888', $mahasiswa->phone);
        $this->assertEquals('mahasiswa', $mahasiswa->role);
        $this->assertEquals('F1G125001', $mahasiswa->nim_nip);
        // Pastikan akun dosen tidak tersentuh
        $this->assertEquals('dosen@uho.ac.id', $dosen->email);

        // Ubah kata sandi gagal jika current_password salah
        $badPwd = $this->actingAs($mahasiswa)->put(route('profile.password'), [
            'current_password' => 'salah',
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ]);
        $badPwd->assertSessionHasErrors(['current_password']);

        // Ubah kata sandi berhasil
        $goodPwd = $this->actingAs($mahasiswa)->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ]);
        $goodPwd->assertRedirect(route('profile.edit'));
        $goodPwd->assertSessionHas('success');
        $this->assertTrue(Hash::check('PasswordBaru123', $mahasiswa->fresh()->password));
    }

    /**
     * 10. Pembatasan akses untuk tamu (guest) dan peran berbeda.
     */
    public function test_pembatasan_akses_profil_dan_role_middleware(): void
    {
        // Guest tidak boleh mengakses halaman profil
        $this->get(route('profile.index'))->assertRedirect(route('login'));
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->put(route('profile.update'), ['name' => 'Anonim'])->assertRedirect(route('login'));

        // Pengguna terautentikasi tidak boleh mengakses halaman guest (/login, /register)
        $mahasiswa = User::where('email', 'mahasiswa@uho.ac.id')->firstOrFail();
        $this->actingAs($mahasiswa)->get(route('login'))->assertRedirect();
        $this->actingAs($mahasiswa)->get(route('register'))->assertRedirect();

        // Mahasiswa tidak dapat mengakses Dashboard Admin (RoleMiddleware M1)
        $this->actingAs($mahasiswa)->get(route('admin.dashboard'))->assertStatus(403);
    }
}
