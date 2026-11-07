<?php

namespace App\Providers;

use App\Models\Debt;
use App\Models\Receivable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
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

            if (! $user || ! $user->id_business) {
                return $view->with(['lowStockCount' => 0, 'dueCount' => 0]);
            }

            $lowStock = DB::table('v_low_stock_items')->where('id_business', $user->id_business)->count();

            $due = 0;
            if ($user->isOwner()) {
                $limit = now()->addDays(7)->toDateString();
                $due = Debt::where('status', '!=', 'paid')->whereNotNull('due_date')->where('due_date', '<=', $limit)->count()
                     + Receivable::where('status', '!=', 'paid')->whereNotNull('due_date')->where('due_date', '<=', $limit)->count();
            }

            $view->with(['lowStockCount' => $lowStock, 'dueCount' => $due]);
        });
    }
}
