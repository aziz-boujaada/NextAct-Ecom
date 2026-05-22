<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $table = 'paytments';

    protected $fillable = [
        'sale_id',
        'amount_paid',
        'paid_at'
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function allocations():HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }
}
