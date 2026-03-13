<?php

namespace App\Console\Commands;

use App\Models\AstrologerBookNowPrice;
use Illuminate\Console\Command;

class ResetAstrologerCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'astrologers:reset-credits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset available credits of all astrologers to 0 daily.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        AstrologerBookNowPrice::query()->update(['available_credits' => 0]);
        $this->info('Available credits reset to 0 for all astrologers.');
    }
}
