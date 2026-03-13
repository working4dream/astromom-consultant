<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

class CleanupOldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:old-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes data older than 24 hours from all non-excluded tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (env('APP_ENV') === 'production') {
            $this->info("Skipping cleanup in production environment.");
            return;
        }
        if (env('OLD_DATA_DELETE')) {
            $tables = DB::select('SHOW TABLES');
            $exclude = config('temp_cleanup.exclude');
            $tableKey = 'Tables_in_' . DB::getDatabaseName();

            foreach ($tables as $tableObj) {
                $table = $tableObj->$tableKey;

                if (in_array($table, $exclude)) {
                    continue;
                }

                if (Schema::hasColumn($table, 'created_at')) {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    
                    $deleted = DB::table($table)
                        ->where('created_at', '>=', now()->subDay())
                        ->delete();

                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                    if ($deleted > 0) {
                        $this->info("🗑 Deleted $deleted rows from {$table}");
                    }
                    Setting::where('name', 'primary_color')->update(['data' => NULL]);
                    Setting::where('name', 'secondary_color')->update(['data' => NULL]);
                    Setting::where('name', 'brand_logo')->update(['data' => NULL]);
                }
            }

            $this->info("✅ Cleanup completed successfully!");
        } else {
            $this->info("✅ Cleanup skipped. OLD_DATA_DELETE is not set.");
        }
    }
}
