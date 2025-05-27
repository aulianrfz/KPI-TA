<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PenjadwalanController;

Route::post('/generate-jadwal', [PenjadwalanController::class, 'generateSchedule']);
Route::get('/jadwal', [PenjadwalanController::class, 'getSchedule']);
Route::get('/jadwal/detail', [PenjadwalanController::class, 'getScheduleDetail']);

Route::get('/penjadwalan/variabel-x', [PenjadwalanController::class, 'generateVariabelX']);
Route::get('/penjadwalan/peserta', [PenjadwalanController::class, 'processPesertaKategoriLomba']);