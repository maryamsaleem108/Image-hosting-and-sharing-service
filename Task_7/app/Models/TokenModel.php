<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenModel extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'user_token';
    protected $primaryKey = 'tokenid';

    protected $fillable = [
        'userid',
        'token',
    ];
}
