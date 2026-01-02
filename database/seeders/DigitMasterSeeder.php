<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DigitMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('digit_master')->insert([
            [
                'name' => 'A',
                'type' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'B',
                'type' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'C',
                'type' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'AB',
                'type' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BC',
                'type' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'AC',
                'type' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ABC',
                'type' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'XABC',
                'type' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ABC (BC)',
                'type' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ABC (C)',
                'type' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'XABC (ABC)',
                'type' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
