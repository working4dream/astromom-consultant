<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::query()->truncate();
        $statuses = [
            [
                'type' => 'order_status',
                'name' => 'Pending',
            ],
            [
                'type' => 'order_status',
                'name' => 'Completed',
            ],
            [
                'type' => 'order_status',
                'name' => 'Cancelled',
            ],
            [
                'type' => 'payment_status',
                'name' => 'Pending',
            ],
            [
                'type' => 'payment_status',
                'name' => 'Successful',
            ],
            [
                'type' => 'payment_status',
                'name' => 'Failed',
            ],
            [
                'type' => 'refund_status',
                'name' => 'Pending',
            ],
            [
                'type' => 'refund_status',
                'name' => 'Approved',
            ],
            [
                'type' => 'refund_status',
                'name' => 'Rejected',
            ],
            [
                'type' => 'booking_status',
                'name' => 'Booked',
            ],
            [
                'type' => 'booking_status',
                'name' => 'Cancelled',
            ],
            [
                'type' => 'booking_status',
                'name' => 'Completed',
            ],
            [
                'type' => 'live_session',
                'name' => 'Active',
            ],
            [
                'type' => 'live_session',
                'name' => 'Ended',
            ],
            [
                'type' => 'live_session',
                'name' => 'Cancelled',
            ],
            [
                'type' => 'messages',
                'name' => 'Send',
            ],
            [
                'type' => 'messages',
                'name' => 'Delivered',
            ],
            [
                'type' => 'messages',
                'name' => 'Read',
            ],
            [
                'type' => 'call_logs',
                'name' => 'Started',
            ],
            [
                'type' => 'call_logs',
                'name' => 'Ended',
            ],
            [
                'type' => 'call_logs',
                'name' => 'Cancelled',
            ],
            [
                'type' => 'withdrawals',
                'name' => 'Pending',
            ],
            [
                'type' => 'withdrawals',
                'name' => 'Completed',
            ],
            [
                'type' => 'withdrawals',
                'name' => 'Rejected',
            ],
        ];
        foreach ($statuses as $status) {
            Status::insert(
                [
                    'type' => $status['type'],
                    'name' => $status['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
