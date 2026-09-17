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
                <div class="cardPrin-body">
                    <div class="d-flex justify-content-end mb-2">
                        {{ $expedientes->links() }}
                    </div>
                    @include('livewire.expedientes.modals')
                    <div class="tablaCont">
                        <table class="table tabBase ch">
                            <thead>
                                <tr>
                                    <th>Expediente</th>
                                    <th>Juzgado</th>
                                    <th>Actor</th>
                                    <th>Demandado</th>
                                    <th>Asunto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expedientes as $row)
                                    <tr>
                                        <td>{{ $row->expediente }}</td>
                                        <td>{{ $row->Organo->organo }}</td>
                                        <td>{{ $row->Actor->persona }}</td>
                                        <td>{{ $row->Demandado?->persona ?? null}}</td>
                                        <td>{{ $row->asunto }}</td>
                                        <td width="90">
                                            <div class="d-flex justify-content-around align-items-center gap-1">
                                                <button wire:click="detalles({{ $row->id }})" class="bot botAzul botChico"
                                                    title="ver detalles">
                                                    <i class="fas fa-book"></i>
                                                </button>
                                                <button wire:click="pendientes({{ $row->id }})" class="bot botVerde botChico"
                                                    title="ver detalles">
                                                    <i class="fas fa-clock"></i>
                                                </button>
                                                <button wire:click="edit({{ $row->id }})" class="bot botNaranja botChico"
                                                    title="Editar">
                                                    <i class="bi-pencil-square"></i>
                                                </button>
                                                <button wire:click="destroy({{ $row->id }})" class="bot botRojo botChico"
                                                    onclick="confirm('¿Estás seguro de eliminar este registro?') || event.stopImmediatePropagation()">
                                                    <i class="bi-trash3-fill"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>