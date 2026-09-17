<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Persona;
use Livewire\Attributes\Computed;
use App\Models\{Util};
use Illuminate\Support\Facades\DB;

class Personas extends Component
{
    use WithPagination;
	protected $paginationTheme = 'bootstrap';
    public $verModalPersona=false, $selected_id, $keyWord, $persona;
	
	public $generales = [];
	public $adicionales = [];
    public function mount(){}
    public function updatedKeyWord(){$this->resetPage();}
    #[Computed]
	public function filteredPersonas()
	{
		$keyWord = '%' . $this->keyWord . '%';
		return Persona::Where('id','>',0)
			->where(function ($query) use ($keyWord) {
				$query
						->orWhere('persona', 'LIKE', $keyWord)
						->orWhere('generales', 'LIKE', $keyWord);
			})
			->paginate(12);
	}
	public function render()
	{
		return view('livewire.personas.view', [
			'personas' => $this->filteredPersonas,
		]);
	}
    public function cancel()
    {
        $this->resetInput();
        $this->verModalPersona = false;
    }
    public function resetInput()
    {
        $this->resetExcept('keyWord');
    }
    public function edit($id)
    {
        $this->selected_id = $id;
		$this->fill(Persona::findOrFail($id)->toArray());
        $this->verModalPersona = true;
    }
    public function create()
    {
        $this->resetInput();
        $this->verModalPersona = true;
    }    
    public function save()
    {
        $this->validate([
		'persona' => 'required',
        ]);

        Persona::updateOrCreate(
			['id' => $this->selected_id],
			[
				'persona' => $this-> persona,
				'generales' => $this-> generales
			]
		);
        $this->resetInput();
        $this->verModalPersona = false;
    }
    public function paginationView()
    {
        return 'livewire.paginacionBase';
    }
    public function destroy($id)
    {
        if ($id) {
            Persona::where('id', $id)->delete();
        }
    }
}