<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'sale_id',
        'amount_paid',
        'paid_at'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
