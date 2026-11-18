<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $primaryKey = 'id_customer';

    protected $guarded = [];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'id_customer');
    }

    public function receivables()
    {
        return $this->hasMany(Receivable::class, 'id_customer');
    }
}
