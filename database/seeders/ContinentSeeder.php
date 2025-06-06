<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContinentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run()
    {
        $continents = [
            ['name' => 'Africa', 'code' => 'AFR'],
            ['name' => 'Antarctica', 'code' => 'ANT'],
            ['name' => 'Asia', 'code' => 'ASI'],
            ['name' => 'Europe', 'code' => 'EUR'],
            ['name' => 'North America', 'code' => 'NAM'],
            ['name' => 'Oceania', 'code' => 'OCE'],
            ['name' => 'South America', 'code' => 'SAM'],
        ];

        DB::table('continents')->insert($continents);
    }
}
