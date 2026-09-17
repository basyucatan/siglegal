@extends('layouts.app')
@section('title', __('Welcome'))
@section('content')
<style>
    .textoFuego {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: 2px;
        margin-top: -10px;
        color: #fff;
        text-shadow: 0 0 10px #fff, 0 0 20px #ffcc00, 0 0 30px #ff4d00, 0 0 40px #ff4d00;
        position: relative;
        z-index: 2;
    }
</style>

<div class="container py-1">
    <div class="text-center shadow-lg" style="padding: 5px; background: linear-gradient(45deg, #000, #022b8a); border-radius: 20px;">
        
        @include('logo')

        <div class="textoFuego">Web Master Model</div>
        <p class="text-white-50 lead">WMM © {{ date('Y') }} | Sistemas Informáticos</p>
        @auth
            <div class="mt-4">
                @role('inquilino')
                    <a href="/cobhuesped" class="bot botVerde">Mi cuenta</a>
                @endrole
                @unlessrole('inquilino')
                    <a href="/expedientes" class="bot botVerde">Expedientes</a>
                @endunlessrole
            </div>
        @endauth
    </div>
</div>
@endsection