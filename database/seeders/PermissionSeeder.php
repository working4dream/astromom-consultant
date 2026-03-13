<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $permissions = [
            ['name' => 'dashboard', 'category' => 'dashboard'],

            ['name' => 'create media library', 'category' => 'media library'],
            ['name' => 'view media library', 'category' => 'media library'],
            ['name' => 'delete media library', 'category' => 'media library'],

            ['name' => 'create categories', 'category' => 'categories'],
            ['name' => 'view categories', 'category' => 'categories'],
            ['name' => 'edit categories', 'category' => 'categories'],
            ['name' => 'delete categories', 'category' => 'categories'],
            
            ['name' => 'view orders', 'category' => 'orders'],
            
            ['name' => 'view disputes', 'category' => 'disputes'],

            ['name' => 'create coupons', 'category' => 'coupons'],
            ['name' => 'view coupons', 'category' => 'coupons'],
            ['name' => 'edit coupons', 'category' => 'coupons'],
            ['name' => 'delete coupons', 'category' => 'coupons'],
            
            ['name' => 'create experts', 'category' => 'experts'],
            ['name' => 'view experts', 'category' => 'experts'],
            ['name' => 'edit experts', 'category' => 'experts'],
            ['name' => 'delete experts', 'category' => 'experts'],
            
            ['name' => 'create customers', 'category' => 'customers'],
            ['name' => 'view customers', 'category' => 'customers'],
            ['name' => 'edit customers', 'category' => 'customers'],
            ['name' => 'delete customers', 'category' => 'customers'],

            ['name' => 'create faq', 'category' => 'faq'],
            ['name' => 'view faq', 'category' => 'faq'],
            ['name' => 'edit faq', 'category' => 'faq'],
            ['name' => 'delete faq', 'category' => 'faq'],

            ['name' => 'prices', 'category' => 'settings'],
            ['name' => 'services', 'category' => 'settings'],
            ['name' => 'booking prices', 'category' => 'settings'],
            ['name' => 'customer banner', 'category' => 'settings'],
            ['name' => 'expert banner', 'category' => 'settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                [
                    'category' => $permission['category'],
                ]
            );
        }
    }
}
