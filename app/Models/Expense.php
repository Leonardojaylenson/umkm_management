<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use BelongsToBusiness;

    protected $primaryKey = 'id_expense';

    protected $guarded = [];

    protected $casts = ['expense_date' => 'date'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'id_expense_category');
    }
}
