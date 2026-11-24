<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BorrowRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'type', // barang, ruangan
        'facility_id',
        'borrow_date',
        'start_time',
        'end_time',
        'duration_hours',
        'purpose',
        'participant_count',
        'status', // menunggu, disetujui, dipinjam, dikembalikan, ditolak, selesai
        'reject_reason',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'borrow_date' => 'date',
            'duration_hours' => 'integer',
            'participant_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'facility_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BorrowItem::class, 'borrow_request_id');
    }

    public function borrowItems(): HasMany
    {
        return $this->items();
    }

    public function qrTokens(): HasMany
    {
        return $this->hasMany(QrToken::class, 'borrow_request_id');
    }

    public function activeQrToken(): HasOne
    {
        return $this->hasOne(QrToken::class, 'borrow_request_id')
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latestOfMany();
    }

    public function returnRecord(): HasOne
    {
        return $this->hasOne(ReturnRecord::class, 'borrow_request_id');
    }

    public function scheduleLogs(): HasMany
    {
        return $this->hasMany(ScheduleLog::class, 'borrow_request_id');
    }
}
