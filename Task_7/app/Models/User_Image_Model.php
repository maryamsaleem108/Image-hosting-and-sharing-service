<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_Image_Model extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'user_image';
    protected $primaryKey = 'id';

    protected $fillable = [
        'userid',
        'imageid',
    ];
}
