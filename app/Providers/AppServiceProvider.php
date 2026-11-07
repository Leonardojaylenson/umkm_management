<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        Paginator::useBootstrapFive();

        View::composer('template.navbar', function ($view) {
            $user = auth()->user();

            $lowStock = $user && $user->id_business
                ? DB::table('v_low_stock_items')->where('id_business', $user->id_business)->count()
                : 0;

            $view->with('lowStockCount', $lowStock);
        });
    }
}
