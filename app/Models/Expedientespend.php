<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expedientespend extends Model
{
	use HasFactory;
	
    public $timestamps = false;

    protected $table = 'expedientespends';

    protected $fillable = ['IdExpediente','pendiente','fechaPro','fechaCum','adicionales'];
    protected $casts = [
        'adicionales' => 'array'
    ];
	
        public function expediente()
        {
            return $this->hasOne('App\Models\Expediente', 'id', 'IdExpediente');
        }
        
}
