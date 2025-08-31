<?php

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartItemSelectedAttribute extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['cart_item_id', 'category_attribute_id', 'category_value_id', 'value'];

    public function cartItem()
    {
        return $this->belongsTo(CartItem::class);
    }
    public function attribute()
    {
        return $this->belongsTo(CategoryAttribute::class);
    }

    public function value()
    {
        return $this->belongsTo(CategoryValue::class);
    }
}
