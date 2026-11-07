<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToBusiness;

    protected $primaryKey = 'id_customer';

    protected $guarded = [];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'id_customer');
    }

    public function receivables(): HasMany
    {
        return $this->hasMany(Receivable::class, 'id_customer');
    }
}
