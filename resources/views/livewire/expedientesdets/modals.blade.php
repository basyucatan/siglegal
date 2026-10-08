
@if($verModalExpedientesdet)
    <div class="modal-overlay">
        <div x-data="{}" x-init="dragModal($el)" class="modal-dialog modalDialog" wire:ignore.self>
            <div class="modal-content">
                <div class="cardPrin">
                    <div class="cardPrin-header" style="cursor: move;">
                        <span>{{ $selected_id ? 'Editar Detalle Expediente' : 'Crear Detalle Expediente' }}</span>
                    </div>
                    <div class="cardPrin-body" style="padding:10px;max-height:450px;overflow-y:auto;">
                        <form wire:submit.prevent="save">
                            <div class="row gx-1 gy-2">
                                <div class="col-12" x-data="{ desc: @entangle('descripcion') }">
                                    <label class="etiBase">Descripción</label>
                                    <div class="position-relative d-flex align-items-center">
                                        <input wire:model="descripcion" x-model="desc" type="text" class="inpBase pe-5" onfocus="this.select()">
                                        <span class="bot botChico position-absolute end-0 me-1"
                                            :class="(desc?.length || 0) > 255 ? 'botRojo' : ((desc?.length || 0) > 220 ? 'botAmarillo' : 'botGris')"
                                            style="pointer-events:none;z-index:4;font-size:0.72rem;">
                                            <span x-text="desc?.length || 0"></span>/255
                                            <template x-if="(desc?.length || 0) > 255">
                                                <span>(+<strong x-text="(desc?.length || 0) - 255"></strong>)</span>
                                            </template>
                                        </span>
                                    </div>
                                    @error('descripcion')
                                        <span class="error text-danger d-block small mt-1">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                        </span>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="etiBase">Fecha Promoción</label>
                                    <input wire:model="fechaPre" type="date" class="inpBase">
                                    @error('fechaPre')
                                        <span class="error text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="etiBase">Fecha Acuerdo</label>
                                    <input wire:model="fechaAcu" type="date" class="inpBase">
                                    @error('fechaAcu')
                                        <span class="error text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="etiBase">Fecha Publicación</label>
                                    <input wire:model="fechaPub" type="date" class="inpBase">
                                    @error('fechaPub')
                                        <span class="error text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                @foreach([
                                    ['tipo' => 'promocion', 'titulo' => 'Promociones', 'icono' => 'bi-file-earmark-text', 'archivos' => 'filesPromocion', 'nuevos' => 'nuevosPromocion'],
                                    ['tipo' => 'acuerdo', 'titulo' => 'Acuerdos', 'icono' => 'bi-file-earmark-check', 'archivos' => 'filesAcuerdo', 'nuevos' => 'nuevosAcuerdo'],
                                    ['tipo' => 'anexo', 'titulo' => 'Anexos', 'icono' => 'bi-paperclip', 'archivos' => 'filesAnexo', 'nuevos' => 'nuevosAnexo']
                                ] as $grupo)
                                    @php
                                        $guardados = $Expedientesdet?->documentos?->where('tipo', $grupo['tipo']) ?? collect();
                                        $pendientes = $this->{$grupo['archivos']};
                                    @endphp
                                    <div class="col-md-4" wire:key="grupo-{{ $grupo['tipo'] }}">
                                        <div class="border rounded p-2 bg-light h-100 d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold">
                                                    <i class="bi {{ $grupo['icono'] }} me-1"></i>{{ $grupo['titulo'] }}
                                                </span>
                                                <span class="badge bg-secondary">{{ $guardados->count() + count($pendientes) }}</span>
                                            </div>
                                            <div class="flex-grow-1 d-flex flex-wrap align-content-start gap-2">
                                                @foreach($guardados as $doc)
                                                    @php
                                                        $ext = strtolower(pathinfo($doc->archivo, PATHINFO_EXTENSION));
                                                        $esImagen = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                                                    @endphp
                                                    <div class="position-relative border rounded bg-white overflow-hidden"
                                                        style="width:65px;height:65px;"
                                                        wire:key="documento-{{ $doc->id }}">
                                                        <a href="{{ $doc->url }}" target="_blank" rel="noopener noreferrer"
                                                            class="d-block w-100 h-100" title="Ver documento">
                                                            @if($esImagen)
                                                                <img src="{{ $doc->url }}" loading="lazy"
                                                                    style="width:100%;height:100%;object-fit:cover;">
                                                            @elseif($ext === 'pdf')
                                                                <object data="{{ $doc->url }}#toolbar=0&navpanes=0&scrollbar=0"
                                                                    type="application/pdf"
                                                                    style="width:100%;height:100%;pointer-events:none;">
                                                                </object>
                                                            @else
                                                                <div class="d-flex justify-content-center align-items-center h-100">
                                                                    <i class="bi bi-file-earmark fs-2"></i>
                                                                </div>
                                                            @endif
                                                        </a>
                                                        <button type="button"
                                                            wire:click="eliminarDocumento({{ $doc->id }})"
                                                            wire:confirm="¿Eliminar definitivamente este documento?"
                                                            wire:loading.attr="disabled"
                                                            class="bot botRojo botChico position-absolute top-0 end-0"
                                                            title="Eliminar documento"
                                                            style="z-index:2;">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                                @foreach($pendientes as $indice => $file)
                                                    @php
                                                        $ext = strtolower($file->getClientOriginalExtension());
                                                        $esImagen = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                                                    @endphp
                                                    <div class="position-relative border border-success rounded bg-white overflow-hidden"
                                                        style="width:65px;height:65px;"
                                                        wire:key="pendiente-{{ $grupo['tipo'] }}-{{ $indice }}">
                                                        @if($esImagen)
                                                            <img src="{{ $file->temporaryUrl() }}"
                                                                style="width:100%;height:100%;object-fit:cover;">
                                                        @else
                                                            <div class="d-flex justify-content-center align-items-center h-100">
                                                                <i class="bi bi-file-earmark-pdf text-danger fs-2"></i>
                                                            </div>
                                                        @endif
                                                        <button type="button"
                                                            wire:click="eliminarArchivoPendiente('{{ $grupo['tipo'] }}', {{ $indice }})"
                                                            wire:loading.attr="disabled"
                                                            class="bot botRojo botChico position-absolute top-0 end-0"
                                                            title="Quitar archivo pendiente"
                                                            style="z-index:2;">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                        <span class="position-absolute bottom-0 end-0 badge bg-success"
                                                            style="pointer-events:none;">
                                                            <i class="bi bi-plus"></i>
                                                        </span>
                                                    </div>
                                                @endforeach
                                                @if($guardados->isEmpty() && count($pendientes) === 0)
                                                    <div class="text-muted small text-center w-100 py-3">
                                                        Sin documentos
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="mt-2">
                                                <input type="file"
                                                    wire:model="{{ $grupo['nuevos'] }}"
                                                    multiple
                                                    class="form-control form-control-sm"
                                                    accept=".pdf,.jpg,.jpeg,.png,.webp">
                                                <div wire:loading wire:target="{{ $grupo['nuevos'] }}" class="text-primary small mt-1">
                                                    <span class="spinner-border spinner-border-sm me-1"></span>Cargando archivos...
                                                </div>
                                                @error($grupo['nuevos'])
                                                    <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                                @foreach($errors->get($grupo['nuevos'] . '.*') as $mensajes)
                                                    @foreach($mensajes as $mensaje)
                                                        <span class="text-danger small d-block">{{ $mensaje }}</span>
                                                    @endforeach
                                                @endforeach
                                                @error($grupo['archivos'])
                                                    <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                                @foreach($errors->get($grupo['archivos'] . '.*') as $mensajes)
                                                    @foreach($mensajes as $mensaje)
                                                        <span class="text-danger small d-block">{{ $mensaje }}</span>
                                                    @endforeach
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </form>
                    </div>
                    <div class="cardPrin-footer mt-3 d-flex justify-content-end gap-2">
                        <button type="button" wire:click.prevent="cancel()"
                            class="bot botNegro botChico"
                            wire:loading.attr="disabled">
                            Cerrar
                        </button>
                        <button type="button" wire:click.prevent="save()"
                            class="bot botVerde botChico"
                            wire:loading.attr="disabled"
                            wire:target="nuevosPromocion,nuevosAcuerdo,nuevosAnexo,save">
                            <span wire:loading.remove wire:target="save">Guardar</span>
                            <span wire:loading wire:target="save">Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
