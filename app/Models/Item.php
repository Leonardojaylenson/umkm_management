<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use BelongsToBusiness;

    protected $primaryKey = 'id_item';

    protected $guarded = [];

    protected $casts = ['is_service' => 'boolean'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'id_category');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'id_unit');
    }

    public function stockHistories(): HasMany
    {
        return $this->hasMany(StockHistory::class, 'id_item')
            ->latest('created_at')->latest('id_stock_history');
    }

    public function getIsLowStockAttribute(): bool
    {
        return ! $this->is_service && (float) $this->stock <= (float) $this->min_stock;
    }
}
