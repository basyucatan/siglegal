<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Expedientesdoc;
use Livewire\Attributes\Computed;
use App\Models\{Util};
use Illuminate\Support\Facades\DB;

class Expedientesdocs extends Component
{
    use WithPagination;
	protected $paginationTheme = 'bootstrap';
    public $verModalExpedientesdoc=false, $selected_id, $keyWord, $IdExpedienteDet, $tipo, $archivo;
	
	public $adicionales = [];
    public function mount(){}
    public function updatedKeyWord(){$this->resetPage();}
    #[Computed]
	public function filteredExpedientesdocs()
	{
		$keyWord = '%' . $this->keyWord . '%';
		return Expedientesdoc::Where('id','>',0)
			->where(function ($query) use ($keyWord) {
				$query
						->orWhere('IdExpedienteDet', 'LIKE', $keyWord)
						->orWhere('tipo', 'LIKE', $keyWord)
						->orWhere('archivo', 'LIKE', $keyWord);
			})
			->paginate(12);
	}
	public function render()
	{
		return view('livewire.expedientesdocs.view', [
			'expedientesdocs' => $this->filteredExpedientesdocs,
		]);
	}
    public function cancel()
    {
        $this->resetInput();
        $this->verModalExpedientesdoc = false;
    }
    public function resetInput()
    {
        $this->resetExcept('keyWord');
    }
    public function edit($id)
    {
        $this->selected_id = $id;
		$this->fill(Expedientesdoc::findOrFail($id)->toArray());
        $this->verModalExpedientesdoc = true;
    }
    public function create()
    {
        $this->resetInput();
        $this->verModalExpedientesdoc = true;
    }    
    public function save()
    {
        $this->validate([
		'IdExpedienteDet' => 'required',
		'tipo' => 'required',
		'archivo' => 'required',
        ]);

        Expedientesdoc::updateOrCreate(
			['id' => $this->selected_id],
			[
				'IdExpedienteDet' => $this-> IdExpedienteDet,
				'tipo' => $this-> tipo,
				'archivo' => $this-> archivo
			]
		);
        $this->resetInput();
        $this->verModalExpedientesdoc = false;
    }
    public function paginationView()
    {
        return 'livewire.paginacionBase';
    }
    public function destroy($id)
    {
        if ($id) {
            Expedientesdoc::where('id', $id)->delete();
        }
    }
}