<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SSO\SSOContoller;
use Illuminate\Support\Facades\Auth;
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
    return view('welcome');
});
Route::get('sso/login', [SSOContoller::class, 'getLogin'])->name('sso.login');


Auth::routes(['register' => false, 'reset' => false]);
Route::get('callback', [SSOContoller::class, 'getCallback'])->name('sso.callback');

Route::get('sso/authuser', [SSOContoller::class, 'getAuthUser'])->name('sso.authuser');
// Route::get('sso/logout', [SSOContoller::class, 'getLogout'])->name('sso.logout');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
Route::get('clients', [SSOContoller::class, 'getClients']);


