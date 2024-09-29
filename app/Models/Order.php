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

//    public static function boot(): void
//    {
//        parent::boot();
//
//        static::creating(static function ($callback) {
//            $request = json_decode(request()->get('components')[0]['snapshot'])->data->data[0];
//
//            $order_fee = (int)$callback->order_fee ?? 0;
//            $delivery_fee = (int)$callback->delivery_fee ?? 0;
//            $tip = (int)$callback->tip ?? 0;
//            $discount = (int)$callback->discount ?? 0;
//            $additional_discount = (int)$callback->additional_discount ?? 0;
//            $total = (int)$callback->total ?? 0;
//            $total_with_promo = (int)$callback->total_with_promo ?? 0;
//
//            $callback->promo = (int)$callback->promo ?? 100;
//            $callback->order_fee = $order_fee;
//            $callback->delivery_fee = $delivery_fee;
//            $callback->tip = $tip;
//            $callback->total_fee = (int)array_sum([$order_fee, $delivery_fee, $tip]);
//            $callback->discount = $discount;
//            $callback->discount_percent = ceil($discount / $total_with_promo * 100);
//            $callback->additional_discount = $additional_discount;
//            $callback->additional_discount_percent = (ceil($additional_discount / $total * 100));
//            $callback->total = $total;
//            $callback->total_with_promo = $total_with_promo;
//            $callback->total_items = count($request->order_list);
//        });
//    }


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
    public function getDiscountWithPercentageAttribute()
    {
        $discount = number_format($this->discount, 0, ',', '.');
        return "Rp. " . $discount . " (" . $this->discount_percent . "%)";
    }

    public function getAdditionalDiscountWithPercentageAttribute()
    {
        $additional_discount = number_format($this->additional_discount, 0, ',', '.');
        return "Rp. " . $additional_discount . " (" . $this->additional_discount_percent . "%)";
    }
}
