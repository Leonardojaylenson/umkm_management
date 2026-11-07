<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use App\Models\Concerns\HasInstallments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receivable extends Model
{
    use BelongsToBusiness, HasInstallments;

    protected $primaryKey = 'id_receivable';

    protected $guarded = [];

    protected $casts = ['receivable_date' => 'date', 'due_date' => 'date'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'id_transaction');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ReceivablePayment::class, 'id_receivable')
            ->latest('payment_date')->latest('id_receivable_payment');
    }

    protected function afterPayment(float $amount): void
    {
        $trx = $this->transaction;

        if (! $trx) {
            return;
        }

        $trx->paid_amount = (float) $trx->paid_amount + $amount;
        $trx->payment_status = $trx->paid_amount >= (float) $trx->total_amount ? 'paid' : 'down_payment';
        $trx->save();
    }
}
