<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'borrow_request_id',
        'date',
        'start_time',
        'end_time',
        'status', // terjadwal, berlangsung, selesai, dibatalkan
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'facility_id');
    }

    public function borrowRequest(): BelongsTo
    {
        return $this->belongsTo(BorrowRequest::class, 'borrow_request_id');
    }
}
