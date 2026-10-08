<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use App\Models\{Expedientesdet, Expedientesdoc, Expediente, Util};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Expedientesdets extends Component
{
    use WithPagination, WithFileUploads;
    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['expedienteElegido' => 'cargarDetalles'];
    public $verModalExpedientesdet = false, $selected_id, $keyWord = '', $IdExpediente;
    public $descripcion, $fechaPre, $fechaAcu, $fechaPub;
    public $filesPromocion = [], $nuevosPromocion = [];
    public $filesAcuerdo = [], $nuevosAcuerdo = [];
    public $filesAnexo = [], $nuevosAnexo = [];
    public ?Expedientesdet $Expedientesdet = null;

    protected function rules()
    {
        return [
            'IdExpediente' => 'required|integer|exists:expedientes,id',
            'descripcion' => 'required|string|max:255',
            'fechaPre' => 'required|date',
            'fechaAcu' => 'nullable|date',
            'fechaPub' => 'nullable|date',
            'filesPromocion' => 'array',
            'filesPromocion.*' => 'file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'filesAcuerdo' => 'array',
            'filesAcuerdo.*' => 'file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'filesAnexo' => 'array',
            'filesAnexo.*' => 'file|mimes:pdf,jpg,jpeg,png,webp|max:10240'
        ];
    }

    public function cargarDetalles($idExpediente)
    {
        $this->resetPage();
        $this->IdExpediente = $idExpediente;
        unset($this->filteredExpedientesdets, $this->expedientePadre);
    }

    public function updatedKeyWord()
    {
        $this->resetPage();
    }

    public function updatedNuevosPromocion()
    {
        $this->acumularArchivos('nuevosPromocion', 'filesPromocion');
    }

    public function updatedNuevosAcuerdo()
    {
        $this->acumularArchivos('nuevosAcuerdo', 'filesAcuerdo');
    }

    public function updatedNuevosAnexo()
    {
        $this->acumularArchivos('nuevosAnexo', 'filesAnexo');
    }

    private function acumularArchivos(string $origen, string $destino): void
    {
        $nuevos = $this->{$origen};
        if (!is_array($nuevos)) {
            $nuevos = $nuevos ? [$nuevos] : [];
        }
        $this->{$destino} = array_merge($this->{$destino}, $nuevos);
        $this->{$origen} = [];
        $this->resetValidation($destino);
    }

    public function eliminarArchivoPendiente(string $tipo, int $indice)
    {
        $propiedades = [
            'promocion' => 'filesPromocion',
            'acuerdo' => 'filesAcuerdo',
            'anexo' => 'filesAnexo'
        ];
        if (!isset($propiedades[$tipo])) {
            return;
        }
        $propiedad = $propiedades[$tipo];
        if (!array_key_exists($indice, $this->{$propiedad})) {
            return;
        }
        $archivos = $this->{$propiedad};
        unset($archivos[$indice]);
        $this->{$propiedad} = array_values($archivos);
        $this->resetValidation($propiedad);
    }

    #[Computed]
    public function filteredExpedientesdets()
    {
        if (!$this->IdExpediente) {
            return Expedientesdet::whereRaw('1 = 0')->paginate(12);
        }
        $keyWord = '%' . ($this->keyWord ?? '') . '%';
        return Expedientesdet::with('documentos')
            ->where('IdExpediente', $this->IdExpediente)
            ->where(function ($query) use ($keyWord) {
                $query->where('descripcion', 'LIKE', $keyWord)
                    ->orWhereHas('documentos', function ($q) use ($keyWord) {
                        $q->where('archivo', 'LIKE', $keyWord)
                            ->orWhere('tipo', 'LIKE', $keyWord);
                    });
            })
            ->orderByDesc('id')
            ->paginate(12);
    }

    public function render()
    {
        return view('livewire.expedientesdets.view', [
            'expedientesdets' => $this->filteredExpedientesdets
        ]);
    }

    public function resetInput()
    {
        $this->reset([
            'selected_id',
            'descripcion',
            'fechaPre',
            'fechaAcu',
            'fechaPub',
            'filesPromocion',
            'nuevosPromocion',
            'filesAcuerdo',
            'nuevosAcuerdo',
            'filesAnexo',
            'nuevosAnexo'
        ]);
        $this->Expedientesdet = null;
        $this->resetValidation();
    }

    public function cancel()
    {
        $this->resetInput();
        $this->verModalExpedientesdet = false;
    }

    public function create()
    {
        $this->resetInput();
        $this->verModalExpedientesdet = true;
    }

    public function edit($id)
    {
        $this->resetInput();
        $registro = Expedientesdet::with('documentos')
            ->where('IdExpediente', $this->IdExpediente)
            ->findOrFail($id);
        $this->selected_id = $registro->id;
        $this->Expedientesdet = $registro;
        $this->descripcion = $registro->descripcion;
        $this->fechaPre = $registro->fechaPre;
        $this->fechaAcu = $registro->fechaAcu;
        $this->fechaPub = $registro->fechaPub;
        $this->verModalExpedientesdet = true;
    }
private function validarDocumentosObligatorios(): void
{
    if (!$this->fechaAcu) {
        return;
    }
    $tieneAcuerdos = count($this->filesAcuerdo) > 0;
    if ($this->selected_id && !$tieneAcuerdos) {
        $tieneAcuerdos = Expedientesdoc::where('IdExpedienteDet', $this->selected_id)
            ->where('tipo', 'acuerdo')
            ->exists();
    }
    if (!$tieneAcuerdos) {
        throw ValidationException::withMessages([
            'filesAcuerdo' => 'Debe adjuntar al menos un acuerdo cuando existe fecha de acuerdo.'
        ]);
    }
}
    public function save()
    {
        $this->validate();
        $this->validarDocumentosObligatorios();
        $archivosGuardados = [];
        try {
            DB::transaction(function () use (&$archivosGuardados) {
                if ($this->selected_id) {
                    $registro = Expedientesdet::where('IdExpediente', $this->IdExpediente)
                        ->findOrFail($this->selected_id);
                } else {
                    $registro = new Expedientesdet();
                }
                $registro->fill([
                    'IdExpediente' => $this->IdExpediente,
                    'descripcion' => iconv('UTF-8', 'UTF-8//IGNORE', $this->descripcion),
                    'fechaPre' => $this->fechaPre ?: null,
                    'fechaAcu' => $this->fechaAcu ?: null,
                    'fechaPub' => $this->fechaPub ?: null
                ]);
                $registro->save();
                foreach ([
                    'promocion' => ['filesPromocion', 'promo'],
                    'acuerdo' => ['filesAcuerdo', 'acuerdo'],
                    'anexo' => ['filesAnexo', 'anexo']
                ] as $tipo => [$propiedad, $carpeta]) {
                    foreach ($this->{$propiedad} as $archivo) {
                        $prefijo = ['promocion' => 'pro', 'acuerdo' => 'acu', 'anexo' => 'ane'][$tipo];
                        $base = $prefijo . '_' . now()->format('ym') . '_' . Str::lower(Str::random(12));                        
                        $ruta = "documentos/{$carpeta}";
                        $nombre = Util::guardarArchivo($archivo, $base, $ruta);
                        if (!$nombre) {
                            throw new \RuntimeException('No se pudo guardar el documento.');
                        }
                        $archivosGuardados[] = [$ruta, $nombre];
                        $registro->documentos()->create([
                            'tipo' => $tipo,
                            'archivo' => $nombre
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            foreach ($archivosGuardados as [$carpeta, $nombre]) {
                Util::borrarArchivo($carpeta, $nombre);
            }
            throw $e;
        }
        $this->resetInput();
        $this->verModalExpedientesdet = false;
        unset($this->filteredExpedientesdets);
    }

    public function eliminarDocumento($id)
    {
        if (!$this->selected_id || !$this->IdExpediente) {
            return;
        }
        $documento = Expedientesdoc::where('IdExpedienteDet', $this->selected_id)
            ->whereHas('expedientedet', function ($query) {
                $query->where('IdExpediente', $this->IdExpediente);
            })
            ->findOrFail($id);
        $documento->delete();
        $this->Expedientesdet?->load('documentos');
        unset($this->filteredExpedientesdets);
    }

    public function paginationView()
    {
        return 'livewire.paginacionBase';
    }

    #[Computed]
    public function expedientePadre()
    {
        return $this->IdExpediente
            ? Expediente::find($this->IdExpediente)
            : null;
    }

    public function destroy($id)
    {
        if (!$this->IdExpediente) {
            return;
        }
        $registro = Expedientesdet::where('IdExpediente', $this->IdExpediente)
            ->findOrFail($id);
        $registro->delete();
        unset($this->filteredExpedientesdets);
    }
}
