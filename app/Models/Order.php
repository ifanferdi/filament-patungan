<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'orders';
    protected $guarded = ['id'];

    // RELATIONSHIP
    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // ACCESSOR
    public function getDiscountWithPercentageAttribute(): string
    {
        $discount = number_format($this->discount, 2, ',', '.');
        return "Rp ".$discount." (".$this->discount_percent."%)";
    }

    public function getAdditionalDiscountWithPercentageAttribute(): string
    {
        $additional_discount = number_format($this->additional_discount, 2, ',', '.');
        return "Rp ".$additional_discount." (".$this->additional_discount_percent."%)";
    }
}
