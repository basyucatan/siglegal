@if($verModalExpedientesdet)
    <div class="modal-overlay">
        <div x-data="{}" x-init="dragModal($el)" class="modal-dialog modalDialog" wire:ignore.self>
            <div class="modal-content">
                <div class="cardPrin">
                    <div class="cardPrin-header" style="cursor: move;">
                        <span>{{ $selected_id ? 'Editar Detalle Expediente' : 'Crear Detalle Expediente' }}</span>
                    </div>
                    <div class="cardPrin-body" style="padding: 10px; max-height: 450px; overflow-y: auto;">
                        <form>
                            <div class="row gx-1 gy-2">
                                @if ($selected_id)
                                    <input type="hidden" wire:model="selected_id">
                                @endif
                                <div class="col-12" x-data="{ desc: @entangle('descripcion') }">
                                    <label class="etiBase">Descripción</label>
                                    <div class="position-relative d-flex align-items-center">
                                        <input wire:model="descripcion" x-model="desc" type="text" class="inpBase pe-5"
                                            onfocus="this.select()">
                                        <span class="bot botChico position-absolute end-0 me-1" :class="(desc?.length || 0) > 255 
                                            ? 'botRojo' 
                                            : ((desc?.length || 0) > 220 
                                                ? 'botAmarillo' 
                                                : 'botGris')" style="pointer-events: none; z-index: 4; font-size: 0.72rem;">
                                            <span x-text="desc?.length || 0"></span>/255
                                            <template x-if="(desc?.length || 0) > 255">
                                                <span>(+<strong x-text="(desc?.length || 0) - 255"></strong>)</span>
                                            </template>
                                        </span>
                                    </div>
                                    @error('descripcion')
                                        <span class="error text-danger d-block small mt-1">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }} (Ingresaste
                                            {{ strlen($descripcion) }} caracteres)
                                        </span>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="etiBase">Fecha Presentación</label>
                                    <input wire:model="fechaPre" type="date" class="inpBase" onfocus="this.select()">
                                    @error('fechaPre') <span class="error text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="etiBase">Fecha Cumplido</label>
                                    <input wire:model="fechaAcu" type="date" class="inpBase" onfocus="this.select()">
                                    @error('fechaAcu') <span class="error text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="etiBase">Fecha Publicación</label>
                                    <input wire:model="fechaPub" type="date" class="inpBase" onfocus="this.select()">
                                    @error('fechaPub') <span class="error text-danger">{{ $message }}</span> @enderror
                                </div>

                                <!-- Documento Promoción -->
                                <div class="col-4 d-flex flex-column justify-content-between border rounded p-2 bg-light">
                                    <div class="text-center mb-2 d-flex flex-column align-items-center justify-content-center flex-grow-1"
                                        style="min-height: 110px;">
                                        @if ($filePromocion)
                                            <div class="text-success small word-break p-2">
                                                {{ mb_convert_encoding($filePromocion->getClientOriginalName(), 'UTF-8', 'UTF-8') }}
                                            </div>
                                            <div class="text-info mt-1"><small>Nuevo Doc Promoción</small></div>
                                        @elseif (!empty($Expedientesdet?->urlPromocion))
                                            @php $extPromo = strtolower(pathinfo($Expedientesdet->urlPromocion, PATHINFO_EXTENSION)); @endphp
                                            @if(in_array($extPromo, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
                                                <img src="{{ mb_convert_encoding($Expedientesdet->urlPromocion, 'UTF-8', 'UTF-8') }}"
                                                    style="max-width: 100%; max-height: 80px; object-fit: contain;"
                                                    class="border rounded">
                                            @else
                                                <embed
                                                    src="{{ mb_convert_encoding($Expedientesdet->urlPromocion, 'UTF-8', 'UTF-8') }}"
                                                    style="width: 100%; height: 80px;" class="border rounded">
                                            @endif
                                            <div class="d-flex gap-2 align-items-center mt-1">
                                                <small class="text-secondary">Doc. Promoción Actual</small>
                                                <button type="button" class="bot botRojo btn-sm py-0 px-1"
                                                    style="font-size: 10px;" wire:click="eliminarDocPromo">Eliminar</button>
                                            </div>
                                        @else
                                            <div class="text-muted small">Sin Doc Promoción</div>
                                        @endif
                                    </div>
                                    <div class="mt-auto">
                                        <div wire:loading wire:target="filePromocion" class="text-primary small mb-1">
                                            Cargando Promoción…</div>
                                        <input type="file" wire:model="filePromocion" class="form-control form-control-sm"
                                            accept="application/pdf,image/*" capture="environment">
                                        @error('filePromocion')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Documento Acuerdo -->
                                <div class="col-4 d-flex flex-column justify-content-between border rounded p-2 bg-light">
                                    <div class="text-center mb-2 d-flex flex-column align-items-center justify-content-center flex-grow-1"
                                        style="min-height: 110px;">
                                        @if ($fileAcuerdo)
                                            <div class="text-success small word-break p-2">
                                                {{ mb_convert_encoding($fileAcuerdo->getClientOriginalName(), 'UTF-8', 'UTF-8') }}
                                            </div>
                                            <div class="text-info mt-1"><small>Nuevo Doc Acuerdo</small></div>
                                        @elseif (!empty($Expedientesdet?->urlAcuerdo))
                                            @php $extAcu = strtolower(pathinfo($Expedientesdet->urlAcuerdo, PATHINFO_EXTENSION)); @endphp
                                            @if(in_array($extAcu, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
                                                <img src="{{ mb_convert_encoding($Expedientesdet->urlAcuerdo, 'UTF-8', 'UTF-8') }}"
                                                    style="max-width: 100%; max-height: 80px; object-fit: contain;"
                                                    class="border rounded">
                                            @else
                                                <embed
                                                    src="{{ mb_convert_encoding($Expedientesdet->urlAcuerdo, 'UTF-8', 'UTF-8') }}"
                                                    style="width: 100%; height: 80px;" class="border rounded">
                                            @endif
                                            <div class="d-flex gap-2 align-items-center mt-1">
                                                <small class="text-secondary">Doc. Acuerdo Actual</small>
                                                <button type="button" class="bot botRojo btn-sm py-0 px-1"
                                                    style="font-size: 10px;" wire:click="eliminarDocAcuerdo">Eliminar</button>
                                            </div>
                                        @else
                                            <div class="text-muted small">Sin Doc Acuerdo</div>
                                        @endif
                                    </div>
                                    <div class="mt-auto">
                                        <div wire:loading wire:target="fileAcuerdo" class="text-primary small mb-1">Cargando
                                            Acuerdo…</div>
                                        <input type="file" wire:model="fileAcuerdo" class="form-control form-control-sm"
                                            accept="application/pdf,image/*" capture="environment">
                                        @error('fileAcuerdo')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Documento Anexo -->
                                <div class="col-4 d-flex flex-column justify-content-between border rounded p-2 bg-light">
                                    <div class="text-center mb-2 d-flex flex-column align-items-center justify-content-center flex-grow-1"
                                        style="min-height: 110px;">
                                        @if ($fileAnexo)
                                            <div class="text-success small word-break p-2">
                                                {{ mb_convert_encoding($fileAnexo->getClientOriginalName(), 'UTF-8', 'UTF-8') }}
                                            </div>
                                            <div class="text-info mt-1"><small>Nuevo Doc Anexo</small></div>
                                        @elseif (!empty($Expedientesdet?->urlAnexo))
                                            @php $extAnx = strtolower(pathinfo($Expedientesdet->urlAnexo, PATHINFO_EXTENSION)); @endphp
                                            @if(in_array($extAnx, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
                                                <img src="{{ mb_convert_encoding($Expedientesdet->urlAnexo, 'UTF-8', 'UTF-8') }}"
                                                    style="max-width: 100%; max-height: 80px; object-fit: contain;"
                                                    class="border rounded">
                                            @else
                                                <embed src="{{ mb_convert_encoding($Expedientesdet->urlAnexo, 'UTF-8', 'UTF-8') }}"
                                                    style="width: 100%; height: 80px;" class="border rounded">
                                            @endif
                                            <div class="d-flex gap-2 align-items-center mt-1">
                                                <small class="text-secondary">Doc. Anexo Actual</small>
                                                <button type="button" class="bot botRojo btn-sm py-0 px-1"
                                                    style="font-size: 10px;" wire:click="eliminarDocAnexo">Eliminar</button>
                                            </div>
                                        @else
                                            <div class="text-muted small">Sin Doc Anexo</div>
                                        @endif
                                    </div>
                                    <div class="mt-auto">
                                        <div wire:loading wire:target="fileAnexo" class="text-primary small mb-1">Cargando
                                            Anexo…</div>
                                        <input type="file" wire:model="fileAnexo" class="form-control form-control-sm"
                                            accept="application/pdf,image/*" capture="environment">
                                        @error('fileAnexo')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="cardPrin-footer mt-3 d-flex justify-content-end gap-2">
                        <button wire:click.prevent="cancel()" class="bot botNegro botChico"
                            wire:loading.attr="disabled">Cerrar</button>
                        <button wire:click.prevent="save()" class="bot botVerde botChico" wire:loading.attr="disabled"
                            wire:target="filePromocion, fileAnexo, fileAcuerdo, save">
                            <span wire:loading.remove wire:target="save">Guardar</span>
                            <span wire:loading wire:target="save">Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif