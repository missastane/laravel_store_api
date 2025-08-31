<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission_Role extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'permission_role';
    protected $fillable = ['permission_id', 'role_id'];
}
