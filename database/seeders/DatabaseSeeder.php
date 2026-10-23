<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\Participant::firstOrCreate(
            ['national_id' => 'V-20123456'],
            [
                'bib_number' => '5K-0001',
                'bib_sequence' => 1,
                'first_name' => 'Carlos',
                'last_name' => 'Gómez',
                'national_id_clean' => '20123456',
                'phone' => '0414-1234567',
            ]
        );

        \App\Models\Participant::firstOrCreate(
            ['national_id' => 'V-24789012'],
            [
                'bib_number' => '5K-0002',
                'bib_sequence' => 2,
                'first_name' => 'María',
                'last_name' => 'Rodríguez',
                'national_id_clean' => '24789012',
                'phone' => '0412-9876543',
            ]
        );
    }
}
