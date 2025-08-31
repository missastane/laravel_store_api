<?php

namespace App\Models\Content;

use App\Models\Tag;
use App\Models\User;
use App\Traits\HideRelationAttributes;
use Cviebrock\EloquentSluggable\Sluggable;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Content\PostCategory;

/**
 * @OA\Schema(
 *     schema="Post",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="تأثیر هوش مصنوعی بر دنیای دیجیتال"),
 *     @OA\Property(property="slug", type="string", maxLength=255, example="example-slug"),
 *     @OA\Property(property="summary", type="string", example="خلاصه ای از تأثیر هوش مصنوعی بر دنیای دیجیتال"),
 *     @OA\Property(property="body", type="string", example="توضیح تأثیر هوش مصنوعی بر دنیای دیجیتال"),
 *     @OA\Property(property="image",type="object",
 *        @OA\Property(property="indexArray",type="object",
 *           @OA\Property(property="large", type="string", format="uri", example="images\\post\\2025\\02\\03\\1738570484\\1738570484_large.jpg"),
 *           @OA\Property(property="medium", type="string", format="uri", example="images\\post\\2025\\02\\03\\1738570484\\1738570484_medium.jpg"),
 *           @OA\Property(property="small", type="string", format="uri", example="images\\post\\2025\\02\\03\\1738570484\\1738570484_small.jpg")
 *        ),
 *        @OA\Property(property="directory",type="string",example="images\\post\\2025\\02\\03\\1738570484"),
 *        @OA\Property(property="currentImage",type="string",example="medium")
 *      ),
 *     @OA\Property(property="published_at", description="publish datetime", type="string", format="date-time", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="Product status: 'active' if 1, 'inactive' if 2", example="فعال"),
 *     @OA\Property(property="commentable_value", type="string", description="commentable value: 'yes' if 1, 'no' if 2", example="فعال"),
 *        @OA\Property(
 *          property="postCategory",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="تازه های دیجیتال")
 *               )
 *            ),
 *    @OA\Property(
 *          property="user",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="first_name", type="string", example="ایمان"),
 *                  @OA\Property(property="last_name", type="string", example="مدائنی"),
 *               )
 *            ),
 *     @OA\Property(
 *          property="tags",
 *          type="array",
 *          description="Array of related tags with both ID and name",
 *             @OA\Items(
 *                  type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="تازه های دیجیتال")
 *               )
 *        ),
 * )
 */
class Post extends Model
{
    use HasFactory, Sluggable, SoftDeletes, CascadeSoftDeletes;
    use HideRelationAttributes;
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['title']
            ]
        ];
    }
    protected $cascadeDeletes = ['comments'];

    protected $dates = ['deleted_at'];
    protected $hidden = ['status', 'commentable', 'post_category_id', 'author_id'];
    protected $appends = ['status_value', 'commentable_value'];
    protected $casts = ['image' => 'array'];
    protected $fillable = ['title', 'summary', 'slug', 'image', 'status', 'body', 'commentable', 'published_at', 'post_category_id', 'author_id'];

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
    public function getCommentableValueAttribute()
    {
        if($this->commentable == 1)
        {
            return 'فعال';
        }
        else{
            return 'غیرفعال';
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
    public function postCategory()
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function comments()
    {
        return $this->morphMany('App\Models\Content\Comment', 'commentable');
    }
}
