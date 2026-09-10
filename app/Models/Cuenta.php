<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
	use HasFactory;
	
    public $timestamps = false;

    protected $table = 'cuentas';

    protected $fillable = ['cuenta','nombre','adicionales'];
    protected $casts = [
        'adicionales' => 'array'
    ];
	
    public function pagos()
    {
        return $this->hasMany('App\Models\Pago', 'IdCuenta', 'id');
    }
    
}
