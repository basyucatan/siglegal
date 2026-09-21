<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class Util
{
    public static function Dinero($Numero, $centavos = 2)
    {
        $num = '$ ' . number_format(abs($Numero), $centavos);
        if ($Numero < 0) {
            $num = '- ' . $num;
        }
        return $num;
    }
    public static function Miles($Numero, $centavos = 0)
    {
        $num = number_format(abs($Numero), $centavos);
        if ($Numero < 0) {
            $num = '- ' . $num;
        }
        return $num;
    }
    public static function Divide($numerador, $denominador)
    {
        $division = $denominador != 0 ? $numerador / $denominador : 0;
        return $division;
    }
    public static function formatFecha($date, $formato = 'Larga')
    {
        $carbonDate = Carbon::parse($date);
        if (is_null($date) || $date === '') {return '';}
        $diasSemana = ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'];
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        switch ($formato) {
            case 'Corta': //Dom 9/Feb
                $fecha = sprintf(
                    "%s %d/%s",
                    $diasSemana[$carbonDate->dayOfWeek],
                    $carbonDate->day,
                    $meses[$carbonDate->month - 1]
                );
                break;
            case 'DDMMM HH:mm': //29Feb 22:55
                $fecha = sprintf(
                    "%d%s %02d:%02d",
                    $carbonDate->day,
                    $meses[$carbonDate->month - 1],
                    $carbonDate->hour,
                    $carbonDate->minute
                );
            break;                
            case 'MMM/AA': //Feb/25	
                $fecha = sprintf(
                    "%s/%s",
                    $meses[$carbonDate->month - 1],
                    $carbonDate->format('y')
                );
                break;
            case 'D/MMM': //8/Feb
                $fecha = sprintf(
                    "%d/%s",
                    $carbonDate->day,
                    $meses[$carbonDate->month - 1]
                );
                break;              
            case 'D/MMM/AA': //8/Feb/25	
                $fecha = sprintf(
                    "%d/%s/%02d",
                    $carbonDate->day,
                    $meses[$carbonDate->month - 1],
                    $carbonDate->year % 100 // Obtiene los últimos dos dígitos del año
                );
                break;
            case 'MM/DD/YY':
                $fecha = sprintf(
                    "%02d/%02d/%02d",
                    $carbonDate->month,
                    $carbonDate->day,
                    $carbonDate->year % 100
                );
                break;                
            case 'abreviada': // S8Feb25|2331
                $fecha = sprintf(
                    "%s%d%s%02dH%02d%02d",
                    mb_substr($diasSemana[$carbonDate->dayOfWeek], 0, 1),
                    $carbonDate->day,
                    $meses[$carbonDate->month - 1],
                    $carbonDate->year % 100,
                    $carbonDate->hour,
                    $carbonDate->minute
                );
                break;

            case 'Larga': //Sab 8/Feb/25 23:31
            default:
                $fecha = sprintf(
                    "%s %d/%s/%s %02d:%02d",
                    $diasSemana[$carbonDate->dayOfWeek],
                    $carbonDate->day,
                    $meses[$carbonDate->month - 1],
                    $carbonDate->format('y'),
                    $carbonDate->hour,
                    $carbonDate->minute
                );
                break;
        }
        return $fecha;
    }
    public static function getArray(string $tabla, ?string $campo = null): array
    {
        //Si no se especifica $campo, es con base a la tabla, ejemplo: $this->vidrios = Util::getArray('vidrios');
        if (empty($campo)) {
            $campo = Str::singular($tabla);
        }
        $columna = DB::select("SHOW COLUMNS FROM {$tabla} LIKE '{$campo}'")[0] ?? null;

        if (!$columna) {
            throw new \InvalidArgumentException("El campo '{$campo}' no existe en la tabla '{$tabla}'");
        }
        if (Str::startsWith($columna->Type, 'enum(')) { 
            // Extraer los valores del enum y ordenarlos alfabéticamente
            // $this->cancelerias = Util::getArray('presupuestos', 'canceleria'); canceleria es tipo enum de presupuestos
            $values = substr($columna->Type, 5, -1);
            $values = str_getcsv($values, ',', "'");
            natcasesort($values); //esto aplica orden alfabético
            return array_combine($values, $values);
        }
        return DB::table($tabla)->orderBy($campo, 'asc')->pluck($campo, 'id')->toArray();
    }

    public static function getArrayJS(string $catalogo, ?string $campo = null): array
    {
        $data = config("settings.catalogos.$catalogo", []);
        if (empty($data)) {
            return [];
        }
        $coleccion = collect($data);
        if ($campo) {
            if (!isset($data[0][$campo])) {
                throw new \InvalidArgumentException("El campo '{$campo}' no existe en el catálogo '{$catalogo}'");
            }
            return $coleccion->sortBy($campo)->pluck($campo, 'id')->toArray();
        }
        return $coleccion->keyBy('id')->toArray();
    }
    public static function guardarArchivo($archivo, $nombreBase, $carpeta, $esBase64 = false)
    {
        if (!$archivo || !$carpeta) return null;
        $directorioTemp = storage_path('app/tmp');
        if (!is_dir($directorioTemp)) {
            mkdir($directorioTemp, 0755, true);
        }
        chmod($directorioTemp, 0755);
        $directorioPublico = storage_path('app/public');
        chmod($directorioPublico, 0755);
        $rutaAcumulada = $directorioPublico;
        foreach (explode('/', trim($carpeta, '/')) as $directorio) {
            $rutaAcumulada .= '/' . $directorio;
            if (!is_dir($rutaAcumulada)) {
                mkdir($rutaAcumulada, 0755);
            }
            chmod($rutaAcumulada, 0755);
        }
        if ($esBase64) {
            $datos = base64_decode(
                preg_replace('/^data:image\/\w+;base64,/', '', $archivo)
            );
            $rutaTemp = $directorioTemp . '/' . Str::random(10) . '.png';
            file_put_contents($rutaTemp, $datos);
            $archivo = new \Illuminate\Http\File($rutaTemp);
        }
        $base = Str::slug(pathinfo($nombreBase, PATHINFO_FILENAME));
        if (strlen($base) > 96) {
            $base = substr($base, 0, 96) . '-' . Str::random(4);
        }
        $extension = $esBase64 ? 'png' : $archivo->extension();
        $nombreArchivo = $base . '.' . $extension;
        $esImagen = in_array(
            strtolower($extension),
            ['jpg', 'jpeg', 'png', 'webp']
        );
        if ($esImagen) {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($archivo->getRealPath());
            $image->scaleDown(width: 1000, height: 1000);
            $rutaFinal = $directorioTemp . '/' . $nombreArchivo;
            foreach ([90, 70, 50] as $q) {
                $image->save($rutaFinal, $q);

                if (filesize($rutaFinal) <= 500 * 1024) {
                    break;
                }
            }
            Storage::putFileAs(
                "public/{$carpeta}",
                new \Illuminate\Http\File($rutaFinal),
                $nombreArchivo
            );
            @unlink($rutaFinal);
        } else {
            Storage::putFileAs(
                "public/{$carpeta}",
                $archivo,
                $nombreArchivo
            );
        }
        if ($esBase64) {
            @unlink($archivo->getRealPath());
        }
        return $nombreArchivo;
    }
    public static function borrarArchivo($carpeta, $nombreArchivo)
    {
        if (!$nombreArchivo || !$carpeta) return false;
        $ruta = "public/{$carpeta}/{$nombreArchivo}";
        if (Storage::exists($ruta)) {
            Storage::delete($ruta);
        }
        return true;
    }
    public static function getLonLat($id, $tabla) {
        $registroFila = DB::table($tabla)->where('id', $id)->first();
        if (!$registroFila || empty($registroFila->gmaps)) return null;
        $urlGmaps = $registroFila->gmaps;
        if (str_contains($urlGmaps, 'goo.gl') || str_contains($urlGmaps, 'maps.google')) {
            $cabeceras = @get_headers($urlGmaps, 1);
            if (isset($cabeceras['Location'])) {
                $urlGmaps = is_array($cabeceras['Location']) ? end($cabeceras['Location']) : $cabeceras['Location'];
            }
        }
        $coordenadas = null;
        $patrones = [
            '/@(-?\d+\.\d+),(-?\d+\.\d+)/',
            '/q=(-?\d+\.\d+),(-?\d+\.\d+)/',
            '/query=(-?\d+\.\d+),(-?\d+\.\d+)/',
            '/place\/(-?\d+\.\d+),(-?\d+\.\d+)/'
        ];
        foreach ($patrones as $patron) {
            if (preg_match($patron, $urlGmaps, $coincidencias)) {
                $coordenadas = $coincidencias[1] . ',' . $coincidencias[2];
                break;
            }
        }
        return $coordenadas;
    }
}
