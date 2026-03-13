<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'currency_code' => 'USD',
                'symbol' => '$',
                'currency_placement' => 'before',
                'status' => true,
            ],
            [
                'currency_code' => 'EUR',
                'symbol' => '€',
                'currency_placement' => 'before',
                'status' => true,
            ],
            [
                'currency_code' => 'INR',
                'symbol' => '₹',
                'currency_placement' => 'before',
                'status' => true,
            ],
            [
                'currency_code' => 'GBP',
                'symbol' => '£',
                'currency_placement' => 'before',
                'status' => true,
            ],
        ];
        foreach ($currencies as $currency) {
            Currency::firstOrCreate(
                ['currency_code' => $currency['currency_code']],
                [
                    'symbol' => $currency['symbol'],
                    'currency_placement' => $currency['currency_placement'],
                    'status' => $currency['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
