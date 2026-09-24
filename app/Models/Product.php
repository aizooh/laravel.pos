<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'category',
        'buying_price', 'selling_price',
        'stock', 'low_stock', 'active',
    ];

    protected $casts = [
        'buying_price'  => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock'         => 'integer',
        'low_stock'     => 'integer',
        'active'        => 'boolean',
    ];

    public function adjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->low_stock;
    }

    public function scopeActive($q)
    {
        return $q->where('active', true);
    }

    public function scopeLowStock($q)
    {
        return $q->whereColumn('stock', '<=', 'low_stock');
    }
}