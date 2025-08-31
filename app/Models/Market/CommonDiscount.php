<?php

namespace App\Models\Market;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="CommonDiscount",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="عید سعید قربان"),
 *     @OA\Property(property="percentage", type="integer", example=5),
 *     @OA\Property(property="discount_ceiling", type="float", example=65.500),
 *     @OA\Property(property="minimal_order_amount", type="float", example=65.500),
 *     @OA\Property(property="start_date", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="end_date", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="Common Discount status: 'active' if 1, 'inactive' if 2", example="فعال"),
 * )
 */
class CommonDiscount extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $table = 'common_discount';
    protected $cascadeDeletes = ['orders'];

    protected $dates = ['deleted_at'];
    protected $fillable = ['title', 'percentage', 'discount_ceiling', 'minimal_order_amount', 'status', 'start_date', 'end_date'];
    protected $hidden = ['status'];
    protected $appends = ['status_value'];
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
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

}
