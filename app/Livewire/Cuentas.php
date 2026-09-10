<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cuenta;
use Livewire\Attributes\Computed;
use App\Models\{Util};
use Illuminate\Support\Facades\DB;

class Cuentas extends Component
{
    use WithPagination;
	protected $paginationTheme = 'bootstrap';
    public $verModalCuenta=false, $selected_id, $keyWord, $cuenta, $nombre;
	
	public $adicionales = [];
    public function mount(){}
    public function updatedKeyWord(){$this->resetPage();}
    #[Computed]
	public function filteredCuentas()
	{
		$keyWord = '%' . $this->keyWord . '%';
		return Cuenta::Where('id','>',0)
			->where(function ($query) use ($keyWord) {
				$query
						->orWhere('cuenta', 'LIKE', $keyWord)
						->orWhere('nombre', 'LIKE', $keyWord);
			})
			->paginate(12);
	}
	public function render()
	{
		return view('livewire.cuentas.view', [
			'cuentas' => $this->filteredCuentas,
		]);
	}
    public function cancel()
    {
        $this->resetInput();
        $this->verModalCuenta = false;
    }
    public function resetInput()
    {
        $this->resetExcept('keyWord');
    }
    public function edit($id)
    {
        $this->selected_id = $id;
		$this->fill(Cuenta::findOrFail($id)->toArray());
        $this->verModalCuenta = true;
    }
    public function create()
    {
        $this->resetInput();
        $this->verModalCuenta = true;
    }    
    public function save()
    {
        $this->validate([
		'cuenta' => 'required',
		'nombre' => 'required',
        ]);

        Cuenta::updateOrCreate(
			['id' => $this->selected_id],
			[
				'cuenta' => $this-> cuenta,
				'nombre' => $this-> nombre
			]
		);
        $this->resetInput();
        $this->verModalCuenta = false;
    }
    public function paginationView()
    {
        return 'livewire.paginacionBase';
    }
    public function destroy($id)
    {
        if ($id) {
            Cuenta::where('id', $id)->delete();
        }
    }
}