<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockHistory extends Model
{
    const UPDATED_AT = null;

    protected $primaryKey = 'id_stock_history';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
