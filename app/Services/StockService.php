<?php

namespace App\Services;

use App\Models\Item;
use App\Models\StockHistory;

class StockService
{
    public function change(Item $item, string $type, float $quantity, ?string $note = null, ?int $idTransaction = null): void
    {
        if ($item->is_service) {
            return;
        }

        $before = (float) $item->stock;
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

    public function correctTo(Item $item, float $newStock, ?string $note = null): void
    {
        $this->change($item, 'adjustment', $newStock - (float) $item->stock, $note ?: 'Koreksi stok');
    }
}
