<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColorProject extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id',
        'color1',
        'color2',
        'color3',
    ];
}
