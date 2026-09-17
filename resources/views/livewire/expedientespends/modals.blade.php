@if($verModalExpedientespend)
    <div class="modal-overlay">
        <div x-data="{}" x-init="dragModal($el)" class="modal-dialog" wire:ignore.self>
            <div class="modal-content">
                <div class="cardPrin">
                    <div class="cardPrin-header" style="cursor: move;">
                        <span>{{ $selected_id ? 'Editar Expedientespend' : 'Crear Expedientespend' }}</span>
                    </div>
                    <div class="cardPrin-body" style="padding: 10px; max-height: 400px; overflow-y: auto;">
                        <form>
                            <div class="row gx-1 gy-1">
                                @if ($selected_id)
                                    <input type="hidden" wire:model="selected_id">
                                @endif
                                <div class="col-12" x-data="{ desc: @entangle('pendiente') }">
                                    <label class="etiBase">Pendiente</label>
                                    <div class="position-relative d-flex align-items-center">
                                        <input wire:model="pendiente" x-model="desc" type="text" class="inpBase pe-5"
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
                                    @error('pendiente')
                                        <span class="error text-danger d-block small mt-1">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }} (Ingresaste
                                            {{ strlen($pendiente) }} caracteres)
                                        </span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="etiBase">Fechapro</label>
                                    <input wire:model="fechaPro" type="date" class="inpBase" onfocus="this.select()">
                                    @error('fechaPro') <span class="error text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="etiBase">Fechacum</label>
                                    <input wire:model="fechaCum" type="date" class="inpBase" onfocus="this.select()">
                                    @error('fechaCum') <span class="error text-danger">{{ $message }}</span> @enderror
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="cardPrin-footer mt-3 d-flex justify-content-end gap-2">
                        <button wire:click.prevent="cancel()" class="bot botNegro botChico">Cerrar</button>
                        <button wire:click.prevent="save()" class="bot botVerde botChico">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif