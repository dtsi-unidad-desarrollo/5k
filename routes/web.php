<?php

use App\Http\Controllers\ParticipantController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ParticipantController::class, 'index'])->name('home');
Route::post('/registro', [ParticipantController::class, 'store'])->name('participants.store');
Route::match(['get', 'post'], '/consultar', [ParticipantController::class, 'lookup'])->name('participants.lookup');
Route::get('/dorsal/{bib_number}', [ParticipantController::class, 'bib'])->name('participants.bib');
Route::get('/organizador/participantes', [ParticipantController::class, 'list'])->name('participants.list');
Route::get('/organizador/exportar-csv', [ParticipantController::class, 'exportCsv'])->name('participants.export');
