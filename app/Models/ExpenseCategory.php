<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    use BelongsToBusiness;

    protected $primaryKey = 'id_expense_category';

    protected $guarded = [];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'id_expense_category');
    }
}
