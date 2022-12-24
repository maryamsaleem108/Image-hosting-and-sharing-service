<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'users';
    protected $primaryKey = 'userid';

    protected $fillable = [
        'name',
        'age',
        'email',
        'phone_number',
        'password',
        'profilepicture'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

}


