<?php

namespace App\Models\Content;

use App\Models\Tag;
use Cviebrock\EloquentSluggable\Sluggable;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;


/**
 * @OA\Schema(
 *     schema="PostCategory",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="تکنولوژی"),
 *     @OA\Property(property="description", type="string", example="توضیحات تکنولوژی"),
 *     @OA\Property(property="image",type="object",
 *        @OA\Property(property="indexArray",type="object",
 *           @OA\Property(property="large", type="string", format="uri", example="images\\post-category\\2025\\02\\03\\1738570484\\1738570484_large.jpg"),
 *           @OA\Property(property="medium", type="string", format="uri", example="images\\post-category\\2025\\02\\03\\1738570484\\1738570484_medium.jpg"),
 *           @OA\Property(property="small", type="string", format="uri", example="images\\post-category\\2025\\02\\03\\1738570484\\1738570484_small.jpg")
 *        ),
 *        @OA\Property(property="directory",type="string",example="images\\post-category\\2025\\02\\03\\1738570484"),
 *        @OA\Property(property="currentImage",type="string",example="medium")
 *      ),
 *     @OA\Property(property="slug", type="string", maxLength=255, example="example-slug"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="PostCategory status: 'active' if 1, 'inactive' if 2", example="فعال"),
 *     @OA\Property(
 *          property="tags",
 *          type="array",
 *          description="Array of related tags with both ID and name",
 *             @OA\Items(
 *                  type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="تکنولوژی")
 *               )
 *            ),
 * 
 * )
 */

class PostCategory extends Model
{
    use HasFactory, SoftDeletes, Sluggable, CascadeSoftDeletes;

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['name', 'id']
            ]
        ];
    }
    protected $cascadeDeletes = ['posts'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    protected $dates = ['deleted_at'];

    protected $appends = ['status_value'];
    protected $hidden = ['status'];
    protected $casts = ['image' => 'array'];
    protected $fillable = ['name', 'description', 'slug', 'image', 'status'];
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
    public function getStatusValueAttribute()
    {
        if ($this->status == 1) {
            return 'فعال';
        } else {
            return 'غیرفعال';
        }
    }
}
