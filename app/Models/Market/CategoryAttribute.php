<?php

namespace App\Models\Market;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



/**
 * @OA\Schema(
 *     schema="CategoryAttribute",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="دوربین"),
 *     @OA\Property(property="unit", type="string", example="مگاپیکسل"),
*      @OA\Property(property="type_value", description="type_value: 'active' if 1, 'inactive' if 2", type="string", example="فعال"),
 *     @OA\Property(property="category", type="object",
 *          @OA\Property(property="id", type="integer", example=2),
 *          @OA\Property(property="name", type="string", example="لوازم الکترونیکی"),
 *      ),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string",description="delete datetime", format="datetime", example="2025-02-22T14:30:00Z")
 * )
 */
class CategoryAttribute extends Model
{
    use HasFactory,SoftDeletes,CascadeSoftDeletes;

    protected $fillable = ['name', 'type', 'unit','category_id'];
    protected $cascadeDeletes = ['cartItemSelectedAttributes', 'values', 'orderItemSelectedAttributes'];

    protected $hidden = ['type', 'category_id'];
    protected $appends = ['type_value'];
    protected $dates = ['deleted_at'];
    public function getTypeValueAttribute()
    {
        if($this->status == 1)
        {
            return 'فعال';
        }
        else{
            return 'غیرفعال';
        }
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItemSelectedAttributes()
    {
        return $this->hasMany(OrderItemSelectedAttribute::class);

    }
    public function cartItemSelectedAttributes()
    {
        return $this->hasMany(CartItemSelectedAttribute::class);
    }
    public function values()
    {
        return $this->hasMany(CategoryValue::class);
    }
}
