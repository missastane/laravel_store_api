<?php

namespace App\Models\Market;

use App\Models\User;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Copan",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="جشنواره قربان تا غدیر آمازون"),
 *     @OA\Property(property="amount", type="float", example=65.500),
 *     @OA\Property(property="discount_ceiling", type="float", example=65.500),
 *     @OA\Property(property="start_date", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="end_date", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="Copan status: 'active' if 1, 'inactive' if 2", example="فعال"),
 *     @OA\Property(property="amount_type_value", type="string", description="Amount Type: 'price unit' if 1, 'percentage' if 2", example="درصدی"),
 *     @OA\Property(property="type_value", type="string", description="Coapn Type: 'special for one user' if 1, 'common which means every user can use it' if 2", example="عمومی"),
 *     @OA\Property(
 *          property="user",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="first_name", type="string", example="ایمان"),
 *                  @OA\Property(property="last_name", type="string", example="مدائنی"),
 *               )
 *            ),
 * )
 */
class Copan extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;
    protected $fillable = ['code', 'amount', 'amount_type', 'discount_ceiling', 'type', 'status', 'start_date', 'user_id', 'end_date'];
    protected $hidden = ['amount_type', 'type', 'status', 'user_id'];
    protected $appends = ['status_value', 'amount_type_value', 'type_value'];
    protected $cascadeDeletes = ['orders'];
    protected $dates = ['deleted_at'];
    public function getAmountTypeValueAttribute()
    {
        if($this->status == 1)
        {
            return 'واحد پول';
        }
        else{
            return 'درصدی';
        }
    }
    public function getTypeValueAttribute()
    {
        if($this->status == 1)
        {
            return 'عمومی';
        }
        else{
            return 'اختصاصی برای یک کاربر';
        }
    }
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
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
