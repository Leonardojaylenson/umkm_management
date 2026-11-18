<?php

namespace App\Http\Controllers;

use App\Models\StockHistory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function businessId()
    {
        return auth()->user()->id_business;
    }

    protected function changeStock($item, $type, $quantity, $note, $idTransaction = null)
    {
        if ($item->is_service) {
            return;
        }

        $before = $item->stock;
        $after  = $before + $quantity;

        $item->update(['stock' => $after]);

        StockHistory::create([
            'id_item'        => $item->id_item,
            'id_user'        => auth()->id(),
            'id_transaction' => $idTransaction,
            'type'           => $type,
            'quantity'       => $quantity,
            'stock_before'   => $before,
            'stock_after'    => $after,
            'note'           => $note,
        ]);
    }
}
