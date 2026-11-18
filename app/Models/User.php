<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'id_user';

    protected $guarded = [];

    protected $hidden = ['password'];

    protected $casts = ['is_active' => 'boolean'];

    public function getRememberTokenName()
    {
        return '';
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'id_business');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'id_level');
    }

    public function isOwner()
    {
        return $this->level !== null && $this->level->is_owner;
    }

    public function canAccess($menu)
    {
        if ($this->isOwner()) {
            return true;
        }

        if ($this->level === null) {
            return false;
        }

        return $this->level->menus->contains('menu', $menu);
    }
}
