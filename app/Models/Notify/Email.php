<?php

namespace App\Models\Notify;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Email",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="subject", type="string", example="حراج فصل آمازون"),
 *     @OA\Property(property="body", type="string", example="توضیحات حراج فصل آمازون"),
 *     @OA\Property(property="published_at", type="string", format="date-time", description="published datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="Product status: 'active' if 1, 'inactive' if 2", example="فعال"),
 * )
 */
class Email extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $table = 'public_mail';
    protected $dates = ['deleted_at'];
    protected $cascadeDeletes = ['files'];
    protected $fillable = ['subject', 'body', 'status', 'published_at'];
    protected $hidden = ['status'];
    protected $appends = ['status_value'];
    public function getStatusValueAttribute()
    {
        if ($this->status == 1) {
            return 'فعال';
        } else {
            return 'غیرفعال';
        }
    }
    public function files()
    {
        return $this->hasMany(EmailFile::class, 'public_mail_id', 'id');
    }
}