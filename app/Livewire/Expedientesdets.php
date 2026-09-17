<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Expedientesdet;
use App\Models\Util;
use App\Models\Expediente;
use Illuminate\Validation\Rule;

class Expedientesdets extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $verModalExpedientesdet = false, $selected_id, $keyWord, $IdExpediente;
    public $descripcion, $fechaPre, $fechaAcu, $fechaPub;

    public $docPromocion, $filePromocion;
    public $docAnexo, $fileAnexo;
    public $docAcuerdo, $fileAcuerdo;

    public $versionesDocumentos = [];
    public ?Expedientesdet $Expedientesdet = null;

	protected $listeners = ['expedienteElegido' => 'cargarDetalles'];
    public function cargarDetalles($idExpediente)
    {
        $this->resetPage();
        $this->IdExpediente = $idExpediente;
    }
    protected function rules()
    {
        $hasPromoActual = !empty($this->docPromocion) || !empty($this->Expedientesdet?->docPromocion);
        $hasAcuerdoActual = !empty($this->docAcuerdo) || !empty($this->Expedientesdet?->docAcuerdo);

        return [
            'IdExpediente' => 'required',
            'descripcion' => 'required|string|max:255',
            'fechaPre' => 'nullable|date',
            'fechaAcu' => 'nullable|date',
            'fechaPub' => 'nullable|date',
            'filePromocion' => [
                Rule::when(!empty($this->filePromocion), ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240']),
                function ($attribute, $value, $fail) use ($hasPromoActual) {
                    if (!empty($this->fechaPre) && empty($value) && !$hasPromoActual) {
                        $fail('El documento de promoción es obligatorio si se especifica la fecha de presentación.');
                    }
                },
            ],
            'fileAcuerdo' => [
                Rule::when(!empty($this->fileAcuerdo), ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240']),
                function ($attribute, $value, $fail) use ($hasAcuerdoActual) {
                    if (!empty($this->fechaPub) && empty($value) && !$hasAcuerdoActual) {
                        $fail('El documento de acuerdo es obligatorio si se especifica la fecha de publicación.');
                    }
                },
            ],
            'fileAnexo' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ];
    }
    public function updatedKeyWord()
    {
        $this->resetPage();
    }

    #[Computed]
    public function filteredExpedientesdets()
    {
        if (!$this->IdExpediente) {
            return Expedientesdet::whereRaw('1 = 0')->paginate(12);
        }

        $keyWord = '%' . $this->keyWord . '%';
        return Expedientesdet::where('IdExpediente', $this->IdExpediente)
            ->where(function ($query) use ($keyWord) {
                $query
                    ->orWhere('descripcion', 'LIKE', $keyWord)
                    ->orWhere('docPromocion', 'LIKE', $keyWord)
                    ->orWhere('docAnexo', 'LIKE', $keyWord)
                    ->orWhere('docAcuerdo', 'LIKE', $keyWord);
            })
            ->paginate(12);
    }

    public function render()
    {
        return view('livewire.expedientesdets.view', [
            'expedientesdets' => $this->filteredExpedientesdets,
        ]);
    }

    public function cancel()
    {
        $this->resetInput();
        $this->verModalExpedientesdet = false;
    }

    public function resetInput()
    {
        $this->resetExcept(['keyWord', 'IdExpediente']);
        $this->reset([
            'filePromocion', 'fileAnexo', 'fileAcuerdo',
            'docPromocion', 'docAnexo', 'docAcuerdo',
            'selected_id', 'descripcion', 'fechaPre', 'fechaAcu', 'fechaPub'
        ]);
        $this->Expedientesdet = null;
    }

    public function create()
    {
        $this->resetInput();
        $this->verModalExpedientesdet = true;
    }

    public function edit($id)
    {
        $this->resetInput();
        $this->selected_id = $id;
        $this->Expedientesdet = Expedientesdet::findOrFail($id);
        $this->fill($this->Expedientesdet->toArray());
        $this->verModalExpedientesdet = true;
    }

    public function save()
    {
        $this->validate();
        $descTruncada = mb_substr($this->descripcion, 0, 30);
        $nombrePromoFinal = $this->docPromocion;
        $nombreAnexoFinal = $this->docAnexo;
        $nombreAcuerdoFinal = $this->docAcuerdo;

        if ($this->filePromocion) {
            $basePromo = "promo_{$this->IdExpediente}_" . $descTruncada;
            if ($this->docPromocion) {
                Util::borrarArchivo('documentos/promo', $this->docPromocion);
            }
            $nombrePromoFinal = Util::guardarArchivo($this->filePromocion, $basePromo, 'documentos/promo');
        }

        if ($this->fileAnexo) {
            $baseAnexo = "anexo_{$this->IdExpediente}_" . $descTruncada;
            if ($this->docAnexo) {
                Util::borrarArchivo('documentos/anexo', $this->docAnexo);
            }
            $nombreAnexoFinal = Util::guardarArchivo($this->fileAnexo, $baseAnexo, 'documentos/anexo');
        }

        if ($this->fileAcuerdo) {
            $baseAcuerdo = "acuerdo_{$this->IdExpediente}_" . $descTruncada;
            if ($this->docAcuerdo) {
                Util::borrarArchivo('documentos/acuerdo', $this->docAcuerdo);
            }
            $nombreAcuerdoFinal = Util::guardarArchivo($this->fileAcuerdo, $baseAcuerdo, 'documentos/acuerdo');
        }

        $registro = Expedientesdet::updateOrCreate(
            ['id' => $this->selected_id],
            [
                'IdExpediente' => $this->IdExpediente,
                'descripcion'  => iconv('UTF-8', 'UTF-8//IGNORE', $this->descripcion),
                'fechaPre'     => $this->fechaPre ?: null,
                'fechaAcu'     => $this->fechaAcu ?: null,
                'fechaPub'     => $this->fechaPub ?: null,
                'docPromocion' => $nombrePromoFinal,
                'docAnexo'     => $nombreAnexoFinal,
                'docAcuerdo'   => $nombreAcuerdoFinal,
            ]
        );

        if ($this->selected_id && ($this->filePromocion || $this->fileAnexo || $this->fileAcuerdo)) {
            $this->versionesDocumentos[$this->selected_id] = ($this->versionesDocumentos[$this->selected_id] ?? 0) + 1;
        } elseif (!$this->selected_id && ($this->filePromocion || $this->fileAnexo || $this->fileAcuerdo)) {
            $this->versionesDocumentos[$registro->id] = 1;
        }

        $this->resetInput();
        $this->verModalExpedientesdet = false;
    }

    public function eliminarDocPromo()
    {
        if ($this->selected_id) {
            $det = Expedientesdet::find($this->selected_id);
            if ($det && $det->docPromocion) {
                Util::borrarArchivo('documentos/promo', $det->docPromocion);
                $det->update(['docPromocion' => null]);
            }
        }
        $this->docPromocion = null;
        $this->filePromocion = null;
        $this->Expedientesdet = $this->selected_id ? Expedientesdet::find($this->selected_id) : null;
    }

    public function eliminarDocAnexo()
    {
        if ($this->selected_id) {
            $det = Expedientesdet::find($this->selected_id);
            if ($det && $det->docAnexo) {
                Util::borrarArchivo('documentos/anexo', $det->docAnexo);
                $det->update(['docAnexo' => null]);
            }
        }
        $this->docAnexo = null;
        $this->fileAnexo = null;
        $this->Expedientesdet = $this->selected_id ? Expedientesdet::find($this->selected_id) : null;
    }

    public function eliminarDocAcuerdo()
    {
        if ($this->selected_id) {
            $det = Expedientesdet::find($this->selected_id);
            if ($det && $det->docAcuerdo) {
                Util::borrarArchivo('documentos/acuerdo', $det->docAcuerdo);
                $det->update(['docAcuerdo' => null]);
            }
        }
        $this->docAcuerdo = null;
        $this->fileAcuerdo = null;
        $this->Expedientesdet = $this->selected_id ? Expedientesdet::find($this->selected_id) : null;
    }

    public function paginationView()
    {
        return 'livewire.paginacionBase';
    }
#[Computed]
public function expedientePadre()
{
    return $this->IdExpediente ? Expediente::find($this->IdExpediente) : null;
}
public function destroy($id)
{
    if ($id) {
        $det = Expedientesdet::find($id);
        if ($det) {
            $det->delete();
        }
    }
}
}