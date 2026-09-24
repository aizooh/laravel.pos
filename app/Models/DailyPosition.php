<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyPosition extends Model
{
    protected $fillable = [
        'date', 'type',
        'kcb', 'equity', 'absa', 'mpesa', 'cash', 'other', 'other_label',
        'total', 'notes', 'user_id',
    ];

    protected $casts = [
        'date'   => 'date',
        'kcb'    => 'decimal:2',
        'equity' => 'decimal:2',
        'absa'   => 'decimal:2',
        'mpesa'  => 'decimal:2',
        'cash'   => 'decimal:2',
        'other'  => 'decimal:2',
        'total'  => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOpening($q) { return $q->where('type', 'opening'); }
    public function scopeClosing($q) { return $q->where('type', 'closing'); }

    public static function sumFields(array $data): float
    {
        return (float) ($data['kcb']    ?? 0)
             + (float) ($data['equity'] ?? 0)
             + (float) ($data['absa']   ?? 0)
             + (float) ($data['mpesa']  ?? 0)
             + (float) ($data['cash']   ?? 0)
             + (float) ($data['other']  ?? 0);
    }
}