<?php

namespace App\Models\Market;

use App\Models\Tag;
use Cviebrock\EloquentSluggable\Sluggable;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;


/**
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="کالای دیجیتال"),
 *     @OA\Property(property="description", type="string", example="توضیحات کالای دیجیتال"),
 *     @OA\Property(property="image",type="object",
 *        @OA\Property(property="indexArray",type="object",
 *           @OA\Property(property="large", type="string", format="uri", example="images\\product-category\\2025\\02\\03\\1738570484\\1738570484_large.jpg"),
 *           @OA\Property(property="medium", type="string", format="uri", example="images\\product-category\\2025\\02\\03\\1738570484\\1738570484_medium.jpg"),
 *           @OA\Property(property="small", type="string", format="uri", example="images\\product-category\\2025\\02\\03\\1738570484\\1738570484_small.jpg")
 *        ),
 *        @OA\Property(property="directory",type="string",example="images\\product-category\\2025\\02\\03\\1738570484"),
 *        @OA\Property(property="currentImage",type="string",example="medium")
 *      ),
 *     @OA\Property(property="slug", type="string", maxLength=255, example="example-slug"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="Product status: 'active' if 1, 'inactive' if 2", example="فعال"),
 *     @OA\Property(property="show_in_menu_value", type="string", description="show In Menu: 'yes' if 1, 'no' if 2", example="بله"),
 *        @OA\Property(
 *          property="parent",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="کالای دیجیتال")
 *               )
 *            ),
 *     @OA\Property(
 *          property="tags",
 *          type="array",
 *          description="Array of related tags with both ID and name",
 *             @OA\Items(
 *                  type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="کالای دیجیتال")
 *               )
 *            ),
 * 
 * )
 */
class Category extends Model
{
    use HasFactory, Sluggable, SoftDeletes, CascadeSoftDeletes;

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['name']
            ]
        ];
    }
    protected $cascadeDeletes = ['children', 'attributes', 'products'];

    protected $dates = ['deleted_at'];

    protected $appends = ['status_value', 'show_in_menu_value'];
    protected $hidden = ['status', 'show_in_menu', 'parent_id'];
    protected $casts = ['image' => 'array'];
    protected $fillable = ['name', 'description', 'slug', 'image', 'status', 'parent_id', 'show_in_menu'];

    protected $table = 'product_categories';
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
    public function getShowInMenuValueAttribute()
    {
        if($this->status == 1)
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
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id')->with('parent');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('children');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function attributes()
    {
        return $this->hasMany(CategoryAttribute::class);
    }

}
