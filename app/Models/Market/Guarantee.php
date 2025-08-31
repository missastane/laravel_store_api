<?php

namespace App\Models\Market;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



/**
 * @OA\Schema(
 *     schema="Guarantee",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="ضمانت 24 ماهه پاکشوما"),
 *     @OA\Property(property="price_increase", type="number", format="float", example=10.5),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string",description="delete datetime", format="datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", description="ProductGuarantee status: 'active' if 1, 'inactive' if 2", type="string", example="فعال"),
 *     @OA\Property(property="product", type="object",
 *          @OA\Property(property="id", type="integer", example=2),
 *          @OA\Property(property="name", type="string", example="ماوس میوا"),
 *      )
 * )
 */
class Guarantee extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $fillable = ['name', 'product_id', 'price_increase', 'status'];
    protected $cascadeDeletes = ['orderItems'];
    protected $hidden = ['status', 'product_id'];
    protected $appends = ['status_value'];
    protected $dates = ['deleted_at'];
    public function getStatusValueAttribute()
    {
        if ($this->status == 1) {
            return 'فعال';
        } else {
            return 'غیرفعال';
        }
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
