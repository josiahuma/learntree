<?php

use App\Http\Controllers\Instructor\InstructorController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Admin\HomeSettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CourseApprovalController;
use App\Http\Controllers\QuizQuestionController;
use App\Http\Controllers\CourseReviewController;
use App\Http\Controllers\LessonCompletionController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CourseController::class, 'landing'])->name('home');

Route::get('/courses/search', function () {
    return redirect()->route('courses.index');
})->name('courses.search');


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Routes
// ✅ Namespaced under admin.* and prefixed with /admin
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

    // Home settings
    Route::get('/home-settings/edit', [HomeSettingsController::class, 'edit'])->name('home-settings.edit');
    Route::put('/home-settings/update', [HomeSettingsController::class, 'update'])->name('home-settings.update');

    // User management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');

    // NEW: Approve Courses page
    Route::get('/courses', [CourseApprovalController::class, 'index'])
        ->name('courses.index');
    Route::put('/courses/{course}', [CourseApprovalController::class, 'update'])
        ->name('courses.update');
});


// Protected Routes
Route::middleware('auth')->group(function () {

    // Profile Management
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Course & Lesson Management
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
    Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
    Route::resource('lessons', LessonController::class)->only(['edit', 'update', 'destroy']);
    Route::get('/courses/{course}/lessons/create', [LessonController::class, 'create'])->name('lessons.create');
    Route::post('/courses/{course}/lessons', [LessonController::class, 'store'])->name('lessons.store');
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store'])->name('courses.enroll');
    Route::get('/my-courses', [StudentController::class, 'myCourses'])->name('student.courses');
    Route::get('/instructor/dashboard', [InstructorController::class, 'dashboard'])->name('instructor.dashboard');
    Route::get('/courses/{course}/students', [CourseController::class, 'students'])->name('courses.students');
    Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
    Route::post('/lessons/{lesson}/complete', [LessonCompletionController::class, 'store'])->name('lessons.complete');
    Route::get('/courses/{course}/reviews/{review}/edit', [CourseReviewController::class, 'edit'])->name('courses.reviews.edit');
    Route::put('/courses/{course}/reviews/{review}', [CourseReviewController::class, 'update'])->name('courses.reviews.update');
    Route::delete('/courses/{course}/reviews/{review}', [CourseReviewController::class, 'destroy'])->name('courses.reviews.destroy');


    // Quiz Management (Create / Edit / Delete)
    Route::get('/lessons/{lesson}/quiz/create', [QuizController::class, 'create'])
        ->name('quizzes.create');

    Route::post('/lessons/{lesson}/quiz/create', [QuizController::class, 'store'])
        ->name('quizzes.store');

    // If/when you implement editing a quiz:
    Route::get('/quizzes/{quiz}/edit', [QuizController::class, 'edit'])
        ->name('quizzes.edit');
    Route::put('/quizzes/{quiz}', [QuizController::class, 'update'])
        ->name('quizzes.update');

    // Optional detailed view of a quiz
    Route::get('/quizzes/{quiz}', [QuizController::class, 'view'])
        ->name('quizzes.view');

    //Delete entire quiz
    Route::delete('/quizzes/{quiz}', [QuizController::class, 'destroy'])
        ->name('quizzes.destroy');

    // Delete an individual question (still handled by QuizQuestionController)
    Route::delete('/questions/{question}', [QuizQuestionController::class, 'destroy'])
        ->name('questions.destroy');

    // Paginated Quiz Flow
    Route::get('/lessons/{lesson}/quiz/take', [QuizController::class, 'takePaginated'])
        ->name('lessons.quiz.paginated');

    Route::post('/lessons/{lesson}/quiz/take', [QuizController::class, 'storePaginatedAnswer'])
        ->name('lessons.quiz.paginated.store');

    Route::get('/lessons/{lesson}/quiz/submit', [QuizController::class, 'submitPaginated'])
        ->name('lessons.quiz.paginated.submit');

    Route::get('/lessons/{lesson}/quiz/result', [QuizController::class, 'result'])
        ->name('lessons.quiz.result');


    // ✅ Paginated Quiz Flow (only use these, remove old quiz routes)
    Route::get('/lessons/{lesson}/quiz/take', [QuizController::class, 'takePaginated'])->name('lessons.quiz.paginated');
    Route::post('/lessons/{lesson}/quiz/take', [QuizController::class, 'storePaginatedAnswer'])->name('lessons.quiz.paginated.store');
    Route::get('/lessons/{lesson}/quiz/submit', [QuizController::class, 'submitPaginated'])->name('lessons.quiz.paginated.submit');

    // ✅ Quiz Result Page
    Route::get('/lessons/{lesson}/quiz/result', [QuizController::class, 'result'])->name('lessons.quiz.result');

    // Student Dashboard
    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');

    // Certificate Download
    Route::get('/certificate/{course}', [\App\Http\Controllers\CertificateController::class, 'download'])
    ->middleware('auth')
    ->name('certificate.download');

});

// Public Routes
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::post('/courses/{course}/reviews', [CourseReviewController::class, 'store'])->name('courses.reviews.store');

Route::middleware('guest')->group(function () {
    Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])
        ->name('auth.google.redirect');

    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])
        ->name('auth.google.callback');
});


require __DIR__ . '/auth.php';
