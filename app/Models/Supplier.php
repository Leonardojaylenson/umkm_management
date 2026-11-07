<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use BelongsToBusiness;

    protected $primaryKey = 'id_supplier';

    protected $guarded = [];

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class, 'id_supplier');
    }
}
