<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Expediente;
use Livewire\Attributes\Computed;
use App\Models\{Util};
use Illuminate\Support\Facades\DB;

class Expedientes extends Component
{
    use WithPagination;
	protected $paginationTheme = 'bootstrap';
    public $verModalExpediente=false, $verModalDetalles=false,  $verModalPendientes=false, 
        $selected_id, $keyWord, $expediente, $IdOrgano, $IdActor, $IdDemandado, $asunto;
	
	public $adicionales = [], $organos = [],  $personas = [];
    public function mount(){
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
    public function updatedKeyWord(){$this->resetPage();}
    #[Computed]
    public function filteredExpedientes()
    {
        $keyWord = '%' . $this->keyWord . '%';
        return Expediente::where('id', '>', 0)
            ->where(function ($query) use ($keyWord) {
                $query
                    ->where('expediente', 'LIKE', $keyWord)
                    ->orWhere('asunto', 'LIKE', $keyWord)
                    ->orWhereHas('organo', function ($q) use ($keyWord) {
                        $q->where('organo', 'LIKE', $keyWord);
                    })
                    ->orWhereHas('Actor', function ($q) use ($keyWord) {
                        $q->where('persona', 'LIKE', $keyWord);
                    })
                    ->orWhereHas('Demandado', function ($q) use ($keyWord) {
                        $q->where('persona', 'LIKE', $keyWord);
                    });
            })
            ->orderBy('expediente')
            ->paginate(50);
    }
	public function render()
	{
		return view('livewire.expedientes.view', [
			'expedientes' => $this->filteredExpedientes,
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
				'expediente' => $this-> expediente,
				'IdOrgano' => $this-> IdOrgano,
				'IdActor' => $this-> IdActor,
				'IdDemandado' => $this-> IdDemandado,
				'asunto' => $this-> asunto
			]
		);
        $this->resetInput();
        $this->verModalExpediente = false;
    }
    public function paginationView()
    {
        return 'livewire.paginacionBase';
    }
    public function destroy($id)
    {
        if ($id) {
            Expediente::where('id', $id)->delete();
        }
    }
}