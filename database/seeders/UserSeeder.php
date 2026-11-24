<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Admin
        User::updateOrCreate(
            ['email' => 'admin@uho.ac.id'],
            [
                'name' => 'Administrator SIPAFKA',
                'nim_nip' => 'ADM-ILKOM-01',
                'role' => 'admin',
                'password' => Hash::make('password'),
                'phone' => '081234567890',
            ]
        );

        // 2. Dosen
        User::updateOrCreate(
            ['email' => 'dosen@uho.ac.id'],
            [
                'name' => 'Dosen Pembimbing, S.Kom., M.Cs.',
                'nim_nip' => '198501012010121001',
                'role' => 'dosen',
                'password' => Hash::make('password'),
                'phone' => '081234567891',
            ]
        );

        // 3. Mahasiswa (Adam Muzakir Eni - NIM: F1G125001)
        User::updateOrCreate(
            ['email' => 'mahasiswa@uho.ac.id'],
            [
                'name' => 'Adam Muzakir Eni',
                'nim_nip' => 'F1G125001',
                'role' => 'mahasiswa',
                'password' => Hash::make('password'),
                'phone' => '081234567892',
            ]
        );
    }
}
