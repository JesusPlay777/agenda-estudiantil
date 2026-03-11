<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

Route::post('/locale', function (Request $request) {
    $supportedLocales = array_keys(config('app.supported_locales', []));

    $validated = $request->validate([
        'locale' => ['required', 'string', Rule::in($supportedLocales)],
    ]);

    $request->session()->put('locale', $validated['locale']);

    return back();
})->name('locale.switch');

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function (Request $request) {
        return match ($request->user()->role) {
            User::ROLE_ADMIN => redirect('/admin'),
            User::ROLE_TEACHER => redirect()->route('teacher.dashboard'),
            User::ROLE_STUDENT => redirect()->route('student.dashboard'),
            default => abort(403),
        };
    })->name('dashboard');

    Route::middleware(['role:teacher'])
        ->prefix('teacher')
        ->as('teacher.')
        ->group(function () {
            Route::view('dashboard', 'dashboards.teacher')->name('dashboard');
        });

    Route::middleware(['role:student'])
        ->prefix('student')
        ->as('student.')
        ->group(function () {
            Route::view('dashboard', 'dashboards.student')->name('dashboard');
        });
});

require __DIR__.'/settings.php';
