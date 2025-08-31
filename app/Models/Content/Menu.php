<?php

namespace App\Models\Content;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Menu",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="موبایل"),
 *     @OA\Property(property="url", type="string", format="url", description="a valid url", example="https://example.com"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="Menu status: 'active' if 1, 'inactive' if 2", example="فعال"),
 *        @OA\Property(
 *          property="parent",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="کالای دیجیتال")
 *               )
 *         ),
 *    )
 */
class Menu extends Model
{
    use HasFactory, SoftDeletes,CascadeSoftDeletes;

    protected $fillable = ['name', 'url', 'parent_id', 'status'];
    protected $hidden = ['parent_id', 'status'];
    protected $appends = ['status_value'];
    protected $cascadeDeletes = ['children'];

    protected $dates = ['deleted_at'];

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
    public function children()
    {
        return $this->hasMany($this,'parent_id', 'id')->with('children');
    }

    public function parent()
    {
        return $this->belongsTo($this,'parent_id', 'id')->with('parent');
    }
}
