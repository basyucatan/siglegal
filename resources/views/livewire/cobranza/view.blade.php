@section('title', __('Cobranza'))
<div class="container-fluid p-0" style="max-height:90vh;">
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
                        <select wire:model="IdCasa" wire:change="elegirCasa()" class="inpBase">
                            <option value="">-- Seleccionar Casa --</option>
                            @foreach ($casas as $key => $val)
                                <option value="{{ $key }}">{{ $val }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="etiBase">Cuarto (Ocupados)</label>
                        <select wire:model="IdCuarto" 
                                wire:change="elegirCuarto()"
                                wire:key="select-cuarto-{{ $IdCasa }}-{{ count($cuartos) }}" 
                                class="inpBase"
                                @if (!$IdCasa || $sinCuartosVigentes) disabled @endif>
                            <option value="">-- Seleccionar Cuarto --</option>
                            @foreach ($cuartos as $key => $val)
                                <option value="{{ $key }}" {{ (string)$IdCuarto === (string)$key ? 'selected' : '' }}>
                                    {{ $val }}
                                </option>
                            @endforeach
                        </select>
                        @if ($sinCuartosVigentes)
                            <small class="text-danger d-block mt-1">Sin cuartos con contrato vigente.</small>
                        @endif
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
                                <i class="bi bi-printer"></i></span> Reporte
                            <span wire:loading wire:target="imprimirReporte">⏳</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-8" x-data="{ tab: 'aniejamiento' }">
            <div class="ficha d-flex flex-column border rounded overflow-hidden bg-white">
                <div class="ficha-headers d-flex border-bottom bg-light">
                    <button type="button" class="ficha-boton flex-fill border-0 border-end py-1 px-2 fw-semibold text-secondary"
                        :class="{ 'active': tab === 'estadoCuenta' }" @click="tab = 'estadoCuenta'">
                        Estado de Cuenta
                    </button>
                    <button type="button" class="ficha-boton flex-fill border-0 border-end py-1 px-2 fw-semibold text-secondary"
                        :class="{ 'active': tab === 'aniejamiento' }" @click="tab = 'aniejamiento'">
                        Añejamiento
                    </button>
                    <button type="button" class="ficha-boton flex-fill border-0 py-1 px-2 fw-semibold text-secondary"
                        :class="{ 'active': tab === 'liquidacion' }" @click="tab = 'liquidacion'">
                        Liquidación Semanal
                    </button>
                </div>
                <div class="ficha-wrapper flex-grow-1 p-2 overflow-auto" style="max-height: 60vh;">
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