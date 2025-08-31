<?php

namespace App\Models\Market;

use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Content\Comment;
use Nagy\LaravelRating\Traits\Rateable;

/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="گوشی سامسونگ"),
 *     @OA\Property(property="image",type="object",
 *        @OA\Property(property="indexArray",type="object",
 *           @OA\Property(property="large", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_large.jpg"),
 *           @OA\Property(property="medium", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_medium.jpg"),
 *           @OA\Property(property="small", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_small.jpg")
 *        ),
 *        @OA\Property(property="directory",type="string",example="images\\market\\product\\12\\2025\\02\\03\\1738570484"),
 *        @OA\Property(property="currentImage",type="string",example="medium")
 *      ),
 *     @OA\Property(property="view", type="integer", example=1),
 *     @OA\Property(property="slug", type="string", maxLength=255, example="example-slug"),
 *     @OA\Property(property="width", type="number", format="float", example=0.5),
 *     @OA\Property(property="length", type="number", format="float", example=0.5),
 *     @OA\Property(property="weight", type="number", format="float", example=0.5),
 *     @OA\Property(property="height", type="number", format="float", example=0.5),
 *     @OA\Property(property="price", type="number", format="float", example=0.5),
 *     @OA\Property(property="introduction", type="string", example="گوشی a71 یکی از گوشی های میان رده سامسونگ است"),
 *      @OA\Property(property="marketable_number", type="integer", example=1),
 *      @OA\Property(property="frozen_number", type="integer", example=1),
 *      @OA\Property(property="sold_number", type="integer", example=1),
 *     @OA\Property(property="published_at", description="publish datetime", type="string", format="date-time", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="Product status: 'active' if 1, 'inactive' if 2", example="فعال"),
 *      @OA\Property(property="marketable_value", type="string", description="marketable_value: 'قابل فروش' if 1, 'غیرقابل فروش' if 2", example="قابل فروش"),
 *      @OA\Property(
 *          property="related_products_value",
 *          type="array",
 *          description="Array of related products with both ID and name",
 *             @OA\Items(
 *                  type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="Gaming Mouse")
 *               )
 *            ),
 *           @OA\Property(property="brand", type="object",
 *     @OA\Property(property="id", type="integer", example=2),
 *     @OA\Property(property="persian_name", type="string", example="اسنوا")
 * ),
 * @OA\Property(property="category", type="object",
 *     @OA\Property(property="id", type="integer", example=2),
 *     @OA\Property(property="name", type="string", example="لوازم الکترونیکی")
 * ),
 *  @OA\Property(
 *          property="tags",
 *          type="array",
 *          description="Array of related tags with both ID and name",
 *             @OA\Items(
 *                  type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="گوشی سامسونگ")
 *               )
 *            ),
 * )
 */
class Product extends Model
{
    use HasFactory, SoftDeletes, Sluggable, CascadeSoftDeletes, Rateable;

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['name']
            ]
        ];
    }
    protected $fillable = ['name', 'introduction', 'view', 'slug', 'image', 'status', 'related_products', 'length', 'width', 'height', 'weight', 'price', 'marketable', 'sold_number', 'frozen_number', 'marketable_number', 'category_id', 'brand_id', 'published_at'];
    protected $appends = ['status_value', 'marketable_value', 'related_products_value'];
    protected $casts = ['image' => 'array'];

    protected $hidden = ['brand_id', 'category_id', 'marketable', 'status', 'related_products'];
    protected $cascadeDeletes = ['orderItems', 'cartItems', 'metas', 'colors', 'images', 'values', 'guarantees', 'amazingSales', 'comments'];
    protected $dates = ['deleted_at'];

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
    public function getRelatedProductsValueAttribute()
    {
        $productIds = explode(',', $this->related_products);
        $result = [];
        foreach ($productIds as $id) {
            $product = Product::where('id', $id)->select('id', 'name')->get()->makeHidden(['status_value', 'marketable_value', 'related_products_value']);
            array_push($result, $product);
        }
        return $result;
    }
    public function getMarketableValueAttribute()
    {
        if ($this->status == 1) {
            return 'قابلیت فروش دارد';
        } else {
            return 'قابلیت فروش ندارد';
        }
    }
    public function getStatusValueAttribute()
    {
        if ($this->status == 1) {
            return 'فعال';
        } else {
            return 'غیرفعال';
        }
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function metas()
    {
        return $this->hasMany(ProductMeta::class);
    }

    public function colors()
    {
        return $this->hasMany(ProductColor::class);
    }

    public function images()
    {
        return $this->hasMany(Gallery::class);
    }

    public function values()
    {
        return $this->hasMany(CategoryValue::class);
    }

    public function attributes()
    {
        return $this->hasManyThrough(CategoryAttribute::class, CategoryValue::class);
    }
    public function guarantees()
    {
        return $this->hasMany(Guarantee::class);
    }

    public function amazingSales()
    {
        return $this->hasMany(AmazingSale::class);
    }
    public function comments()
    {
        return $this->morphMany('App\Models\Content\Comment', 'commentable');
    }

    public function activeAmazingSale()
    {
        return $this->amazingSales()->where('start_date', '<', Carbon::now())->where('end_date', '>', Carbon::now())->where('status', 1)->first();
    }
    public function activeComments()
    {
        return $this->comments()->where('approved', 1)->where('parent_id', null)->get();
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'product_user');
    }

    public function compares()
    {
        return $this->belongsToMany(Compare::class);
    }
}
