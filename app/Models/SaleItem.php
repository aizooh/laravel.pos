<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id', 'item_type', 'item_id',
        'name', 'price', 'buying_price', 'qty', 'subtotal',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'buying_price' => 'decimal:2',
        'subtotal'     => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function profit(): float
    {
        return (float) $this->subtotal - ((float) $this->buying_price * $this->qty);
    }
}