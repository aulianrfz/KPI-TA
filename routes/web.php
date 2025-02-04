<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportExcelController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/import-excel', [ImportExcelController::class, 'import_excel']);
Route::post('/import-excel', [ImportExcelController::class, 'import_excel_post'])->name('import_excel_post');
