<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\DealController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    
    // User management routes (admin only)
    Route::middleware('role:Admin')->group(function () {
        Route::resource('users', UserController::class);
    });
    
    // The rest of your CRM routes will go here, protected by auth middleware
    // We'll add the specific routes as we implement each feature

    // Contact Management Routes
    Route::resource('contacts', ContactController::class);
    Route::post('contacts/import', [ContactController::class, 'import'])->name('contacts.import');
    Route::get('contacts/export', [ContactController::class, 'export'])->name('contacts.export');
    // Add note to contact
Route::post('contacts/{contact}/notes', [ContactController::class, 'addNote'])->name('contacts.add-note');
});

// Company routes
Route::resource('companies', CompanyController::class);
Route::post('companies/{company}/notes', [CompanyController::class, 'addNote'])->name('companies.add-note');

// Lead Management Routes
Route::resource('leads', LeadController::class);
Route::get('leads/{lead}/convert', [LeadController::class, 'showConvert'])->name('leads.convert.show');
Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
Route::post('leads/{lead}/notes', [LeadController::class, 'addNote'])->name('leads.add-note');

// Placeholder routes for features under development
// Deal Management Routes
Route::get('/deals/pipeline', [App\Http\Controllers\DealController::class, 'pipeline'])->name('deals.pipeline');
Route::resource('deals', DealController::class);
Route::post('deals/{deal}/change-status', [DealController::class, 'changeStatus'])->name('deals.change-status');
Route::post('deals/{deal}/notes', [DealController::class, 'addNote'])->name('deals.add-note');



// Task routes
Route::get('/tasks/create', function() { 
    return inertia('ComingSoon', ['feature' => 'Task Creation']); 
})->name('tasks.create');

Route::get('/tasks/{id}', function($id) { 
    return inertia('ComingSoon', ['feature' => 'Task Details', 'id' => $id]); 
})->name('tasks.show');

require __DIR__.'/auth.php';