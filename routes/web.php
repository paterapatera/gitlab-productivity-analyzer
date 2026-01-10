<?php

use App\Presentation\Controller\ProjectController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('example');
})->name('home');

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::post('/projects/sync', [ProjectController::class, 'sync'])->name('projects.sync');
