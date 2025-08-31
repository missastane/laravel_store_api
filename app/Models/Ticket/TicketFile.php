<?php

namespace App\Models\Ticket;

use App\Models\User;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketFile extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;
    protected $hidden = ['file_type', 'status', 'ticket_id', 'user_id', 'file_size', 'deleted_at', 'created_at', 'updated_at'];
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    protected $fillable = ['file_path', 'file_size', 'file_type', 'status', 'ticket_id', 'user_id'];
}
