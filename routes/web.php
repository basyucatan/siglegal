<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::view('registro', 'livewire.registro.index');

Route::middleware("auth")->group(function () {
    Route::view('users', 'livewire.users.index');
    Route::view('deptos', 'livewire.deptos.index');
    Route::view('roles', 'livewire.roles.index');
    Route::view('permisos', 'livewire.permissions.index');
    Route::view('gestionPermisos', 'livewire.gestionPermisos.index');    

    Route::view('welcome', 'livewire.welcome.index');
    Route::view('mensajes', 'livewire.mensajes.index');

    Route::view('catalogos', 'livewire.catalogos.index');
    
    Route::view('arbolcasas', 'livewire.arbolcasas.index');
    Route::view('control', 'livewire.control.index');

    Route::view('cuentas', 'livewire.cuentas.index');
    Route::view('casas', 'livewire.casas.index');
    Route::view('cuartos', 'livewire.cuartos.index');
    Route::view('inquilinos', 'livewire.inquilinos.index');
    Route::view('propietarios', 'livewire.propietarios.index');
    Route::view('vehiculos', 'livewire.vehiculos.index');
    Route::view('tecnicos', 'livewire.tecnicos.index');
    Route::view('asignacions', 'livewire.asignacions.index');
    Route::view('prioridads', 'livewire.prioridads.index');
    Route::view('fallas', 'livewire.fallas.index');
    Route::view('contratos', 'livewire.contratos.index');
    Route::view('evidencias', 'livewire.evidencias.index');
    Route::view('control', 'livewire.control.index');

    Route::view('gastos', 'livewire.gastos.index');
    Route::view('contratos', 'livewire.contratos.index');
    Route::view('ocupacion', 'livewire.ocupacion.index');
    Route::view('cobranza', 'livewire.cobranza.index');
    Route::view('cobhuesped', 'livewire.cobhuesped.index');

    Route::view('penas', 'livewire.penas.index');
    Route::view('asistencias', 'livewire.asistencias.index');
    Route::view('asignacions', 'livewire.asignacions.index');
});
