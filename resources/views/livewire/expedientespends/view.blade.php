@section('title', __('Expedientespends'))
<div class="container-fluid p-0">
    <div class="row g-0 justify-content-center">
        <div class="col-12">
            <div class="cardPrin">
                <div class="cardPrin-header" style="cursor: move;">
                    <span>Pendientes </span> {{-- Aqui quiero Expediente->expediente| --}}
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
                        <button class="bot botVerde" wire:click="create" title="Nuevo Expedientespend">
                            <i class="bi bi-file-earmark-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="cardPrin-body">
                    <div class="d-flex justify-content-end mb-2">
                        {{ $expedientespends->links() }}
                    </div>
                    @include('livewire.expedientespends.modals')
                    <div class="tablaCont">
                        <table class="table tabBase ch">
                            <thead>
                                <tr>
                                    <th>Pendiente</th>
                                    <th>Fecha Programada</th>
                                    <th>Cumplimiento</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expedientespends as $row)
                                    <tr>
                                        <td>{{ $row->pendiente }}</td>
                                        <td>{{ Util::formatFecha($row->fechaPro) }}</td>
                                        <td>{{ Util::formatFecha($row->fechaCum) }}</td>
                                        <td width="60">
                                            <div class="d-flex justify-content-around align-items-center gap-1">
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