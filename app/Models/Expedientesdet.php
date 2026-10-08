<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expedientesdet extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'expedientesdets';
    protected $fillable = [
        'IdExpediente',
        'descripcion',
        'fechaPre',
        'fechaAcu',
        'fechaPub',
        'adicionales'
    ];
    protected $casts = [
        'adicionales' => 'array'
    ];
    protected static function booted()
    {
        static::deleting(function ($model) {
            foreach ($model->documentos as $documento) {
                $documento->delete();
            }
        });
    }
    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'IdExpediente', 'id');
    }
    public function documentos()
    {
        return $this->hasMany(Expedientesdoc::class, 'IdExpedienteDet', 'id');
    }
}
