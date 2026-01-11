<?php

use App\Presentation\Controller\CommitController;
use App\Presentation\Controller\ProjectController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('example');
})->name('home');

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::post('/projects/sync', [ProjectController::class, 'sync'])->name('projects.sync');

Route::get('/commits/collect', [CommitController::class, 'collectShow'])->name('commits.collect');
Route::post('/commits/collect', [CommitController::class, 'collect'])->name('commits.collect.store');

Route::get('/commits/recollect', [CommitController::class, 'recollectShow'])->name('commits.recollect');
Route::post('/commits/recollect', [CommitController::class, 'recollect'])->name('commits.recollect.store');
