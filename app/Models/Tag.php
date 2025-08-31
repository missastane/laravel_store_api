<?php

namespace App\Models;

use App\Models\Content\Faq;
use App\Models\Content\Page;
use App\Models\Content\Post;
use App\Models\Content\PostCategory;
use App\Models\Market\Brand;
use App\Models\Market\Category;
use App\Models\Market\Product;
use App\Models\Setting\Setting;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Tag",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="برنامه نویسی"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *        @OA\Property(
 *          property="taggable",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="taggable_id", description="Id of Object which related to Current tag", type="integer", example=3),
 *                  @OA\Property(property="taggable_type_value", type="string", example="کالای دیجیتال")
 *               )
 *          ), 
 *    )
 */
class Tag extends Model
{
    use SoftDeletes, CascadeSoftDeletes;
    protected $fillable = ['name'];

    public function taggables()
    {
        return $this->hasMany(Taggable::class,'tag_id');
    }
    public function products()
    {
        return $this->morphedByMany(Product::class,'taggable');
    }

    public function categories()
    {
        return $this->morphedByMany(Category::class,'taggable');
    }

    public function posts()
    {
        return $this->morphedByMany(Post::class,'taggable');
    }

    public function postCategories()
    {
        return $this->morphedByMany(PostCategory::class,'taggable');
    }
    
    public function pages()
    {
        return $this->morphedByMany(Page::class,'taggable');
    }
    public function faqs()
    {
        return $this->morphedByMany(Faq::class,'taggable');
    }

    public function brands()
    {
        return $this->morphedByMany(Brand::class,'taggable');
    }

    public function settings()
    {
        return $this->morphedByMany(Setting::class,'taggable');
    }
}
