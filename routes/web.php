<?php

use App\Http\Controllers\AdminUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Rutas Públicas (Catálogo)
Route::get('/', function () {
    return view('welcome');
});

Route::get('/catalogo', function () {
    return view('welcome');
});

// Rutas de Autenticación
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        return redirect()->intended('/admin');
    }

    return back()->with('error', 'Las credenciales ingresadas son incorrectas.');
});

// Ruta Protegida del Dashboard Administrativo
Route::get('/admin', function () {
    return view('welcome-admin');
})->middleware('auth')->name('admin');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/usuarios/nuevo', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/usuarios', [AdminUserController::class, 'store'])->name('users.store');
});

// Ruta para Cerrar Sesión
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
});
