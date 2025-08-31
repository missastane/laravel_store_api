<?php

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItemSelectedAttribute extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['order_item_id', 'category_attribute_id', 'category_value_id', 'value'];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function attribute()
    {
        return $this->belongsTo(CategoryAttribute::class, 'category_attribute_id');
    }

    public function value()
    {
        return $this->belongsTo(CategoryValue::class);
    }
}
