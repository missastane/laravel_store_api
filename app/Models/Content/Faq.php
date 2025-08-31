<?php

namespace App\Models\Content;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;

/**
 * @OA\Schema(
 *     schema="Faq",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="question", type="string", example="آیا هوش مصنوعی می تواند جای برنامه نویسان را در آینده پر کند؟"),
 *     @OA\Property(property="answer", type="string", example="خیر.. هوش مصنوعی نمی تواند جای انسان ها را اشغال کند"),
 *     @OA\Property(property="slug", type="string", maxLength=255, example="example-slug"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="Faq status: 'active' if 1, 'inactive' if 2", example="فعال"),
 *     @OA\Property(
 *          property="tags",
 *          type="array",
 *          description="Array of related tags with both ID and name",
 *             @OA\Items(
 *                  type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="هوش مصنوعی")
 *               )
 *            ),
 * 
 * )
 */
class Faq extends Model
{
    use HasFactory, SoftDeletes, Sluggable;

    protected $fillable = ['question', 'answer', 'slug', 'status'];

    protected $appends = ['status_value'];
    protected $hidden = ['status'];
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
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
    public function sluggable() : array
    {
        return [
            'slug' => [
                'source' => 'question'
            ]
        ];
    }

}
