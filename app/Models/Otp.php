<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;
    protected $table = 'otps';
    protected $fillable = ['token', 'user_id', 'otp_code', 'login_id', 'type', 'used', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
