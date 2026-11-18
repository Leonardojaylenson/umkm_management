<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $primaryKey = 'id_supplier';

    protected $guarded = [];

    public function debts()
    {
        return $this->hasMany(Debt::class, 'id_supplier');
    }
}
