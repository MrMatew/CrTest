<?php

use App\Http\Controllers\Account;
use App\Http\Controllers\AccountOptions;
use App\Http\Controllers\AccountTfa;
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

Route::get('/', function ()
{
    return view('welcome');
});

Route::controller(Account::class)->group(function ()
{
    Route::post('/account/change-dob', 'changeDob');
    Route::post('/account/change-dob-confirm', 'changeDobConfirm');
});

Route::controller(AccountTfa::class)->group(function ()
{
    Route::get('/account/two-factor', 'index');
    Route::post('/account/two-factor/{providerId}/enable-method', 'enableMethod');
});

Route::controller(AccountOptions::class)->group(function ()
{
    Route::get('/account/options', 'index');
    Route::post('/account/options/{optionId}/change-tfa', 'changeTfaProvider');
});
