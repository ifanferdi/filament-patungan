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
            self::processOrderDetailData($callback);
        });
    }

    // FUNCTION
    public static function processOrderDetailData($data): OrderDetail
    {
        $order = $data->order;
        $price = (int)$data->price;

        $discount_by_percentage = $price * $order->promo / 100;
        $discount = $discount_by_percentage * $order->discount / $order->total_with_promo;
        $additional_discount = $price * $order->additional_discount / $order->total;

        $price_after_discount = $order->total_with_promo > $order->discout
            ? $price - ($discount + $additional_discount)
            : $price - ($discount_by_percentage + $additional_discount);

        $fee = $order->total_fee / $order->total_items;
        $final_price = $price_after_discount + $fee;

        $data->discount_by_percentage = $discount_by_percentage;
        $data->discount = $discount;
        $data->additional_discount = $additional_discount;
        $data->price_after_discount = $price_after_discount;
        $data->fee = $fee;
        $data->final_price = $final_price;

        return $data;
    }

    // RELATIONSHIP
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
