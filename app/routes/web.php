<?php

use App\Http\Controllers\ComponentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ComponentController::class, 'index']);
Route::get('/component/{component}', [ComponentController::class, 'show'])->where('component', '[a-zA-Z0-9\-\.]+');
Route::get('/preview/{component}', [ComponentController::class, 'preview'])->where('component', '[a-zA-Z0-9\-\.]+');
Route::match(['get', 'post'], '/render', [ComponentController::class, 'render']);
