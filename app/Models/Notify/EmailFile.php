<?php

namespace App\Models\Notify;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="EmailFile",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="فایل مهم"),
 *     @OA\Property(property="original_name", type="string", example="xyz.jpg"),
 *     @OA\Property(property="file_path", type="string", example="/path/file.zip"),
 *     @OA\Property(property="file_size", type="integer", example=956321),
 *     @OA\Property(property="file_type", type="string", example="jpg,zip,docs,pdf"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="EmailFile status: 'active' if 1, 'inactive' if 2", example="فعال"),
 *        @OA\Property(
 *          property="email",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="subject", type="string", example="حراج فصل")
 *               )
 *            ),
 * )
 */
class EmailFile extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'public_mail_files';

    protected $fillable = ['public_mail_id','name','original_name','file_path', 'file_size', 'file_type', 'status'];
    protected $hidden = ['status', 'public_mail_id'];
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
    public function email()
    {
        return $this->belongsTo(Email::class, 'public_mail_id', 'id');
    }
}
