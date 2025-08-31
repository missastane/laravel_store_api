<?php

namespace App\Models\Market;

use App\Models\User;
use App\Models\User\Address;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Order",
 *     title="Order",
 *     description="Represents an order placed by a user",
 *     @OA\Property(property="id", type="integer", description="The unique identifier for the order", example=24),
 *     @OA\Property(property="postal_tracking_code", type="string", description="Tracking code for the shipment", example="1234567890123456789"),
 *     @OA\Property(
 *         property="address_object",
 *         type="object",
 *         description="User's address details",
 *         @OA\Property(property="id", type="integer", example=3),
 *         @OA\Property(property="user_id", type="integer", example=18),
 *         @OA\Property(property="city_id", type="integer", example=3),
 *         @OA\Property(property="recipient_first_name", type="string", example="راضیه"),
 *         @OA\Property(property="recipient_last_name", type="string", example="آذری آستانه"),
 *         @OA\Property(property="mobile", type="string", example="09115577889"),
 *         @OA\Property(property="postal_code", type="string", example="4441775584"),
 *         @OA\Property(property="unit", type="string", example="2"),
 *         @OA\Property(property="no", type="string", example="-"),
 *         @OA\Property(property="address", type="string", example="خ شهید نواب صفوی، کوی یاسر رحیمی پور"),
 *         @OA\Property(property="status", type="integer", example=2),
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-11T08:21:25.000000Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-11T08:21:25.000000Z"),
 *         @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null)
 *     ),
 *     @OA\Property(
 *         property="payment_object",
 *         type="object",
 *         description="Payment details of the order",
 *         @OA\Property(property="id", type="integer", example=5),
 *         @OA\Property(property="user_id", type="integer", example=18),
 *         @OA\Property(property="amount", type="string", example="3250000.000"),
 *         @OA\Property(property="pay_date", type="string", format="date-time", example="2024-11-17T16:12:02.679967Z"),
 *         @OA\Property(property="transaction_id", type="string", nullable=true, example=null),
 *         @OA\Property(property="status", type="integer", example=1),
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-17T16:12:02.000000Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-17T16:12:02.000000Z")
 *     ),
 *     @OA\Property(
 *         property="delivery_object",
 *         type="object",
 *         description="Delivery service details",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="پیک موتوری"),
 *         @OA\Property(property="amount", type="float", example="50000.000"),
 *         @OA\Property(property="delivery_time", type="integer", example=1),
 *         @OA\Property(property="delivery_time_unit", type="string", example="ساعت"),
 *         @OA\Property(property="status", type="integer", example=1),
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-21T08:22:58.000000Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-21T09:39:46.000000Z"),
 *         @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null)
 *     ),
 *     @OA\Property(property="delivery_amount", type="string", example="50000.000"),
 *     @OA\Property(property="delivery_date", type="string", format="date-time", nullable=true, example=null),
 *     @OA\Property(property="order_final_amount", type="string", example="3250000.000"),
 *     @OA\Property(property="order_discount_amount", type="string", example="0.000"),
 *     @OA\Property(
 *         property="copan_object",
 *         type="object",
 *         description="Copan details",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="code", type="string", example="eyedenorouz"),
 *         @OA\Property(property="amount", type="float", example="50000.000"),
 *         @OA\Property(
 *                 property="amount_type",
 *                 oneOf={
 *                     @OA\Schema(type="integer", example=1, description="1 = price unit"),
 *                     @OA\Schema(type="integer", example=2, description="2 = percentage")
 *                 }
 *             ),
 *         @OA\Property(property="discount_ceiling", type="integer", example=1),
 *         @OA\Property(
 *                 property="type",
 *                 oneOf={
 *                     @OA\Schema(type="integer", example=1, description="1 = private for `one user`"),
 *                     @OA\Schema(type="integer", example=2, description="2 = common for `every user`")
 *                 }
 *             ),
 *         @OA\Property(
 *                 property="status",
 *                 oneOf={
 *                     @OA\Schema(type="integer", example=1, description="1 = active"),
 *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
 *                 }
 *             ),
 *         @OA\Property(property="start_date", type="string", format="date-time", example="2024-10-21T08:22:58.000000Z"),
 *         @OA\Property(property="end_date", type="string", format="date-time", example="2024-10-21T08:22:58.000000Z"),
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-21T08:22:58.000000Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-21T09:39:46.000000Z"),
 *         @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null)
 *     ),
 *     @OA\Property(property="order_copan_discount_amount", type="string", nullable=true, example=null),
 *     @OA\Property(
 *         property="common_discount_object",
 *         type="object",
 *         description="Common Discount details",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="title", type="string", example="ملاید امام حسین ع"),
 *         @OA\Property(property="percentage", type="integer", example=10),
 *         @OA\Property(property="discount_ceiling", type="float", example="50000.000"),
 *         @OA\Property(property="minimal_order_amount", type="float", example="50000.000"),
 *        
 *         @OA\Property(
 *                 property="status",
 *                 oneOf={
 *                     @OA\Schema(type="integer", example=1, description="1 = active"),
 *                     @OA\Schema(type="integer", example=2, description="2 = inactive")
 *                 }
 *             ),
 *         @OA\Property(property="start_date", type="string", format="date-time", example="2024-10-21T08:22:58.000000Z"),
 *         @OA\Property(property="end_date", type="string", format="date-time", example="2024-10-21T08:22:58.000000Z"),
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-21T08:22:58.000000Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-21T09:39:46.000000Z"),
 *         @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null)
 *     ),
 *     @OA\Property(property="order_common_discount_amount", type="string", example="0.000"),
 *     @OA\Property(property="order_total_products_discount_amount", type="string", example="0.000"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-17T16:11:53.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-09T16:31:16.000000Z"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null),
 *     @OA\Property(property="payment_status_value", type="string", example="پرداخت شده"),
 *     @OA\Property(property="payment_type_value", type="string", example="آفلاین"),
 *     @OA\Property(property="delivery_status_value", type="string", example="در حال ارسال"),
 *     @OA\Property(property="order_status_value", type="string", example="باطل شده"),
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         description="User details",
 *         @OA\Property(property="id", type="integer", example=18),
 *         @OA\Property(property="first_name", type="string", example="آناهید"),
 *         @OA\Property(property="last_name", type="string", example="آذری آستانه"),
 *     )
 * )
 */
class Order extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;
    protected $fillable = ['user_id', 'postal_tracking_code', 'address_id', 'address_object', 'payment_id', 'payment_object', 'payment_type', 'payment_status', 'delivery_id', 'delivery_object', 'delivery_amount', 'delivery_status', 'delivery_date', 'order_final_amount', 'order_discount_amount', 'copan_id', 'copan_object', 'order_copan_discount_amount', 'common_discount_id', 'common_discount_object', 'order_common_discount_amount', 'order_total_products_discount_amount', 'order_status'];
    protected $cascadeDeletes = ['orderItems'];

    protected $dates = ['deleted_at'];

    protected $hidden = [
        'user_id',
        'address_id',
        'payment_id',
        'payment_type',
        'payment_status',
        'delivery_id',
        'delivery_status',
        'copan_id',
        'common_discount_id',
        'order_status'
    ];
    protected $casts = [
        'address_object' => 'array',
        'payment_object' => 'array',
        'delivery_object' => 'array',
        'copan_object' => 'array',
        'common_discount_object' => 'array',
    ];
    protected $appends = ['payment_status_value', 'payment_type_value', 'delivery_status_value', 'order_status_value'];
    public function address()
    {
        return $this->belongsTo(Address::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }
    public function copan()
    {
        return $this->belongsTo(Copan::class);
    }

    public function commonDiscount()
    {
        return $this->belongsTo(CommonDiscount::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getPaymentStatusValueAttribute()
    {
        switch ($this->payment_status) {
            case 0:
                $result = 'پرداخت نشده';
                break;
            case 1:
                $result = 'پرداخت شده';
                break;
            case 2:
                $result = 'باطل شده';
                break;
            default:
                $result = 'مرجوع شده';
        }
        return $result;
    }

    public function getPaymentTypeValueAttribute()
    {
        switch ($this->payment_type) {
            case 0:
                $result = 'آنلاین';
                break;
            case 1:
                $result = 'آفلاین';
                break;
            case 2:
                $result = 'پرداخت شده';
                break;
            default:
                $result = 'در محل';
        }
        return $result;
    }


    public function getDeliveryStatusValueAttribute()
    {
        switch ($this->delivery_status) {
            case 0:
                $result = 'ارسال نشده';
                break;
            case 1:
                $result = 'در حال ارسال';
                break;
            case 2:
                $result = 'ارسال شده';
                break;

            default:
                $result = 'تحویل داده شده';
        }
        return $result;
    }

    public function getOrderStatusValueAttribute()
    {
        switch ($this->order_status) {
            case 1:
                $result = 'در انتظار تأیید';
                break;
            case 2:
                $result = 'تأیید نشده';
                break;
            case 3:
                $result = 'تأیید شده';
                break;
            case 4:
                $result = 'باطل شده';
                break;
            case 5:
                $result = 'مرجوع شده';
                break;
            default:
                $result = 'بررسی نشده';
        }
        return $result;
    }

    public function scopeFilter($query, $filters)
    {
        return $query->when(isset($filters['order_status']), function ($q) use ($filters) {
            $q->whereIn('order_status', $this->getOrderStatusCodes($filters['order_status']));
        })->when(isset($filters['payment_status']), function ($q) use ($filters) {
            $q->whereIn('payment_status', $this->getPaymentStatusCodes($filters['payment_status']));
        })->when(isset($filters['delivery_status']), function ($q) use ($filters) {
            $q->whereIn('delivery_status', $this->getDeliveryStatusCodes($filters['delivery_status']));
        });
    }

    private function getOrderStatusCodes($statuses)
    {
        return $this->convertTocodes(
            $statuses,
            [
                'unseen' => 0,
                'processing' => 1,
                'not-approved' => 2,
                'approved' => 3,
                'canceled' => 4,
                'returned' => 5
            ]
        );
    }

    private function getPaymentStatusCodes($statuses)
    {
        return $this->convertTocodes(
            $statuses,
            [
                'unpaid' => 0,
                'paid' => 1,
                'canceled' => 2,
                'returned' => 3
            ]
        );
    }

    private function getDeliveryStatusCodes($statuses)
    {
        return $this->convertTocodes(
            $statuses,
            [
                'not_sending' => 0,
                'sending' => 1,
                'sent' => 2,
                'delivered' => 3
            ]
        );
    }

    private function convertToCodes($statuses, $mapping)
    {
        // get statuses('processing, sending,canceling) and return an array and convert to a cellection
        return collect(explode(',', $statuses))
            ->map(fn($s) => $mapping[trim($s)] ?? (is_numeric($s) ? (int) $s : null))
            ->filter()
            ->toArray();
        // after enters a loop and each member will be trim. if a member exist in mapping , returns a code
        // will be returns as int unless returns null. then null will be removed by filter method and convert toarray.
    }
}
