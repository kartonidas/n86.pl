<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;

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

$router->post('/ipn/paynow', [PaymentController::class, "ipnPaynow"]);
$router->get('/pomoc/{any?}', [PageController::class, "help"]);
$router->get('/', [PageController::class, "index"]);

Route::get('{any?}', function () {
    return view('vue');
})->where('any', '^(?!api).*$');
