<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'desc',
        'status',
    ];
    
    public function projectImages()
    {
        return $this->hasMany(ProjectImage::class, 'project_id', 'id');
    }
    public function techsProject()
    {
        return $this->hasMany(TechProject::class, 'project_id', 'id');
    }

}
