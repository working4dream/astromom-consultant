<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserActivity;
use App\Models\User;
use Carbon\Carbon;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;

class MarkUsersOffline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:mark-offline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark users as offline if they have been inactive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inactiveTime = Carbon::now()->subMinutes(60);
        $inactiveUsers = UserActivity::where('last_activity', '<', $inactiveTime)->pluck('user_id');
        User::whereIn('id', $inactiveUsers)->update(['is_online' => false]);
        $path = base_path('storage/firebase'.'/'.env('FIREBASE_LIVE_FILE'));
        $factory = (new Factory)->withServiceAccount($path)->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        foreach ($inactiveUsers as $userId) {
            $database
                ->getReference('users/' . $userId)
                ->update([
                    'online' => false,
                ]);
        }

        $this->info('Inactive users marked as offline.');
    }
}
