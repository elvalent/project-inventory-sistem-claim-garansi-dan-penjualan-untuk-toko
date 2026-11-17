<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\WarrantyController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password;

Route::get('/', function () {
    return view('welcome');
});

// Generic login is a role selector. Each role's actual login uses its own URL
// and therefore its own browser session cookie.
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');

Route::get('/admin/login', fn (AuthenticatedSessionController $c) => $c->createForRole('admin'))->name('admin.login');
Route::post('/admin/login', fn (\App\Http\Requests\Auth\LoginRequest $r, AuthenticatedSessionController $c) => $c->storeForRole($r, 'admin'))->name('admin.login.store');
Route::get('/sales/login', fn (AuthenticatedSessionController $c) => $c->createForRole('sales'))->name('sales.login');
Route::post('/sales/login', fn (\App\Http\Requests\Auth\LoginRequest $r, AuthenticatedSessionController $c) => $c->storeForRole($r, 'sales'))->name('sales.login.store');
Route::get('/customer/login', fn (AuthenticatedSessionController $c) => $c->createForRole('customer'))->name('customer.login');
Route::post('/customer/login', fn (\App\Http\Requests\Auth\LoginRequest $r, AuthenticatedSessionController $c) => $c->storeForRole($r, 'customer'))->name('customer.login.store');

// ADMIN: cookie suffix _admin is selected before StartSession.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroyForRole'])->defaults('routeRole', 'admin')->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

    Route::get('/catalog', [ProductController::class, 'catalog'])->name('products.catalog');
    Route::resource('products', ProductController::class)->except(['show']);

    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');

    Route::get('/warranties', [WarrantyController::class, 'index'])->name('warranties.index');
    Route::get('/warranties/{warranty}', [WarrantyController::class, 'show'])->name('warranties.show');
    Route::patch('/warranties/{warranty}', [WarrantyController::class, 'update'])->name('warranties.update');

    // Pengelolaan akun Sales oleh Admin.
    Route::get('/sales-accounts', function () {
        return view('admin.sales-accounts', [
            'salesUsers' => User::where('role', 'sales')->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    })->name('sales-accounts.index');

    Route::post('/sales-accounts', function (Request $request) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(10)],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'role' => 'sales',
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('admin.sales-accounts.index')->with('status', 'Akun Sales berhasil dibuat.');
    })->middleware('throttle:10,1')->name('sales-accounts.store');

    Route::put('/sales-accounts/{user}/password', function (Request $request, User $user) {
        abort_unless($user->role === 'sales', 404);
        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(10)],
        ]);
        $user->update(['password' => Hash::make($data['password'])]);

        return redirect()->route('admin.sales-accounts.index')->with('status', 'Password Sales berhasil diganti.');
    })->middleware('throttle:10,1')->name('sales-accounts.password');
});

// SALES: separate session cookie and separate URL namespace.
Route::prefix('sales')->name('sales.')->middleware(['auth', 'role:sales'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroyForRole'])->defaults('routeRole', 'sales')->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'sales'])->name('dashboard');

    Route::get('/catalog', [ProductController::class, 'catalog'])->name('products.catalog');
    Route::resource('products', ProductController::class)->except(['show']);

    Route::get('/transactions', [SaleController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [SaleController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [SaleController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{sale}', [SaleController::class, 'show'])->name('transactions.show');

    Route::get('/warranties', [WarrantyController::class, 'index'])->name('warranties.index');
    Route::get('/warranties/{warranty}', [WarrantyController::class, 'show'])->name('warranties.show');
});

// CUSTOMER: separate session cookie and separate URL namespace.
Route::prefix('customer')->name('customer.')->middleware(['auth', 'role:pelanggan'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroyForRole'])->defaults('routeRole', 'customer')->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'customer'])->name('dashboard');
    Route::get('/catalog', [ProductController::class, 'catalog'])->name('catalog');

    Route::get('/transactions', [SaleController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{sale}', [SaleController::class, 'show'])->name('transactions.show');

    Route::get('/warranties', [WarrantyController::class, 'index'])->name('warranties.index');
    Route::get('/warranties/{warranty}', [WarrantyController::class, 'show'])->name('warranties.show');
    Route::post('/warranties/{warranty}/claim', [WarrantyController::class, 'claim'])->name('warranties.claim');
});

// Keep the legacy /dashboard endpoint for older links, but role dashboards above
// are the correct destinations for the independent tabs.
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

require __DIR__ . '/auth.php';
