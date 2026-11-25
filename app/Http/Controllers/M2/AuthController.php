<?php

namespace App\Http\Controllers\M2;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login (PDF Page 2).
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Proses autentikasi pengguna menggunakan Email, NIM, atau NIP.
     */
    public function login(Request $request): RedirectResponse
    {
        if (!$request->filled('identity') && $request->filled('email')) {
            $request->merge(['identity' => $request->input('email')]);
        }

        $credentials = $request->validate([
            'identity' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'identity.required' => 'Identitas akun (NIM / NIP / Email) wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $identity = trim($credentials['identity']);
        $fieldType = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'nim_nip';

        if (Auth::attempt([$fieldType => $identity, 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();

            return $this->redirectAfterLogin($user);
        }

        return back()
            ->withErrors([
                'identity' => 'Kombinasi NIM / NIP / Email dan kata sandi tidak cocok dengan data kami.',
            ])
            ->onlyInput('identity');
    }

    /**
     * Tampilkan halaman registrasi akun mahasiswa (PDF Page 3).
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Proses pendaftaran akun baru (role default: mahasiswa).
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:50', 'unique:users,nim_nip'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'jenjang' => ['required', 'string', 'in:D3,S1,S2,S3'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nim.required' => 'Nomor Induk Mahasiswa (NIM) wajib diisi.',
            'nim.unique' => 'NIM ini sudah terdaftar pada akun lain.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah digunakan.',
            'jenjang.required' => 'Jenjang pendidikan wajib dipilih.',
            'jenjang.in' => 'Pilihan jenjang pendidikan tidak valid.',
            'phone.regex' => 'Format nomor HP hanya boleh berisi angka, spasi, +, -, atau tanda kurung.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        // Catatan integrasi: kolom `jenjang` divalidasi di form, namun belum tersedia
        // pada tabel `users` milik M1 sehingga tidak disimpan ke database tanpa koordinasi M1.
        $user = User::create([
            'name' => trim($validated['name']),
            'nim_nip' => trim($validated['nim']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => isset($validated['phone']) && trim($validated['phone']) !== '' ? trim($validated['phone']) : null,
            'password' => Hash::make($validated['password']),
            'role' => 'mahasiswa',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        if (Route::has('mahasiswa.dashboard')) {
            return redirect()
                ->route('mahasiswa.dashboard')
                ->with('success', 'Pendaftaran akun berhasil! Selamat datang di SIPAFKA, ' . $user->name . '.');
        }

        return redirect()
            ->route('profile.index')
            ->with('success', 'Pendaftaran akun berhasil! Selamat datang di SIPAFKA, ' . $user->name . '.')
            ->with('warning', 'Modul Dashboard Mahasiswa (M4) belum tersedia. Anda dialihkan ke halaman profil.');
    }

    /**
     * Keluar dari sesi aplikasi.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Pengalihan aman sesuai peran pengguna setelah login.
     */
    protected function redirectAfterLogin(User $user): RedirectResponse
    {
        if ($user->role === 'admin') {
            if (Route::has('admin.dashboard')) {
                return redirect()
                    ->intended(route('admin.dashboard'))
                    ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
            }

            return redirect()
                ->route('profile.index')
                ->with('warning', 'Login berhasil. Modul Dashboard Admin (M1) belum tersedia, Anda dialihkan ke halaman profil.');
        }

        if ($user->role === 'dosen') {
            if (Route::has('dosen.dashboard')) {
                return redirect()
                    ->intended(route('dosen.dashboard'))
                    ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
            }

            return redirect()
                ->route('profile.index')
                ->with('warning', 'Login berhasil. Modul Dashboard Dosen (M5) belum tersedia, Anda dialihkan ke halaman profil.');
        }

        if (Route::has('mahasiswa.dashboard')) {
            return redirect()
                ->intended(route('mahasiswa.dashboard'))
                ->with('success', 'Selamat datang di SIPAFKA, ' . $user->name . '!');
        }

        return redirect()
            ->route('profile.index')
            ->with('warning', 'Login berhasil. Modul Dashboard Mahasiswa (M4) belum tersedia, Anda dialihkan ke halaman profil.');
    }
}
