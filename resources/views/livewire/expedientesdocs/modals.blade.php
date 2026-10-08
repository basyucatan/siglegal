@if($verModalExpedientesdoc)
    <div class="modal-overlay">
        <div x-data="{}" x-init="dragModal($el)" class="modal-dialog" wire:ignore.self>            
            <div class="modal-content">
                <div class="cardPrin">
                    <div class="cardPrin-header" style="cursor: move;">
                        <span>{{ $selected_id ? 'Editar Expedientesdoc' : 'Crear Expedientesdoc' }}</span>
                    </div>
                    <div class="cardPrin-body" style="padding: 10px; max-height: 70vh; overflow-y: auto;">
                        <form>
                            <div class="row gx-1 gy-1">
                                @if ($selected_id)
                                    <input type="hidden" wire:model="selected_id">
                                @endif

<div class="col-md-6">
    <label class="etiBase">Idexpedientedet</label>
    <input wire:model="IdExpedienteDet" type="text" class="inpBase" onfocus="this.select()">
    @error('IdExpedienteDet') <span class="error text-danger">{{ $message }}</span> @enderror
</div>
<div class="col-md-6">
    <label class="etiBase">Tipo</label>
    <input wire:model="tipo" type="text" class="inpBase" onfocus="this.select()">
    @error('tipo') <span class="error text-danger">{{ $message }}</span> @enderror
</div>
<div class="col-md-6">
    <label class="etiBase">Archivo</label>
    <input wire:model="archivo" type="text" class="inpBase" onfocus="this.select()">
    @error('archivo') <span class="error text-danger">{{ $message }}</span> @enderror
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