<?php

namespace App\Http\Controllers\M2;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Tampilkan form lupa kata sandi.
     */
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim tautan reset kata sandi menggunakan Laravel Password Broker.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => 'Alamat email terdaftar wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with(
                'status',
                'Tautan atur ulang kata sandi telah dikirim ke email Anda. Silakan periksa kotak masuk atau folder spam.'
            );
        }

        $errorMessage = match ($status) {
            Password::INVALID_USER => 'Alamat email tersebut tidak terdaftar pada sistem SIPAFKA.',
            Password::RESET_THROTTLED => 'Permintaan terlalu cepat. Harap tunggu beberapa saat sebelum meminta tautan baru.',
            default => 'Tidak dapat mengirim tautan atur ulang kata sandi. Silakan coba kembali.',
        };

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $errorMessage]);
    }

    /**
     * Tampilkan form atur ulang kata sandi dengan token dari tautan email.
     */
    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', old('email', '')),
        ]);
    }

    /**
     * Validasi token dan simpan kata sandi baru secara aman.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'token.required' => 'Token atur ulang kata sandi tidak ditemukan.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('status', 'Kata sandi Anda berhasil diatur ulang. Silakan masuk menggunakan kata sandi baru.');
        }

        $errorMessage = match ($status) {
            Password::INVALID_TOKEN => 'Token atur ulang kata sandi tidak valid atau telah kedaluwarsa.',
            Password::INVALID_USER => 'Alamat email tersebut tidak ditemukan dalam data pengguna kami.',
            default => 'Gagal mengatur ulang kata sandi. Silakan ajukan permintaan baru.',
        };

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $errorMessage]);
    }
}
