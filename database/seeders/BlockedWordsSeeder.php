<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class BlockedWordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $response = Http::get('https://raw.githubusercontent.com/LDNOOBW/List-of-Dirty-Naughty-Obscene-and-Otherwise-Bad-Words/master/en');

        if (!$response->ok()) {
            throw new \Exception("Failed to fetch blocked words.");
        }

        $words = explode("\n", $response->body());

        $insert = [];
        foreach ($words as $word) {
            $cleaned = trim(strtolower($word));
            if (!empty($cleaned)) {
                $insert[] = ['word' => $cleaned];
            }
        }

        DB::table('blocked_words')->insertOrIgnore($insert);

        $this->command->info(count($insert) . " English blocked words seeded.");
    }
}
