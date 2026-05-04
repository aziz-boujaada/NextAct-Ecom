<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['client_id', 'total', 'status'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function refund()
    {
        return $this->hasOne(Refund::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
