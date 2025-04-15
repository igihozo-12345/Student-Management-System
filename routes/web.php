<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Student authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Admin authentication routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login']);
    Route::get('/register', [AdminController::class, 'showRegistrationForm'])->name('admin.register');
    Route::post('/register', [AdminController::class, 'register']);
});

// Protected student routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
    
    // Profile routes
    Route::get('/profile/edit', [StudentController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [StudentController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [StudentController::class, 'updatePhoto'])->name('profile.photo');
    
    // Course enrollment routes
    Route::get('/courses/available', [StudentController::class, 'availableCourses'])->name('courses.available');
    Route::post('/courses/{course}/enroll', [StudentController::class, 'enroll'])->name('courses.enroll');
    Route::post('/courses/{course}/drop', [StudentController::class, 'drop'])->name('courses.drop');
    Route::get('/my-courses', [StudentController::class, 'enrolledCourses'])->name('courses.enrolled');
});

// Protected admin routes
Route::prefix('admin')->middleware(['auth:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    
    // Student management
    Route::get('/students/{student}/edit', [AdminController::class, 'editStudent'])->name('admin.students.edit');
    Route::put('/students/{student}', [AdminController::class, 'updateStudent'])->name('admin.students.update');
    Route::delete('/students/{student}', [AdminController::class, 'deleteStudent'])->name('admin.students.delete');
    Route::get('/students/create', [AdminController::class, 'createStudent'])->name('admin.students.create');
    Route::post('/students', [AdminController::class, 'storeStudent'])->name('admin.students.store');
    Route::get('/students', [AdminController::class, 'students'])->name('admin.students');
    
    // Course management
    Route::get('/courses', [AdminController::class, 'courses'])->name('admin.courses');
    Route::get('/courses/create', [AdminController::class, 'createCourse'])->name('admin.courses.create');
    Route::post('/courses', [AdminController::class, 'storeCourse'])->name('admin.courses.store');
    Route::get('/courses/{course}/edit', [AdminController::class, 'editCourse'])->name('admin.courses.edit');
    Route::put('/courses/{course}', [AdminController::class, 'updateCourse'])->name('admin.courses.update');
    Route::delete('/courses/{course}', [AdminController::class, 'deleteCourse'])->name('admin.courses.delete');
    
    // Department management
    Route::get('/departments', [AdminController::class, 'departments'])->name('admin.departments');
    Route::get('/departments/create', [AdminController::class, 'createDepartment'])->name('admin.departments.create');
    Route::post('/departments', [AdminController::class, 'storeDepartment'])->name('admin.departments.store');
    Route::get('/departments/{department}/edit', [AdminController::class, 'editDepartment'])->name('admin.departments.edit');
    Route::put('/departments/{department}', [AdminController::class, 'updateDepartment'])->name('admin.departments.update');
    Route::delete('/departments/{department}', [AdminController::class, 'deleteDepartment'])->name('admin.departments.delete');
});
