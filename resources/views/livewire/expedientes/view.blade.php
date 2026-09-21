@section('title', __('Expedientes'))
<div class="container-fluid p-0">
    <div class="row g-0 justify-content-center">
        <div class="col-12">
            <div class="cardPrin">
                <div class="cardPrin-header" style="cursor: move;">
                    <span>Expedientes</span>
                    <div class="me-2 position-relative" style="display:inline-block;">
                        <input wire:model.lazy="keyWord" class="inpSolo" wire:keydown.escape="$set('keyWord','')"
                            onfocus="this.select()" placeholder="Buscar...">
                        @if($keyWord)
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
                
                <div class="cardPrin-body" x-data="{ materiaAbierta: '{{ array_key_first($materiasAgrupadas->toArray()) }}', organoAbierto: null }">
                    @include('livewire.expedientes.modals')

                    @forelse($materiasAgrupadas as $materiaNombre => $organos)
                        <div class="border rounded mb-3 bg-light">
                            <!-- Header de Materia -->
                            <div class="p-3 d-flex justify-content-between align-items-center bg-white border-bottom" 
                                style="cursor: pointer;" 
                                @click="materiaAbierta = (materiaAbierta === '{{ $materiaNombre }}' ? null : '{{ $materiaNombre }}')">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi" :class="materiaAbierta === '{{ $materiaNombre }}' ? 'bi-folder2-open text-primary fs-5' : 'bi-folder2 text-secondary fs-5'"></i>
                                    <span class="fw-bold text-uppercase">{{ $materiaNombre }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-2">
                                        {{ $organos->flatten()->count() }} Expedientes
                                    </span>
                                    <button type="button" class="bot botNegro botChico">
                                        <i class="bi" :class="materiaAbierta === '{{ $materiaNombre }}' ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Contenido de Materia (Órganos) -->
                            <div x-show="materiaAbierta === '{{ $materiaNombre }}'" class="p-2">
                                @foreach($organos as $organoNombre => $expedientes)
                                    @php
                                        $llaveOrgano = $materiaNombre . '-' . $organoNombre;
                                    @endphp
                                    <div class="border rounded mb-2 bg-white">
                                        <!-- Header de Órgano / Juzgado -->
                                        <div class="p-2 d-flex justify-content-between align-items-center bg-light border-bottom" 
                                            style="cursor: pointer;" 
                                            @click="organoAbierto = (organoAbierto === '{{ $llaveOrgano }}' ? null : '{{ $llaveOrgano }}')">
                                            <span class="fw-bold small text-muted">
                                                <i class="bi bi-building me-1"></i>
                                                {{ $organoNombre }}
                                            </span>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-secondary rounded-pill">{{ $expedientes->count() }} expedientes</span>
                                                <button type="button" class="bot botNegro botChico">
                                                    <i class="bi" :class="organoAbierto === '{{ $llaveOrgano }}' ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Contenido de Órgano (Tabla de Expedientes) -->
                                        <div x-show="organoAbierto === '{{ $llaveOrgano }}'" class="p-2 p-md-3">
                                            <div class="tablaCont">
                                                <table class="table tabBase ch mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Expediente</th>
                                                            <th>Actor</th>
                                                            <th>Demandado</th>
                                                            <th>Asunto</th>
                                                            <th class="text-center">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($expedientes as $row)
                                                            <tr>
                                                                <td><strong>{{ $row->expediente }}</strong></td>
                                                                <td>{{ $row->Actor?->persona ?? 'N/A' }}</td>
                                                                <td>{{ $row->Demandado?->persona ?? 'N/A' }}</td>
                                                                <td>{{ $row->asunto }}</td>
                                                                <td width="120">
                                                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                                                        <button wire:click="detalles({{ $row->id }})" class="bot botAzul botChico" title="Ver detalles">
                                                                            <i class="fas fa-book"></i>
                                                                        </button>
                                                                        <button wire:click="pendientes({{ $row->id }})" class="bot botVerde botChico" title="Pendientes">
                                                                            <i class="fas fa-clock"></i>
                                                                        </button>
                                                                        <button wire:click="edit({{ $row->id }})" class="bot botNaranja botChico" title="Editar">
                                                                            <i class="bi-pencil-square"></i>
                                                                        </button>
                                                                        <button wire:click="destroy({{ $row->id }})" class="bot botRojo botChico"
                                                                            onclick="confirm('¿Estás seguro de eliminar este registro?') || event.stopImmediatePropagation()">
                                                                            <i class="bi-trash3-fill"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
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