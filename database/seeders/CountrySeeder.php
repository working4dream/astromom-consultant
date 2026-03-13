<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Country::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $countries = [
            ['name' => 'Australia'],
            ['name' => 'Canada'],
            ['name' => 'India'],
            ['name' => 'New Zealand'],
            ['name' => 'United Arab Emirates'],
            ['name' => 'United Kingdom'],
            ['name' => 'United States'],
        ];
        foreach ($countries as $country) {
            \DB::table('countries')->insert(
                [
                    'name' => $country['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
