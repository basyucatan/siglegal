<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organo extends Model
{
	use HasFactory;
	
    public $timestamps = false;

    protected $table = 'organos';

    protected $fillable = ['IdMateria','organo','adicionales'];
    protected $casts = [
        'adicionales' => 'array'
    ];
	
    public function expedientes()
    {
        return $this->hasMany('App\Models\Expediente', 'IdOrgano', 'id');
    }
    
    public function materia()
    {
        return $this->hasOne('App\Models\Materia', 'id', 'IdMateria');
    }
    
}
