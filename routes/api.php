<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BalanceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DictionaryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->middleware(['auth:sanctum', 'locale'])->group(function () use($router) {
    // PAKIETY I CENY
    $router->get('/packages', [IndexController::class, "packages"]);
    
    $router->get("/is-login", [UserController::class, "isLogin"]);
    $router->get('/logout', [UserController::class, "logout"]);
    
    $router->get('/dashboard', [IndexController::class, "dashboard"]);
    $router->get('/subscription', [IndexController::class, "getActiveSubscription"]);
    $router->get('/current-stats', [IndexController::class, "getCurrentStats"]);
    $router->get('/search', [IndexController::class, "search"]);
    $router->get('/search/{source}', [IndexController::class, "searchIn"]);
    
    
    // NIERUCHOMOŚCI
    $router->get('/items', [ItemController::class, "list"]);
    $router->get('/items/settings', [ItemController::class, "settings"]);
    $router->put('/item', [ItemController::class, "create"]);
    $router->get('/item/{id}', [ItemController::class, "get"])->where("id", "[0-9]+");
    $router->put('/item/{id}', [ItemController::class, "update"])->where("id", "[0-9]+");
    $router->delete('/item/{id}', [ItemController::class, "delete"])->where("id", "[0-9]+");
    $router->post('/item/validate', [ItemController::class, "validateData"]);
    $router->get('/item/{id}/bills', [ItemController::class, "bills"])->where("id", "[0-9]+");
    $router->put('/item/{id}/bill', [ItemController::class, "billCreate"])->where("id", "[0-9]+");
    $router->get('/item/{id}/bill/{bid}', [ItemController::class, "billGet"])->where("id", "[0-9]+")->where("bid", "[0-9]+");
    $router->put('/item/{id}/bill/{bid}', [ItemController::class, "billUpdate"])->where("id", "[0-9]+")->where("bid", "[0-9]+");
    $router->delete('/item/{id}/bill/{bid}', [ItemController::class, "billDelete"])->where("id", "[0-9]+")->where("bid", "[0-9]+");
    $router->post('/item/{id}/bill/{bid}/paid', [ItemController::class, "billPaid"])->where("id", "[0-9]+")->where("bid", "[0-9]+");
    $router->post('/item/{id}/bill/{bid}/unpaid', [ItemController::class, "billUnpaid"])->where("id", "[0-9]+")->where("bid", "[0-9]+");
    $router->post('/item/{id}/bill/{bid}/payment', [ItemController::class, "billPayment"])->where("id", "[0-9]+")->where("bid", "[0-9]+");
    $router->get('/item/{id}/fees', [ItemController::class, "fees"])->where("id", "[0-9]+");
    $router->put('/item/{id}/fee', [ItemController::class, "feeCreate"])->where("id", "[0-9]+");
    
    $router->get('/item/{id}/fee/{fid}/costs', [ItemController::class, "feeCosts"])->where("id", "[0-9]+")->where("fid", "[0-9]+");
    $router->put('/item/{id}/fee/{fid}/cost', [ItemController::class, "feeCostCreate"])->where("id", "[0-9]+")->where("fid", "[0-9]+");
    $router->get('/item/{id}/fee/{fid}/cost/{cid}', [ItemController::class, "feeCostGet"])->where("id", "[0-9]+")->where("fid", "[0-9]+")->where("cid", "[0-9]+");
    $router->put('/item/{id}/fee/{fid}/cost/{cid}', [ItemController::class, "feeCostUpdate"])->where("id", "[0-9]+")->where("fid", "[0-9]+")->where("cid", "[0-9]+");
    $router->delete('/item/{id}/fee/{fid}/cost/{cid}', [ItemController::class, "feeCostDelete"])->where("id", "[0-9]+")->where("fid", "[0-9]+")->where("cid", "[0-9]+");
    
    $router->get('/item/{id}/fee/{fid}', [ItemController::class, "feeGet"])->where("id", "[0-9]+")->where("fid", "[0-9]+");
    $router->put('/item/{id}/fee/{fid}', [ItemController::class, "feeUpdate"])->where("id", "[0-9]+")->where("fid", "[0-9]+");
    $router->delete('/item/{id}/fee/{fid}', [ItemController::class, "feeDelete"])->where("id", "[0-9]+")->where("fid", "[0-9]+");
    
    // NAJEMCY
    $router->get('/tenants', [TenantController::class, "list"]);
    $router->put('/tenant', [TenantController::class, "create"]);
    $router->get('/tenant/{id}', [TenantController::class, "get"])->where("id", "[0-9]+");
    $router->put('/tenant/{id}', [TenantController::class, "update"])->where("id", "[0-9]+");
    $router->delete('/tenant/{id}', [TenantController::class, "delete"])->where("id", "[0-9]+");
    $router->post('/tenant/validate', [TenantController::class, "validateData"]);
    $router->get('/tenant/{id}/history', [TenantController::class, "history"])->where("id", "[0-9]+");
    
    // WYNAJEM
    $router->get('/rentals', [RentalController::class, "list"]);
    $router->get('/rental/{id}', [RentalController::class, "get"])->where("id", "[0-9]+");
    $router->post('/rental/validate', [RentalController::class, "validateData"]);
    $router->put('/rental/rent', [RentalController::class, "rent"]);
    $router->delete('/rental/{id}', [RentalController::class, "delete"])->where("id", "[0-9]+");
    
    // SŁOWNIKI
    $router->get('/dictionary/types', [DictionaryController::class, "types"]);
    $router->get('/dictionaries', [DictionaryController::class, "list"]);
    $router->get('/dictionaries/{type}', [DictionaryController::class, "listByType"]);
    $router->put('/dictionary', [DictionaryController::class, "create"]);
    $router->get('/dictionary/{id}', [DictionaryController::class, "get"])->where("id", "[0-9]+");
    $router->put('/dictionary/{id}', [DictionaryController::class, "update"])->where("id", "[0-9]+");
    $router->delete('/dictionary/{id}', [DictionaryController::class, "delete"])->where("id", "[0-9]+");
    
    // KLIENCI
    $router->get('/customers', [CustomerController::class, "list"]);
    $router->put('/customer', [CustomerController::class, "create"]);
    $router->get('/customer/{id}', [CustomerController::class, "get"])->where("id", "[0-9]+");
    $router->put('/customer/{id}', [CustomerController::class, "update"])->where("id", "[0-9]+");
    $router->delete('/customer/{id}', [CustomerController::class, "delete"])->where("id", "[0-9]+");
    
    $router->put('/item/deposit', [BalanceController::class, "itemDeposit"]);
    $router->put('/item/deposit/{id}', [BalanceController::class, "updateItemDeposit"])->where("id", "[0-9]+");
    $router->delete('/item/deposit/{id}', [BalanceController::class, "deleteItemDeposit"])->where("id", "[0-9]+");
    
    // PRACOWNICY
    $router->get('/users', [UserController::class, "list"]);
    $router->put('/user', [UserController::class, "create"]);
    $router->post('/invite', [UserController::class, "invite"]);
    $router->get('/user/{id}', [UserController::class, "get"])->where("id", "[0-9]+");
    $router->put('/user/{id}', [UserController::class, "update"])->where("id", "[0-9]+");
    $router->delete('/user/{id}', [UserController::class, "delete"])->where("id", "[0-9]+");
    $router->get('/user/permission', [UserController::class, "getPermissions"]);
    $router->get('/profile', [UserController::class, "profile"]);
    $router->put('/profile', [UserController::class, "profileUpdate"]);
    $router->post('/profile/avatar', [UserController::class, "profileAvatarUpdate"]);
    $router->get('/settings', [UserController::class, "settings"]);
    $router->put('/settings', [UserController::class, "settingsUpdate"]);
    
    $router->get('/get-firm-id', [UserController::class, "getFirmId"]);
    $router->get('/get-id', [UserController::class, "getId"]);
    $router->get('/user/getActiveTimer', [UserController::class, "getActiveTimer"]);
    
    // UPRAWNIENIA
    $router->get('/permissions', [PermissionController::class, "list"]);
    $router->put('/permission', [PermissionController::class, "create"]);
    $router->get('/permission/{id}', [PermissionController::class, "get"])->where("id", "[0-9]+");
    $router->put('/permission/{id}', [PermissionController::class, "update"])->where("id", "[0-9]+");
    $router->delete('/permission/{id}', [PermissionController::class, "delete"])->where("id", "[0-9]+");
    $router->put('/permission/{id}/add', [PermissionController::class, "addPermission"])->where("id", "[0-9]+");
    $router->delete('/permission/{id}/del', [PermissionController::class, "removePermission"])->where("id", "[0-9]+");
    $router->get('/permission/modules', [PermissionController::class, "permissionModules"]);
    
    // ZAMÓWIENIA / PŁATNOŚCI
    $router->get('/payment/status/{md5}', [PaymentController::class, "status"]);
    $router->post('/order/create', [OrderController::class, "create"]);
    
    $router->get('/invoices', [OrderController::class, "invoices"]);
    $router->get('/invoice/{id}', [OrderController::class, "invoice"])->where("id", "[0-9]+");
    $router->get('/validate-invoicing-data', [OrderController::class, "validateInvoicingData"]);
    
    $router->get('/firm-data', [UserController::class, "getFirmData"]);
    $router->post('/firm-data', [UserController::class, "firmDataUpdate"]);
    $router->get('/invoice-data', [UserController::class, "getInvoiceData"]);
    $router->post('/invoice-data', [UserController::class, "invoiceDataUpdate"]);
    
    $router->get('/notifications', [NotificationController::class, "list"]);
    $router->get('/notification/{id}', [NotificationController::class, "get"])->where("id", "[0-9]+");
    $router->put('/notification/read/{id}', [NotificationController::class, "setRead"])->where("id", "[0-9]+");
    
    // STATYSTYKI
    $router->get('/stats/user/{id}/daily', [StatsController::class, "userDaily"])->where("id", "[0-9]+");
    $router->get('/stats/user/{id}/monthly', [StatsController::class, "userMonthly"])->where("id", "[0-9]+");
    $router->get('/stats/project/{id}/daily', [StatsController::class, "projectDaily"])->where("id", "[0-9]+");
    $router->get('/stats/project/{id}/monthly', [StatsController::class, "projectMonthly"])->where("id", "[0-9]+");
    $router->get('/stats/task/{id}/daily', [StatsController::class, "taskDaily"])->where("id", "[0-9]+");
    $router->get('/stats/task/{id}/monthly', [StatsController::class, "taskMonthly"])->where("id", "[0-9]+");
    $router->get('/stats/total', [StatsController::class, "total"]);
    
    // USUNIĘCIE KONTA
    $router->delete('removeAccount', [UserController::class, "removeAccount"]);
});

Route::prefix('v1')->middleware(['locale'])->group(function () use($router) {
    // REJESTRACJA
    $router->post("/register", [RegisterController::class, "register"]);
    $router->get("/register/confirm/{token}", [RegisterController::class, "get"]);
    $router->post("/register/confirm/{token}", [RegisterController::class, "confirm"]);
    
    // REJESTRACJA Z ZAPROSZENIA
    $router->get('/invite/{token}', [UserController::class, "inviteGet"]);
    $router->put('/invite/{token}', [UserController::class, "inviteConfirm"]);
    
    // LOGOWANIE / PRZYPOMNIENIE HASŁA
    $router->post("/login", [UserController::class, "login"]);
    $router->post("/get-token", [UserController::class, "getToken"]);
    $router->post("/forgot-password", [UserController::class, "forgotPassword"]);
    $router->get("/reset-password", [UserController::class, "resetPasswordGet"]);
    $router->post("/reset-password", [UserController::class, "resetPassword"]);
    $router->get("/get-email-firm-ids", [UserController::class, "getUserFirmIds"]);
    
    $router->get('/countries', [IndexController::class, "countries"]);
    $router->get('/packages-site', [IndexController::class, "packages"]);
});

