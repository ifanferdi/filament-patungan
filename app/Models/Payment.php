<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // RELATIONSHIP
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ACCESSOR
    public function getProviderLabelAttribute(): string
    {
        return config('payment.providers')[$this->provider];
    }
}
