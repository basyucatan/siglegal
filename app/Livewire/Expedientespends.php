<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Expedientespend;
use Livewire\Attributes\Computed;
use App\Models\{Util};
use Illuminate\Support\Facades\DB;

class Expedientespends extends Component
{
    use WithPagination;
	protected $paginationTheme = 'bootstrap';
    public $verModalExpedientespend=false, $selected_id, $keyWord, $IdExpediente, $pendiente, $fechaPro, $fechaCum;
	
	public $adicionales = [];
	protected $listeners = ['cargarPendientes' => 'cargarDetalles'];
    public function cargarDetalles($idExpediente)
    {
        $this->resetPage();
        $this->IdExpediente = $idExpediente;
    }
    public function mount(){}
    public function updatedKeyWord(){$this->resetPage();}
    #[Computed]
	public function filteredExpedientespends()
	{
		$keyWord = '%' . $this->keyWord . '%';
		return Expedientespend::Where('IdExpediente', $this->IdExpediente)
			->where(function ($query) use ($keyWord) {
				$query
						->orWhere('IdExpediente', 'LIKE', $keyWord)
						->orWhere('pendiente', 'LIKE', $keyWord)
						->orWhere('fechaPro', 'LIKE', $keyWord)
						->orWhere('fechaCum', 'LIKE', $keyWord);
			})
			->paginate(12);
	}
	public function render()
	{
		return view('livewire.expedientespends.view', [
			'expedientespends' => $this->filteredExpedientespends,
		]);
	}
    public function cancel()
    {
        $this->resetInput();
        $this->verModalExpedientespend = false;
    }
    public function resetInput()
    {
        $this->resetExcept('keyWord', 'IdExpediente');
    }
    public function edit($id)
    {
        $this->selected_id = $id;
		$this->fill(Expedientespend::findOrFail($id)->toArray());
        $this->verModalExpedientespend = true;
    }
    public function create()
    {
        $this->resetInput();
        $this->verModalExpedientespend = true;
    }    
    public function save()
    {
        $this->validate([
		'IdExpediente' => 'required',
		'pendiente' => 'required',
		'fechaPro' => 'required',
        ]);

        Expedientespend::updateOrCreate(
			['id' => $this->selected_id],
			[
				'IdExpediente' => $this-> IdExpediente,
				'pendiente' => $this-> pendiente,
				'fechaPro' => $this-> fechaPro,
				'fechaCum' => $this-> fechaCum
			]
		);
        $this->resetInput();
        $this->verModalExpedientespend = false;
    }
    public function paginationView()
    {
        return 'livewire.paginacionBase';
    }
    public function destroy($id)
    {
        if ($id) {
            Expedientespend::where('id', $id)->delete();
        }
    }
}