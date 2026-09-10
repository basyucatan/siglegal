<div class="cardSec mb-3" x-data="{ mesAbierto: '{{ array_key_first($pagosAgrupados->toArray()) }}', semanaAbierta: null }">
    <div class="cardSec-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-cash-stack me-1"></i> Historial de Pagos</span>
    </div>
    <div class="cardSec-body p-2 p-md-3">
        @forelse($pagosAgrupados as $mesClave => $semanas)
            @php
                $fechaMes = \Carbon\Carbon::createFromFormat('Y-m', $mesClave);
                $totalMes = $semanas->flatten(2)->sum('montoPago');
            @endphp
            <div class="border rounded mb-3 bg-light">
                <div class="p-3 d-flex justify-content-between align-items-center bg-white border-bottom" style="cursor: pointer;" @click="mesAbierto = (mesAbierto === '{{ $mesClave }}' ? null : '{{ $mesClave }}')">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi" :class="mesAbierto === '{{ $mesClave }}' ? 'bi-folder2-open text-primary fs-5' : 'bi-folder2 text-secondary fs-5'"></i>
                        <span class="fw-bold text-uppercase">{{ $fechaMes->translatedFormat('F Y') }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-2">
                            Total: ${{ number_format($totalMes, 2) }}
                        </span>
                        <button type="button" class="bot botNegro botChico">
                            <i class="bi" :class="mesAbierto === '{{ $mesClave }}' ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        </button>
                    </div>
                </div>
                <div x-show="mesAbierto === '{{ $mesClave }}'" class="p-2">
                    @foreach($semanas as $numSemana => $tipos)
                        @php
                            $primerPago = $tipos->flatten()->first();
                            $primerDia = \Carbon\Carbon::parse($primerPago->fecha)->startOfWeek();
                            $ultimoDia = \Carbon\Carbon::parse($primerPago->fecha)->endOfWeek();
                            $totalSemana = $tipos->flatten()->sum('montoPago');
                            $llaveSemana = $mesClave . '-' . $numSemana;
                        @endphp
                        <div class="border rounded mb-2 bg-white">
                            <div class="p-2 d-flex justify-content-between align-items-center bg-light border-bottom" style="cursor: pointer;" @click="semanaAbierta = (semanaAbierta === '{{ $llaveSemana }}' ? null : '{{ $llaveSemana }}')">
                                <span class="fw-bold small text-muted">
                                    <i class="bi bi-calendar-week me-1"></i>
                                    Semana {{ $numSemana }} (del {{ $primerDia->format('d/m') }} al {{ $ultimoDia->format('d/m') }})
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-2" style="font-size:0.75rem;">
                                        Subtotal: ${{ number_format($totalSemana, 2) }}
                                    </span>
                                    <span class="badge bg-secondary rounded-pill">{{ $tipos->flatten()->count() }} pagos</span>
                                </div>
                            </div>
                            <div x-show="semanaAbierta === '{{ $llaveSemana }}'" class="p-2 p-md-3">
                                @foreach($tipos as $tipoNombre => $pagos)
                                    @php
                                        $badgeColor = $tipoNombre === 'efectivo' ? 'bg-success' : ($tipoNombre === 'transferencia' ? 'bg-primary' : 'bg-secondary');
                                        $borderColor = str_replace('bg-', 'border-', $badgeColor);
                                    @endphp
                                    <div class="mb-3">
                                        <div class="d-flex align-items-center gap-2 mb-2 pb-1 border-bottom">
                                            <span class="badge {{ $badgeColor }} text-uppercase">
                                                {{ $tipoNombre }}
                                            </span>
                                            <span class="small fw-bold text-muted">(${{ number_format($pagos->sum('montoPago'), 2) }})</span>
                                        </div>
                                        @if($tipoNombre === 'efectivo')
                                            @php
                                                $totalesPorUsuario = $pagos->groupBy('IdValida');
                                            @endphp
                                            <div class="p-2 mb-2 bg-light rounded border">
                                                <div class="small fw-bold text-muted mb-1" style="font-size: 0.8rem;">
                                                    <i class="bi bi-wallet2 me-1"></i> Efectivo esta semana:
                                                </div>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($totalesPorUsuario as $idValida => $pagosUsuario)
                                                        @php
                                                            $nombreUsuario = $pagosUsuario->first()->valida?->name ?? 'Sin asignar';
                                                            $montoUsuario = $pagosUsuario->sum('montoPago');
                                                        @endphp
                                                        <span class="badge bg-white text-dark border px-2 py-1 d-flex align-items-center gap-1 shadow-sm" style="font-size: 0.78rem;">
                                                            <i class="bi bi-person-check text-success"></i>
                                                            <span>{{ $nombreUsuario }}:</span>
                                                            <strong class="text-success">${{ number_format($montoUsuario, 2) }}</strong>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                        <div class="row g-2">
                                            @foreach($pagos as $pago)
                                                @php
                                                    $inquilino = $pago->recibo?->contrato?->inquilino?->inquilino ?? 'N/A';
                                                    $cuarto = $pago->recibo?->contrato?->cuarto?->cuarto ?? 'N/A';
                                                    $casa = $pago->recibo?->contrato?->cuarto?->casa?->casa ?? '';
                                                @endphp
                                                <div class="col-12 col-md-6 col-lg-4">
                                                    <div class="card h-100 shadow-sm border-start border-4 {{ $borderColor }}">
                                                        <div class="card-body p-2 d-flex flex-column justify-content-between">
                                                            <div>
                                                                <div class="d-flex justify-content-between align-items-center gap-1 mb-1 pb-1 border-bottom">
                                                                    <span class="fw-bold text-dark small">
                                                                        <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($pago->fecha)->format('d/m/Y') }}
                                                                    </span>
                                                                    <div class="text-end">
                                                                        @if($pago->tipo === 'transferencia')
                                                                            <span class="badge bg-light text-dark border me-1" style="font-size:0.7rem;"><i class="bi bi-bank me-1"></i>{{ $pago->cuenta?->nombre ?? 'Sin cuenta' }}</span>
                                                                        @elseif($pago->tipo === 'efectivo')
                                                                            <span class="badge bg-light text-dark border me-1" style="font-size:0.7rem;"><i class="bi bi-person-check me-1"></i>{{ $pago->valida?->name ?? 'Sin asignar' }}</span>
                                                                        @endif
                                                                        <span class="fw-bold text-success" style="font-size:0.9rem;">${{ number_format($pago->montoPago, 2) }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                                    <div class="text-truncate" style="max-width: 65%;">
                                                                        <strong class="text-dark small ms-1">{{ $inquilino }}</strong>
                                                                    </div>
                                                                    @if($cuarto !== 'N/A')
                                                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.7rem;">{{ $casa }} - {{ $cuarto }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="pt-1">
                                                                <button type="button" class="bot botNaranja botChico" wire:click="abrirModalFoto({{ $pago->id }})">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-muted">
                No hay pagos registrados para agrupar.
            </div>
        @endforelse
    </div>
</div>