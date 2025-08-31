<?php

namespace App\Models\Market;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="AmazingSale",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="percentage", type="int", example=10),
 *     @OA\Property(property="start_date", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="end_date", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="AmazingSale status: 'active' if 1, 'inactive' if 2", example="فعال"),
 *        @OA\Property(
 *          property="product",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="سرخکن کنوود"),
 *               )
 *            ),
 * )
 */
class AmazingSale extends Model
{
    use HasFactory,SoftDeletes,CascadeSoftDeletes;

    
    protected $fillable = ['product_id','percentage', 'status', 'start_date', 'end_date'];
    protected $hidden = ['product_id', 'status'];
    protected $appends = ['status_value'];
    protected $cascadeDeletes = ['orderItems'];
    protected $dates = ['deleted_at'];
    public function getStatusValueAttribute()
    {
        if($this->status == 1)
        {
            return 'فعال';
        }
        else{
            return 'غیرفعال';
        }
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
