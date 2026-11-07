<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class RevenueTarget extends Model
{
    use BelongsToBusiness;

    protected $primaryKey = 'id_revenue_target';

    protected $guarded = [];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];
}
