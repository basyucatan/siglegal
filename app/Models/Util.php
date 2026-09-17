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
    $diasSemana = ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'];
    $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $mes = $meses[$carbonDate->month - 1];
    $mesCorto = mb_substr($mes, 0, 3);
    switch ($formato) {
        case 'Texto':
            $letras = new \Luecano\NumeroALetras\NumeroALetras();
            $fecha = mb_strtoupper(sprintf(
                '%s DE %s DE %s',
                $letras->toWords($carbonDate->day),
                $mes,
                $letras->toWords($carbonDate->year)
            ));
            break;
        case 'Corta':
            $fecha = sprintf(
                "%s %d/%s",
                $diasSemana[$carbonDate->dayOfWeek],
                $carbonDate->day,
                $mesCorto
            );
            break;
        case 'DDMMM HH:mm':
            $fecha = sprintf(
                "%d%s %02d:%02d",
                $carbonDate->day,
                $mesCorto,
                $carbonDate->hour,
                $carbonDate->minute
            );
            break;
        case 'hm':
            $fecha = sprintf(
                "%02d:%02d",
                $carbonDate->hour,
                $carbonDate->minute
            );
            break;
        case 'Dhm':
            $fecha = sprintf(
                "%d | %02d:%02d",
                $carbonDate->day,
                $carbonDate->hour,
                $carbonDate->minute
            );
            break;
        case 'CortaDhm':
            $fecha = sprintf(
                "%s %d/%s %02d:%02d",
                $diasSemana[$carbonDate->dayOfWeek],
                $carbonDate->day,
                $mesCorto,
                $carbonDate->hour,
                $carbonDate->minute
            );
            break;
        case 'MMM/AA':
            $fecha = sprintf(
                "%s/%s",
                $mesCorto,
                $carbonDate->format('y')
            );
            break;
        case 'D/MMM':
            $fecha = sprintf(
                "%d/%s",
                $carbonDate->day,
                $mesCorto
            );
            break;
        case 'prefijo':
            $fecha = sprintf(
                "%02d%02d%02d",
                $carbonDate->year % 100,
                $carbonDate->month,
                $carbonDate->day
            );
            break;

        case 'D/MMM/AA':
            $fecha = sprintf(
                "%d/%s/%02d",
                $carbonDate->day,
                $mesCorto,
                $carbonDate->year % 100
            );
            break;
        case 'abreviada':
            $fecha = sprintf(
                "%s%d%s%02dH%02d%02d",
                mb_substr($diasSemana[$carbonDate->dayOfWeek], 0, 1),
                $carbonDate->day,
                $mesCorto,
                $carbonDate->year % 100,
                $carbonDate->hour,
                $carbonDate->minute
            );
            break;
        case 'Larga':
        default:
            $fecha = sprintf(
                "%s %d/%s/%s %02d:%02d",
                $diasSemana[$carbonDate->dayOfWeek],
                $carbonDate->day,
                $mesCorto,
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
        // 1. Normalizar entrada: convertir base64 a archivo si es necesario
        if ($esBase64) {
            $datos = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $archivo));
            $rutaTemp = storage_path('app/tmp/' . Str::random(10) . '.png');
            if (!is_dir(dirname($rutaTemp))) mkdir(dirname($rutaTemp), 0755, true);
            file_put_contents($rutaTemp, $datos);
            $archivo = new \Illuminate\Http\File($rutaTemp);
        }
        // 2. Preparar nombre y extensión
        $base = Str::slug(pathinfo($nombreBase, PATHINFO_FILENAME));
        if (strlen($base) > 96) $base = substr($base, 0, 96) . '-' . Str::random(4);
        $nombreArchivo = $base . '.' . ($esBase64 ? 'png' : $archivo->extension());
        // 3. Procesar si es imagen
        $esImagen = in_array(strtolower($archivo->extension()), ['jpg', 'jpeg', 'png', 'webp']);
        if ($esImagen) {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($archivo->getRealPath());
            $maxSide = 1000;
            // Lógica de redimensionamiento
            $image->scaleDown(width: $maxSide, height: $maxSide);
            // Guardado optimizado por calidad
            $directorioTemp = storage_path('app/tmp');
            if (!is_dir($directorioTemp)) {
                mkdir($directorioTemp, 0755, true);
            }
            $rutaFinal = storage_path('app/tmp/' . $nombreArchivo);
            foreach ([90, 70, 50] as $q) {
                $image->save($rutaFinal, $q);
                if (filesize($rutaFinal) <= 500 * 1024) break;
            }
            Storage::putFileAs("public/{$carpeta}", new \Illuminate\Http\File($rutaFinal), $nombreArchivo);
            @unlink($rutaFinal);
        } else {
            // 4. Guardado directo para documentos
            Storage::putFileAs("public/{$carpeta}", $archivo, $nombreArchivo);
        }
        // Limpieza de temporal si se creó uno
        if ($esBase64) @unlink($archivo->getRealPath());
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
