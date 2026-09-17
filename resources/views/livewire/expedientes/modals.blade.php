@if($verModalExpediente)
    <div class="modal-overlay">
        <div x-data="{}" x-init="dragModal($el)" class="modal-dialog modalDialog" wire:ignore.self>
            <div class="modal-content">
                <div class="cardPrin">
                    <div class="cardPrin-header" style="cursor: move;">
                        <span>{{ $selected_id ? 'Editar Expediente' : 'Crear Expediente' }}</span>
                    </div>
                    <div class="cardPrin-body" style="padding: 10px; max-height: 400px; overflow-y: auto;">
                        <form>
                            <div class="row gx-1 gy-1">
                                @if ($selected_id)
                                    <input type="hidden" wire:model="selected_id">
                                @endif

                                <div class="col-md-6">
                                    <label class="etiBase">Expediente</label>
                                    <input wire:model="expediente" type="text" class="inpBase" onfocus="this.select()">
                                    @error('expediente') <span class="error text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="etiBase">Juzgado</label>
                                    <select wire:model="IdOrgano" class="inpBase">
                                        <option value=""></option>
                                        @foreach ($organos as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>  
                                <div class="col-md-6">
                                    <label class="etiBase">Actor</label>
                                    <select wire:model="IdActor" class="inpBase">
                                        <option value=""></option>
                                        @foreach ($personas as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>  
                                <div class="col-md-6">
                                    <label class="etiBase">Actor</label>
                                    <select wire:model="IdDemandado" class="inpBase">
                                        <option value=""></option>
                                        @foreach ($personas as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>  
                                <div class="col-md-6">
                                    <label class="etiBase">Asunto</label>
                                    <input wire:model="asunto" type="text" class="inpBase" onfocus="this.select()">
                                    @error('asunto') <span class="error text-danger">{{ $message }}</span> @enderror
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

@if($verModalDetalles)
    <div class="modal-overlay">
        <div x-data="{}" x-init="dragModal($el)" class="modal-dialog modalDialog" wire:ignore.self>
            <div class="modal-content">
                <div class="cardPrin">
                    <div class="cardPrin-body" style="padding: 10px; max-height: 500px; overflow-y: auto;">
                        @livewire('expedientesdets')
                    </div>
                    <div class="cardPrin-footer d-flex justify-content-end">
                        <button wire:click="cancel" class="bot botNegro botChico">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if($verModalPendientes)
    <div class="modal-overlay">
        <div x-data="{}" x-init="dragModal($el)" class="modal-dialog modalDialog" wire:ignore.self>
            <div class="modal-content">
                <div class="cardPrin">
                    <div class="cardPrin-body" style="padding: 10px; max-height: 500px; overflow-y: auto;">
                        @livewire('expedientespends')
                    </div>
                    <div class="cardPrin-footer d-flex justify-content-end">
                        <button wire:click="cancel" class="bot botNegro botChico">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif