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

    // FUNCTION

    public static function processOrder(array $data): array
    {
        $author_id = auth()->id();
        $order_fee = (int)($data['order_fee'] ?? 0);
        $delivery_fee = (int)($data['delivery_fee'] ?? 0);
        $tip = (int)($data['tip'] ?? 0);
        $discount = (int)($data['discount'] ?? 0);
        $additional_discount = (int)($data['additional_discount'] ?? 0);
        $total = (int)($data['total'] ?? 0);
        $total_with_promo = (int)($data['total_with_promo'] ?? 0);

        return [
            ...$data,
            'promo' => (int)($data['promo'] ?? 100),
            'order_fee' => $order_fee,
            'delivery_fee' => $delivery_fee,
            'tip' => $tip,
            'total_fee' => array_sum([$order_fee, $delivery_fee, $tip]),
            'discount' => $discount,
            'discount_percent' => ceil($discount / $total_with_promo * 100),
            'additional_discount' => $additional_discount,
            'additional_discount_percent' => ceil($additional_discount / $total * 100),
            'total' => $total,
            'total_with_promo' => $total_with_promo,
            'total_items' => count($data['order_list']),
            'author_id' => $author_id
        ];
    }

    public static function markAllPaid(Model $order): void
    {
        $order->details()->update(['is_paid' => true]);
        $order->save();
    }

    // RELATIONSHIP
    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    public function details_unpaid(): HasMany
    {
        return $this->details()->where('is_paid', false);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // ACCESSOR
    public function getDiscountWithPercentageAttribute(): string
    {
        $discount = number_format($this->discount, 2, ',', '.');
        return "Rp " . $discount . " (" . $this->discount_percent . "%)";
    }

    public function getAdditionalDiscountWithPercentageAttribute(): string
    {
        $additional_discount = number_format($this->additional_discount, 2, ',', '.');
        return "Rp " . $additional_discount . " (" . $this->additional_discount_percent . "%)";
    }
}
