<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Person extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $table = 'people';

    // RELATIONSHIP
    public function order_details(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'person_id');
    }

    public function nameWithPhoneNumber(): Attribute
    {
        return Attribute::make(get: fn () => Str::ucfirst($this->name) . " ($this->phone_number)");
    }

}
