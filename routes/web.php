<?php

use App\Http\Controllers\Dashboard\StudentDashboardController;
use App\Http\Controllers\Dashboard\StudentAssignmentController;
use App\Http\Controllers\Dashboard\TeacherAssignmentController;
use App\Http\Controllers\Dashboard\TeacherAssignmentSubmissionController;
use App\Http\Controllers\Dashboard\AssignmentSubmissionAttachmentController;
use App\Http\Controllers\Dashboard\TeacherDashboardController;
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

    if ($request->user()) {
        $request->user()->forceFill([
            'preferred_locale' => $validated['locale'],
        ])->save();
    }

    return back();
})->name('locale.switch');

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('submission-attachments/{attachment}/download', [AssignmentSubmissionAttachmentController::class, 'download'])
        ->name('submission-attachments.download');

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
            Route::get('dashboard', TeacherDashboardController::class)->name('dashboard');
            Route::get('assignments', [TeacherAssignmentController::class, 'index'])->name('assignments.index');
            Route::get('assignments/create', [TeacherAssignmentController::class, 'create'])->name('assignments.create');
            Route::post('assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');
            Route::get('assignments/{assignment}/edit', [TeacherAssignmentController::class, 'edit'])->name('assignments.edit');
            Route::put('assignments/{assignment}', [TeacherAssignmentController::class, 'update'])->name('assignments.update');
            Route::delete('assignments/{assignment}', [TeacherAssignmentController::class, 'destroy'])->name('assignments.destroy');
            Route::get('assignments/{assignment}/submissions', [TeacherAssignmentSubmissionController::class, 'index'])->name('assignments.submissions.index');
            Route::get('assignments/{assignment}/submissions/{submission}', [TeacherAssignmentSubmissionController::class, 'show'])->name('assignments.submissions.show');
            Route::put('assignments/{assignment}/submissions/{submission}/review', [TeacherAssignmentSubmissionController::class, 'review'])->name('assignments.submissions.review');
            Route::delete('assignments/{assignment}/submissions/{submission}/review', [TeacherAssignmentSubmissionController::class, 'clearReview'])->name('assignments.submissions.clear-review');
        });

    Route::middleware(['role:student'])
        ->prefix('student')
        ->as('student.')
        ->group(function () {
            Route::get('dashboard', StudentDashboardController::class)->name('dashboard');
            Route::get('assignments', [StudentAssignmentController::class, 'index'])->name('assignments.index');
            Route::get('assignments/{assignment}', [StudentAssignmentController::class, 'show'])->name('assignments.show');
            Route::post('assignments/{assignment}/submit', [StudentAssignmentController::class, 'submit'])->name('assignments.submit');
        });
});

require __DIR__.'/settings.php';
