<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContentPlanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginLogController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskTypeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// المصادقة
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // إدارة المستخدمين — للأدمن فقط
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/logins', [LoginLogController::class, 'index'])->name('logins.index');
    });

    // خطة المحتوى
    Route::get('/content', [ContentPlanController::class, 'index'])->name('content.index');
    Route::post('/content', [ContentPlanController::class, 'store'])->name('content.store');
    Route::put('/content/{contentPlan}', [ContentPlanController::class, 'update'])->name('content.update');
    Route::delete('/content/{contentPlan}', [ContentPlanController::class, 'destroy'])->name('content.destroy');
    Route::post('/content/{contentPlan}/contribute', [ContentPlanController::class, 'contribute'])->name('content.contribute');
    Route::post('/content/{contentPlan}/advance', [ContentPlanController::class, 'advanceStep'])->name('content.advance');
    Route::post('/content/{contentPlan}/design', [ContentPlanController::class, 'uploadDesign'])->name('content.design');
    Route::post('/content/approve', [ContentPlanController::class, 'approve'])->name('content.approve');
    Route::post('/content/bulk-approve', [ContentPlanController::class, 'bulkApprove'])->name('content.bulkApprove');
    Route::post('/content/reject', [ContentPlanController::class, 'reject'])->name('content.reject');

    // المهام
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');

    // الإشعارات
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');

    // صيانة الموقع — أدمن/مشرف
    Route::middleware('role:admin|supervisor')->group(function () {
        Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
        Route::post('/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
        Route::put('/maintenance/{maintenance}', [MaintenanceController::class, 'update'])->name('maintenance.update');
        Route::delete('/maintenance/{maintenance}', [MaintenanceController::class, 'destroy'])->name('maintenance.destroy');
    });

    // النقاط والتارجت
    Route::get('/points', [PointController::class, 'index'])->name('points.index');

    // الحضور والإجازات
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/attendance/{leave}/status', [AttendanceController::class, 'setStatus'])->name('attendance.status');

    // الرواتب — أدمن فقط
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');

    // التقارير — أدمن/مشرف
    Route::middleware('role:admin|supervisor')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

    // إدارة الأدوار والصلاحيات
    Route::middleware('permission:manage roles')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // إدارة أنواع المهام
    Route::middleware('permission:manage task types')->group(function () {
        Route::get('/task-types', [TaskTypeController::class, 'index'])->name('tasktypes.index');
        Route::post('/task-types', [TaskTypeController::class, 'store'])->name('tasktypes.store');
        Route::put('/task-types/{taskType}', [TaskTypeController::class, 'update'])->name('tasktypes.update');
        Route::delete('/task-types/{taskType}', [TaskTypeController::class, 'destroy'])->name('tasktypes.destroy');
    });

    // إعدادات النظام
    Route::middleware('permission:manage settings')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
