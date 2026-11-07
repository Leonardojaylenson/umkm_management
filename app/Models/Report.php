<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use BelongsToBusiness;

    protected $primaryKey = 'id_report';

    protected $guarded = [];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'filters' => 'array'];
}
