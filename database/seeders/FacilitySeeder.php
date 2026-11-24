<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facilities = [
            [
                'code' => 'BRG-01',
                'name' => 'Proyektor',
                'type' => 'barang',
                'description' => 'Proyektor LCD Epson resolusi tinggi untuk presentasi kuliah/seminar.',
                'total_stock' => 2,
                'available_stock' => 2,
                'capacity' => 0,
                'condition' => 'Baik',
                'is_active' => true,
            ],
            [
                'code' => 'BRG-02',
                'name' => 'Kabel HDMI',
                'type' => 'barang',
                'description' => 'Kabel HDMI panjang 3 meter standar gold-plated.',
                'total_stock' => 2,
                'available_stock' => 2,
                'capacity' => 0,
                'condition' => 'Baik',
                'is_active' => true,
            ],
            [
                'code' => 'BRG-03',
                'name' => 'Kabel Terminal Cabang',
                'type' => 'barang',
                'description' => 'Stop kontak kabel ekstensi 4 lubang panjang 5 meter.',
                'total_stock' => 5,
                'available_stock' => 5,
                'capacity' => 0,
                'condition' => 'Baik',
                'is_active' => true,
            ],
            [
                'code' => 'LAB-01',
                'name' => 'Lab Komputer Basis Data',
                'type' => 'ruangan',
                'description' => 'Laboratorium Komputer Basis Data lantai 2 Gedung FMIPA.',
                'total_stock' => 1,
                'available_stock' => 1,
                'capacity' => 50,
                'condition' => 'Baik',
                'is_active' => true,
            ],
            [
                'code' => 'LAB-02',
                'name' => 'Lab Komputer Unit Teknologi Informasi',
                'type' => 'ruangan',
                'description' => 'Laboratorium Komputer UPT TIK Gedung FMIPA.',
                'total_stock' => 1,
                'available_stock' => 1,
                'capacity' => 50,
                'condition' => 'Baik',
                'is_active' => true,
            ],
        ];

        foreach ($facilities as $facility) {
            Facility::updateOrCreate(
                ['code' => $facility['code']],
                $facility
            );
        }
    }
}
