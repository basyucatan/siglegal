@section('title', __('Cobranza'))
<div class="container-fluid p-0" style="max-height:90vh;" 
     x-data="{
        tab: 'aniejamiento',
        idCasa: @entangle('IdCasa'),
        idCuarto: @entangle('IdCuarto'),
        cuartos: {{ json_encode($cuartos) }},
        get cuartosFiltrados() {
            if (!this.idCasa) return [];
            return this.cuartos.filter(c => c.IdCasa == this.idCasa);
        }
     }"
     x-init="$watch('idCasa', value => { idCuarto = ''; })">
    <div class="row g-2">
        <div class="col-12 col-md-4">
            <div class="cardPrin mb-2">
                <div class="cardPrin-header">Cobranza</div>
                <div class="cardPrin-body p-2">
                    <div class="mb-2">
                        <label class="etiBase">Buscar Inquilino / Teléfono</label>
                        <div class="position-relative">
                            <input wire:model.lazy="keyWord" class="inpSolo" wire:keydown.escape="$set('keyWord','')"
                                   onfocus="this.select()" placeholder="Buscar por nombre o teléfono...">
                            @if ($keyWord)
                                <span wire:click="$set('keyWord','')" class="bot botNegro botChico"
                                      style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                    X
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="etiBase">Casa</label>
                        <select class="inpBase" x-model="idCasa">
                            <option value="">-- Seleccionar Casa --</option>
                            @foreach ($casas as $key => $val)
                                <option value="{{ $key }}">{{ $val }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="etiBase">Cuarto (Ocupados)</label>
                        <select class="inpBase" x-model="idCuarto" :disabled="!idCasa || cuartosFiltrados.length === 0" wire:change="elegirCuarto()">
                            <option value="">-- Seleccionar Cuarto --</option>
                            <template x-for="cuarto in cuartosFiltrados" :key="cuarto.id">
                                <option :value="cuarto.id" x-text="cuarto.cuarto" :selected="idCuarto == cuarto.id"></option>
                            </template>
                        </select>
                        <template x-if="idCasa && cuartosFiltrados.length === 0">
                            <small class="text-danger d-block mt-1">Sin cuartos con contrato vigente.</small>
                        </template>
                    </div>

                    @if ($mostrarSelectContrato)
                        <div class="mb-2">
                            <label class="etiBase">Contrato / Inquilino</label>
                            <select wire:model="IdContrato" class="inpBase">
                                <option value="">-- Seleccionar Contrato --</option>
                                @foreach ($contratos as $key => $val)
                                    <option value="{{ $key }}">{{ $val }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>

            <div class="cardSec">
                <div class="cardSec-header">Reporte de Cobros</div>
                <div class="cardSec-body p-2">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="etiBase">Fecha Inicial</label>
                            <input wire:model="fechaIni" type="date" class="inpBase">
                            @error('fechaIni')
                                <span class="error text-danger" style="font-size:11px;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-6">
                            <label class="etiBase">Fecha Final</label>
                            <input wire:model="fechaFin" type="date" class="inpBase">
                            @error('fechaFin')
                                <span class="error text-danger" style="font-size:11px;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-2 text-end">
                        <button class="bot botVerde botChico" wire:click="imprimirReporte"
                                wire:loading.attr="disabled" wire:target="imprimirReporte" title="Imprimir Reporte">
                            <span wire:loading.remove wire:target="imprimirReporte">
                                <i class="bi bi-printer"></i>
                            </span> Reporte
                            <span wire:loading wire:target="imprimirReporte">⏳</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-8">
            <div class="ficha d-flex flex-column border rounded overflow-hidden">
                <div class="ficha-headers d-flex border-bottom">
                    <button type="button" class="ficha-boton flex-fill border-0 border-end py-1 px-2 fw-semibold"
                            :class="{ 'active': tab === 'estadoCuenta' }" @click="tab = 'estadoCuenta'">
                        Estado de Cuenta
                    </button>
                    <button type="button" class="ficha-boton flex-fill border-0 border-end py-1 px-2 fw-semibold"
                            :class="{ 'active': tab === 'aniejamiento' }" @click="tab = 'aniejamiento'">
                        Añejamiento
                    </button>
                    <button type="button" class="ficha-boton flex-fill border-0 py-1 px-2 fw-semibold"
                            :class="{ 'active': tab === 'liquidacion' }" @click="tab = 'liquidacion'">
                        Liquidación Semanal
                    </button>
                </div>
                <div class="ficha-wrapper flex-grow-1 p-2 overflow-auto" style="max-height: 80vh;">
                    <div class="ficha-body" :class="{ 'active': tab === 'estadoCuenta' }">
                        @include('livewire.cobranza.estadoCuenta')
                    </div>
                    <div class="ficha-body" :class="{ 'active': tab === 'aniejamiento' }">
                        @include('livewire.cobranza.aniejamiento')
                    </div>
                    <div class="ficha-body" :class="{ 'active': tab === 'liquidacion' }">
                        @include('livewire.cobranza.liquidacion')
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('livewire.cobranza.modals')
</div>