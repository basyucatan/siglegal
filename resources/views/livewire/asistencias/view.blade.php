@section('title', __('Asistencias'))
<div class="cardPrin d-flex flex-column h-100">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <div class="cardPrin-body" style="max-height:90vh;">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="cardSec mb-1">
                    <div class="cardSec-header">
                        <span>Registro</span>
                        @if (auth()->user()->roles->min('nivel') < 3)
                            <div>
                                <select wire:model.live="IdUser" class="inpSolo">
                                    @foreach ($this->users as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    <div class="cardSec-body" x-data="{
                        init() {
                            window.addEventListener('paste', e => {
                                if (e.clipboardData && e.clipboardData.files.length > 0) {
                                    let item = e.clipboardData.files[0];
                                    if (item.type.indexOf('image') !== -1) {
                                        let reader = new FileReader();
                                        reader.onload = (evt) => {
                                            $wire.setFotoBase64(evt.target.result);
                                        };
                                        reader.readAsDataURL(item);
                                    }
                                }
                            });
                        }
                    }">
                        @if (session()->has('mensaje'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('mensaje') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                        <div class="text-start">
                            <label for="justificacion" class="etiBase">Justificación / Observación</label>
                            <textarea id="justificacion" class="inpBase w-100" rows="2"
                                placeholder="Escribe aquí el motivo en caso de retraso o incidencia..." wire:model="justificacion"></textarea>
                        </div>
                        <div wire:loading wire:target="setFotoBase64" class="text-center my-2">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            <div class="small text-muted mt-1">Subiendo imagen...</div>
                        </div>
                        <div wire:loading.remove wire:target="setFotoBase64">
                            @if ($fotoTemp)
                                <div class="position-relative d-inline-block">
                                    <img src="{{ $fotoTemp }}" class="img-thumbnail" style="max-height:120px;"
                                        alt="Previsualización">
                                    <button type="button"
                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle"
                                        wire:click="quitarFoto" style="line-height:1;padding:2px 6px;">
                                        &times;
                                    </button>
                                </div>
                            @endif
                        </div>
                        <input type="file" id="inpCamaraFoto" accept="image/*" capture="environment"
                            style="display:none;" onchange="procesarImagenInput(this)">

                        <div class="d-flex gap-2 justify-content-center align-items-center">
                            <button id="btnChecada" type="button" class="bot botVerde botChico"
                                onclick="obtenerUbicacionYRegistrar()" wire:loading.attr="disabled"
                                wire:target="registrarAsistencia">
                                <span id="textoChecada" wire:loading.remove wire:target="registrarAsistencia">
                                    <i class="bi bi-geo-alt-fill"></i> Registrar
                                </span>
                                <span id="cargandoChecada" style="display:none;">
                                    ⏳ Obteniendo ubicación...
                                </span>
                                <span wire:loading wire:target="registrarAsistencia">
                                    ⏳ Registrando...
                                </span>
                            </button>

                            <button type="button" class="bot botVerde botChico"
                                onclick="document.getElementById('inpCamaraFoto').click()"
                                title="Capturar / Subir Foto (o Ctrl+V)">
                                <i class="bi bi-camera-fill"></i>
                            </button>
                            @if (auth()->user()->roles->min('nivel') < 3)
                                <button class="bot botVerde botChico" wire:click="imprimirNomina"
                                    wire:loading.attr="disabled" wire:target="imprimirNomina" title="Imprimir nómina">
                                    <span wire:loading.remove wire:target="imprimirNomina">
                                        <i class="bi bi-printer"></i></span>
                                    <span wire:loading wire:target="imprimirNomina">⏳</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="cardSec mb-1" x-data="{ verPenas: true, semanaAbierta: null }">
                    <div class="cardSec-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> Incidencias</span>
                        <button type="button" class="bot botNegro botChico" @click="verPenas = !verPenas">
                            <i class="bi" :class="verPenas ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        </button>
                    </div>

                    <div class="cardSec-body p-2" x-show="verPenas" style="max-height: 25vh; overflow-y: auto;">
                        @forelse($this->penasSemanales as $semana => $items)
                            <div class="border rounded mb-2 bg-light">
                                <!-- Cabecera de la Semana -->
                                <div class="p-2 d-flex justify-content-between align-items-center"
                                    style="cursor: pointer;"
                                    @click="semanaAbierta = (semanaAbierta === '{{ $semana }}' ? null : '{{ $semana }}')">
                                    <span class="fw-bold small">
                                        <i class="bi"
                                            :class="semanaAbierta === '{{ $semana }}' ? 'bi-folder2-open text-primary' :
                                                'bi-folder2 text-secondary'"></i>
                                        {{ $semana }}
                                    </span>
                                    <span class="badge bg-danger rounded-pill">{{ $items->count() }}</span>
                                </div>

                                <!-- Lista de Incidencias -->
                                <div x-show="semanaAbierta === '{{ $semana }}'"
                                    class="border-top bg-white p-2">
                                    @foreach ($items as $item)
                                        <div
                                            class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-1 last-border-0 {{ $item['tipo'] === 'falta' ? 'bg-danger bg-opacity-10 p-1 rounded' : '' }}">
                                            <div>
                                                <div class="fw-bold" style="font-size: 0.75rem;">
                                                    {{ Util::formatFecha($item['fecha'], 'Corta') }}
                                                </div>
                                                <div class="text-muted" style="font-size: 0.7rem;">
                                                    {{ $item['detalle'] }}
                                                </div>
                                            </div>
                                            <div>
                                                <span class="badge {{ $item['claseBadge'] }}"
                                                    style="font-size: 0.65rem;">
                                                    @if ($item['tipo'] === 'falta')
                                                        <i class="bi bi-x-circle-fill me-1"></i>
                                                    @endif
                                                    {{ $item['titulo'] }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted small py-2">
                                Sin incidencias ni faltas en las últimas 2 semanas.
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="cardSec mb-1" x-data="{ verMapa: true }">
                    <div class="cardSec-header d-flex justify-content-between align-items-center">
                        <span>Mapa de Ubicaciones</span>
                        <button type="button" class="bot botNegro botChico"
                            @click="verMapa = !verMapa; $nextTick(() => { if(verMapa && mapa) mapa.invalidateSize(); })">
                            <i class="bi" :class="verMapa ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            <i class="bi" :class="verPenas ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        </button>
                    </div>
                    <div class="cardSec-body p-0" x-show="verMapa" wire:ignore>
                        <div id="IdMapa" data-casas="{{ json_encode($todasLasCasas) }}"
                            style="height: 30vh; width: 100%;"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-8">
                <div class="cardSec mb-1">
                    <div class="cardSec-header d-flex justify-content-between align-items-center">
                        <span>Historial</span>
                    </div>
                    <div class="cardSec-body" style="max-height:75vh;">
                        @include('livewire.asistencias.modals')
                        <div class="mb-1 d-flex justify-content-end">{{ $asistencias->links() }}</div>
                        @php
                            $semanas = $asistencias->groupBy(function ($item) {
                                return \Carbon\Carbon::parse($item->fecha)->format('Y-W');
                            });
                        @endphp
                        @forelse($semanas as $llaveSemana => $registrosSemana)
                            @php
                                $primerDia = \Carbon\Carbon::parse($registrosSemana->first()->fecha)->startOfWeek();
                                $ultimoDia = \Carbon\Carbon::parse($registrosSemana->first()->fecha)->endOfWeek();
                                $resumenSueldo = $this->calcularSueldoSemana($registrosSemana);
                            @endphp
                            <div class="mb-4">
                                <div
                                    class="border-bottom mb-1 pb-2 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                                    <div>
                                        <strong>Semana: del {{ Util::formatFecha($primerDia, 'Corta') }} al
                                            {{ Util::formatFecha($ultimoDia, 'Corta') }}</strong>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2" style="font-size: 0.85rem;">
                                        <span
                                            class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-2">
                                            Desc: -${{ number_format($resumenSueldo['descuentoTotal'], 2) }}
                                        </span>
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-2">
                                            Sueldo: ${{ number_format($resumenSueldo['sueldoNeto'], 2) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="d-none d-md-block table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Entrada</th>
                                                <th>Salida</th>
                                                <th class="text-end">Acciones / Estatus</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($registrosSemana as $row)
                                                @php
                                                    $esHoy = \Carbon\Carbon::parse($row->fecha)->isToday();
                                                    $adicionales = $row->adicionales;
                                                @endphp
                                                <tr class="{{ $esHoy ? 'table-warning' : '' }}">
                                                    <td><strong>{{ Util::formatFecha($row->fecha, 'Corta') }}</strong>
                                                    </td>
                                                    <td>
                                                        <div><strong>{{ $row->horaEnt }}</strong></div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong>{{ $row->horaSal !== '00:00:00' ? $row->horaSal : '---' }}</strong>
                                                        </div>
                                                    </td>
                                                    <td class="text-end">
                                                        <div
                                                            class="d-flex align-items-center justify-content-end gap-2">
                                                            <div>
                                                                @if (is_array($adicionales))
                                                                    @if (isset($adicionales['penaEntradaId']))
                                                                        <span
                                                                            class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-2"
                                                                            style="font-size: 0.7rem;">Pena Ent.</span>
                                                                    @endif
                                                                    @if (isset($adicionales['penaSalidaId']))
                                                                        <span
                                                                            class="badge bg-warning bg-opacity-10 text-dark px-2 py-1 rounded-2 ms-1"
                                                                            style="font-size: 0.7rem;">Pena Sal.</span>
                                                                    @endif
                                                                @else
                                                                    <span
                                                                        class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-2"
                                                                        style="font-size: 0.7rem;">Cumplido</span>
                                                                @endif
                                                            </div>
                                                            <button wire:click="iniciarEdicion({{ $row->id }})"
                                                                class="bot botNaranja botChico">
                                                                <i class="bi-pencil-square"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-block d-md-none">
                                    @foreach ($registrosSemana as $row)
                                        @php
                                            $esHoy = \Carbon\Carbon::parse($row->fecha)->isToday();
                                            $adicionales = $row->adicionales;
                                        @endphp
                                        <div class="card p-3 mb-1 {{ $esHoy ? 'bg-warning bg-opacity-10' : '' }}">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span><strong>{{ \Carbon\Carbon::parse($row->fecha)->format('d/m/Y') }}</strong></span>
                                                <button wire:click="iniciarEdicion({{ $row->id }})"
                                                    class="bot botNaranja botChico">
                                                    <i class="bi-pencil-square"></i>
                                                </button>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-6">
                                                    <small class="text-muted">ENTRADA</small>
                                                    <div><strong>{{ $row->horaEnt }}</strong></div>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">SALIDA</small>
                                                    <div>
                                                        <strong>{{ $row->horaSal !== '00:00:00' ? $row->horaSal : '---' }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="border-top pt-2">
                                                @if (is_array($adicionales))
                                                    @if (isset($adicionales['penaEntradaId']))
                                                        <span
                                                            class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-2"
                                                            style="font-size: 0.7rem;">Pena Ent.</span>
                                                    @endif
                                                    @if (isset($adicionales['penaSalidaId']))
                                                        <span
                                                            class="badge bg-warning bg-opacity-10 text-dark px-2 py-1 rounded-2 ms-1"
                                                            style="font-size: 0.7rem;">Pena Sal.</span>
                                                    @endif
                                                @else
                                                    <span
                                                        class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-2"
                                                        style="font-size: 0.7rem;">Cumplido</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                No se encontraron registros de asistencia.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('livewire.asistencias.script')
</div>
