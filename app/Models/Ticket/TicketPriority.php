<?php

namespace App\Models\Ticket;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="TicketPriority",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="بسیار مهم"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="status_value", type="string", description="TicketPriority status: 'active' if 1, 'inactive' if 2", example="فعال"),
 * )
 */
class TicketPriority extends Model
{
    use HasFactory,SoftDeletes,CascadeSoftDeletes;

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'priority_id');
    }
    protected $dates = ['deleted_at'];
    protected $cascadeDeletes = ['tickets'];
    protected $fillable = ['name', 'status'];
    protected $hidden = ['status'];
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
}
