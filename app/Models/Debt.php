<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use App\Models\Concerns\HasInstallments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debt extends Model
{
    use BelongsToBusiness, HasInstallments;

    protected $primaryKey = 'id_debt';

    protected $guarded = [];

    protected $casts = ['debt_date' => 'date', 'due_date' => 'date'];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'id_supplier');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class, 'id_debt')
            ->latest('payment_date')->latest('id_debt_payment');
    }
}
