<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $primaryKey = 'id_report';

    protected $guarded = [];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'filters' => 'array'];
}
