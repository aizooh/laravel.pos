<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['description', 'amount', 'category', 'user_id', 'date'];

    protected $casts = [
        'amount' => 'decimal:2',
        'date'   => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeInMonth($q, $year, $month)
    {
        return $q->whereYear('date', $year)->whereMonth('date', $month);
    }

    public function scopeToday($q)
    {
        return $q->whereDate('date', today());
    }
}