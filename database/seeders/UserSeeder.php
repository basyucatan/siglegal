<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class UserSeeder extends Seeder
{    
    public function run()
    {
        $this->crear(['Basilio'],'SuperAdmin',1,9991,1);
        $this->crear(['Rich'],'director',2,9991005001,1);
        $this->crear(['Guerre', 'Anibal', 'Sheeto'],'Admin',201,9991003001,2);
    }
    private function crear($users, $rol, $IdIni, $telIni, $IdDepto)
    {
        foreach ($users as $indice => $nombre) {
            User::create([
                'id' => $IdIni + $indice,
                'name' => $nombre,
                'telefono' => (string)($telIni + $indice),
                'password' => Hash::make($nombre . '$'),
                'activo' => true,
                'IdDepto' => $IdDepto,
                'adicionales' => []
            ])->assignRole($rol);
        }
    }
}