<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\ExportController;

Route::get('/', function () {
    return view('editor');
});

Route::post('/export', [ExportController::class, 'export']);
Route::get('/api/export/status/{id}', [ExportController::class, 'status']);

// Route::get('/', function () {
//     return view('welcome');
// });

