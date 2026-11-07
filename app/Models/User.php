<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'id_user';

    protected $fillable = ['id_business', 'name', 'email', 'phone', 'password', 'role', 'is_active'];

    protected $hidden = ['password'];

    protected $casts = ['is_active' => 'boolean'];

    public function getRememberTokenName()
    {
        return '';
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'id_business');
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->isOwner() ? 'Pemilik' : 'Kasir';
    }
}
