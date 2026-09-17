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
    Route::view('catalogos', 'livewire.catalogos.index');
    //Route Hooks - Do not delete//
	Route::view('expedientespends', 'livewire.expedientespends.index');
	Route::view('expedientesdets', 'livewire.expedientesdets.index');
	Route::view('expedientes', 'livewire.expedientes.index');
	Route::view('organos', 'livewire.organos.index');
	Route::view('materias', 'livewire.materias.index');
	Route::view('personas', 'livewire.personas.index');
});
