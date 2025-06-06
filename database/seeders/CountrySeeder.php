<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $continents = DB::table('continents')->pluck('id', 'code');
        $countries = [
            // Africa
            [
                'name' => 'Zambia',
                'code' => 'ZMB',
                'sample_phone' => '260970000000',
                'phone_number_length' => 12,
                'continent_id' => $continents['AFR'],
                'phone_code' => '+260',
                'capital' => 'Lusaka',
                'currency' => 'Zambian Kwacha',
                'currency_code' => 'ZMW',
                'flag' => 'https://flagcdn.com/zm.svg',
                'description' => 'Landlocked country in southern Africa',
                'is_active' => true,
                'is_verified' => true,
            ],
            [
                'name' => 'South Africa',
                'code' => 'ZAF',
                'sample_phone' => '27820000000',
                'phone_number_length' => 11,
                'continent_id' => $continents['AFR'],
                'phone_code' => '+27',
                'capital' => 'Pretoria',
                'currency' => 'South African Rand',
                'currency_code' => 'ZAR',
                'flag' => 'https://flagcdn.com/za.svg',
                'description' => 'Diverse country at Africa\'s southern tip',
                'is_active' => true,
                'is_verified' => true,
            ],
            [
                'name' => 'Nigeria',
                'code' => 'NGA',
                'sample_phone' => '2348020000000',
                'phone_number_length' => 13,
                'continent_id' => $continents['AFR'],
                'phone_code' => '+234',
                'capital' => 'Abuja',
                'currency' => 'Naira',
                'currency_code' => 'NGN',
                'flag' => 'https://flagcdn.com/ng.svg',
                'description' => 'Most populous country in Africa',
                'is_active' => true,
                'is_verified' => true,
            ],

            // Asia
            [
                'name' => 'China',
                'code' => 'CHN',
                'sample_phone' => '8613800000000',
                'phone_number_length' => 13,
                'continent_id' => $continents['ASI'],
                'phone_code' => '+86',
                'capital' => 'Beijing',
                'currency' => 'Yuan Renminbi',
                'currency_code' => 'CNY',
                'flag' => 'https://flagcdn.com/cn.svg',
                'description' => 'World\'s most populous country',
                'is_active' => true,
                'is_verified' => true,
            ],
            [
                'name' => 'India',
                'code' => 'IND',
                'sample_phone' => '919876543210',
                'phone_number_length' => 12,
                'continent_id' => $continents['ASI'],
                'phone_code' => '+91',
                'capital' => 'New Delhi',
                'currency' => 'Indian Rupee',
                'currency_code' => 'INR',
                'flag' => 'https://flagcdn.com/in.svg',
                'description' => 'Second most populous country',
                'is_active' => true,
                'is_verified' => true,
            ],

            // Europe
            [
                'name' => 'United Kingdom',
                'code' => 'GBR',
                'sample_phone' => '447900000000',
                'phone_number_length' => 12,
                'continent_id' => $continents['EUR'],
                'phone_code' => '+44',
                'capital' => 'London',
                'currency' => 'British Pound',
                'currency_code' => 'GBP',
                'flag' => 'https://flagcdn.com/gb.svg',
                'description' => 'Island nation in northwestern Europe',
                'is_active' => true,
                'is_verified' => true,
            ],
            [
                'name' => 'Germany',
                'code' => 'DEU',
                'sample_phone' => '4915110000000',
                'phone_number_length' => 13,
                'continent_id' => $continents['EUR'],
                'phone_code' => '+49',
                'capital' => 'Berlin',
                'currency' => 'Euro',
                'currency_code' => 'EUR',
                'flag' => 'https://flagcdn.com/de.svg',
                'description' => 'Federal parliamentary republic in Europe',
                'is_active' => true,
                'is_verified' => true,
            ],

            // North America
            [
                'name' => 'United States',
                'code' => 'USA',
                'sample_phone' => '12025550000',
                'phone_number_length' => 11,
                'continent_id' => $continents['NAM'],
                'phone_code' => '+1',
                'capital' => 'Washington, D.C.',
                'currency' => 'US Dollar',
                'currency_code' => 'USD',
                'flag' => 'https://flagcdn.com/us.svg',
                'description' => 'Federal republic of 50 states',
                'is_active' => true,
                'is_verified' => true,
            ],
            [
                'name' => 'Canada',
                'code' => 'CAN',
                'sample_phone' => '16135550000',
                'phone_number_length' => 11,
                'continent_id' => $continents['NAM'],
                'phone_code' => '+1',
                'capital' => 'Ottawa',
                'currency' => 'Canadian Dollar',
                'currency_code' => 'CAD',
                'flag' => 'https://flagcdn.com/ca.svg',
                'description' => 'Northern North American country',
                'is_active' => true,
                'is_verified' => true,
            ],

            // South America
            [
                'name' => 'Brazil',
                'code' => 'BRA',
                'sample_phone' => '5521987654321',
                'phone_number_length' => 13,
                'continent_id' => $continents['SAM'],
                'phone_code' => '+55',
                'capital' => 'Brasília',
                'currency' => 'Brazilian Real',
                'currency_code' => 'BRL',
                'flag' => 'https://flagcdn.com/br.svg',
                'description' => 'Largest country in South America',
                'is_active' => true,
                'is_verified' => true,
            ],

            // Oceania
            [
                'name' => 'Australia',
                'code' => 'AUS',
                'sample_phone' => '61412345678',
                'phone_number_length' => 11,
                'continent_id' => $continents['OCE'],
                'phone_code' => '+61',
                'capital' => 'Canberra',
                'currency' => 'Australian Dollar',
                'currency_code' => 'AUD',
                'flag' => 'https://flagcdn.com/au.svg',
                'description' => 'Country comprising the mainland of the Australian continent',
                'is_active' => true,
                'is_verified' => true,
            ],
        ];

        DB::table('countries')->insert($countries);
    }
}
