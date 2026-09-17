<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Expediente;
use App\Models\Util;
class Expedientesdet extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'expedientesdets';

    protected $fillable = [
        'IdExpediente', 'descripcion', 'fechaPre', 'fechaAcu', 'fechaPub',
        'docPromocion', 'docAnexo', 'docAcuerdo', 'adicionales'
    ];

    protected $casts = [
        'adicionales' => 'array'
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (!empty($model->fechaPre) && empty($model->docPromocion)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'filePromocion' => 'El documento de promoción es obligatorio si se indica la fecha de presentación.'
                ]);
            }
            if (!empty($model->fechaPub) && empty($model->docAcuerdo)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'fileAcuerdo' => 'El documento de acuerdo es obligatorio si se indica la fecha de publicación.'
                ]);
            }
        });

        // AQUÍ ES DONDE SE COLOCA EL BORRADO DE ARCHIVOS
        static::deleting(function ($model) {
            if ($model->docPromocion) {
                Util::borrarArchivo('documentos/promo', $model->docPromocion);
            }
            if ($model->docAnexo) {
                Util::borrarArchivo('documentos/anexo', $model->docAnexo);
            }
            if ($model->docAcuerdo) {
                Util::borrarArchivo('documentos/acuerdo', $model->docAcuerdo);
            }
        });
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'IdExpediente', 'id');
    }

    public function getUrlPromocionAttribute(): ?string
    {
        return $this->docPromocion
            ? Storage::url('documentos/promo/' . $this->docPromocion)
            : null;
    }

    public function getUrlAnexoAttribute(): ?string
    {
        return $this->docAnexo
            ? Storage::url('documentos/anexo/' . $this->docAnexo)
            : null;
    }

    public function getUrlAcuerdoAttribute(): ?string
    {
        return $this->docAcuerdo
            ? Storage::url('documentos/acuerdo/' . $this->docAcuerdo)
            : null;
    }
}