<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $primaryKey = 'id_expense_category';

    protected $guarded = [];

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'id_expense_category');
    }
}
