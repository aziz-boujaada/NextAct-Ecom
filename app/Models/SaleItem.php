<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sale_id', 'product_id', 'price', 'quantity', 'total',
        'refund_quantity', 'refund_total', 'refund_status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'refund_total' => 'decimal:2',
        'refund_status' => 'string',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
