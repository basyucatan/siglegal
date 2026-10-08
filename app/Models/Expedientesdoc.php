<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Expedientesdoc extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'expedientesDocs';
    protected $fillable = [
        'IdExpedienteDet',
        'tipo',
        'archivo',
        'adicionales'
    ];
    protected $casts = [
        'adicionales' => 'array'
    ];
    protected static function booted()
    {
        static::deleting(function ($model) {
            if ($model->archivo) {
                Util::borrarArchivo($model->carpeta, $model->archivo);
            }
        });
    }
    public function expedientedet()
    {
        return $this->belongsTo(Expedientesdet::class, 'IdExpedienteDet', 'id');
    }
    public function getCarpetaAttribute(): string
    {
        return match ($this->tipo) {
            'promocion' => 'documentos/promo',
            'acuerdo' => 'documentos/acuerdo',
            'anexo' => 'documentos/anexo',
            default => 'documentos'
        };
    }
    public function getUrlAttribute(): ?string
    {
        return $this->archivo
            ? Storage::url($this->carpeta . '/' . $this->archivo)
            : null;
    }
}
