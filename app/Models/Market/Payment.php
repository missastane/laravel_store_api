<?php

namespace App\Models\Market;

use App\Models\User;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @OA\Schema(
 *     schema="Payment",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="amount", type="float", example=5000.000),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="Payment status: 'notPaid' if 0, 'paid' if 1, 'canceled' if 2, 'returned' if 3", example="پرداخت شده"),
 *     @OA\Property(property="type_value", type="string", description="type value 'online' if 0, 'offline' if 1, 'cash' if 2", example="آنلاین"),
 *     @OA\Property(property="paymentable_type_value", type="string", example="نوع روش پرداخت"),
 *     @OA\Property(
 *          property="paymentable",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="amount", type="float", example=100000.00),
 *                  @OA\Property(property="pay_date", type="string", format="datetime",description="paydate datetime", example="2025-02-22T14:30:00Z"),
 * 
 *               )
 *            ),
 *        @OA\Property(
 *          property="user",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="first_name", type="string", example="راضیه"),
 *                  @OA\Property(property="last_name", type="string", example="آذری آستانه"),
 *               )
 *            ),
 * )
 */
class Payment extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $fillable = ['amount', 'user_id', 'type', 'paymentable_id', 'paymentable_type', 'status'];
    protected $cascadeDeletes = ['orders'];
    protected $hidden = ['status', 'paymentable_type', 'paymentable_id', 'user_id', 'type'];
    protected $appends = ['status_value', 'type_value','paymentable_type_value'];
    protected $dates = ['deleted_at'];

    public function getPaymentableTypeValueAttribute()
    {
        if ($this->paymentable_type == OnlinePayment::class) {
            return 'روش پرداخت آنلاین است';
        } 
        elseif ($this->paymentable_type == OfflinePayment::class) {
            return 'روش پرداخت آفلاین است';
        } 
        else {
            return 'روش پرداخت نقدی است';
        }
    }
    public function getStatusValueAttribute()
    {
        switch ($this->type) {
            case 0:
                $result = 'پرداخت نشده';
                break;
            case 1:
                $result = 'پرداخت شده';
                break;
            case 2:
                $result = 'لغو شده';
                break;
            case 3:
                $result = 'مرجوع شده';
                break;
        }
        return $result;
    }
    public function getTypeValueAttribute()
    {
        switch ($this->type) {
            case 0:
                $result = 'آنلاین';
                break;
            case 1:
                $result = 'آفلاین';
                break;
            case 2:
                $result = 'نقدی';
                break;
        }
        return $result;
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function paymentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFilterByType($query, $type)
    {
        // convert type to class model
        $paymentTypes = [
            'online' => OnlinePayment::class,
            'offline' => OfflinePayment::class,
            'cash' => CashPayment::class,
        ];

        // if the type is valid filters query
        if (isset($paymentTypes[$type])) {
            return $query->where('paymentable_type', $paymentTypes[$type]);
        }
        // return all the payments
        return $query;
    }



}
