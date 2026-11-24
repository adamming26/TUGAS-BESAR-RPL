<?php

namespace App\Services;

use App\Models\BorrowRequest;
use App\Models\QrToken;
use Carbon\Carbon;
use Illuminate\Support\Str;

class QRCodeService
{
    /**
     * Masa berlaku token dalam menit (standar: 15 menit sesuai koordinasi M5 & M6).
     */
    public const VALIDITY_MINUTES = 15;

    /**
     * Format Token:
     * Penyerahan:   QR-SERAH-{12 Karakter Alfanumerik}
     * Pengembalian: QR-KEMBALI-{12 Karakter Alfanumerik}
     */
    public const PREFIX_PENYERAHAN = 'QR-SERAH-';
    public const PREFIX_PENGEMBALIAN = 'QR-KEMBALI-';

    /**
     * Terbitkan QR token untuk penyerahan atau pengembalian.
     * Masa berlaku: 15 menit.
     */
    public function generateToken(BorrowRequest $borrowRequest, string $type = 'penyerahan'): QrToken
    {
        // Nonaktifkan token bertipe sama yang belum digunakan
        QrToken::where('borrow_request_id', $borrowRequest->id)
            ->where('type', $type)
            ->where('is_used', false)
            ->delete();

        $prefix = $type === 'penyerahan' ? self::PREFIX_PENYERAHAN : self::PREFIX_PENGEMBALIAN;
        $tokenString = $prefix . Str::upper(Str::random(12));

        return QrToken::create([
            'borrow_request_id' => $borrowRequest->id,
            'token' => $tokenString,
            'type' => $type,
            'expires_at' => Carbon::now()->addMinutes(self::VALIDITY_MINUTES),
            'is_used' => false,
        ]);
    }

    /**
     * Validasi token QR yang dipindai Dosen.
     * Mengembalikan array:
     * [
     *   'valid'   => bool,
     *   'status'  => 'valid' | 'expired' | 'already_used' | 'not_found',
     *   'message' => string,
     *   'qrToken' => QrToken|null
     * ]
     */
    public function validateToken(string $token): array
    {
        $qrToken = QrToken::with(['borrowRequest.user', 'borrowRequest.facility', 'borrowRequest.items.facility'])
            ->where('token', trim($token))
            ->first();

        if (!$qrToken) {
            return [
                'valid' => false,
                'status' => 'not_found',
                'message' => 'QR Code tidak ditemukan dalam sistem.',
                'qrToken' => null,
            ];
        }

        if ($qrToken->is_used) {
            return [
                'valid' => false,
                'status' => 'already_used',
                'message' => 'QR Code sudah pernah digunakan.',
                'qrToken' => $qrToken,
            ];
        }

        if (Carbon::now()->isAfter($qrToken->expires_at)) {
            return [
                'valid' => false,
                'status' => 'expired',
                'message' => 'Masa berlaku 15 menit sudah habis. Silakan buat ulang QR Code.',
                'qrToken' => $qrToken,
            ];
        }

        return [
            'valid' => true,
            'status' => 'valid',
            'message' => 'QR Code valid dan siap dikonfirmasi.',
            'qrToken' => $qrToken,
        ];
    }

    /**
     * Tandai token sebagai telah digunakan.
     */
    public function markAsUsed(QrToken $qrToken): bool
    {
        return $qrToken->update([
            'is_used' => true,
            'used_at' => Carbon::now(),
        ]);
    }

    /**
     * Hitung sisa detik masa berlaku QR.
     */
    public function getRemainingSeconds(QrToken $qrToken): int
    {
        $now = Carbon::now();
        if ($now->isAfter($qrToken->expires_at)) {
            return 0;
        }

        return $now->diffInSeconds($qrToken->expires_at);
    }
}
