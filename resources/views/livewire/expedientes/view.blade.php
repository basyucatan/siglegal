@section('title', __('Expedientes'))
<div class="container-fluid p-0">
    <div class="row g-0 justify-content-center">
        <div class="col-12">
            <div class="cardPrin">
                <div class="cardPrin-header" style="cursor: move;">
                    <div>
                        <span class="badge bg-primary rounded-pill me-1" style="font-size: 0.85rem;">
                            {{ $materiasAgrupadas->flatten()->count() }}
                        </span>
                        <span>Expedientes</span>
                    </div>
                    <div class="me-2 position-relative" style="display:inline-block;">
                        <input wire:model.lazy="keyWord" class="inpSolo" wire:keydown.escape="$set('keyWord','')"
                            onfocus="this.select()" placeholder="Buscar...">
                        @if ($keyWord)
                            <span wire:click="$set('keyWord','')" class="bot botNegro botChico"
                                style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                X
                            </span>
                        @endif
                    </div>
                    <div>
                        <button class="bot botVerde" wire:click="create" title="Nuevo Expediente">
                            <i class="bi bi-file-earmark-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="cardPrin-body" style="max-height: 70vh;" x-data="{ materiaAbierta: '{{ array_key_first($materiasAgrupadas->toArray()) }}', organoAbierto: null }">
                    @include('livewire.expedientes.modals')

                    @forelse($materiasAgrupadas as $materiaNombre => $organos)
                        <div class="border rounded mb-3 bg-light">
                            <!-- Nivel 1: Materia -->
                            <div class="p-3 d-flex justify-content-between align-items-center bg-white border-bottom"
                                style="cursor: pointer;"
                                @click="materiaAbierta = (materiaAbierta === '{{ $materiaNombre }}' ? null : '{{ $materiaNombre }}')">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi"
                                        :class="materiaAbierta === '{{ $materiaNombre }}' ?
                                            'bi-folder2-open text-primary fs-5' : 'bi-folder2 text-secondary fs-5'"></i>
                                    <span class="fw-bold text-uppercase">{{ $materiaNombre }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span
                                        class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-2">
                                        {{ $organos->flatten()->count() }} Expedientes
                                    </span>
                                    <button type="button" class="bot botNegro botChico">
                                        <i class="bi"
                                            :class="materiaAbierta === '{{ $materiaNombre }}' ? 'bi-chevron-up' :
                                                'bi-chevron-down'"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Nivel 2: Órganos / Juzgados -->
                            <div x-show="materiaAbierta === '{{ $materiaNombre }}'" class="p-2">
                                @foreach ($organos as $organoNombre => $expedientes)
                                    @php
                                        $llaveOrgano = $materiaNombre . '-' . $organoNombre;
                                    @endphp
                                    <div class="border rounded mb-2 bg-white">
                                        <!-- Header Órgano -->
                                        <div class="p-2 d-flex justify-content-between align-items-center bg-light border-bottom"
                                            style="cursor: pointer;"
                                            @click="organoAbierto = (organoAbierto === '{{ $llaveOrgano }}' ? null : '{{ $llaveOrgano }}')">
                                            <span class="fw-bold small text-muted">
                                                <i class="bi bi-building me-1"></i>
                                                {{ $organoNombre }}
                                            </span>
                                            <div class="d-flex align-items-center gap-2">
                                                <span
                                                    class="badge bg-secondary rounded-pill">{{ $expedientes->count() }}
                                                    expedientes</span>
                                                <button type="button" class="bot botNegro botChico">
                                                    <i class="bi"
                                                        :class="organoAbierto === '{{ $llaveOrgano }}' ? 'bi-chevron-up' :
                                                            'bi-chevron-down'"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Nivel 3: Cards Grid (1 col en móvil, 4 cols en PC) -->
                                        <div x-show="organoAbierto === '{{ $llaveOrgano }}'" class="p-2 p-md-3">
                                            <div class="row g-2">
                                                @foreach ($expedientes as $row)
                                                    <div class="col-12 col-md-6 col-lg-3">
                                                        <!-- Estado independiente para expandir texto con Alpine -->
                                                        <div class="card h-100 shadow-sm border-start border-4 border-primary bg-light rounded"
                                                            x-data="{ expandir: false }">
                                                            <div
                                                                class="card-body p-2 d-flex flex-column justify-content-between">
                                                                <div>
                                                                    <!-- Header Card: Nro Expediente, ID Badge y Botón Ojo -->
                                                                    <div
                                                                        class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                                                        <span class="fw-bold text-dark small">
                                                                            <i
                                                                                class="bi bi-folder me-1 text-primary"></i>{{ $row->expediente }}
                                                                        </span>
                                                                        <div class="d-flex align-items-center gap-1">
                                                                            <span
                                                                                class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25"
                                                                                style="font-size:0.7rem;">
                                                                                ID: #{{ $row->id }}
                                                                            </span>
                                                                            <button type="button"
                                                                                @click="expandir = !expandir"
                                                                                class="bot botChico"
                                                                                :class="expandir ? 'botAzul' : 'botNegro'"
                                                                                title="Expandir/Ocultar detalles">
                                                                                <i class="fas fa-eye"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Partes involucradas (se remueve 'text-truncate' cuando expandir sea true) -->
                                                                    <div class="mb-1" style="font-size:0.82rem;">
                                                                        <div class="mb-1"
                                                                            :class="expandir ? '' : 'text-truncate'">
                                                                            <strong class="text-secondary"><i
                                                                                    class="bi bi-person-fill text-success me-1"></i>Actor:</strong>
                                                                            <span
                                                                                class="text-dark">{{ $row->Actor?->persona ?? 'N/A' }}</span>
                                                                        </div>
                                                                        <div class="mb-1"
                                                                            :class="expandir ? '' : 'text-truncate'">
                                                                            <strong class="text-secondary"><i
                                                                                    class="bi bi-person-fill text-danger me-1"></i>Demandado:</strong>
                                                                            <span
                                                                                class="text-dark">{{ $row->Demandado?->persona ?? 'N/A' }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Asunto / Descripción (también se des-trunca si aplica) -->
                                                                    @if ($row->asunto)
                                                                        <div class="p-1 mb-2 bg-white rounded border small text-muted"
                                                                            :class="expandir ? '' : 'text-truncate'"
                                                                            title="{{ $row->asunto }}">
                                                                            <i
                                                                                class="bi bi-info-circle me-1"></i>{{ $row->asunto }}
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                                <!-- Card Footer: Botones de acción -->
                                                                <div
                                                                    class="pt-2 border-top d-flex justify-content-end align-items-center gap-1">
                                                                    <button wire:click="detalles({{ $row->id }})"
                                                                        class="bot botAzul botChico"
                                                                        title="Ver detalles">
                                                                        <i class="fas fa-book"></i>
                                                                    </button>
                                                                    <button
                                                                        wire:click="pendientes({{ $row->id }})"
                                                                        class="bot botVerde botChico"
                                                                        title="Pendientes">
                                                                        <i class="fas fa-clock"></i>
                                                                    </button>
                                                                    <button wire:click="edit({{ $row->id }})"
                                                                        class="bot botNaranja botChico" title="Editar">
                                                                        <i class="bi-pencil-square"></i>
                                                                    </button>
                                                                    @if(auth()->user()->roles->min('nivel') < 3)
                                                                        <button wire:click="destroy({{ $row->id }})"
                                                                            class="bot botRojo botChico"
                                                                            onclick="confirm('¿Estás seguro de eliminar este registro?') || event.stopImmediatePropagation()">
                                                                            <i class="bi-trash3-fill"></i>
                                                                        </button>
                                                                    @endif
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            No se encontraron expedientes registrados.
                        </div>
                    @endforelse

                </div>
            </div>
        </div>
    </div>
</div>
