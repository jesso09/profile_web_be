<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tech extends Model
{
    use HasFactory;
   
    protected $fillable = [
        'name',
        'status',
    ];
    
    public function techsProject()
    {
        return $this->hasMany(TechProject::class, 'techs_id', 'id');
    }
}
