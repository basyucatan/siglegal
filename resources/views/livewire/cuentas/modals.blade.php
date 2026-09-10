@if($verModalCuenta)
    <div class="modal-overlay">
        <div x-data="{}" x-init="dragModal($el)" class="modal-dialog" wire:ignore.self>            
            <div class="modal-content">
                <div class="cardPrin">
                    <div class="cardPrin-header" style="cursor: move;">
                        <span>{{ $selected_id ? 'Editar Cuenta' : 'Crear Cuenta' }}</span>
                    </div>
                    <div class="cardPrin-body" style="padding: 10px; max-height: 400px; overflow-y: auto;">
                        <form>
                            <div class="row gx-1 gy-1">
                                @if ($selected_id)
                                    <input type="hidden" wire:model="selected_id">
                                @endif

<div class="col-md-6">
    <label class="etiBase">Cuenta</label>
    <input wire:model="cuenta" type="text" class="inpBase" onfocus="this.select()">
    @error('cuenta') <span class="error text-danger">{{ $message }}</span> @enderror
</div>
<div class="col-md-6">
    <label class="etiBase">Nombre</label>
    <input wire:model="nombre" type="text" class="inpBase" onfocus="this.select()">
    @error('nombre') <span class="error text-danger">{{ $message }}</span> @enderror
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