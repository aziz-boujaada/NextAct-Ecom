<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'type',
        'source',
        'reference_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
