<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'invoice_no', 'user_id', 'total',
        'payment_method', 'mpesa_reference',
        'voided_at', 'voided_by', 'void_reason',
    ];

    protected $casts = [
        'total'     => 'decimal:2',
        'voided_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Hide voided sales from every query by default
        static::addGlobalScope('not_voided', function (Builder $q) {
            $q->whereNull('voided_at');
        });
    }

    // ---- route binding: still resolve voided sales ----
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->withoutGlobalScope('not_voided')
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->firstOrFail();
    }

    // ---- scopes ----
    public function scopeWithVoided($q)
    {
        return $q->withoutGlobalScope('not_voided');
    }

    public function scopeOnlyVoided($q)
    {
        return $q->withoutGlobalScope('not_voided')->whereNotNull('voided_at');
    }

    // ---- relations ----
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    // ---- helpers ----
    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }

    public function scopeToday($q)
    {
        return $q->whereDate('created_at', today());
    }
}