<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevisItem extends Model
{
    protected $fillable = [
        'devis_id',
        'product_id',
        'quantity',
        'price',
        'total',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function devis()
    {
        return $this->belongsTo(Devis::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
