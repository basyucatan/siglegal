<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Materia;
use Livewire\Attributes\Computed;
use App\Models\{Util};
use Illuminate\Support\Facades\DB;

class Materias extends Component
{
    use WithPagination;
	protected $paginationTheme = 'bootstrap';
    public $verModalMateria=false, $selected_id, $keyWord, $materia;
	
	public $adicionales = [];
    public function mount(){}
    public function updatedKeyWord(){$this->resetPage();}
    #[Computed]
	public function filteredMaterias()
	{
		$keyWord = '%' . $this->keyWord . '%';
		return Materia::Where('id','>',0)
			->where(function ($query) use ($keyWord) {
				$query
						->orWhere('materia', 'LIKE', $keyWord);
			})
			->paginate(12);
	}
	public function render()
	{
		return view('livewire.materias.view', [
			'materias' => $this->filteredMaterias,
		]);
	}
    public function cancel()
    {
        $this->resetInput();
        $this->verModalMateria = false;
    }
    public function resetInput()
    {
        $this->resetExcept('keyWord');
    }
    public function edit($id)
    {
        $this->selected_id = $id;
		$this->fill(Materia::findOrFail($id)->toArray());
        $this->verModalMateria = true;
    }
    public function create()
    {
        $this->resetInput();
        $this->verModalMateria = true;
    }    
    public function save()
    {
        $this->validate([
		'materia' => 'required',
        ]);

        Materia::updateOrCreate(
			['id' => $this->selected_id],
			[
				'materia' => $this-> materia
			]
		);
        $this->resetInput();
        $this->verModalMateria = false;
    }
    public function paginationView()
    {
        return 'livewire.paginacionBase';
    }
    public function destroy($id)
    {
        if ($id) {
            Materia::where('id', $id)->delete();
        }
    }
}