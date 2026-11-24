<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman masuk (Figma Page 2).
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Proses autentikasi masuk (Bisa menggunakan NIM, NIP, atau Email).
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'identity' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'identity.required' => 'Identitas akun (NIM / NIP / Email) wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $identity = $credentials['identity'];
        $fieldType = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'nim_nip';

        if (Auth::attempt([$fieldType => $identity, 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->role === 'admin') {
                $target = \Illuminate\Support\Facades\Route::has('admin.dashboard') ? route('admin.dashboard') : route('home');
                return redirect()->intended($target)->with('success', 'Selamat datang kembali, Administrator!');
            } elseif ($user->role === 'dosen') {
                $target = \Illuminate\Support\Facades\Route::has('dosen.dashboard') ? route('dosen.dashboard') : route('home');
                return redirect()->intended($target)->with('success', 'Selamat datang kembali, Bapak/Ibu Dosen!');
            } else {
                $target = \Illuminate\Support\Facades\Route::has('mahasiswa.dashboard') ? route('mahasiswa.dashboard') : route('home');
                return redirect()->intended($target)->with('success', 'Selamat datang di SIPAFKA, ' . $user->name . '!');
            }
        }

        return back()->withErrors([
            'identity' => 'Kombinasi identitas akun dan kata sandi tidak cocok dengan data kami.',
        ])->onlyInput('identity');
    }

    /**
     * Tampilkan halaman pendaftaran akun mahasiswa (Figma Page 3).
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Proses pendaftaran akun mahasiswa baru.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:50', 'unique:users,nim_nip'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nim.required' => 'Nomor Induk Mahasiswa (NIM) wajib diisi.',
            'nim.unique' => 'NIM ini sudah terdaftar sebelumnya.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'nim_nip' => $validated['nim'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'mahasiswa',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $target = \Illuminate\Support\Facades\Route::has('mahasiswa.dashboard') ? 'mahasiswa.dashboard' : 'home';
        return redirect()->route($target)->with('success', 'Pendaftaran akun berhasil! Selamat datang di SIPAFKA.');
    }

    /**
     * Keluar dari sesi aplikasi.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Halaman profil pengguna.
     */
    public function showProfile()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    /**
     * Perbarui data profil.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Perbarui kata sandi pengguna.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
