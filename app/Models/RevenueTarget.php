<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueTarget extends Model
{
    protected $primaryKey = 'id_revenue_target';

    protected $guarded = [];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];
}
