<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentAllocation extends Model
{
    public function payment():BelongsTo {
        return $this->belongsTo(Payment::class);
    }

    public function payable():MorphTo{
        return $this->morphTo();
    }
}
