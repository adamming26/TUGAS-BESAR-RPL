<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrow_request_id',
        'lecturer_id',
        'return_date',
        'returned_quantity',
        'condition', // Baik, Rusak Ringan, Rusak Berat
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'datetime',
            'returned_quantity' => 'integer',
        ];
    }

    public function borrowRequest(): BelongsTo
    {
        return $this->belongsTo(BorrowRequest::class, 'borrow_request_id');
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->lecturer();
    }
}
