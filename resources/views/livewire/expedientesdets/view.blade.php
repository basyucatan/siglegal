@section('title', __('Expedientesdets'))

@php
    $renderMin = function($url, $titulo) {
        if (!$url) return '-';
        $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        $esImagen = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
        
        $html = '<a href="' . e($url) . '" target="_blank" class="d-inline-block position-relative border rounded overflow-hidden align-middle" style="width: 30px; height: 30px;" title="' . e($titulo) . '">';
        if ($esImagen) {
            $html .= '<img src="' . e($url) . '" style="width: 30px; height: 30px; object-fit: cover;">';
        } else {
            $html .= '<object data="' . e($url) . '#toolbar=0&navpanes=0&scrollbar=0" type="application/pdf" style="width: 30px; height: 30px; pointer-events: none; overflow: hidden;"></object>';
        }
        $html .= '</a>';
        
        return $html;
    };
@endphp

<div class="container-fluid p-0">
    <div class="row g-0 justify-content-center">
        <div class="col-12">
            <div class="cardPrin">
                <div class="cardPrin-header" style="cursor: move;">
                    <span>Historial</span>
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
                        <button class="bot botVerde" wire:click="create" title="Nuevo Expedientesdet">
                            <i class="bi bi-file-earmark-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="cardPrin-body">
                    <div class="d-flex justify-content-end mb-2">
                        {{ $expedientesdets->links() }}
                    </div>
                    @include('livewire.expedientesdets.modals')
                    <div class="tablaCont">
                        <table class="table tabBase ch">
                            <thead>
                                <tr>
                                    <th rowspan="2">Descripcion</th>
                                    <th colspan="3" class="text-center">Fechas</th>
                                    <th colspan="3" class="text-center">Documentos</th>
                                    <th rowspan="2">Acciones</th>
                                </tr>
                                <tr>
                                    <th>Presentación</th>
                                    <th>Cumplida</th>
                                    <th>Publicación</th>
                                    <th>Promoción</th>
                                    <th>Acuerdo</th>
                                    <th>Anexo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expedientesdets as $row)
                                    <tr>
                                        <td>{{ $row->descripcion }}</td>
                                        <td>{{ Util::formatFecha($row->fechaPre, 'D/MMM/AA') }}</td>
                                        <td>{{ Util::formatFecha($row->fechaPub, 'D/MMM/AA') }}</td>
                                        <td>{{ Util::formatFecha($row->fechaAcu, 'D/MMM/AA') }}</td>
                                        <td class="text-center align-middle">{!! $renderMin($row->urlPromocion, 'Ver Promoción') !!}</td>
                                        <td class="text-center align-middle">{!! $renderMin($row->urlAcuerdo, 'Ver Acuerdo') !!}</td>
                                        <td class="text-center align-middle">{!! $renderMin($row->urlAnexo, 'Ver Anexo') !!}</td>
                                        <td width="60">
                                            <div class="d-flex justify-content-around align-items-center gap-1">
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