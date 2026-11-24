<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotifikasiService
{
    /**
     * Mengirim notifikasi ke pengguna.
     * Kontrak: NotifikasiService::kirim(User $to, string $title, string $msg, ?string $url = null): void
     */
    public static function kirim(User $to, string $title, string $msg, ?string $url = null): void
    {
        // Catat pengiriman notifikasi ke log sistem
        Log::info("SIPAFKA Notification sent to [ID: {$to->id}, Role: {$to->role}, Email: {$to->email}]: {$title} - {$msg}" . ($url ? " [URL: {$url}]" : ""));

        // Jika penerima adalah user yang sedang aktif pada session, set flash info
        if (Auth::check() && Auth::id() === $to->id) {
            session()->flash('info', "{$title}: {$msg}");
        }
    }

    /**
     * Set notifikasi flash session sukses.
     */
    public function success(string $message): void
    {
        session()->flash('success', $message);
    }

    /**
     * Set notifikasi flash session error.
     */
    public function error(string $message): void
    {
        session()->flash('error', $message);
    }

    /**
     * Set notifikasi flash session peringatan / info.
     */
    public function warning(string $message): void
    {
        session()->flash('warning', $message);
    }

    /**
     * Format badge status peminjaman.
     */
    public function getStatusBadge(string $status): array
    {
        return match ($status) {
            'menunggu' => [
                'label' => 'Menunggu Dosen',
                'bg' => 'bg-amber-100',
                'text' => 'text-amber-800',
            ],
            'disetujui' => [
                'label' => 'Disetujui',
                'bg' => 'bg-emerald-100',
                'text' => 'text-emerald-800',
            ],
            'dipinjam' => [
                'label' => 'Dipinjam',
                'bg' => 'bg-blue-100',
                'text' => 'text-blue-800',
            ],
            'dikembalikan', 'selesai' => [
                'label' => 'Selesai / Dikembalikan',
                'bg' => 'bg-teal-100',
                'text' => 'text-teal-800',
            ],
            'ditolak' => [
                'label' => 'Ditolak',
                'bg' => 'bg-rose-100',
                'text' => 'text-rose-800',
            ],
            default => [
                'label' => ucfirst($status),
                'bg' => 'bg-slate-100',
                'text' => 'text-slate-800',
            ],
        };
    }
}
