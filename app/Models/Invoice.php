<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'sale_id',
        'total'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
