<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $primaryKey = 'id_unit';

    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(Item::class, 'id_unit');
    }
}
