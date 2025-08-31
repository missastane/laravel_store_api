<?php

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @OA\Schema(
 *     schema="CategoryValue",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(
 *             property="value",
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="value", type="string", example="example_value"),
 *                @OA\Property(property="price_increase", type="number", format="float", example=10.5)
 *     )
 * ),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string",description="delete datetime", format="datetime", example="2025-02-22T14:30:00Z"),
 * @OA\Property(property="type_value", type="string", description="type_value: 'multiple values select by customers (effects on price)' if 1, 'simple' if 2", example="ساده"),
 *     @OA\Property(property="attribute", type="object",
 *          @OA\Property(property="id", type="integer", example=2),
 *          @OA\Property(property="name", type="string", example="پردازنده"),
 *          @OA\Property(property="unit", type="string", example="mhrtz"),
 *          @OA\Property(property="type_value", description="Attribute type: 'active' if 1, 'inactive' if 2", type="string", example="فعال"),
 *      ),
 *          @OA\Property(property="product", type="object",
 *          @OA\Property(property="id", type="integer", example=2),
 *          @OA\Property(property="name", type="string", example="گوشی سامسونگ a71"),
 *      ),
 * )
 */
class CategoryValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['value', 'product_id', 'category_attribute_id', 'type'];
    protected $cascadeDeletes = ['cartItemSelectedAttributes', 'orderItemSelectedAttributes'];
    protected $appends = ['type_value'];
    protected $hidden = ['type', 'category_attribute_id', 'product_id'];
    protected $casts = [
        'value' => 'array'
    ];
    protected $dates = ['deleted_at'];
    public function getTypeValueAttribute()
    {
        if ($this->type == 1) {
            return 'جزء مقادیر چندگانه است که بر قیمت تأثیر می گذارد';
        } else {
            return 'ساده';
        }
    }
    public function orderItemSelectedAttributes()
    {
        return $this->hasMany(OrderItemSelectedAttribute::class);
    }


    public function cartItemSelectedAttributes()
    {
        return $this->hasMany(CartItemSelectedAttribute::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute()
    {
        return $this->belongsTo(CategoryAttribute::class, 'category_attribute_id');
    }
}
