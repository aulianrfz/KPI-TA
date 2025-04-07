<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PenjadwalanController;

Route::post('/generate-schedule', [PenjadwalanController::class, 'generateSchedule']);