<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\{Contrato, Recibo, Pago, Cuarto, Util};
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
class Cobranza extends Component
{
    use WithPagination, WithFileUploads;
    protected $paginationTheme = 'bootstrap';
    public $IdCasa, $IdCuarto, $IdContrato, $keyWord, $IdRecibo, $idPagoEdicion,
        $tipo = 'efectivo', $IdCuenta, $IdValida, $aniosVisibles = 1,
        $montoPago, $fechaIni, $fechaFin, $mostrarSelectContrato = false,
        $filtroVista = 'proximo', $sinCuartosVigentes = false,
        $fechaPago, $foto, $fotoActual, $verModalPago = false;
    public $casas = [], $cuartos = [], $contratos = [], $cuentas = [], $users = [];
    public function mount()
    {
        $this->casas = Util::getArray('casas');
        $this->cuentas = Util::getArray('cuentas');
        $this->users = Util::getArray('users','name');
        $this->fechaPago = date('Y-m-d');
        $this->fechaIni = date('Y-m-01');
        $this->fechaFin = date('Y-m-t');
    }
    public function updatedKeyWord()
    {
        $keyWord = trim($this->keyWord);
        if (empty($keyWord)) { return; }
        $primerRecibo = Recibo::with(['contrato.inquilino', 'contrato.cuarto'])
            ->whereHas('contrato.inquilino', function ($q) use ($keyWord) {
                $q->where('inquilino', 'LIKE', '%' . $keyWord . '%')
                  ->orWhere('telefono', 'LIKE', '%' . $keyWord . '%');
            })
            ->orderBy('fechaVence', 'asc')
            ->first();
        if ($primerRecibo && $contrato = $primerRecibo->contrato) {
            if ($contrato->cuarto?->IdCasa) {
                $this->IdCasa = $contrato->cuarto->IdCasa;
                $this->cargarCuartos($this->IdCasa);
            }
            if ($contrato->IdCuarto) {
                $this->IdCuarto = $contrato->IdCuarto;
                $this->cargarContratos($contrato->IdCuarto);
            }
            $this->IdContrato = $contrato->id;
            $this->keyWord = null;
        }
    }
    public function elegirCasa()
    {
        $this->cuartos = [];
        $this->contratos = [];
        $this->keyWord = null;
        $this->IdCuarto = null;
        $this->IdContrato = null;
        $this->mostrarSelectContrato = false;
        $this->sinCuartosVigentes = false;
        if (!$this->IdCasa) { return; }
        $this->cargarCuartos($this->IdCasa);
    }
    public function elegirCuarto()
    {
        $this->contratos = [];
        $this->keyWord = null;
        $this->IdContrato = null;
        $this->mostrarSelectContrato = false;
        if (!$this->IdCuarto) { return; }
        $this->cargarContratos($this->IdCuarto);
    }
    private function cargarCuartos($idCasa)
    {
        $hoy = date('Y-m-d');
        $this->cuartos = Cuarto::where('IdCasa', $idCasa)
            ->whereHas('contratos', fn($q) => $q->where('fechaFin', '>=', $hoy))
            ->pluck('cuarto', 'id')
            ->toArray();
        if (empty($this->cuartos)) {
            $this->sinCuartosVigentes = true;
        }
    }
    private function cargarContratos($idCuarto)
    {
        $hoy = date('Y-m-d');
        $todosContratos = Contrato::where('IdCuarto', $idCuarto)
            ->with('inquilino')
            ->orderBy('fechaFin', 'desc')
            ->get();
        if ($todosContratos->isEmpty()) { return; }
        $vigentes = $todosContratos->filter(fn($c) => $c->fechaFin >= $hoy);
        if ($vigentes->count() === 1) {
            $this->IdContrato = $vigentes->first()->id;
        } elseif ($vigentes->count() > 1) {
            $this->mostrarSelectContrato = true;
            $this->contratos = $vigentes->mapWithKeys(fn($item) => [
                $item->id => 'Contrato #' . $item->id . ' - ' . ($item->inquilino?->inquilino ?? 'Sin Inquilino')
            ])->toArray();
        } else {
            $this->IdContrato = $todosContratos->first()->id;
        }
    }
    public function abrirModalPago($idRecibo)
    {
        $recibo = Recibo::with('pagos')->findOrFail($idRecibo);
        $this->IdRecibo = $recibo->id;
        $this->idPagoEdicion = null;
        $totalPagado = $recibo->pagos->sum('montoPago');
        $saldo = $recibo->montoRenta - $totalPagado;
        $this->montoPago = $saldo > 0 ? $saldo : 0;
        $this->fechaPago = date('Y-m-d');
        $this->tipo = 'efectivo';
        $this->IdCuenta = null;
        $this->IdValida = auth()->id();
        $this->foto = null;
        $this->fotoActual = null;
        $this->verModalPago = true;
    }
    public function abrirModalFoto($idPago)
    {
        $pago = Pago::findOrFail($idPago);
        $this->idPagoEdicion = $pago->id;
        $this->IdRecibo = $pago->IdRecibo;
        $this->montoPago = $pago->montoPago;
        $this->fechaPago = $pago->fecha;
        $this->foto = null;
        $this->fotoActual = $pago->adicionales['foto'] ?? null;
        $this->verModalPago = true;
    }
public function guardarPago()
{
    $rules = [
        'montoPago' => 'required|numeric|min:0.01',
        'fechaPago' => 'required|date',
        'tipo' => 'required|in:efectivo,transferencia,otro',
        'foto' => 'nullable|image|max:4096'
    ];

    if ($this->tipo === 'transferencia') {
        $rules['IdCuenta'] = 'required';
    } elseif ($this->tipo === 'efectivo') {
        $rules['IdValida'] = 'required';
    }

    $this->validate($rules);

    $data = [
        'montoPago' => $this->montoPago,
        'fecha' => $this->fechaPago,
        'tipo' => $this->tipo,
        'IdCuenta' => $this->tipo === 'transferencia' ? $this->IdCuenta : null,
        'IdValida' => $this->tipo === 'efectivo' ? $this->IdValida : auth()->id(),
    ];

    if ($this->idPagoEdicion) {
        $pago = Pago::findOrFail($this->idPagoEdicion);
        $adicionales = $pago->adicionales ?? [];
        if ($this->foto) {
            if (isset($adicionales['foto'])) {
                Storage::disk('public')->delete($adicionales['foto']);
            }
            $adicionales['foto'] = $this->foto->store('comprobantes', 'public');
        }
        $data['adicionales'] = $adicionales;
        $pago->update($data);
    } else {
        $this->validate(['IdRecibo' => 'required']);
        $adicionales = [];
        if ($this->foto) {
            $adicionales['foto'] = $this->foto->store('comprobantes', 'public');
        }
        $data['IdRecibo'] = $this->IdRecibo;
        $data['adicionales'] = $adicionales;
        Pago::create($data);
    }

    $this->verModalPago = false;
    $this->reset(['IdRecibo', 'idPagoEdicion', 'montoPago', 'foto', 'fotoActual', 
        'tipo', 'IdCuenta', 'IdValida']);
}
    public function eliminarPago($idPago)
    {
        $pago = Pago::find($idPago);
        if ($pago) {
            if (isset($pago->adicionales['foto'])) {
                Storage::disk('public')->delete($pago->adicionales['foto']);
            }
            $pago->delete();
        }
    }
    public function imprimirReporte()
    {
        $this->validate([
            'fechaIni' => 'required|date',
            'fechaFin' => 'required|date|after_or_equal:fechaIni'
        ]);
        $pagos = Pago::with(['recibo.contrato.inquilino', 'recibo.contrato.cuarto.casa'])
            ->whereBetween('fecha', [$this->fechaIni, $this->fechaFin])
            ->orderBy('fecha', 'asc')
            ->get();
        $totalCobrado = $pagos->sum('montoPago');
        $pagosPorCasa = $pagos->groupBy(function ($pago) {
            return $pago->recibo?->contrato?->cuarto?->casa?->casa ?? 'Sin Casa Asignada';
        });
        $pdf = Pdf::loadView('livewire.cobranza.reportePDF', [
            'pagosPorCasa' => $pagosPorCasa,
            'totalCobrado' => $totalCobrado,
            'fechaIni' => Carbon::parse($this->fechaIni)->format('d/m/Y'),
            'fechaFin' => Carbon::parse($this->fechaFin)->format('d/m/Y')
        ]);
        $pdf->setPaper('letter', 'portrait');
        return response()->streamDownload(fn() => print($pdf->output()), "reporte_cobranza.pdf", ['Content-Type' => 'application/pdf']);
    }
    #[Computed]
    public function todosLosRecibos()
    {
        if (!$this->IdContrato) {
            return collect([]);
        }
        return Recibo::with(['pagos', 'contrato.inquilino', 'contrato.cuarto'])
            ->where('IdContrato', $this->IdContrato)
            ->orderBy('fechaVence', 'asc')
            ->get();
    }
    #[Computed]
    public function analiticaRecibos()
    {
        $recibos = $this->todosLosRecibos;
        $pendientes = collect([]);
        $pagados = collect([]);
        $proximoRecibo = null;
        $hoy = Carbon::today();
        foreach ($recibos as $recibo) {
            $totalPagado = $recibo->pagos->sum('montoPago');
            $saldo = $recibo->montoRenta - $totalPagado;
            if ($saldo > 0) {
                $pendientes->push($recibo);
                if (!$proximoRecibo) {
                    $proximoRecibo = $recibo;
                }
            } else {
                $pagados->push($recibo);
            }
        }
        $pagados = $pagados->sortByDesc('fechaVence')->values();
        $estadoSemaforo = 'al_dia'; 
        $diasDiferencia = 0;
        if ($proximoRecibo) {
            $fechaVence = Carbon::parse($proximoRecibo->fechaVence);
            $diasDiferencia = $hoy->diffInDays($fechaVence, false);
            if ($diasDiferencia < 0) {
                $estadoSemaforo = 'vencido';
            } elseif ($diasDiferencia <= 3) {
                $estadoSemaforo = 'por_vencer';
            } else {
                $estadoSemaforo = 'al_dia';
            }
        }
        return [
            'proximoRecibo' => $proximoRecibo, 
            'pendientes' => $pendientes,
            'pagados' => $pagados,
            'estadoSemaforo' => $estadoSemaforo,
            'diasDiferencia' => abs((int)$diasDiferencia)
        ];
    }
    public function cargarMas()
    {
        $this->aniosVisibles++;
    }
    #[Computed]
    public function pagosAgrupados()
    {
        return Pago::with(['recibo.contrato.inquilino', 'recibo.contrato.cuarto', 'cuenta', 'valida'])
            ->whereNotNull('fecha')
            ->orderBy('fecha', 'desc')
            ->get()
            ->groupBy(function ($pago) {
                return Carbon::parse($pago->fecha)->format('Y-m');
            })
            ->map(function ($pagosMes) {
                return $pagosMes->groupBy(function ($pago) {
                    return Carbon::parse($pago->fecha)->format('W');
                })->map(function ($pagosSemana) {
                    return $pagosSemana->groupBy('tipo');
                });
            });
    }
    #[Computed]
    public function contratosAniejados()
    {
        return Contrato::with([
            'inquilino', 
            'cuarto.casa', 
            'recibos.pagos'
        ])->get();
    }
    public function render()
    {
        return view('livewire.cobranza.view', [
            'analitica' => $this->analiticaRecibos,
            'contratosAniejados' => $this->contratosAniejados,
            'pagosAgrupados' => $this->pagosAgrupados
        ]);
    }
}