<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
	use HasFactory;
	
    public $timestamps = false;

    protected $table = 'personas';

    protected $fillable = ['persona','generales','adicionales'];
    protected $casts = [
        'adicionales' => 'array'
    ];
	
        public function expedientes()
        {
            return $this->hasMany('App\Models\Expediente', 'IdDemandado', 'id');
        }
        
        public function expedientesActor()
        {
            return $this->hasMany('App\Models\Expediente', 'IdActor', 'id');
        }
        
}
