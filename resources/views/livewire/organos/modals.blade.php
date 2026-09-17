@if($verModalOrgano)
    <div class="modal-overlay">
        <div x-data="{}" x-init="dragModal($el)" class="modal-dialog" wire:ignore.self>
            <div class="modal-content">
                <div class="cardPrin">
                    <div class="cardPrin-header" style="cursor: move;">
                        <span>{{ $selected_id ? 'Editar Organo' : 'Crear Organo' }}</span>
                    </div>
                    <div class="cardPrin-body" style="padding: 10px; max-height: 90vh; overflow-y: auto;">
                        <form>
                            <div class="row gx-1 gy-1">
                                @if ($selected_id)
                                    <input type="hidden" wire:model="selected_id">
                                @endif
                                <div class="col-md-12">
                                    <label class="etiBase">Materia</label>
                                    <select wire:model="IdMateria" class="inpBase">
                                        <option value=""></option>
                                        @foreach ($materias as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>  
                                <div class="col-md-12">
                                    <label class="etiBase">Organo</label>
                                    <input wire:model="organo" type="text" class="inpBase" onfocus="this.select()">
                                    @error('organo') <span class="error text-danger">{{ $message }}</span> @enderror
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