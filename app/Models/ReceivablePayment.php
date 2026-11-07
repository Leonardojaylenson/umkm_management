<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivablePayment extends Model
{
    const UPDATED_AT = null;

    protected $primaryKey = 'id_receivable_payment';

    protected $guarded = [];

    protected $casts = ['payment_date' => 'date'];
}
