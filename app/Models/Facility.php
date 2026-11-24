<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type', // barang, ruangan
        'description',
        'total_stock',
        'available_stock',
        'capacity',
        'condition', // Baik, Rusak Ringan, Rusak Berat
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'total_stock' => 'integer',
            'available_stock' => 'integer',
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function borrowRequests(): HasMany
    {
        return $this->hasMany(BorrowRequest::class, 'facility_id');
    }

    public function borrowItems(): HasMany
    {
        return $this->hasMany(BorrowItem::class, 'facility_id');
    }

    public function scheduleLogs(): HasMany
    {
        return $this->hasMany(ScheduleLog::class, 'facility_id');
    }

    public function isBarang(): bool
    {
        return $this->type === 'barang';
    }

    public function isRuangan(): bool
    {
        return in_array($this->type, ['ruangan', 'lab', 'laboratorium']);
    }

    public function isLab(): bool
    {
        return $this->isRuangan();
    }
}
