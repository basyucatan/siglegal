@if ($IdContrato)
    @php
        $proximo = $analitica['proximoRecibo'];
        $semaforo = $analitica['estadoSemaforo'];
        $dias = $analitica['diasDiferencia'];
        $saldoProximo = $proximo ? ($proximo->montoRenta - $proximo->pagos->sum('montoPago')) : 0;
        $nombreInquilino = $proximo?->contrato?->inquilino?->inquilino ?? 'Inquilino no especificado';
    @endphp
    <h5 class="fw-bold text-primary mb-2">
        <i class="bi bi-person-fill me-1"></i>{{ $nombreInquilino }}
    </h5>
    @if($proximo)
        @if($semaforo === 'al_dia')
            <div class="cardPrin mb-2 border-start border-4 border-success bg-white shadow-sm p-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-success-subtle text-success border border-success mb-1">
                            <i class="bi bi-check-circle-fill me-1"></i> Inquilino al Día
                        </span>
                        <div class="fw-bold text-dark fs-6">{{ $proximo->adicionales['concepto'] ?? 'Renta Mensual' }}</div>
                        <div class="small text-muted">Próximo vencimiento: <strong class="fs-5">{{ Util::formatFecha($proximo->fechaVence,'Corta') }}</strong></div>
                    </div>
                    <div class="text-end">
                        <span class="text-uppercase text-muted fs-7 fw-semibold d-block">Monto Próximo</span>
                        <h5 class="m-0 fw-bold text-dark">${{ number_format($saldoProximo, 2) }}</h5>
                    </div>
                </div>
            </div>
        @elseif($semaforo === 'por_vencer')
            <div class="cardPrin mb-2 border-start border-4 border-warning bg-white shadow-sm p-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-warning-subtle text-dark border border-warning mb-1">
                            Próximo a Vencer ({{ $dias }} días)
                        </span>
                        <div class="fw-bold text-dark fs-6">{{ $proximo->adicionales['concepto'] ?? 'Renta Mensual' }}</div>
                        <div class="small text-muted">Vence el: <strong>{{ Util::formatFecha($proximo->fechaVence,'Corta') }}</strong></div>
                    </div>
                    <div class="text-end">
                        <span class="text-uppercase text-muted fs-7 fw-semibold d-block">Monto por Cobrar</span>
                        <h5 class="m-0 fw-bold text-dark">${{ number_format($saldoProximo, 2) }}</h5>
                    </div>
                </div>
            </div>
        @else
            <div class="cardPrin mb-2 border-start border-4 border-danger bg-white shadow-sm p-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-danger-subtle text-danger border border-danger mb-1">
                            Cobro Vencido ({{ $dias }} días de mora)
                        </span>
                        <div class="fw-bold text-danger fs-6">{{ $proximo->adicionales['concepto'] ?? 'Renta Mensual' }}</div>
                        <div class="small text-muted">Venció el: <strong class="fs-5">{{ Util::formatFecha($proximo->fechaVence,'Corta') }}</strong></div>
                    </div>
                    <div class="text-end">
                        <span class="text-uppercase text-muted fs-7 fw-semibold d-block">Monto Requerido</span>
                        <h5 class="m-0 fw-bold text-danger">${{ number_format($saldoProximo, 2) }}</h5>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="cardPrin mb-2 border-start border-4 border-success bg-white p-2 shadow-sm">
            <span class="badge bg-success-subtle text-success border border-success">
                <i class="bi bi-check-all me-1"></i> Cartera Liquidada
            </span>
            <div class="small text-muted mt-1">Este inquilino ha cubierto la totalidad de sus recibos generados.</div>
        </div>
    @endif
@endif

<div class="cardSec">
    <div class="cardSec-header d-flex justify-content-between align-items-center">
        <span>Estado de Cuenta</span>
    @if ($IdContrato)
        <div class="d-flex gap-1">
            <input type="radio" class="btn-check" name="filtroVista"
                id="fProx" value="proximo" wire:model.live="filtroVista">
            <label
                class="btn btn-sm rounded-pill px-3 py-1
                    {{ $filtroVista === 'proximo'
                        ? 'btn-primary fw-bold'
                        : 'btn-outline-light' }}"
                for="fProx">
                Pendientes
                <span class="badge rounded-pill {{ $filtroVista === 'proximo' ? 'bg-dark' : 'bg-light text-dark' }}">
                    {{ $analitica['pendientes']->count() }}
                </span>
            </label>
            <input type="radio" class="btn-check" name="filtroVista"
                id="fHist" value="historico" wire:model.live="filtroVista">

            <label
                class="btn btn-sm rounded-pill px-3 py-1
                    {{ $filtroVista === 'historico'
                        ? 'btn-primary fw-bold'
                        : 'btn-outline-light' }}"
                for="fHist">
                Pagados
                <span class="badge rounded-pill {{ $filtroVista === 'historico' ? 'bg-dark' : 'bg-light text-dark' }}">
                    {{ $analitica['pagados']->count() }}
                </span>
            </label>
        </div>
    @endif
    </div>
    <div class="cardSec-body p-2" style="max-height: 70vh;">
        @php
            $listaAMostrar = $filtroVista === 'proximo' ? $analitica['pendientes'] : $analitica['pagados'];
        @endphp

        @if ($listaAMostrar->count() > 0)
            @foreach ($listaAMostrar as $recibo)
                @php
                    $pagado = $recibo->pagos->sum('montoPago');
                    $saldo = $recibo->montoRenta - $pagado;
                    $esPagado = $saldo <= 0;
                    $badgeColor = $esPagado ? 'bg-success' : ($pagado > 0 ? 'bg-warning text-dark' : 'bg-danger');
                    $estadoTexto = $esPagado ? 'PAGADO' : ($pagado > 0 ? 'PARCIAL' : 'PENDIENTE');

                    $fVence = \Carbon\Carbon::parse($recibo->fechaVence)->startOfDay();
                    $diasRestantes = \Carbon\Carbon::today()->diffInDays($fVence, false);

                    if ($esPagado) {
                        $claseBorde = 'border-secondary';
                    } else {
                        if ($diasRestantes <= 0) {
                            $claseBorde = 'border-danger';
                        } elseif ($diasRestantes > 0 && $diasRestantes <= 30) {
                            $claseBorde = 'border-warning';
                        } else {
                            $claseBorde = 'border-success';
                        }
                    }
                @endphp
                <div class="cardPrin mb-2 border-start border-4 {{ $claseBorde }} bg-light rounded">
                    <div class="cardPrin-body p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold text-dark fs-6 d-inline-block">
                                    {{ $recibo->adicionales['concepto'] ?? 'Recibo de Renta' }}
                                </span>
                                <div class="small text-muted">Vence: <strong class="fs-6">{{ Util::formatFecha($recibo->fechaVence, 'Corta') }}</strong></div>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $badgeColor }}">{{ $estadoTexto }}</span>
                                <h6 class="m-0 mt-1 fw-bold text-dark">${{ number_format($recibo->montoRenta, 2) }}</h6>
                            </div>
                        </div>
                        @if ($recibo->pagos->count() > 0)
                            <div class="mt-2 pt-2 border-top">
                                <span class="text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Abonos realizados</span>
                                <table class="table table-sm tabBase m-0 mt-1">
                                    <thead>
                                        <tr>
                                            <th>Monto</th>
                                            <th class="text-center">Comprobante</th>
                                            <th class="text-end">Foto</th>
                                            <th class="text-end">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recibo->pagos as $pago)
                                            <tr>
                                                <td>{{ Util::formatFecha($pago->fecha,'Corta') }}</td>
                                                <td>${{ number_format($pago->montoPago, 2) }}</td>
                                                <td class="text-center">
                                                    @if (isset($pago->adicionales['foto']))
                                                        <a href="{{ asset('storage/' . $pago->adicionales['foto']) }}"
                                                            target="_blank"
                                                            class="bot botAzul botChico p-0 px-1 me-1">Ver</a>
                                                        <button
                                                            wire:click="abrirModalFoto({{ $pago->id }})"
                                                            class="bot botGris botChico p-0 px-1"
                                                            title="Editar pago o foto">✎</button>
                                                    @else
                                                        <button
                                                            wire:click="abrirModalFoto({{ $pago->id }})"
                                                            class="bot botVerde botChico p-0 px-1">+ Foto</button>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if(auth()->user()->roles->min('nivel') < 3)
                                                        <button wire:click="eliminarPago({{ $pago->id }})"
                                                            class="bot botRojo botChico p-0 px-1"
                                                            title="Eliminar abono">✕</button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="cardSec-footer p-2 bg-transparent d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Saldo: <strong class="text-success fw-bold fs-5">${{ number_format($saldo, 2) }}</strong></span>
                        @if ($saldo > 0)
                            <button wire:click="abrirModalPago({{ $recibo->id }})"
                                class="bot botNaranja">Pagar</button>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-4 text-muted">
                {{ $IdContrato ? ($filtroVista === 'proximo' ? 'No hay recibos pendientes.' : 'No hay historial de recibos pagados.') : 'Seleccione casa y cuarto para cargar los recibos de cobranza.' }}
            </div>
        @endif
    </div>
</div>