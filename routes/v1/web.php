<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin|super-admin'])
    ->group(base_path('routes/v1/admin.php'));
