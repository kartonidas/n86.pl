<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BalanceController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerInvoicesController;
use App\Http\Controllers\DictionaryController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\FaultController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\MyNotificationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleRegisterController;
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
    
    $router->get('/get-firm-id', [UserController::class, "getFirmId"]);
    $router->get('/get-id', [UserController::class, "getId"]);
    
    // ZAMÓWIENIA / PŁATNOŚCI
    $router->get('/payment/status/{md5}', [PaymentController::class, "status"]);
    $router->post('/order/create', [OrderController::class, "create"]);
    $router->post('/order/prolong', [OrderController::class, "prolong"]);
    $router->post('/order/extend', [OrderController::class, "extend"]);
    
    $router->get('/invoices', [OrderController::class, "invoices"]);
    $router->get('/invoice/{id}', [OrderController::class, "invoice"])->where("id", "[0-9]+");
    $router->get('/validate-invoicing-data', [OrderController::class, "validateInvoicingData"]);
    
    $router->get('/firm-data', [UserController::class, "getFirmData"]);
    $router->post('/firm-data', [UserController::class, "firmDataUpdate"]);
    $router->get('/invoice-data', [UserController::class, "getInvoiceData"]);
    $router->post('/invoice-data', [UserController::class, "invoiceDataUpdate"]);
    
    // KONFIGURACJA
    $router->get('/config', [ConfigController::class, "get"]);
    $router->put('/config', [ConfigController::class, "update"]);
    
    // USUNIĘCIE KONTA
    $router->delete('removeAccount', [UserController::class, "removeAccount"]);
});

Route::prefix('v1')->middleware(['auth:sanctum', 'locale', 'package', 'limit'])->group(function () use($router) {
    $router->put('/item', [ItemController::class, "create"]);
});

Route::prefix('v1')->middleware(['auth:sanctum', 'locale', 'package'])->group(function () use($router) {
    // NIERUCHOMOŚCI
    $router->get('/items', [ItemController::class, "list"]);
    $router->get('/items/settings', [ItemController::class, "settings"]);
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
    
    $router->post('/item/{id}/archive', [ItemController::class, "archive"])->where("id", "[0-9]+");
    $router->post('/item/{id}/lock', [ItemController::class, "lock"])->where("id", "[0-9]+");
    $router->post('/item/{id}/unlock', [ItemController::class, "unlock"])->where("id", "[0-9]+");
    
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
    $router->put('/rental/{id}', [RentalController::class, "update"])->where("id", "[0-9]+");
    $router->post('/rental/validate', [RentalController::class, "validateData"]);
    $router->get('/rental/item/{id}/getDates', [RentalController::class, "getDates"])->where("id", "[0-9]+");
    $router->put('/rental/rent', [RentalController::class, "rent"]);
    $router->delete('/rental/{id}', [RentalController::class, "delete"])->where("id", "[0-9]+");
    $router->post('/rental/{id}/terminate', [RentalController::class, "termination"])->where("id", "[0-9]+");
    $router->get('/rental/{id}/bills', [RentalController::class, "bills"])->where("id", "[0-9]+");
    
    $router->put('/rental/{id}/bill', [RentalController::class, "billCreate"])->where("id", "[0-9]+");
    $router->get('/rental/{id}/bill/{bid}', [RentalController::class, "billGet"])->where("id", "[0-9]+")->where("bid", "[0-9]+");
    $router->put('/rental/{id}/bill/{bid}', [RentalController::class, "billUpdate"])->where("id", "[0-9]+")->where("bid", "[0-9]+");
    $router->delete('/rental/{id}/bill/{bid}', [RentalController::class, "billDelete"])->where("id", "[0-9]+")->where("bid", "[0-9]+");
    $router->post('/rental/{id}/bill/{bid}/payment', [RentalController::class, "billPayment"])->where("id", "[0-9]+")->where("bid", "[0-9]+");
    $router->post('/rental/{id}/document', [RentalController::class, "generateTemplateDocument"])->where("id", "[0-9]+");
    $router->put('/rental/{id}/document', [RentalController::class, "addDocument"])->where("id", "[0-9]+");
    $router->get('/rental/{id}/documents', [RentalController::class, "getDocuments"])->where("id", "[0-9]+");
    $router->get('/rental/{id}/document/{did}', [RentalController::class, "getDocument"])->where("id", "[0-9]+");
    $router->put('/rental/{id}/document/{did}', [RentalController::class, "updateDocument"])->where("id", "[0-9]+");
    $router->delete('/rental/{id}/document/{did}', [RentalController::class, "deleteDocument"])->where("id", "[0-9]+")->where("did", "[0-9]+");
    $router->get('/rental/{id}/document/{did}/pdf', [RentalController::class, "getDocumentPdf"])->where("id", "[0-9]+")->where("did", "[0-9]+");
    $router->get('/rental/{id}/payments', [RentalController::class, "payments"])->where("id", "[0-9]+");
    $router->delete('/rental/{id}/payment/{pid}', [RentalController::class, "deletePayment"])->where("id", "[0-9]+")->where("pid", "[0-9]+");
    $router->put('/rental/{id}/deposit', [RentalController::class, "deposit"])->where("id", "[0-9]+");
    
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
    
    // UPRAWNIENIA
    $router->get('/permissions', [PermissionController::class, "list"]);
    $router->put('/permission', [PermissionController::class, "create"]);
    $router->get('/permission/{id}', [PermissionController::class, "get"])->where("id", "[0-9]+");
    $router->put('/permission/{id}', [PermissionController::class, "update"])->where("id", "[0-9]+");
    $router->delete('/permission/{id}', [PermissionController::class, "delete"])->where("id", "[0-9]+");
    $router->put('/permission/{id}/add', [PermissionController::class, "addPermission"])->where("id", "[0-9]+");
    $router->delete('/permission/{id}/del', [PermissionController::class, "removePermission"])->where("id", "[0-9]+");
    $router->get('/permission/modules', [PermissionController::class, "permissionModules"]);
    
    // DOKUMENTY
    $router->get('/documents/templates', [DocumentTemplateController::class, "list"])->where("id", "[0-9]+");
    $router->get('/documents/templates/group', [DocumentTemplateController::class, "listGroupByType"])->where("id", "[0-9]+");
    $router->put('/documents/template', [DocumentTemplateController::class, "create"]);
    $router->get('/documents/template/{id}', [DocumentTemplateController::class, "get"])->where("id", "[0-9]+");
    $router->put('/documents/template/{id}', [DocumentTemplateController::class, "update"])->where("id", "[0-9]+");
    $router->delete('/documents/template/{id}', [DocumentTemplateController::class, "delete"])->where("id", "[0-9]+");
    $router->get('/documents', [DocumentController::class, "list"])->where("id", "[0-9]+");
    $router->get('/document/{id}/pdf', [DocumentController::class, "getDocumentPdf"])->where("id", "[0-9]+");
    $router->get('/document/{id}', [DocumentController::class, "get"])->where("id", "[0-9]+");
    $router->delete('/document/{id}', [DocumentController::class, "delete"])->where("id", "[0-9]+");
    $router->put('/document/{id}', [DocumentController::class, "update"])->where("id", "[0-9]+");
    
    $router->get('/history/{type}/{id}', [HistoryController::class, "list"])->where("id", "[0-9]+");
    
    $router->get('/report/chart/item/{id}', [ReportController::class, "chartItemData"])->where("id", "[0-9]+");
    $router->get('/report/chart/rental/{id}', [ReportController::class, "chartRentalData"])->where("id", "[0-9]+");
    
    // STATYSTYKI
    $router->get('/stats/user/{id}/daily', [StatsController::class, "userDaily"])->where("id", "[0-9]+");
    $router->get('/stats/user/{id}/monthly', [StatsController::class, "userMonthly"])->where("id", "[0-9]+");
    $router->get('/stats/project/{id}/daily', [StatsController::class, "projectDaily"])->where("id", "[0-9]+");
    $router->get('/stats/project/{id}/monthly', [StatsController::class, "projectMonthly"])->where("id", "[0-9]+");
    $router->get('/stats/task/{id}/daily', [StatsController::class, "taskDaily"])->where("id", "[0-9]+");
    $router->get('/stats/task/{id}/monthly', [StatsController::class, "taskMonthly"])->where("id", "[0-9]+");
    $router->get('/stats/total', [StatsController::class, "total"]);
    
    
    // REJESTR SPRZEDAŻY
    $router->get('/sale-register', [SaleRegisterController::class, "list"]);
    $router->put('/sale-register', [SaleRegisterController::class, "create"]);
    $router->get('/sale-register/{id}', [SaleRegisterController::class, "get"])->where("id", "[0-9]+");
    $router->put('/sale-register/{id}', [SaleRegisterController::class, "update"])->where("id", "[0-9]+");
    $router->delete('/sale-register/{id}', [SaleRegisterController::class, "delete"])->where("id", "[0-9]+");
    
    // FAKTURY
    $router->get('/customer-invoices', [CustomerInvoicesController::class, "list"]);
    $router->put('/customer-invoice', [CustomerInvoicesController::class, "create"]);
    $router->get('/customer-invoice/{id}', [CustomerInvoicesController::class, "get"])->where("id", "[0-9]+");
    $router->put('/customer-invoice/{id}', [CustomerInvoicesController::class, "update"])->where("id", "[0-9]+");
    $router->delete('/customer-invoice/{id}', [CustomerInvoicesController::class, "delete"])->where("id", "[0-9]+");
    $router->put('/customer-invoice/from-proforma/{pid}', [CustomerInvoicesController::class, "fromProforma"])->where("pid", "[0-9]+");
    $router->put('/customer-invoice/correction/{invoiceId}', [CustomerInvoicesController::class, "correctionCreate"])->where("invoiceId", "[0-9]+");
    $router->put('/customer-invoice/correction/update/{id}', [CustomerInvoicesController::class, "correctionUpdate"])->where("id", "[0-9]+");
    $router->get('/customer-invoice/{id}/pdf', [CustomerInvoicesController::class, "getPdf"])->where("id", "[0-9]+");
    $router->get('/customer-invoice/invoice-data', [CustomerInvoicesController::class, "customerInvoiceData"]);
    $router->put('/customer-invoice/invoice-data', [CustomerInvoicesController::class, "customerInvoiceDataUpdate"]);
    $router->get('/customer-invoice/number/{srid}', [CustomerInvoicesController::class, "getInvoiceNextNumber"])->where("srid", "[0-9]+");
    
    // USTERKI
    $router->get('/faults', [FaultController::class, "list"]);
    $router->put('/fault', [FaultController::class, "create"]);
    $router->get('/fault/{id}', [FaultController::class, "get"])->where("id", "[0-9]+");
    $router->put('/fault/{id}', [FaultController::class, "update"])->where("id", "[0-9]+");
    $router->delete('/fault/{id}', [FaultController::class, "delete"])->where("id", "[0-9]+");
    
    // POWIADOMIENIA
    $router->get('/my-notifications', [MyNotificationController::class, "list"]);
    $router->put('/my-notification', [MyNotificationController::class, "create"]);
    $router->get('/my-notification/{id}', [MyNotificationController::class, "get"])->where("id", "[0-9]+");
    $router->put('/my-notification/{id}', [MyNotificationController::class, "update"])->where("id", "[0-9]+");
    $router->delete('/my-notification/{id}', [MyNotificationController::class, "delete"])->where("id", "[0-9]+");
    
    // RACHUNKI
    $router->get('/bills', [BalanceController::class, "bills"]);
    $router->get('/deposits', [BalanceController::class, "deposits"]);
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

