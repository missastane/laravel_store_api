<?php

namespace App\Models\Ticket;

use App\Models\User;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @OA\Schema(
 *     schema="Ticket",
 *     type="object",
 *     title="Ticket Model",
 *     description="Schema for a Ticket",
 * 
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="subject", type="string", example="مشکل در رابطه با خرید محصول"),
 *     @OA\Property(property="description", type="string", example="توضیح مشکل خرید محصول"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-08T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-08T12:30:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", example="2025-03-08T12:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="Ticket status: 'closed' if 1, 'copen' if 2", example="تیکت باز"),
 *     @OA\Property(property="seen_value", type="string", description="Ticket Seen status: 'seen' if 1, 'unseen' if 2", example="دیده نشده"),
 *     @OA\Property(property="author_value", type="string", description="Ticket Author status: 'admin' if 1, 'customer' if 2", example="ادمین"),
 * 
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *          @OA\Property(property="id", type="integer", example=3),
 *          @OA\Property(property="first_name", type="string", example="ایمان"),
 *          @OA\Property(property="last_name", type="string", example="مدائنی"),
 *     ),
 *     @OA\Property(
 *         property="admin",
 *         type="object",
 *          @OA\Property(property="id", type="integer", example=3),
 *          @OA\Property(
 *           property="user",
 *           type="object",
 *           @OA\Property(property="id", type="integer", example=3),
 *           @OA\Property(property="first_name", type="string", example="ایمان"),
 *           @OA\Property(property="last_name", type="string", example="مدائنی"),
 *          ),
 *     ),
 *     @OA\Property(
 *         property="category",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=2),
 *         @OA\Property(property="name", type="string", example="پشتیبانی فنی")
 *     ),
 *     @OA\Property(
 *         property="priority",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="بسیار مهم")
 *     ),
 *     @OA\Property(
 *         property="parent",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=5),
 *         @OA\Property(property="subject", type="string", example="مشکل در خرید محصول")
 *     )
 * )
 */
class Ticket extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $fillable = ['subject', 'description', 'author', 'reference_id', 'ticket_id', 'priority_id', 'category_id', 'seen', 'status', 'user_id'];
    protected $appends = ['status_value', 'author_value', 'seen_value'];
    protected $hidden = ['author', 'reference_id', 'ticket_id', 'user_id', 'priority_id', 'category_id', 'status', 'seen'];
    protected $dates = ['deleted_at'];
    protected $cascadeDeletes = ['children', 'ticketFiles'];
    public function getStatusValueAttribute()
    {
        if ($this->status == 1) {
            return 'بسته شده';
        } else {
            return 'باز';
        }
    }
    public function getSeenValueAttribute()
    {
        if ($this->seen == 1) {
            return 'دیده شده';
        } else {
            return 'دیده نشده';
        }
    }
    public function getAuthorValueAttribute()
    {
        if ($this->author == 1) {
            return 'ادمین';
        } else {
            return 'مشتری';
        }
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(TicketAdmin::class, 'reference_id');
    }

    public function parent()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id')->with('parent');
    }

    public function children()
    {
        return $this->hasMany(Ticket::class, 'ticket_id')->with('children');
    }

    public function ticketFile()
    {
        return $this->hasOne(TicketFile::class, 'ticket_id');
    }

    public function priority()
    {
        return $this->belongsTo(TicketPriority::class, 'priority_id');
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function scopeFilter($query, $filters)
    {
        $statusMap = [
            'تیکت باز' => 2,
            'تیکت بسته' => 1,
        ];

        $seenMap = [
            'تیکت جدید' => 2,
            'تیکت دیده شده' => 1,
        ];

        if (!empty($filters['status']) && isset($statusMap[$filters['status']])) {
            $query->where('status', $statusMap[$filters['status']]);
        } elseif (!empty($filters['status']) && !isset($statusMap[$filters['status']])) {

            return $query->whereRaw('1 = 0');
        }
        if (!empty($filters['seen']) && isset($seenMap[$filters['seen']])) {
            $query->where('seen', $seenMap[$filters['seen']]);

        } elseif (!empty($filters['seen']) && !isset($seenMap[$filters['seen']])) {

            return $query->whereRaw('1 = 0');
        }

        return $query;
    }
}
