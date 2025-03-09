<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Buildings Management
    Route::resource('buildings', BuildingController::class);
    
    // Floors Management
    Route::resource('floors', FloorController::class);
    
    // Spaces Management
    Route::resource('spaces', SpaceController::class);
    
    // Assets Management
    Route::resource('assets', AssetController::class);
    
    // Work Orders
    Route::resource('work-orders', WorkOrderController::class);
    
    // Maintenance
    Route::resource('maintenance', MaintenanceController::class);
    
    // Reports
    Route::resource('reports', ReportController::class);
    
    // Admin Settings
    Route::get('admin/settings', [AdminSettingsController::class, 'index'])->name('admin.settings');
    Route::post('admin/settings', [AdminSettingsController::class, 'update'])->name('admin.settings.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
