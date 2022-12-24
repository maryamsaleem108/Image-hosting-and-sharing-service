<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageModel extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'images';
    protected $primaryKey = 'imageid';

    protected $fillable = [
        'name',
        'extension',
        'date',
        'time',
        'visibility',
        'imagepath'
    ];

    protected $attributes = [
        'visibility' => 'hidden',
    ];

}
