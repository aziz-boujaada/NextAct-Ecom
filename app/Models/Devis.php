<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devis extends Model
{
    protected $table = 'devis';

    protected $fillable = [
        'reference',
        'client_id',
        'subtotal',
        'discount',
        'tax',
        'total',
        'status',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'expires_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(DevisItem::class);
    }

    protected static function booted()
    {
        static::updating(function ($devis) {
            $original = $devis->getOriginal('status');
            $current = $devis->status;

            if ($original !== $current) {
                $now = now();

                if ($current === 'sent' && is_null($devis->sent_at)) {
                    $devis->sent_at = $now;
                }

                if ($current === 'accepted' && is_null($devis->accepted_at)) {
                    $devis->accepted_at = $now;
                }

                if ($current === 'rejected' && is_null($devis->rejected_at)) {
                    $devis->rejected_at = $now;
                }
            }
        });
    }
}
