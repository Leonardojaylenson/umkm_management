<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $primaryKey = 'id_transaction';

    protected $guarded = [];

    protected $casts = ['transaction_date' => 'datetime'];

    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'id_transaction');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function receivable()
    {
        return $this->hasOne(Receivable::class, 'id_transaction');
    }
}
