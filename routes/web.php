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
$router->get('/pomoc/{any?}', [PageController::class, "help"])->name("help")->where('any', '.*');

$router->get('/', [PageController::class, "index"]);
$router->get('/regulamin', [PageController::class, "regulations"])->name("regulations");
$router->get('/polityka-prywatnosci', [PageController::class, "privacyPolicy"])->name("privacy_policy");
$router->get('/ciasteczka', [PageController::class, "cookies"])->name("cookies");

$router->post('/kontakt', [PageController::class, "contact"])->name("contact");
$router->post('/fb-track', [PageController::class, "fbtrack"])->name("fbtrack");

$router->get('/najwazniejsze-funkcje/zarzadzanie-obiektami', [PageController::class, "features"])->name("zarzadzanie_obiektami");
$router->get('/najwazniejsze-funkcje/zarzadzanie-klientami', [PageController::class, "features"])->name("zarzadzanie_klientami");
$router->get('/najwazniejsze-funkcje/zarzadzanie-najemcami', [PageController::class, "features"])->name("zarzadzanie_najemcami");
$router->get('/najwazniejsze-funkcje/obsluga-usterek', [PageController::class, "features"])->name("obsluga_usterek");
$router->get('/najwazniejsze-funkcje/generowanie-dokumentow', [PageController::class, "features"])->name("generowanie_dokumentow");
$router->get('/najwazniejsze-funkcje/powiadomienia', [PageController::class, "features"])->name("powiadomienia");
$router->get('/najwazniejsze-funkcje/statystyki', [PageController::class, "features"])->name("statystyki");
$router->get('/najwazniejsze-funkcje/kontrola-rachunkow', [PageController::class, "features"])->name("kontrola_rachunkow");
$router->get('/najwazniejsze-funkcje/rozwiazania-chmurowe', [PageController::class, "features"])->name("rozwiazania_chmurowe");

Route::get('{any?}', function () {
    return view('vue');
})->where('any', '^(?!api).*$');
