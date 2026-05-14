<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{

   protected $fillable = [
        'product_id',
        'type',
        'stock',
        'alert_stock',
    ];
    public function product():BelongsTo {
         return $this->belongsTo(Product::class , 'product_id');
    }

public function users()
{
    return $this->belongsToMany(User::class)
        ->withPivot('is_read')
        ->withTimestamps();
}
}
