<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IbadatController;
use App\Http\Controllers\StreakController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider.
|
*/

// Public routes
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
});

// Authentication routes (Laravel Breeze)
require __DIR__.'/auth.php';

// Protected routes - require authentication
Route::middleware(['auth'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Dashboard Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
         ->name('dashboard');
    
    Route::get('/dashboard/stats', [DashboardController::class, 'getQuickStats'])
         ->name('dashboard.stats');

    /*
/*
|--------------------------------------------------------------------------
| Ibadat Tracking Routes
|--------------------------------------------------------------------------
*/

Route::prefix('ibadat')->name('ibadat.')->group(function () {

     // Daily tracking page
     Route::get('/', [IbadatController::class, 'index'])
         ->name('index');
 
     Route::get('/{date}', [IbadatController::class, 'index'])
         ->name('date')
         ->where('date', '\d{4}-\d{2}-\d{2}');
 
     // Prayer toggle
     Route::post('/prayer/{prayerId}/toggle', [IbadatController::class, 'togglePrayer'])
         ->name('prayer.toggle');
 
     // Fasting update
     Route::post('/{logId}/fasting', [IbadatController::class, 'updateFasting'])
         ->name('fasting.update');
 
     // Quran update
     Route::post('/{logId}/quran', [IbadatController::class, 'updateQuran'])
         ->name('quran.update');
 
     // Charity update
     Route::post('/{logId}/charity', [IbadatController::class, 'updateCharity'])
         ->name('charity.update');
 
     // Notes (FIXED - NO NESTING)
     Route::post('/{logId}/notes', [IbadatController::class, 'addNote'])
         ->name('notes.add');
 
     Route::delete('/notes/{noteId}', [IbadatController::class, 'deleteNote'])
         ->name('notes.delete');
 
     Route::post('/notes/{noteId}/update', [IbadatController::class, 'updateNote'])
         ->name('notes.update');
 
     // Daily summary (AJAX)
     Route::get('/summary/{date?}', [IbadatController::class, 'getDailySummary'])
         ->name('summary')
         ->where('date', '\d{4}-\d{2}-\d{2}');
 });
    /*
    |--------------------------------------------------------------------------
    | Streak Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('streaks')->name('streaks.')->group(function () {
        // Streak dashboard
        Route::get('/', [StreakController::class, 'index'])
             ->name('index');
        
        // Specific streak details
        Route::get('/{type}', [StreakController::class, 'show'])
             ->name('show')
             ->whereIn('type', ['prayer', 'fasting', 'quran', 'charity']);
        
        // Recalculate streaks
        Route::post('/recalculate', [StreakController::class, 'recalculate'])
             ->name('recalculate');
    });

    /*
    |--------------------------------------------------------------------------
    | Family Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('family')->name('family.')->group(function () {
        // Family dashboard
        Route::get('/', [FamilyController::class, 'index'])
             ->name('index');
        
        // Create family
        Route::get('/create', [FamilyController::class, 'create'])
             ->name('create');
        
        Route::post('/', [FamilyController::class, 'store'])
             ->name('store');
        
        // Join family
        Route::get('/join', [FamilyController::class, 'showJoinForm'])
             ->name('join.form');
        
        Route::post('/join', [FamilyController::class, 'join'])
             ->name('join');
        
        // Show family details
        Route::get('/{familyId}', [FamilyController::class, 'show'])
             ->name('show');
        
        // Parent dashboard
        Route::get('/{familyId}/parent-dashboard', [FamilyController::class, 'parentDashboard'])
             ->name('parent-dashboard');
        
        // Family progress (AJAX)
        Route::get('/{familyId}/progress', [FamilyController::class, 'getFamilyProgress'])
             ->name('progress');
        
        // Update member role (parent only)
        Route::post('/{familyId}/role', [FamilyController::class, 'updateRole'])
             ->name('role.update');
        
        // Remove member (parent only)
        Route::post('/{familyId}/remove-member', [FamilyController::class, 'removeMember'])
             ->name('remove-member');
        
        // Leave family
        Route::post('/{familyId}/leave', [FamilyController::class, 'leave'])
             ->name('leave');
        
        // Delete family (creator only)
        Route::delete('/{familyId}', [FamilyController::class, 'destroy'])
             ->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Report Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('reports')->name('reports.')->group(function () {
        // Reports dashboard
        Route::get('/', [ReportController::class, 'index'])
             ->name('index');
        
        // Weekly report
        Route::get('/daily', [ReportController::class, 'weeklyReport'])
             ->name('daily');
        
        // Custom date range report
        Route::get('/custom', [ReportController::class, 'customReport'])
             ->name('custom');
        
        // Export PDF
        Route::get('/export/pdf', [ReportController::class, 'exportPdf'])
             ->name('export.pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | Profile Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->name('profile.')->group(function () {
        // View profile
        Route::get('/', [ProfileController::class, 'show'])
             ->name('show');
        
        // Edit profile
        Route::get('/edit', [ProfileController::class, 'edit'])
             ->name('edit');
        
        Route::post('/update', [ProfileController::class, 'update'])
             ->name('update');
        
        // Change password
        Route::get('/change-password', [ProfileController::class, 'showChangePassword'])
             ->name('password.form');
        
        Route::post('/change-password', [ProfileController::class, 'updatePassword'])
             ->name('password.update');
        
        // Update avatar
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])
             ->name('avatar.update');
        
        // Get stats (AJAX)
        Route::get('/stats', [ProfileController::class, 'getStats'])
             ->name('stats');
        
        // Delete account
        Route::delete('/', [ProfileController::class, 'destroy'])
             ->name('destroy');
    });

});

/*
|--------------------------------------------------------------------------
| API Routes (for AJAX requests)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    
    // Quick stats
    Route::get('/stats', [DashboardController::class, 'getQuickStats'])
         ->name('stats');
    
    // Daily summary
    Route::get('/daily-summary/{date?}', [IbadatController::class, 'getDailySummary'])
         ->name('daily-summary');
    
    // Family progress
    Route::get('/family/{familyId}/progress', [FamilyController::class, 'getFamilyProgress'])
         ->name('family.progress');
    
    // Profile stats
    Route::get('/profile/stats', [ProfileController::class, 'getStats'])
         ->name('profile.stats');
});
