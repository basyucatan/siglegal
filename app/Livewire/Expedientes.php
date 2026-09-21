<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Expediente;
use Livewire\Attributes\Computed;
use App\Models\{Util};

class Expedientes extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $verModalExpediente = false, $verModalDetalles = false, $verModalPendientes = false;
    public $selected_id, $keyWord, $expediente, $IdOrgano, $IdActor, $IdDemandado, $asunto;
    public $adicionales = [], $organos = [], $personas = [];

    public function mount()
    {
        $this->personas = Util::getArray('personas');
        $this->organos = Util::getArray('organos');
    }

    public function detalles($id)
    {
        $this->dispatch('expedienteElegido', $id);
        $this->verModalDetalles = true;
    }

    public function pendientes($id)
    {
        $this->dispatch('cargarPendientes', $id);
        $this->verModalPendientes = true;
    }

    public function updatedKeyWord()
    {
        $this->resetPage();
    }

    #[Computed]
    public function groupedExpedientes()
    {
        $keyWord = '%' . $this->keyWord . '%';

        $query = Expediente::with(['organo.materia', 'Actor', 'Demandado'])
            ->where('id', '>', 0);

        if ($this->keyWord) {
            $query->where(function ($q) use ($keyWord) {
                $q->where('expediente', 'LIKE', $keyWord)
                    ->orWhere('asunto', 'LIKE', $keyWord)
                    ->orWhereHas('organo', function ($qOrg) use ($keyWord) {
                        $qOrg->where('organo', 'LIKE', $keyWord)
                            ->orWhereHas('materia', function ($qMat) use ($keyWord) {
                                $qMat->where('materia', 'LIKE', $keyWord);
                            });
                    })
                    ->orWhereHas('Actor', function ($qAct) use ($keyWord) {
                        $qAct->where('persona', 'LIKE', $keyWord);
                    })
                    ->orWhereHas('Demandado', function ($qDem) use ($keyWord) {
                        $qDem->where('persona', 'LIKE', $keyWord);
                    });
            });
        }

        $items = $query->orderBy('expediente')->get();

        // Agrupamiento por Materia -> Órgano
        return $items->groupBy(function ($expediente) {
            return $expediente->organo?->materia?->materia ?? 'SIN MATERIA';
        })->map(function ($expedientesPorMateria) {
            return $expedientesPorMateria->groupBy(function ($expediente) {
                return $expediente->organo?->organo ?? 'SIN ÓRGANO / JUZGADO';
            });
        });
    }

    public function render()
    {
        return view('livewire.expedientes.view', [
            'materiasAgrupadas' => $this->groupedExpedientes,
        ]);
    }

    public function cancel()
    {
        $this->resetInput();
        $this->verModalExpediente = false;
    }

    public function resetInput()
    {
        $this->resetExcept('keyWord', 'personas', 'organos');
    }

    public function edit($id)
    {
        $this->selected_id = $id;
        $this->fill(Expediente::findOrFail($id)->toArray());
        $this->verModalExpediente = true;
    }

    public function create()
    {
        $this->resetInput();
        $this->verModalExpediente = true;
    }

    public function save()
    {
        $this->validate([
            'expediente' => 'required',
            'IdActor' => 'required',
        ]);

        Expediente::updateOrCreate(
            ['id' => $this->selected_id],
            [
                'expediente' => $this->expediente,
                'IdOrgano' => $this->IdOrgano,
                'IdActor' => $this->IdActor,
                'IdDemandado' => $this->IdDemandado,
                'asunto' => $this->asunto
            ]
        );
        $this->resetInput();
        $this->verModalExpediente = false;
    }

    public function destroy($id)
    {
        if ($id) {
            Expediente::where('id', $id)->delete();
        }
    }
}