<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtPayment extends Model
{
    const UPDATED_AT = null;

    protected $primaryKey = 'id_debt_payment';

    protected $guarded = [];

    protected $casts = ['payment_date' => 'date'];
}
