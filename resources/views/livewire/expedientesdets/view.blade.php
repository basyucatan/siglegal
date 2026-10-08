
@section('title', __('Expedientesdets'))
@php
    $renderMin = function($documentos, $titulo, $tamano = 38) {
        if ($documentos->isEmpty()) {
            return '<span class="text-muted small">Sin documentos</span>';
        }
        $html = '<div class="d-flex flex-wrap gap-1">';
        foreach ($documentos as $doc) {
            $url = $doc->url;
            $ext = strtolower(pathinfo($doc->archivo, PATHINFO_EXTENSION));
            $esImagen = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
            $html .= '<a href="' . e($url) . '" target="_blank" rel="noopener noreferrer" class="d-inline-flex align-items-center justify-content-center border rounded overflow-hidden bg-white" style="width:' . $tamano . 'px;height:' . $tamano . 'px;flex-shrink:0;" title="' . e($titulo . ': ' . $doc->archivo) . '">';
            if ($esImagen) {
                $html .= '<img src="' . e($url) . '" loading="lazy" style="width:100%;height:100%;object-fit:cover;">';
            } elseif ($ext === 'pdf') {
                $html .= '<object data="' . e($url) . '#toolbar=0&navpanes=0&scrollbar=0" type="application/pdf" style="width:100%;height:100%;pointer-events:none;overflow:hidden;"></object>';
            } else {
                $html .= '<i class="bi bi-file-earmark fs-4"></i>';
            }
            $html .= '</a>';
        }
        return $html . '</div>';
    };
@endphp
<div class="container-fluid p-0">
    <div class="cardPrin">
        <div class="cardPrin-header d-flex align-items-center gap-2" style="cursor:move;">
            <span class="fw-bold flex-shrink-0">Historial</span>
            <div class="position-relative flex-grow-1" style="min-width:0;">
                <input wire:model.live.debounce.300ms="keyWord" class="inpSolo w-100"
                    wire:keydown.escape="$set('keyWord','')" onfocus="this.select()" placeholder="Buscar...">
                @if($keyWord)
                    <span wire:click="$set('keyWord','')" class="bot botNegro botChico position-absolute"
                        style="right:5px;top:50%;transform:translateY(-50%);cursor:pointer;">X</span>
                @endif
            </div>
            <button type="button" class="bot botVerde flex-shrink-0" wire:click="create" title="Nuevo detalle"
                @disabled(!$IdExpediente)>
                <i class="bi bi-file-earmark-plus"></i>
            </button>
        </div>
        <div class="cardPrin-body p-2">
            @include('livewire.expedientesdets.modals')
            <div class="d-flex justify-content-end mb-2">
                {{ $expedientesdets->links() }}
            </div>
            <div class="d-none d-md-block tablaCont">
                <table class="table tabBase ch">
                    <thead>
                        <tr>
                            <th rowspan="2">Descripción</th>
                            <th colspan="3" class="text-center">Fechas y documentos</th>
                            <th rowspan="2">Acciones</th>
                        </tr>
                        <tr>
                            <th>Presentación / Promociones</th>
                            <th>Acuerdo / Documentos</th>
                            <th>Publicación / Anexos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expedientesdets as $row)
                            @php
                                $promociones = $row->documentos->where('tipo', 'promocion');
                                $acuerdos = $row->documentos->where('tipo', 'acuerdo');
                                $anexos = $row->documentos->where('tipo', 'anexo');
                            @endphp
                            <tr wire:key="tabla-det-{{ $row->id }}">
                                <td>{{ $row->descripcion }}</td>
                                @foreach([
                                    ['fecha' => 'fechaPre', 'docs' => $promociones, 'titulo' => 'Promoción'],
                                    ['fecha' => 'fechaAcu', 'docs' => $acuerdos, 'titulo' => 'Acuerdo'],
                                    ['fecha' => 'fechaPub', 'docs' => $anexos, 'titulo' => 'Anexo']
                                ] as $grupo)
                                    <td>
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <span class="text-nowrap">
                                                {{ $row->{$grupo['fecha']} ? Util::formatFecha($row->{$grupo['fecha']}, 'D/MMM/AA') : 'Sin fecha' }}
                                            </span>
                                            {!! $renderMin($grupo['docs'], $grupo['titulo'], 32) !!}
                                        </div>
                                    </td>
                                @endforeach
                                <td>
                                    <div class="d-flex justify-content-around align-items-center gap-1">
                                        <button type="button" wire:click="edit({{ $row->id }})"
                                            class="bot botNaranja botChico" title="Editar">
                                            <i class="bi-pencil-square"></i>
                                        </button>
                                        @if(auth()->user()->roles->min('nivel') < 3)
                                            <button type="button" wire:click="destroy({{ $row->id }})"
                                                wire:confirm="¿Estás seguro de eliminar este registro y todos sus documentos?"
                                                class="bot botRojo botChico" title="Eliminar">
                                                <i class="bi-trash3-fill"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Sin registros</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-md-none">
                @forelse($expedientesdets as $row)
                    @php
                        $promociones = $row->documentos->where('tipo', 'promocion');
                        $acuerdos = $row->documentos->where('tipo', 'acuerdo');
                        $anexos = $row->documentos->where('tipo', 'anexo');
                    @endphp
                    <div class="card border shadow-sm mb-2" wire:key="movil-det-{{ $row->id }}">
                        <div class="card-header bg-light py-2 px-2 d-flex justify-content-between align-items-start gap-2">
                            <span class="fw-bold small text-break flex-grow-1">{{ $row->descripcion }}</span>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <button type="button" wire:click="edit({{ $row->id }})"
                                    class="bot botNaranja botChico" title="Editar">
                                    <i class="bi-pencil-square"></i>
                                </button>
                                @if(auth()->user()->roles->min('nivel') < 3)
                                    <button type="button" wire:click="destroy({{ $row->id }})"
                                        wire:confirm="¿Estás seguro de eliminar este registro y todos sus documentos?"
                                        class="bot botRojo botChico" title="Eliminar">
                                        <i class="bi-trash3-fill"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-2">
                            @foreach([
                                ['titulo' => 'Promociones', 'fecha' => 'fechaPre', 'docs' => $promociones, 'icono' => 'bi-file-earmark-text'],
                                ['titulo' => 'Acuerdos', 'fecha' => 'fechaAcu', 'docs' => $acuerdos, 'icono' => 'bi-file-earmark-check'],
                                ['titulo' => 'Anexos', 'fecha' => 'fechaPub', 'docs' => $anexos, 'icono' => 'bi-paperclip']
                            ] as $grupo)
                                <div class="mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                        <span class="small fw-semibold">
                                            <i class="bi {{ $grupo['icono'] }} me-1"></i>{{ $grupo['titulo'] }}
                                            <span class="badge bg-secondary">{{ $grupo['docs']->count() }}</span>
                                        </span>
                                        <span class="small text-muted text-nowrap">
                                            {{ $row->{$grupo['fecha']} ? Util::formatFecha($row->{$grupo['fecha']}, 'D/MMM/AA') : 'Sin fecha' }}
                                        </span>
                                    </div>
                                    {!! $renderMin($grupo['docs'], $grupo['titulo'], 55) !!}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-3">Sin registros</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
