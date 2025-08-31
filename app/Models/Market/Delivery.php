<?php

namespace App\Models\Market;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Delivery",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="پست ممتاز"),
 *     @OA\Property(property="amount", type="float", example=125000),
 *     @OA\Property(property="delivery_time", type="integer", example=1),
 *     @OA\Property(property="delivery_time_unit", type="string", maxLength=255, example="day or hour"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="Delivery status: 'active' if 1, 'inactive' if 2", example="فعال"),
 * )
 */
class Delivery extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $table = 'delivery';
    protected $cascadeDeletes = ['orders'];
    protected $hidden = ['status'];
    protected $appends = ['status_value'];
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
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    protected $fillable = ['name', 'amount', 'delivery_time', 'delivery_time_unit', 'status'];
}
