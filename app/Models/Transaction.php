<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use BelongsToBusiness;

    protected $primaryKey = 'id_transaction';

    protected $guarded = [];

    protected $casts = ['transaction_date' => 'datetime'];

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'id_transaction');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function receivable(): HasOne
    {
        return $this->hasOne(Receivable::class, 'id_transaction');
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getRemainingAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }
}
