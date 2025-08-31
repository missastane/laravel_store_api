<?php

namespace App\Models\Content;

use App\Models\User;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @OA\Schema(
 *     schema="Comment",
 *     type="object",
 *     title="Comment",
 *     description="Schema for a comment",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="body", type="string", example="این محصول عالیه!"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-25T12:45:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-25T12:50:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", example="2025-02-25T12:50:00Z"),
 *     @OA\Property(property="seen_value", type="string", description="Comment Seen_Value: 'دیده شده' if 1, 'دیده نشده' if 2", example="دیده شده"),
 *     @OA\Property(property="approved_value", type="string", description="Comment Approved_Value: 'تأیید شده' if 1, 'تأیید نشده' if 2", example="تأیید شده"),
 *     @OA\Property(property="status_value", type="string", description="Comment status: 'active' if 1, 'inactive' if 2", example="فعال"),
 *     @OA\Property(property="commentable_type_value", type="string", example="نظر متعلق به یک محصول است"),
 *     @OA\Property(
 *          property="commentable",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="نام پست یا محصول")
 *               )
 *            ),
 *    @OA\Property(
 *          property="parent",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="body", type="string", example="این نظر منه")
 *               )
 *            ),
 *    @OA\Property(
 *          property="user",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="first_name", type="string", example="ایمان"),
 *                  @OA\Property(property="last_name", type="string", example="مدائنی")
 *               )
 *            ),
 * )
 */

class Comment extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $cascadeDeletes = ['children'];
    protected $hidden = ['parent_id', 'author_id', 'status', 'approved', 'seen', 'commentable_id', 'commentable_type'];
    protected $fillable = ['body', 'parent_id', 'author_id', 'commentable_id', 'commentable_type', 'approved', 'status'];
    protected $dates = ['deleted_at'];

    protected $appends = ['status_value', 'approved_value', 'seen_value', 'commentable_type_value'];

    public function getCommentableTypeValueAttribute()
    {
        if($this->commentable_type == Post::class)
        {
            return 'نظر متعلق به یک پست است';
        }
        else{
            return 'نظر متعلق به یک محصول است';
        }
    }
    public function getApprovedValueAttribute()
    {
        if($this->status == 1)
        {
            return 'تأیید شده';
        }
        else{
            return 'تأیید نشده';
        }
    }
    public function getSeenValueAttribute()
    {
        if($this->status == 1)
        {
            return 'دیده شده';
        }
        else{
            return 'دیده نشده';
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
    public function user()
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function parent()
    {
        return $this->belongsTo($this, 'parent_id', 'id')->with('parent');
    }
    public function children()
    {
        return $this->hasMany($this, 'parent_id', 'id')->with('children');
    }

    
}
