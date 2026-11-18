<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receivable extends Model
{
    protected $primaryKey = 'id_receivable';

    protected $guarded = [];

    protected $casts = ['receivable_date' => 'date', 'due_date' => 'date'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'id_transaction');
    }

    public function payments()
    {
        return $this->hasMany(ReceivablePayment::class, 'id_receivable')->latest('payment_date')->latest('id_receivable_payment');
    }
}
