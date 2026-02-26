<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ColocationsController;

Route::get('/', function () {
    return view('welcome');
});

 Route::middleware(['auth', 'verified', 'banned'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

     Route::get('/colocations', [ColocationsController::class, 'index'])->name('colocations.index');
    Route::get('/colocations/create', [ColocationsController::class, 'create'])->name('colocations.create');
    Route::post('/colocations', [ColocationsController::class, 'store'])->name('colocations.store');
    Route::get('/colocations/{colocation}', [ColocationsController::class, 'show'])->name('colocations.show');
Route::post('/colocations/{colocation}/leave', [ColocationsController::class, 'leave'])->name('colocations.leave');
Route::post('/colocations/{colocation}/cancel', [ColocationsController::class, 'cancel'])->name('colocations.cancel');

    Route::post('/colocations/{colocation}/invite', [InvitationController::class, 'store'])->name('invitations.store');
Route::get('/invitations/accept/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');


});

require __DIR__.'/auth.php';
