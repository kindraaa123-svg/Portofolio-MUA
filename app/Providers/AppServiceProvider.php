<?php

namespace App\Providers;

use App\Models\OperationalHour;
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
                $view->with('globalOperationalHours', collect());

                return;
            }

            $view->with('globalSetting', WebsiteSetting::query()->first());

            if (Schema::hasTable('operational_hours')) {
                $view->with('globalOperationalHours', OperationalHour::query()->orderByRaw('FIELD(day_of_week, 1,2,3,4,5,6,0)')->get());
            } else {
                $view->with('globalOperationalHours', collect());
            }
        });
    }
}
