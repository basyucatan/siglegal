<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expediente extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'expedientes';
    protected $fillable = ['expediente', 'IdOrgano', 'IdActor', 'IdDemandado', 'asunto', 'adicionales'];
    protected $casts = ['adicionales' => 'array'];
    public function expedientesdets(){return $this->hasMany('App\Models\Expedientesdet', 'IdExpediente', 'id');}
    public function expedientespends(){return $this->hasMany('App\Models\Expedientespend', 'IdExpediente', 'id');}
    public function organo(){return $this->hasOne('App\Models\Organo', 'id', 'IdOrgano');}
    public function Actor(){return $this->hasOne('App\Models\Persona', 'id', 'IdActor');}
    public function Demandado(){return $this->hasOne('App\Models\Persona', 'id', 'IdDemandado');}

}
