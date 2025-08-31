<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



/**
 * @OA\Schema(
 *     schema="Banner",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="بنر تبلیغاتی صفحه اصلی"),
 *     @OA\Property(property="url", type="string", format="url", description="a valid url", example="https://example.com"),
 *     @OA\Property(property="image", type="string", format="uri", example="\path\image.jpg"),
 *     @OA\Property(property="position", type="integer", description="Each number in the `position` field corresponds to a specific position on the page, determined by designer.For example 0 means above of the main page big slideshow", example=0),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="Banner status: 'active' if 1, 'inactive' if 2", example="فعال"),
 * )
 */
class Banner extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['status_value'];
    protected $casts = ['image' => 'array'];
    protected $fillable = ['title', 'image', 'url', 'position', 'status'];

    protected $hidden = ['status'];
    // static function
    // public static $positions = [
    //     0 => 'اسلایدشو (صفحه اصلی)',
    //     1 => 'کنار اسلایدشو (صفحه اصلی)',
    //     2 => 'دو بنر تبلیغاتی بین دو اسلایدر (صفحه اصلی)',
    //     3 => 'بنر تبلیغاتی بزرگ پایین دو اسلایدر (صفحه اصلی)'
    // ];

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
    // public function getPositionValueAttribute()
    // {
    //     switch($this->position)
    //     {
    //         case 0 :
    //             $result = 'اسلایدشو (صفحه اصلی)';
    //             break;
    //         case 1 :
    //             $result = 'کنار اسلایدشو (صفحه اصلی)';
    //             break;
    //         case 2 :
    //             $result = 'دو بنر تبلیغاتی بین دو اسلایدر (صفحه اصلی)';
    //             break;
    //         case 3:
    //             $result = 'بنر تبلیغاتی بزرگ پایین دو اسلایدر (صفحه اصلی)';
    //             break;
    //     }
    //     return $result;
    // }
}
