<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $primaryKey = 'id_level';

    protected $guarded = [];

    protected $casts = ['is_owner' => 'boolean'];

    public function menus()
    {
        return $this->hasMany(LevelMenu::class, 'id_level');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'id_level');
    }
}
