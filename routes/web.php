<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Legacy URLs from when admin routes used a /v1 prefix in the path.
Route::any('v1/admin/{path?}', function (?string $path = null) {
    $target = '/admin'.($path !== null && $path !== '' ? '/'.$path : '');
    $query = request()->getQueryString();

    return redirect()->to($query ? $target.'?'.$query : $target, 301);
})->where('path', '.*');

require __DIR__.'/auth.php';
