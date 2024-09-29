<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'order_details';
    protected $guarded = ['id'];

    public static function boot(): void
    {
        parent::boot();

        static::creating(static function ($callback) {
            $order = $callback->order;
            $price = (int)$callback->price;

            $discount_by_percentage = $price * $order->promo / 100;
            $discount = $discount_by_percentage * $order->discount_percent / 100;
            $additional_discount = $price * $order->additional_discount_percent / 100;

            $price_after_discount = $order->total_with_promo > $order->discount
                ? $price - ($discount + $order->discount)
                : $price - ($discount + $order->discount_by_percentage);

            $fee = $order->total_fee / $order->total_items;
            $final_price = $price_after_discount + $fee;

            $callback->discount_by_percentage = $discount_by_percentage;
            $callback->discount = $discount;
            $callback->additional_discount = $additional_discount;
            $callback->price_after_discount = $price_after_discount;
            $callback->fee = $fee;
            $callback->final_price = $final_price;
        });
    }


    // RELATIONSHIP
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
