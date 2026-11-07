<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use BelongsToBusiness;

    protected $primaryKey = 'id_category';

    protected $guarded = [];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'id_category');
    }
}
