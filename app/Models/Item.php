<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $primaryKey = 'id_item';

    protected $guarded = [];

    protected $casts = ['is_service' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'id_category');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit');
    }

    public function stockHistories()
    {
        return $this->hasMany(StockHistory::class, 'id_item')->latest('created_at')->latest('id_stock_history');
    }
}
