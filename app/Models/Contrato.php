<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class Contrato extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'contratos';
    protected $fillable = [
        'IdCuarto','IdInquilino','IdPropietario','fechaIni',
        'fechaFin','montoRenta','deposito','penaEntrega','docContrato',
        'docInvMuebles','firma','adicionales'
    ];
    protected $casts = [
        'adicionales' => 'array'
    ];
    private function getRecibosPendientes()
    {
        return $this->recibos->filter(function ($recibo) {
            $totalPagado = $recibo->pagos->sum('montoPago');
            return $totalPagado < $recibo->montoRenta;
        });
    }
    private function getRecibosVencidos()
    {
        $hoy = now()->startOfDay();
        return $this->getRecibosPendientes()
            ->filter(function ($recibo) use ($hoy) {
                if (!$recibo->fechaVence) {
                    return false;
                }
                $vence = Carbon::parse($recibo->fechaVence)->startOfDay();
                return $vence->lt($hoy);
            });
    }
    public function getAniejaAdeudoAttribute()
    {
        $reciboVencido = $this->getRecibosPendientes()
            ->sortBy('fechaVence')
            ->first();
        if (!$reciboVencido || !$reciboVencido->fechaVence) {
            $val = 0;
            $label = 'Al día';
            $color = 'bg-success';
            $fechaVence = null;
            $montoPendiente = 0;
        } else {
            $hoy = now()->startOfDay();
            $vence = Carbon::parse($reciboVencido->fechaVence)->startOfDay();
            $fechaVence = $reciboVencido->fechaVence;
            $montoPendiente = $reciboVencido->montoRenta - $reciboVencido->pagos->sum('montoPago');
            if ($hoy->gt($vence)) {
                $val = (int) $vence->diffInDays($hoy);
                $label = $val . ' d';
                $color = 'bg-danger';
            } else {
                $diasFaltantes = (int) $hoy->diffInDays($vence);
                $val = 0;
                $label = $diasFaltantes . ' d';
                $color = ($diasFaltantes <= 2) ? 'bg-warning text-dark' : 'bg-success';
            }
        }
        return [
            'val' => $val,
            'label' => '⏱️ D ' . $label,
            'color' => $color,
            'fechaVence' => $fechaVence,
            'monto' => $montoPendiente
        ];
    }
    public function getAniejaPagoAttribute()
    {
        $idsRecibos = $this->recibos()->pluck('id');
        $ultimoPago = Pago::whereIn('IdRecibo', $idsRecibos)
            ->orderBy('fecha', 'desc')
            ->first();
        $hayAdeudoVencido = $this->getRecibosVencidos()->isNotEmpty();
        if (!$ultimoPago) {
            $val = null;
            $label = 'Sin pagos';
            $fechaUltimoPago = 'Sin pagos';
            if ($hayAdeudoVencido) {
                $color = 'bg-danger';
            } else {
                $color = 'bg-success';
            }
        } else {
            $fechaPago = Carbon::parse($ultimoPago->fecha)->startOfDay();
            $fechaUltimoPago = $fechaPago->format('d/m/Y');
            $val = (int) $fechaPago->diffInDays(now()->startOfDay());
            $label = $val . ' d';
            if (!$hayAdeudoVencido) {
                $color = 'bg-success';
            } elseif ($val === 0) {
                $color = 'bg-info text-dark';
            } elseif ($val <= 3) {
                $color = 'bg-warning text-dark';
            } else {
                $color = 'bg-danger';
            }
        }
        return [
            'val' => $val,
            'label' => '⏱️ P ' . $label,
            'color' => $color,
            'fecha' => $fechaUltimoPago
        ];
    }
    protected static function booted()
    {
        static::saving(function ($contrato) {
            $contrato->disponibilidad();
        });
    }
    public static function imprimirAnieja()
    {
        $hoy = Carbon::now()->format('Y-m-d');
        $contratos = static::with(['cuarto.casa', 'inquilino', 'recibos.pagos'])
            ->where('fechaIni', '<=', $hoy)
            ->where(function ($q) use ($hoy) {
                $q->whereNull('fechaFin')
                ->orWhere('fechaFin', '>=', $hoy);
            })
            ->get()
            ->sortByDesc(function ($c) {
                return [$c->aniejaAdeudo['val'] ?? 0, $c->aniejaPago['val'] ?? 0];
            });
        $pdf = Pdf::loadView('livewire.contratos.aniejamientoPDF', [
            'contratos' => $contratos,
            'totalContratos' => $contratos->count()
        ]);
        $pdf->setPaper('letter', 'portrait');
        return response()->streamDownload(
            fn() => print($pdf->output()),
            "reporte_aniejamiento_" . date('Ymd_His') . ".pdf",
            ['Content-Type' => 'application/pdf']
        );
    }
    public function disponibilidad()
    {
        $ocupado = static::where('IdCuarto', $this->IdCuarto)
            ->where('id', '!=', $this->id ?? 0)
            ->where(function ($query) {
                $fIni = $this->fechaIni;
                $fFin = $this->fechaFin;
                $query->where(function ($q) use ($fIni, $fFin) {
                    if ($fFin) {
                        $q->where('fechaIni', '<=', $fFin)
                          ->where(function ($sub) use ($fIni) {
                              $sub->whereNull('fechaFin')
                                  ->orWhere('fechaFin', '>=', $fIni);
                          });
                    } else {
                        $q->whereNull('fechaFin')
                          ->orWhere('fechaFin', '>=', $fIni);
                    }
                });
            })
            ->exists();
        if ($ocupado) {
            throw new Exception('Cuarto ocupado en ese rango de fechas.');
        }
    }
    public function cuarto()
    {
        return $this->belongsTo('App\Models\Cuarto', 'IdCuarto', 'id');
    }
    public function inquilino()
    {
        return $this->belongsTo('App\Models\Inquilino', 'IdInquilino', 'id');
    }
    public function propietario()
    {
        return $this->belongsTo('App\Models\Propietario', 'IdPropietario', 'id');
    }
    public function recibos()
    {
        return $this->hasMany('App\Models\Recibo', 'IdContrato', 'id');
    }
}