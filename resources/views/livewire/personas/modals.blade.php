@if($verModalPersona)
    <div class="modal-overlay">
        <div x-data="{}" x-init="dragModal($el)" class="modal-dialog" wire:ignore.self>
            <div class="modal-content">
                <div class="cardPrin">
                    <div class="cardPrin-header" style="cursor: move;">
                        <span>{{ $selected_id ? 'Editar Persona' : 'Crear Persona' }}</span>
                    </div>
                    <div class="cardPrin-body" style="padding: 10px; max-height: 450px; overflow-y: auto;">
                        <form>
                            <div class="row gx-1 gy-1">
                                @if ($selected_id)
                                    <input type="hidden" wire:model="selected_id">
                                @endif

                                <div class="col-12">
                                    <label class="etiBase">Persona</label>
                                    <input wire:model="persona" type="text" class="inpBase" onfocus="this.select()">
                                    @error('persona') <span class="error text-danger d-block small">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="etiBase">Generales</label>
                                    <textarea wire:model="generales" class="inpBase" rows="4" onfocus="this.select()" placeholder="Ingresa los datos generales..."></textarea>
                                    @error('generales') <span class="error text-danger d-block small">{{ $message }}</span> @enderror
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