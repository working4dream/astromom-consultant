<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notifications = [
            // For Customer
            [
                'user_id' => 3,
                'title' => "30% Off",
                'subtitle' => "Get 30% off on your first report",
                'badge_title' => "",
                'image' => "",
                'is_seen' => false,
            ],
            [
                'user_id' => 3,
                'title' => "Unlock 10% off",
                'subtitle' => "",
                'badge_title' => "GET10",
                'image' => "",
                'is_seen' => false,
            ],
            [
                'user_id' => 3,
                'title' => "New Year Deal",
                'subtitle' => "",
                'badge_title' => "NEW10",
                'image' => "",
                'is_seen' => true,
            ],
            [
                'user_id' => 3,
                'title' => "Daily Horoscope Update",
                'subtitle' => "Your daily horoscope for Taurus is now live. Discover what the stars have in store!",
                'badge_title' => "",
                'image' => "",
                'is_seen' => false,
            ],
            [
                'user_id' => 3,
                'title' => "Your Report is Ready!",
                'subtitle' => "Your personalized astrological report is now available. Tap to view!",
                'badge_title' => "",
                'image' => "",
                'is_seen' => true,
            ],
            // For Astrologer
            [
                'user_id' => 2,
                'title' => "20% Off",
                'subtitle' => "Get 30% off on your first report",
                'badge_title' => "",
                'image' => "",
                'is_seen' => false,
            ],
            [
                'user_id' => 2,
                'title' => "Unlock 5% off",
                'subtitle' => "",
                'badge_title' => "GET5",
                'image' => "",
                'is_seen' => false,
            ],
            [
                'user_id' => 2,
                'title' => "New Deal",
                'subtitle' => "",
                'badge_title' => "NEW10",
                'image' => "",
                'is_seen' => true,
            ],
            [
                'user_id' => 2,
                'title' => "Daily Horoscop Update",
                'subtitle' => "Your daily horoscope for Taurus is now live. Discover what the stars have in store!",
                'badge_title' => "",
                'image' => "",
                'is_seen' => false,
            ],
            [
                'user_id' => 2,
                'title' => "Your Report is Ready",
                'subtitle' => "Your personalized astrological report is now available. Tap to view!",
                'badge_title' => "",
                'image' => "",
                'is_seen' => true,
            ],
        ];
        foreach ($notifications as $notification) {
            Notification::firstOrCreate(
                ['title' => $notification['title']],
                [
                    'user_id' => $notification['user_id'],
                    'subtitle' => $notification['subtitle'],
                    'badge_title' => $notification['badge_title'],
                    'image' => $notification['image'],
                    'is_seen' => $notification['is_seen'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
