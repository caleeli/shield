<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('welcome');
})->name('login');

Route::middleware('auth')->get('/main', function () {
    return view('main');
});

Route::post('/login', function (Request $request) {
    // $credentials = $request->only('username', 'password');
    $credentials = [
        'usuario' => $request->username,
        'password' => $request->password,
    ];
    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        if ($user->estado_id !== 1) {
            Auth::logout();
            return response()->json(['message' => 'Tu cuenta no está activa.'], 401);
        }
        $request->session()->regenerate();
        return response()->json([
            'message' => 'Inicio de sesión exitoso.',
            'user' => Auth::user()->toArray(),
        ]);
    } else {
        return response()->json(['message' => 'Credenciales inválidas.'], 401);
    }
});
