<?php

namespace App\Models\Setting;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



/**
 * @OA\Schema(
 *     schema="Setting",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="بنر تبلیغاتی صفحه اصلی"),
 *     @OA\Property(property="description", type="string", example="بنر تبلیغاتی صفحه اصلی"),
 *     @OA\Property(property="icon", type="string", format="uri", example="\path\icon.png"),
 *     @OA\Property(property="logo", type="string", format="uri", example="\path\logo.png"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 * )
 */
class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'icon', 'logo'];
    public function keywords()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

}
