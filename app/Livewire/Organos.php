<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Organo;
use Livewire\Attributes\Computed;
use App\Models\{Util};
use Illuminate\Support\Facades\DB;

class Organos extends Component
{
    use WithPagination;
	protected $paginationTheme = 'bootstrap';
    public $verModalOrgano=false, $selected_id, $keyWord, $IdMateria, $organo;
	
	public $adicionales = [], $materias=[];
    public function mount(){
        $this->materias = Util::getArray('materias');
    }
    public function updatedKeyWord(){$this->resetPage();}
    #[Computed]
	public function filteredOrganos()
	{
		$keyWord = '%' . $this->keyWord . '%';
		return Organo::Where('id','>',0)
			->where(function ($query) use ($keyWord) {
				$query
                    ->orWhereHas('materia', function ($q) use ($keyWord) {
                        $q->where('materia', 'LIKE', $keyWord);
                    })
					->orWhere('organo', 'LIKE', $keyWord);
			})
			->paginate(12);
	}
	public function render()
	{
		return view('livewire.organos.view', [
			'organos' => $this->filteredOrganos,
		]);
	}
    public function cancel()
    {
        $this->resetInput();
        $this->verModalOrgano = false;
    }
    public function resetInput()
    {
        $this->resetExcept('keyWord', 'materias');
    }
    public function edit($id)
    {
        $this->selected_id = $id;
		$this->fill(Organo::findOrFail($id)->toArray());
        $this->verModalOrgano = true;
    }
    public function create()
    {
        $this->resetInput();
        $this->verModalOrgano = true;
    }    
    public function save()
    {
        $this->validate([
		'organo' => 'required',
        ]);

        Organo::updateOrCreate(
			['id' => $this->selected_id],
			[
				'IdMateria' => $this-> IdMateria,
				'organo' => $this-> organo
			]
		);
        $this->resetInput();
        $this->verModalOrgano = false;
    }
    public function paginationView()
    {
        return 'livewire.paginacionBase';
    }
    public function destroy($id)
    {
        if ($id) {
            Organo::where('id', $id)->delete();
        }
    }
}