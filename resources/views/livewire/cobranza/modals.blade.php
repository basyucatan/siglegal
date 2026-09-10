@if($verModalPago)
    <div class="modal-overlay" 
        x-data="{ subiendoFoto: false }" 
        @paste.window="
            if (!@js($idPagoEdicion) || @js($idPagoEdicion)) {
                let items = ($event.clipboardData || $event.originalEvent.clipboardData).items;
                for (let index in items) {
                    let item = items[index];
                    if (item.kind === 'file' && item.type.includes('image')) {
                        let blob = item.getAsFile();
                        let file = new File([blob], 'comprobante_pasted.png', { type: item.type });
                        subiendoFoto = true;
                        @this.upload('foto', file, 
                            () => { subiendoFoto = false; }, 
                            () => { subiendoFoto = false; }
                        );
                    }
                }
            }
        "> 
        <div x-init="dragModal($el)" class="modal-dialog" style="max-width: 600px; width: 90%;">
            <div class="modal-content">
                <div class="cardPrin" style="overflow-y: auto; max-height: 90vh;">
                    <div class="cardPrin-header" style="cursor: move;">
                        {{ $idPagoEdicion ? 'Editar Pago / Comprobante' : 'Registrar Pago / Abono' }}
                    </div>
                    <div class="cardPrin-body">
                        <div style="margin-bottom: 15px;">
                            <label class="etiBase">Monto a Pagar</label>
                            <input wire:model="montoPago" type="number" step="0.01" class="inpBase" onfocus="this.select()">
                            @error('montoPago') <span class="text-danger" style="font-size: 12px;">{{ $message }}</span> @enderror
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label class="etiBase">Fecha de Pago</label>
                            <input wire:model="fechaPago" type="date" class="inpBase">
                            @error('fechaPago') <span class="text-danger" style="font-size: 12px;">{{ $message }}</span> @enderror
                        </div>
<div class="row g-2 mb-2">
    <div class="col-12 col-md-4">
        <label class="etiBase">Tipo de Pago</label>
        <select wire:model.live="tipo" class="inpBase">
            <option value="efectivo">Efectivo</option>
            <option value="transferencia">Transferencia</option>
            <option value="otro">Otro</option>
        </select>
    </div>

    @if($tipo === 'transferencia')
        <div class="col-12 col-md-8">
            <label class="etiBase">Cuenta Receptora</label>
            <select wire:model="IdCuenta" class="inpBase">
                <option value="">-- Seleccionar Cuenta --</option>
                @foreach($cuentas as $id => $cuenta)
                    <option value="{{ $id }}">{{ $cuenta }}</option>
                @endforeach
            </select>
            @error('IdCuenta') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
    @elseif($tipo === 'efectivo')
        <div class="col-12 col-md-8">
            <label class="etiBase">Recibido por</label>
            <select wire:model="IdValida" class="inpBase">
                <option value="">-- Seleccionar Usuario --</option>
                @foreach($users as $id => $nombre)
                    <option value="{{ $id }}">{{ $nombre }}</option>
                @endforeach
            </select>
            @error('IdValida') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
    @endif
</div>
                        <div style="margin-bottom: 15px;">
                            <label class="etiBase">Comprobante (Archivo, Cámara o Ctrl+V)</label>
                            <input type="file" wire:model="foto" accept="image/*" capture="environment" class="inpBase" wire:loading.attr="disabled" wire:target="foto" :disabled="subiendoFoto">
                            @error('foto') <span class="text-danger d-block" style="font-size: 12px;">{{ $message }}</span> @enderror
                        </div>

                        <div wire:loading wire:target="foto" x-show="true">
                            <div style="text-align: center; border: 1px dashed #0d6efd; border-radius: 4px; padding: 15px; background-color: #f8f9fa; margin-top: 10px; margin-bottom: 10px;">
                                <div class="spinner-border spinner-border-sm text-primary" role="status" style="margin-right: 5px;"></div>
                                <span class="etiBase" style="color: #0d6efd; font-weight: bold;">Procesando y subiendo imagen...</span>
                            </div>
                        </div>

                        <div x-show="subiendoFoto && !$wire.get('foto')" x-cloak style="text-align: center; border: 1px dashed #0d6efd; border-radius: 4px; padding: 15px; background-color: #f8f9fa; margin-top: 10px; margin-bottom: 10px;">
                            <div class="spinner-border spinner-border-sm text-primary" role="status" style="margin-right: 5px;"></div>
                            <span class="etiBase" style="color: #0d6efd; font-weight: bold;">Procesando captura del portapapeles...</span>
                        </div>

                        <div wire:loading.remove wire:target="foto" x-show="!subiendoFoto">
                            @if ($foto)
                                <div style="text-align: center; border: 1px solid #ccc; border-radius: 4px; padding: 5px; background-color: #fff; margin-top: 10px;">
                                    <label class="etiBase" style="display: block; text-align: left; margin-bottom: 5px;">Vista previa nueva:</label>
                                    <img src="{{ $foto->temporaryUrl() }}" style="max-height: 200px; object-fit: contain; max-width: 100%;">
                                </div>
                            @elseif ($fotoActual)
                                <div style="text-align: center; border: 1px solid #ccc; border-radius: 4px; padding: 5px; background-color: #fff; margin-top: 10px;">
                                    <label class="etiBase" style="display: block; text-align: left; margin-bottom: 5px;">Comprobante actual:</label>
                                    <img src="{{ asset('storage/' . $fotoActual) }}" style="max-height: 200px; object-fit: contain; max-width: 100%;">
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="cardPrin-footer">
                        <button wire:click="$set('verModalPago', false)" class="bot botNegro" wire:loading.attr="disabled" wire:target="foto" :disabled="subiendoFoto">Cancelar</button>
                        <button wire:click="guardarPago" wire:loading.attr="disabled" :disabled="subiendoFoto" class="bot botVerde">
                            <span wire:loading.remove wire:target="guardarPago">
                                {{ $idPagoEdicion ? 'Actualizar Pago' : 'Guardar Pago' }}
                            </span>
                            <span wire:loading wire:target="guardarPago">
                                Guardando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif