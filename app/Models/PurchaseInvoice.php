<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    protected $table = 'purchase_invoices';

    protected $fillable = [
        'invoice_number',
        'purchase_id',
        'total',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
