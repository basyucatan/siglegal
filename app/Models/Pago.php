<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
	use HasFactory;
	
    public $timestamps = false;

    protected $table = 'pagos';

    protected $fillable = ['IdRecibo','IdValida','montoPago','tipo','IdCuenta','fecha','adicionales'];
    protected $casts = [
        'adicionales' => 'array'
    ];
	
    public function recibo()
    {
        return $this->hasOne('App\Models\Recibo', 'id', 'IdRecibo');
    }
    public function valida()
    {
        return $this->belongsTo(User::class, 'IdValida');
    }

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'IdCuenta');
    }
}
