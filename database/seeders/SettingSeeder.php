<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Setting::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $methods = [
            [
                'name' => 'simple_numerology_report',
                'data' => 499,
            ],
            [
                'name' => 'exclusive_numerology_report',
                'data' => 1499,
            ],
            [
                'name' => 'gemstone_report',
                'data' => 799,
            ],
            [
                'name' => 'astrologer_min_price',
                'data' => 0,
            ],
            [
                'name' => 'astrologer_max_price',
                'data' => 0,
            ],
            [
                'name' => 'service_types',
                'data' => 'Appointment',
            ],
            [
                'name' => 'languages',
                'data' => 'Hindi,English,Bengali,Telugu,Marathi,Tamil,Urdu,Gujarati,Malayalam,Kannada,Odia,Punjabi,Assamese,Maithili,Sanskrit,Konkani'
                                           
            ],
            [
                'name' => 'video_30_min_price',
                'data' => 999,
            ],
            [
                'name' => 'video_30_max_price',
                'data' => 2999,
            ],
            [
                'name' => 'video_60_min_price',
                'data' => 2999,
            ],
            [
                'name' => 'video_60_max_price',
                'data' => 3999,
            ],
            [
                'name' => 'voice_30_min_price',
                'data' => 999,
            ],
            [
                'name' => 'voice_30_max_price',
                'data' => 1999,
            ],
            [
                'name' => 'voice_60_min_price',
                'data' => 1999,
            ],
            [
                'name' => 'voice_60_max_price',
                'data' => 2999,
            ],
            [
                'name' => 'chat_min_price',
                'data' => 15,
            ],
            [
                'name' => 'chat_max_price',
                'data' => 30,
            ],
            [   
                'name' => 'voice_min_price',
                'data' => 25,
            ],
            [
                'name' => 'voice_max_price',
                'data' => 40,
            ],
            [
                'name' => 'video_min_price',
                'data' => 30,
            ],
            [
                'name' => 'video_max_price',
                'data' => 60,
            ],
            [
                'name' => 'specialization',
                'data' => 'Astrology,Numerology,Vastu'
            ],
            [
                'name' => 'expertise',
                'data' => 'Birth Chart Analysis,Career and Finance Guidance,Name Numerology'
            ],
            [
                'name' => 'keywords',
                'data' => 'Love,Marriage,Child,Business,Name,Vastu,Home,Office'
            ],
            [
                'name' => 'expert_subscription_monthly_price',
                'data' => 999,
            ],
            [
                'name' => 'expert_subscription_annual_price',
                'data' => 9999,
            ],
            [
                'name' => 'expert_subscription_gst_type',
                'data' => 'gst_18',
            ],
        ];
        foreach ($methods as $method) {
            Setting::firstOrCreate(
                ['name' => $method['name']],
                [
                    'name' => $method['name'],
                    'data' => $method['data'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
