<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Purchase extends Model
{
    protected $fillable = ['supplier_id', 'total', 'status'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function invoice()
    {
        return $this->hasOne(PurchaseInvoice::class);
    }


    public function paymentAllocations():MorphMany
    {
        return $this->morphMany(PaymentAllocation::class , 'payable');
    }
}
