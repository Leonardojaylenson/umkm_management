<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    protected $primaryKey = 'id_transaction_detail';

    protected $guarded = [];

    public $timestamps = false;
}
