<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Wallet',
                'icon' => null,
                'status' => true,
            ],
            [
                'name' => 'Razorpay',
                'icon' => null,
                'status' => true,
            ],
            [
                'name' => 'Stripe',
                'icon' => null,
                'status' => true,
            ],
            [
                'name' => 'Paypal',
                'icon' => null,
                'status' => true,
            ],
            [
                'name' => 'Instamojo',
                'icon' => null,
                'status' => true,
            ],
        ];
        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(
                ['name' => $method['name']],
                [
                    'icon' => $method['icon'],
                    'status' => $method['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
