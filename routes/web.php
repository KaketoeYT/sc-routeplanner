<?php

use App\Http\Controllers\CommoditiesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoutesController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/commodities', [CommoditiesController::class, 'index'])->name('commodities.index');

Route::get('/routes', [RoutesController::class, 'index'])->name('routes.index');
Route::get('/routes/dump', [RoutesController::class, 'dump'])->name('routes.dump');

Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');