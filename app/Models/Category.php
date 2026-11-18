<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $primaryKey = 'id_category';

    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(Item::class, 'id_category');
    }
}
