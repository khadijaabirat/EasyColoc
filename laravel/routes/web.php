<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ColocationsController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\SettlementsController;
use App\Http\Controllers\MembershipsController;
use App\Http\Controllers\AdminController;

// ── Public welcome ─────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
});

// ── Invitations (Public) ────────────────────────────────────────────────
Route::get('/invitations/accept/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');

// ── Authenticated + verified + not banned ──────────────────────────────
Route::middleware(['auth', 'verified', 'banned'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [ColocationsController::class, 'index'])
        ->name('dashboard');

    // Profile
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Colocations
    Route::get('/colocations',              [ColocationsController::class, 'index'])->name('colocations.index');
    Route::get('/colocations/create',       [ColocationsController::class, 'create'])->name('colocations.create');
    Route::post('/colocations',             [ColocationsController::class, 'store'])->name('colocations.store');
    Route::get('/colocations/{colocation}', [ColocationsController::class, 'show'])->name('colocations.show');
    Route::post('/colocations/{colocation}/leave',  [ColocationsController::class, 'leave'])->name('colocations.leave');
    Route::post('/colocations/{colocation}/cancel', [ColocationsController::class, 'cancel'])->name('colocations.cancel');

    // Invitations
    Route::post('/colocations/{colocation}/invite', [InvitationController::class, 'store'])->name('invitations.store');
    Route::post('/invitations/join',                [InvitationController::class, 'joinManual'])->name('invitations.join');

    // Expenses
    Route::post('/colocations/{colocation}/expenses',               [ExpensesController::class, 'store'])->name('expenses.store');
    Route::put('/colocations/{colocation}/expenses/{expense}',      [ExpensesController::class, 'update'])->name('expenses.update');
    Route::delete('/colocations/{colocation}/expenses/{expense}',   [ExpensesController::class, 'destroy'])->name('expenses.destroy');

    // Categories
    Route::post('/colocations/{colocation}/categories',                     [CategoriesController::class, 'store'])->name('categories.store');
    Route::delete('/colocations/{colocation}/categories/{category}',        [CategoriesController::class, 'destroy'])->name('categories.destroy');

    // Settlements
    Route::post('/colocations/{colocation}/settlements/{settlement}/pay', [SettlementsController::class, 'markPaid'])->name('settlements.pay');

    // Memberships
    Route::delete('/colocations/{colocation}/members/{user}', [MembershipsController::class, 'removeMember'])->name('memberships.remove');

    // Admin (global admin only — checked inside the controller)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/',                 [AdminController::class, 'index'])->name('index');
        Route::post('/users/{user}/ban',   [AdminController::class, 'ban'])->name('users.ban');
        Route::post('/users/{user}/unban', [AdminController::class, 'unban'])->name('users.unban');
    });
});

require __DIR__.'/auth.php';
