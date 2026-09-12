<div class="cardSec">
    <div class="cardSec-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-1"></i> Añejamiento de Cartera</span>
        <span class="badge bg-secondary">Total: {{ count($contratosAniejados) }}</span>
    </div>
    <div class="cardSec-body p-2 p-md-3" style="max-height: 75vh; overflow-y: auto;">
        @if (count($contratosAniejados) > 0)
            <div class="row g-2">
                @foreach ($contratosAniejados as $c)
                    @php
                        $adeudo = $c->aniejaAdeudo;
                        $pago = $c->aniejaPago;
                        $labelAdeudo = trim(str_replace(['⏱️', '⏱'], '', $adeudo['label'] ?? ''));
                        $labelPago = trim(str_replace(['⏱️', '⏱'], '', $pago['label'] ?? ''));
                        $nombreCasa = $c->cuarto?->casa?->casa ?? 'Sin Casa';
                        $nombreCuarto = $c->cuarto?->cuarto ?? 'N/A';
                        $colorBadgeAdeudo = $adeudo['color'] ?? 'bg-secondary';
                        $colorBorde = str_replace('bg-', 'border-', $colorBadgeAdeudo);
                    @endphp
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-start border-4 {{ $colorBorde }}">
                            <div class="card-body p-2 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                                        <span class="fw-bold text-dark small">#{{ str_pad($c->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        <div class="text-end">
                                            <span class="fw-bold text-uppercase small"><i class="bi bi-house me-1"></i>{{ $nombreCasa }}</span>
                                            <span class="badge bg-primary ms-1">{{ $nombreCuarto }}</span>
                                        </div>
                                    </div>
                                    <div class="mb-1 text-truncate">
                                        <strong class="text-dark small ms-1">{{ $c->inquilino?->inquilino ?? 'N/A' }}</strong>
                                    </div>
                                    <div class="p-1 px-2 my-1 bg-light rounded border small">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted">Tel. <strong class="text-dark">{{ $c->inquilino?->telefono ?? 'N/A' }}</strong></span>
                                            @if(!empty($adeudo['fechaVence']))
                                                <span class="text-danger fw-bold">${{ number_format($adeudo['monto'] ?? 0, 2) }}</span>
                                            @else
                                                <span class="text-success fw-semibold">Sin adeudo</span>
                                            @endif
                                        </div>
                                        @if(!empty($adeudo['fechaVence']))
                                            <div class="text-end text-muted">
                                                Vence: {{ Util::formatFecha($adeudo['fechaVence'],'Corta') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end gap-1 mt-1 pt-1 border-top">
                                    <span class="badge {{ $pago['color'] ?? 'bg-secondary' }}" title="Añejamiento de Pago">
                                        P: {{ $labelPago }}
                                    </span>
                                    <span class="badge {{ $colorBadgeAdeudo }}" title="Añejamiento de Adeudo">
                                        D: {{ $labelAdeudo }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4 text-muted">
                No hay información de añejamiento disponible.
            </div>
        @endif
    </div>
</div>