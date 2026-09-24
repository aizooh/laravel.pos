<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'active'   => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    public function stockAdjustments()
{
    return $this->hasMany(StockAdjustment::class);
}
    public function isAttendant(): bool
    {
        return $this->role === 'attendant';
    }
    public function sales()
{
    return $this->hasMany(Sale::class);
}
public function expenses()
{
    return $this->hasMany(Expense::class);
}
public function dailyPositions()
{
    return $this->hasMany(DailyPosition::class);
}
}
