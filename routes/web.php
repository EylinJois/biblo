<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Book;
use App\Models\UserBookProgress;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\MyPetController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;

/********************
 LOGIN & REGISTRATION
 ********************/
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('/Auth/register', [RegisterController::class, 'store'])
        ->name('register.process');

    Route::post('/Auth/login', [LoginController::class, 'authenticate'])
        ->name('login.process');
});

/********************
 EMAIL VERIFICATION
 ********************/
Route::get('/email/verify', function () {
    return view('verify-email');
})->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id) {
    $user = User::findOrFail($id);

    if (!hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
        abort(403);
    }

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    return redirect()->route('onboarding')
        ->with('status', 'Email berhasil diverifikasi. Silakan login.');
})->middleware('signed')->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Link verifikasi telah dikirim ulang.');
})->middleware('throttle:6,1')->name('verification.send');

/********************
 RESET PASSWORD
 ********************/
use App\Http\Controllers\Auth\Logout;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

Route::get('/forgotpass', function () {
    return view('forgot-password');
})->name('forgot-password');

Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])
    ->name('password.request');

Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
    ->name('password.email');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])
    ->name('password.reset');

Route::post('/reset-password', [ResetPasswordController::class, 'store'])
    ->name('password.update');

/********************
 LOGOUT
 ********************/
Route::post('/logout', Logout::class)->name('Logout');

/********************
 PUBLIC
 ********************/
Route::get('/', function () {
    return view('welcome');
})->name('home');

/********************
 AUTHENTICATED APP
 ********************/
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | ONBOARDING
    |--------------------------------------------------------------------------
    */
    Route::get('/onboarding', [CategoryController::class, 'showOnboarding'])->name('onboarding');
    Route::post('/onboarding', [CategoryController::class, 'storeOnboarding'])->name('onboarding.store');

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD / EXPLORE
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/explore', [ExploreController::class, 'index'])->name('explore');
    Route::get('/search', [SearchController::class, 'index'])->name('search.global');

    /*
    |--------------------------------------------------------------------------
    | LIBRARY / NOTES
    |--------------------------------------------------------------------------
    */
    Route::get('/mylibrary', [LibraryController::class, 'index'])->name('mylibrary');

    Route::post('/book/{id}/toggle-library', [BookController::class, 'toggleLibrary'])
        ->name('book.toggle-library');

    Route::post('/notes/save', [NoteController::class, 'store'])->name('notes.store');
    Route::get('/mynotes', [NoteController::class, 'index'])->name('notes.index');
    Route::get('/mynotes/export-pdf', [NoteController::class, 'exportPdf'])->name('notes.export-pdf');
    Route::patch('/mynotes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/mynotes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    /*
    |--------------------------------------------------------------------------
    | BOOK DETAIL / READ / STREAM / PROGRESS
    |--------------------------------------------------------------------------
    */
    Route::get('/book-detail/{book:slug}', [BookController::class, 'show'])->name('book.detail');
    Route::get('/book-read/{book:slug}', [BookController::class, 'read'])->name('book.read');
    Route::get('/stream-book/{book:slug}', [BookController::class, 'stream'])->name('book.stream');
    Route::post('/update-progress/{book:slug}', [BookController::class, 'updateProgress'])->name('reading.update-progress');

    /*
    |--------------------------------------------------------------------------
    | PROFILE / READING GOAL
    |--------------------------------------------------------------------------
    */
    Route::get('/profil', [ProfileController::class, 'show'])->name('profil');
    Route::put('/profil', [ProfileController::class, 'updateProfile'])->name('profil.update');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profil.password.update');
    Route::put('/profil/reading-goal', [ProfileController::class, 'updateReadingGoal'])
        ->name('profil.reading-goal.update');

    /*
    |--------------------------------------------------------------------------
    | NOTIFICATIONS
    |--------------------------------------------------------------------------
    */
    Route::get('/notification', [NotificationController::class, 'index'])->name('notification.index');
    Route::post('/notification/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('notification.mark-as-read');
    Route::post('/notification/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notification.markAllAsRead');
    Route::delete('/notification/{id}', [NotificationController::class, 'destroy'])
        ->name('notification.destroy');

    /*
    |--------------------------------------------------------------------------
    | OTHER APP PAGES
    |--------------------------------------------------------------------------
    */
    Route::get('/mypet', [MyPetController::class, 'index'])->name('mypet');
    Route::post('/mypet/feed', [MyPetController::class, 'feed'])->name('mypet.feed');
    Route::get('/mypet/status', [MyPetController::class, 'getStatus'])->name('mypet.status');

    Route::get('/shop', [ShopController::class, 'index'])->name('shop');
    Route::post('/shop/purchase', [ShopController::class, 'purchase'])->name('shop.purchase');
    Route::post('/shop/equip-skin', [ShopController::class, 'equipSkin'])->name('shop.equip-skin');

    /*
    |--------------------------------------------------------------------------
    | TASKS / QUESTS
    |--------------------------------------------------------------------------
    */
    Route::get('/tasks/level/{levelGate}', [TaskController::class, 'getGateTasks'])->name('tasks.get');
    Route::post('/tasks/complete', [TaskController::class, 'completeTask'])->name('tasks.complete');

    Route::get('/app', function () {
        return view('layouts.app');
    })->name('layout.app.preview');

    Route::get('/guest', function () {
        return view('layouts.guest');
    })->name('layout.guest.preview');

    /*
    |--------------------------------------------------------------------------
    | DEFAULT LARAVEL PROFILE ROUTES
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';




use App\Http\Controllers\ReadingLogController;

Route::post('/reading-log', [ReadingLogController::class, 'store'])
    ->name('reading-log.store')
    ->middleware('auth');