<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class SettingsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (Schema::hasTable('settings')) {

            // Fetch all settings from the database
            $settings = Setting::all()->pluck('data','name')->toArray();

            // Share the settings array with all views
            View::share('settings', $settings);

            // Optionally, share the settings array with all controllers
            $this->app->singleton('settings', function () use ($settings) {
                return $settings;
            });
      }
    }
  
    public function register()
    {
        //
    }
    protected function isRunningMigrationCommand()
    {
        $commands = ['migrate', 'migrate:install', 'migrate:refresh', 'migrate:reset', 'migrate:rollback'];
        if (App::runningInConsole()) {
            $currentCommand = Artisan::output();
            return in_array($currentCommand, $commands);
        }
    }
}
