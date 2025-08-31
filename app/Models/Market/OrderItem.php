<?php

namespace App\Models\Market;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="OrderItem",
 *     title="OrderItem",
 *     description="Represents an orderItem",
 *     @OA\Property(property="id", type="integer", description="The unique identifier for the order", example=24),
 *     @OA\Property(property="product_object", type="object", 
 *          @OA\Property(property="id", type="integer", example=1),
 *          @OA\Property(property="name", type="string", example="گوشی سامسونگ"),
 *          @OA\Property(property="image",type="object",
 *            @OA\Property(property="indexArray",type="object",
 *               @OA\Property(property="large", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_large.jpg"),
 *               @OA\Property(property="medium", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_medium.jpg"),
 *               @OA\Property(property="small", type="string", format="uri", example="images\\market\\product\\12\\2025\\02\\03\\1738570484\\1738570484_small.jpg")
 *             ),
 *           @OA\Property(property="directory",type="string",example="images\\market\\product\\12\\2025\\02\\03\\1738570484"),
 *           @OA\Property(property="currentImage",type="string",example="medium")
 *          ),
 *         @OA\Property(property="view", type="integer", example=1),
 *         @OA\Property(property="slug", type="string", maxLength=255, example="example-slug"),
 *         @OA\Property(property="width", type="number", format="float", example=0.5),
 *         @OA\Property(property="length", type="number", format="float", example=0.5),
 *         @OA\Property(property="weight", type="number", format="float", example=0.5),
 *         @OA\Property(property="height", type="number", format="float", example=0.5),
 *         @OA\Property(property="price", type="number", format="float", example=0.5),
 *         @OA\Property(property="introduction", type="string", example="گوشی a71 یکی از گوشی های میان رده سامسونگ است"),
 *         @OA\Property(property="marketable_number", type="integer", example=1),
 *         @OA\Property(property="frozen_number", type="integer", example=1),
 *         @OA\Property(property="sold_number", type="integer", example=1),
 *         @OA\Property(property="related_products", type="string", description="ProductIds which related to current product", example="9-10-12"),
 *         @OA\Property(property="status", type="integer", description="Product status: 'active' if 1, 'inactive' if 2", example=1),
 *         @OA\Property(property="marketable", type="integer", description="marketable_value: 'قابل فروش' if 1, 'غیرقابل فروش' if 2", example=2),
 *         @OA\Property(property="published_at", description="publish datetime", type="string", format="date-time", example="2025-02-22T10:00:00Z"),
 *         @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *         @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 * )
 * ),
 *     @OA\Property(property="amazing_sale_id", type="integer", description="The unique identifier for the AmazingSale", example=24),
 *     @OA\Property(property="amazing_sale_object", type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="percentage", type="int", example=10),
 *         @OA\Property(property="status", type="integer", description="AmazingSale status: 'active' if 1, 'inactive' if 2", example=1),
 *         @OA\Property(property="start_date", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *         @OA\Property(property="end_date", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *         @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *         @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 * ),
 *     @OA\Property(property="amazing_sale_discount_amount", type="float", example=50000.000),
 *     @OA\Property(property="number", type="number", example=2),
 *     @OA\Property(property="final_product_price", type="float", example=3250000.000),
 *     @OA\Property(property="final_total_price", description="Represent final_product_price * number", type="float", example=600000.000),
 *     @OA\Property(
 *         property="color",
 *         type="object",
 *         description="Product Color details",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="سبز"),
 *         @OA\Property(
 *                 property="status_value",
 *                 oneOf={
 *                     @OA\Schema(type="integer", example=1, description="1 = price unit"),
 *                     @OA\Schema(type="integer", example=2, description="2 = percentage")
 *                 }
 *             ),
 *             ),
 *         @OA\Property(
 *         property="guarantee",
 *         type="object",
 *         description="Product Guarantee details",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="سازگار"),
 *         @OA\Property(
 *                 property="status_value",
 *                 oneOf={
 *                     @OA\Schema(type="integer", example=1, description="1 = price unit"),
 *                     @OA\Schema(type="integer", example=2, description="2 = percentage")
 *                 }
 *             ),
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-17T16:11:53.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-09T16:31:16.000000Z"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null),
 * )
 */
class OrderItem extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;
    protected $fillable = ['order_id', 'product_id', 'product_object', 'amazing_sale_id', 'amazing_sale_object', 'amazing_sale_discount_amount', 'number', 'final_product_price', 'final_total_price', 'color_id', 'guarantee_id'];
    protected $cascadeDeletes = ['orderItemSelectedAttributes'];
    protected $casts = [
        'product_object' => 'array',
        'amazing_sale_object' => 'array',
    ];
    protected $hidden = ['product_id', 'color_id', 'guarantee_id', 'amazing_sale_id'];
    protected $dates = ['deleted_at'];


    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function orderItemSelectedAttributes()
    {
        return $this->hasMany(OrderItemSelectedAttribute::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function amazingSale()
    {
        return $this->belongsTo(AmazingSale::class);
    }

    public function color()
    {
        return $this->belongsTo(ProductColor::class, 'color_id');
    }

    public function guarantee()
    {
        return $this->belongsTo(Guarantee::class);
    }


}
