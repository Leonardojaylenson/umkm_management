<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    protected $primaryKey = 'id_debt';

    protected $guarded = [];

    protected $casts = ['debt_date' => 'date', 'due_date' => 'date'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'id_supplier');
    }

    public function payments()
    {
        return $this->hasMany(DebtPayment::class, 'id_debt')->latest('payment_date')->latest('id_debt_payment');
    }
}
