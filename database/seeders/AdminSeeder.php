<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = DB::table('users')->where('email', 'admin@example.com')->first();

        if (!$admin) {
            $adminId = DB::table('users')->insertGetId([
                'first_name' => 'Subastro',
                'last_name' => 'Admin',
                'email' => 'admin@example.com',
                'mobile_number' => '123456789',
                'password' => Hash::make('Admin@123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $admin = DB::table('users')->find($adminId);
        }
        $adminUser = User::find($admin->id);
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
        }

        $permissions = Permission::all();
        $adminUser->syncPermissions($permissions);
    }
}
