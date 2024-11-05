<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechProject extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id',
        'techs_id',
    ];
    public function project()
    {
        return $this->belongsTo(Project::class, 'id');
    }
    public function techs()
    {
        return $this->belongsTo(Tech::class, 'techs_id');
    }
}
