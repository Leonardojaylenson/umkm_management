<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $primaryKey = 'id_expense';

    protected $guarded = [];

    protected $casts = ['expense_date' => 'date'];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'id_expense_category');
    }
}
