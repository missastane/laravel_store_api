<?php

namespace App\Models\User;

use App\Models\User;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Role",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="create-post"),
 *     @OA\Property(property="description", type="string", example="create-post-description"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="Permission status: 'active' if 1, 'inactive' if 2", example="فعال"),
 *     @OA\Property(
 *         property="permissions",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=5),
 *         @OA\Property(property="name", type="string", example="delete-post")
 *     )
 * )
 */
class Role extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['name', 'description', 'status'];
    protected $hidden = ['status', 'pivot'];
    protected $appends = ['status_value'];
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
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
        public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
