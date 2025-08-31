<?php

namespace App\Models\Market;

use App\Models\User;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="CartItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="number", type="integer", example=2),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *        @OA\Property(
 *          property="user",
 *          type="object",
 *                   @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="first_name", type="string", example="ایمان"),
 *                  @OA\Property(property="last_name", type="string", example="مدائنی"),
 *               )
 *            ),
 *     @OA\Property(
 *          property="product",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="موبایل سامسونگ a71"),
 *                  @OA\Property(property="image",type="object",
 *                     @OA\Property(property="indexArray",type="object",
 *                        @OA\Property(property="large", type="string", format="uri", example="images\\brand\\2025\\02\\03\\1738570484\\1738570484_large.jpg"),
 *                        @OA\Property(property="medium", type="string", format="uri", example="images\\brand\\2025\\02\\03\\1738570484\\1738570484_medium.jpg"),
 *                        @OA\Property(property="small", type="string", format="uri", example="images\\brand\\2025\\02\\03\\1738570484\\1738570484_small.jpg")
 *                      ),
 *                     @OA\Property(property="directory",type="string",example="images\\brand\\2025\\02\\03\\1738570484"),
 *                     @OA\Property(property="currentImage",type="string",example="medium")
 *                   ),
 *                  @OA\Property(property="slug", type="string", example="موبایل-سامسونگ-a71"),
 *               )
 *            ),
 *    @OA\Property(
 *          property="color",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="سبز")
 *               )
 *            ),
 *  @OA\Property(
 *          property="guarantee",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="سازگار")
 *               )
 *            ),
 * 
 * )
 */
class CartItem extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $fillable = ['user_id', 'product_id', 'color_id', 'guarantee_id', 'number'];
    protected $cascadeDeletes = ['cartItemSelectedAttributes'];

    protected $hidden = ['user_id', 'product_id', 'color_id', 'guarantee_id'];
    protected $dates = ['deleted_at'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function cartItemSelectedAttributes()
    {
        return $this->hasMany(CartItemSelectedAttribute::class);
    }
    public function color()
    {
        return $this->belongsTo(ProductColor::class);
    }

    public function guarantee()
    {
        return $this->belongsTo(Guarantee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // price = ProductPrice + ColorPrice + GuaranteePrice
    public function cartItemProductPrice()
    {
        $guaranteePriceIncrease = empty($this->guarantee_id) ? 0 : $this->guarantee->price_increase;
        $colorPriceIncrease = empty($this->color_id) ? 0 : $this->color->price_increase;
        return $this->product->price + $guaranteePriceIncrease + $colorPriceIncrease;
    }

    // ProductDiscount = ProductPrice * (DiscountPercentage/100)

    public function cartItemProductDiscount()
    {
        $cartItemProductPrice = $this->cartItemProductPrice();
        $productDiscount = empty($this->product->activeAmazingSale()) ? 0 : $cartItemProductPrice * ($this->product->activeAmazingSale()->percentage / 100);
        return $productDiscount;
    }

    // number * (cartItemProductPrice - cartItemProductDiscount)
    public function cartItemFinalPrice()
    {
        $cartItemProductPrice = $this->cartItemProductPrice();
        $cartItemProductDiscount = $this->cartItemProductDiscount();
        $cartItemFinalPrice = $this->number * ($cartItemProductPrice - $cartItemProductDiscount);
        return $cartItemFinalPrice;
    }

    // number *  productDiscount
    public function cartItemFinalDiscount()
    {
        $productDiscount = $this->cartItemProductDiscount();
        return $this->number * $productDiscount;
    }

}
