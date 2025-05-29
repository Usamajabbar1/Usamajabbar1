<?php
use App\Http\Controllers\API\CompanyProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Auth & Admin
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\AdminController;

// User, Role, Permission
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;

// Dashboard
use App\Http\Controllers\DashboardController;

// Invoicing
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\SupplierController;


Route::middleware(['force.json'])->group(function () {

    // 🔓 Public routes
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/admin/login', [LoginController::class, 'adminLogin']);

    // 🔐 Authenticated routes
    Route::middleware(['auth:sanctum'])->group(function () {

        // 🧑 Profile
        Route::get('/me', [UserApiController::class, 'getProfile']);

        // 📊 Dashboard stats
        Route::get('/dashboard-stats', [DashboardController::class, 'stats']);

        // 🔓 Logout
        Route::post('/logout', [LoginController::class, 'logout']);

        // 🧑‍💼 Register Admin (must be authenticated first)
        Route::post('/admin/register', [AdminController::class, 'register']);

        // 📄 Invoices (with items)
        Route::apiResource('invoices', InvoiceController::class);
        Route::get('/invoices/{id}/download', [InvoiceController::class, 'download']);

        Route::get('/company-profile', [CompanyProfileController::class, 'show']);
Route::put('/company-profile', [CompanyProfileController::class, 'update']);



        // 🧍 Customers (dynamic for dropdowns & management)
      Route::apiResource('customers', CustomerController::class);
      Route::put('/customers/archive/{id}', [CustomerController::class, 'archive']);

        // 👑 Admin-only routes
        Route::middleware('role:admin')->group(function () {

            // 👥 User Management
            Route::get('/users', [UserApiController::class, 'getUsers']);
            Route::post('/users/add', [UserApiController::class, 'store']);
            Route::get('/users/get/{id}', [UserApiController::class, 'show']);
            Route::put('/users/update/{id}', [UserApiController::class, 'update']);
            Route::delete('/users/delete/{id}', [UserApiController::class, 'destroy']);
            Route::put('/users/{id}/update-status', [UserApiController::class, 'updateStatus']);
            Route::post('/users/{id}/assign-role', [UserApiController::class, 'assignRole'])->middleware('permission:assign-role');
            Route::get('/stats/user-roles', [UserApiController::class, 'userRoleStats']);
            Route::get('/dashboard/user-role-stats', [UserApiController::class, 'userCountByRole']);

            // 🛡️ Role Management
            Route::get('/roles', [RoleController::class, 'index']);
            Route::post('/roles/add', [RoleController::class, 'store']);
            Route::get('/roles/show/{id}', [RoleController::class, 'show']);
            Route::put('/roles/update/{id}', [RoleController::class, 'update']);
            Route::delete('/roles/delete/{id}', [RoleController::class, 'destroy']);
            Route::get('/roles/permissions', [RoleController::class, 'permissions']);
            Route::put('/roles/reassign-users/{id}', [RoleController::class, 'reassignUsers']);

            // 🔐 Permissions (optional)
            // Route::get('/permissions', [PermissionController::class, 'index']);

         Route::apiResource('/suppliers', SupplierController::class);


        });
    });
});
