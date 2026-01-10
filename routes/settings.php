<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('settings/appearance', function () {
    return Inertia::render('settings/appearance');
})->name('appearance.edit');
