<?php

namespace App\Models\Market;

use App\Models\Tag;
use Cviebrock\EloquentSluggable\Sluggable;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Brand",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="persian_name", type="string", example="سامسونگ"),
 *     @OA\Property(property="original_name", type="string", example="Samsung"),
 *     @OA\Property(property="logo",type="object",
 *        @OA\Property(property="indexArray",type="object",
 *           @OA\Property(property="large", type="string", format="uri", example="images\\brand\\2025\\02\\03\\1738570484\\1738570484_large.jpg"),
 *           @OA\Property(property="medium", type="string", format="uri", example="images\\brand\\2025\\02\\03\\1738570484\\1738570484_medium.jpg"),
 *           @OA\Property(property="small", type="string", format="uri", example="images\\brand\\2025\\02\\03\\1738570484\\1738570484_small.jpg")
 *        ),
 *        @OA\Property(property="directory",type="string",example="images\\brand\\2025\\02\\03\\1738570484"),
 *        @OA\Property(property="currentImage",type="string",example="medium")
 *      ),
 *     @OA\Property(property="slug", type="string", maxLength=255, example="example-slug"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="Product status: 'active' if 1, 'inactive' if 2", example="فعال"),
 *       @OA\Property(
 *          property="tags",
 *          type="array",
 *          description="Array of related tags with both ID and name",
 *             @OA\Items(
 *                  type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="برند سامسونگ")
 *               )
 *            ),
 * )
 */
class Brand extends Model
{
    use HasFactory, Sluggable, SoftDeletes, CascadeSoftDeletes;
    protected $cascadeDeletes = ['products'];

    protected $dates = ['deleted_at'];
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['original_name']
            ]
        ];
    }
    protected $casts = ['logo' => 'array'];
    protected $hidden = ['status'];
    protected $appends = ['status_value'];
    protected $fillable = ['persian_name', 'original_name', 'slug', 'logo', 'status'];
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

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
