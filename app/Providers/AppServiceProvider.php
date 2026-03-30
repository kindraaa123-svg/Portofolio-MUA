<?php

namespace App\Providers;

use App\Models\WebsiteSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view): void {
            if (! Schema::hasTable('website_settings')) {
                $view->with('globalSetting', null);

                return;
            }

            $view->with('globalSetting', WebsiteSetting::query()->first());
        });
    }
}
