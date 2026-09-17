<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
	use HasFactory;
	
    public $timestamps = false;

    protected $table = 'materias';

    protected $fillable = ['materia','adicionales'];
    protected $casts = [
        'adicionales' => 'array'
    ];
	
    public function organos()
    {
        return $this->hasMany('App\Models\Organo', 'IdMateria', 'id');
    }
    
}
