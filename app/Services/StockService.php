<?php

namespace App\Services;

use App\Models\Facility;

class StockService
{
    /**
     * Mengurangi stok fasilitas barang saat dipinjam / diserahkan.
     * Kontrak: StockService::reduce(int $facilityId, int $qty): bool
     */
    public static function reduce(int $facilityId, int $qty): bool
    {
        $facility = Facility::find($facilityId);

        if (!$facility || $facility->type !== 'barang') {
            return false;
        }

        if ($facility->available_stock < $qty) {
            return false;
        }

        $facility->decrement('available_stock', $qty);
        return true;
    }

    /**
     * Menambah kembali stok fasilitas barang saat dikembalikan dalam kondisi baik.
     * Kontrak: StockService::restore(int $facilityId, int $goodQty): void
     */
    public static function restore(int $facilityId, int $goodQty): void
    {
        $facility = Facility::find($facilityId);

        if (!$facility || $facility->type !== 'barang' || $goodQty <= 0) {
            return;
        }

        $newAvailable = min($facility->total_stock, $facility->available_stock + $goodQty);
        $facility->update(['available_stock' => $newAvailable]);
    }

    /**
     * Memeriksa apakah stok fasilitas barang mencukupi.
     */
    public function hasSufficientStock(Facility $facility, int $quantity): bool
    {
        if ($facility->type !== 'barang') {
            return true;
        }

        return $facility->available_stock >= $quantity;
    }

    /**
     * Alias method objek: Mengurangi stok.
     */
    public function decreaseStock(Facility $facility, int $quantity): bool
    {
        return self::reduce($facility->id, $quantity);
    }

    /**
     * Alias method objek: Menambah kembali stok.
     */
    public function increaseStock(Facility $facility, int $quantity): bool
    {
        self::restore($facility->id, $quantity);
        return true;
    }
}
