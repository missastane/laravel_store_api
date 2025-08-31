<?php

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Gallery",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="ضمانت 24 ماهه پاکشوما"),
 *     @OA\Property(property="image",type="object",
 *        @OA\Property(property="indexArray",type="object",
 *           @OA\Property(property="large", type="string", format="uri", example="images\\market\\product\\gallery\\12\\2025\\02\\03\\1738570484\\1738570484_large.jpg"),
 *           @OA\Property(property="medium", type="string", format="uri", example="images\\market\\product\\gallery\\12\\2025\\02\\03\\1738570484\\1738570484_medium.jpg"),
 *           @OA\Property(property="small", type="string", format="uri", example="images\\market\\product\\gallery\\12\\2025\\02\\03\\1738570484\\1738570484_small.jpg")
 *        ),
 *        @OA\Property(property="directory",type="string",example="images\\market\\product\\gallery\\12\\2025\\02\\03\\1738570484"),
 *        @OA\Property(property="currentImage",type="string",example="medium")
 *      ),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string",description="delete datetime", format="datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="product", type="object",
 *          @OA\Property(property="id", type="integer", example=2),
 *          @OA\Property(property="name", type="string", example="ماوس میوا"),
 *      )
 * )
 */
class Gallery extends Model
{
    use HasFactory;

    protected $casts = ['image' => 'array'];
    protected $fillable = ['name', 'image', 'product_id'];
    protected $hidden = ['product_id'];
    protected $table = 'product_images';

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
