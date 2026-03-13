<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;

class LogoutInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logout:inactive-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Logout inactive users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inactiveTime = Carbon::now()->subWeeks(2);
        $inactiveUsers = UserActivity::where('last_activity', '<', $inactiveTime)->pluck('user_id');
        User::whereIn('id', $inactiveUsers)->update(['device_token' => null,'is_online' => false]);
        UserActivity::whereIn('user_id', $inactiveUsers)->delete();

        $users = User::whereIn('id', $inactiveUsers)->get();
        foreach ($users as $user) {
            foreach ($user->tokens as $token) {
                $token->delete();
            }
        }
        $this->info('Inactive users logged out.');
    }
}
