<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WalletController;
use App\Http\Controllers\Admin\BettingProviderController;
use App\Http\Controllers\Admin\PublishResultController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authentication\AuthenticatedSessionController;
use App\Http\Controllers\Authentication\RegisteredUserController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Admin\SubAdminController;
use Illuminate\Support\Facades\Log;

Route::get('/run-daily-game', function () {
    Artisan::call('app:schedule-daily-game');
    return 'Daily game command executed!';
});

Route::get('/create-storage-link', function () {
    Artisan::call('storage:link');
    return 'Storage link created successfully!';
});

Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'createCustomer'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'storeCustomer'])->name('login');
});

Route::get('customer-results', [CustomerController::class, 'results'])->name('customer.results');
Route::get('customer-get-results', [CustomerController::class, 'getTableData'])->name('customer.get-results');
Route::get('play-now/{id}/{time_id?}', [CustomerController::class, 'playGame'])->name('customer.play-now');


Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});


//Route::get('/', function () {
    //return redirect()->route('landing-dashboard');
//});

Route::get('/', [AuthenticatedSessionController::class, 'landingDashboard'])->name('landing-dashboard');

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'verified', 'onlyAdmin'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'onlyAdmin'])->group(function () {
    Route::get('/admin/dashboard', [UserController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/get-users-record', [UserController::class, 'getTableData'])->name('admin.users.get-record');
    //create route for create and edit user
    Route::match(['get', 'post'], '/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::match(['get', 'post'], '/admin/users/edit/{user}', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.user.destroy');
    Route::get('/admin/view-transaction/{user}', [UserController::class, 'viewTransaction'])->name('admin.view-transaction');
    Route::get('/admin/get-users-transaction/{user}', [UserController::class, 'getTransactionData'])->name('admin.users.get-transaction');

    Route::get('/admin/subadmin', [SubAdminController::class, 'index'])
        ->name('admin.subadmin.index');

    Route::get('/admin/subadmin/create', [SubAdminController::class, 'create'])
        ->name('admin.subadmin.create');

    Route::post('/admin/subadmin/store', [SubAdminController::class, 'store'])
        ->name('admin.subadmin.store');

    Route::get('/admin/subadmin/{id}/edit', [SubAdminController::class, 'edit'])
        ->name('admin.subadmin.edit');

    Route::post('/admin/subadmin/{id}/update', [SubAdminController::class, 'update'])
        ->name('admin.subadmin.update');

    Route::delete('/admin/subadmin/{id}', [SubAdminController::class, 'destroy'])
        ->name('admin.subadmin.destroy');



    Route::get('/admin/wallet', [WalletController::class, 'index'])->name('admin.wallet.index');
    Route::get('/admin/get-wallet-record', [WalletController::class, 'getTableData'])->name('admin.wallet.get-record');
    Route::match(['get', 'post'], '/admin/wallet/add', [WalletController::class, 'addAmount'])->name('admin.wallet.add');
    Route::match(['get', 'post'], '/admin/wallet/deduct', [WalletController::class, 'deductAmount'])->name('admin.wallet.deduct');
    Route::get('/admin/wallet/{user}', [WalletController::class, 'viewTransactionLogs'])->name('admin.wallet.view-logs');

    //providers

    Route::get('/admin/provider', [BettingProviderController::class, 'index'])->name('admin.provider.index');
    Route::get('/admin/get-provider-record', [BettingProviderController::class, 'getTableData'])->name('admin.provider.get-record');
    Route::match(['get', 'post'], '/admin/provider/add', [BettingProviderController::class, 'addProvider'])->name('admin.provider.add');
    Route::match(['get', 'post'], '/admin/provider/edit/{provider_id}', [BettingProviderController::class, 'editProvider'])->name('admin.provider.edit');
    Route::delete('/admin/provider/delete/{provider_id}', [BettingProviderController::class, 'deleteProvider'])->name('admin.provider.delete');

    //results
    Route::get('/admin/publish-results', [PublishResultController::class, 'publishResult'])->name('admin.publish-results');
    Route::get('/admin/get-publish-result', [PublishResultController::class, 'getTableData'])->name('admin.publish-result.get-record');
    Route::get('/admin/update-result/{provider_id}', [PublishResultController::class, 'updateResult'])->name('admin.update-result');
    Route::post('/admin/update-result/{provider_id}', [PublishResultController::class, 'updateResult'])->name('admin.update-result');


    Route::get('/admin/view-all-results', [PublishResultController::class, 'viewAllResults'])->name('admin.view-all-results');
    Route::get('/admin/view-all-results-data', [PublishResultController::class, 'viewAllResultsData'])->name('admin.view-all-results-data');

    Route::get('/admin/view-all-orders', [PublishResultController::class, 'viewAllOrders'])->name('admin.view-all-orders');
    Route::get('/admin/view-current-day-orders', [PublishResultController::class, 'viewAllResultsData'])->name('admin.view-all-results-data');


    //slider routes
    Route::get('/admin/sliders', [\App\Http\Controllers\Admin\SliderController::class, 'index'])->name('admin.sliders.index');
    Route::get('/admin/sliders/create', [\App\Http\Controllers\Admin\SliderController::class, 'create'])->name('admin.sliders.create');
    Route::post('/admin/sliders', [\App\Http\Controllers\Admin\SliderController::class, 'store'])->name('admin.sliders.store');
    Route::get('/admin/sliders/{slider}/edit', [\App\Http\Controllers\Admin\SliderController::class, 'edit'])->name('admin.sliders.edit');
    Route::put('/admin/sliders/{slider}', [\App\Http\Controllers\Admin\SliderController::class, 'update'])->name('admin.sliders.update');
    Route::delete('/admin/sliders/{slider}', [\App\Http\Controllers\Admin\SliderController::class, 'destroy'])->name('admin.sliders.destroy');

    // Close Time routes
    Route::get('/admin/close-time', [\App\Http\Controllers\Admin\CloseTimeController::class, 'edit'])->name('admin.close-time.edit');
    Route::put('/admin/close-time', [\App\Http\Controllers\Admin\CloseTimeController::class, 'update'])->name('admin.close-time.update');

    Route::get('/admin/today-customer-orders', [\App\Http\Controllers\Admin\CustomerOrderListController::class, 'index'])->name('admin.today-customer-orders');
    Route::get('/admin/today-customer-orders/data', [\App\Http\Controllers\Admin\CustomerOrderListController::class, 'data'])->name('admin.customer-orders.data');
    Route::get('/admin/today-customer-orders/export', [\App\Http\Controllers\Admin\CustomerOrderListController::class, 'exportCsv'])->name('admin.customer-orders.export');
    Route::get('/admin/all-customer-orders', [\App\Http\Controllers\Admin\CustomerOrderAllListController::class, 'index'])->name('admin.all-customer-orders');
    Route::get('/admin/all-customer-orders/data', [\App\Http\Controllers\Admin\CustomerOrderAllListController::class, 'data'])->name('admin.all-customer-orders.data');
    Route::get('/admin/all-customer-orders/export', [\App\Http\Controllers\Admin\CustomerOrderAllListController::class, 'exportCsv'])->name('admin.all-customer-orders.export');


    // Customer Import
    Route::get('/admin/customers/import', [\App\Http\Controllers\Admin\CustomerImportController::class, 'showImportForm'])->name('admin.customers.import');
    Route::post('/admin/customers/import', [\App\Http\Controllers\Admin\CustomerImportController::class, 'import'])->name('admin.customers.import.process');
    Route::post('/admin/customers/import/export-failed', [\App\Http\Controllers\Admin\CustomerImportController::class, 'exportFailed'])->name('admin.customers.import.exportFailed');

    Route::get('/admin/withdraw-requests', [\App\Http\Controllers\Admin\WithdrawAdminController::class, 'index'])->name('admin.withdraw.index');
    Route::post('/admin/withdraw-requests/{id}', [\App\Http\Controllers\Admin\WithdrawAdminController::class, 'update'])->name('admin.withdraw.update');
    Route::get('/admin/slot-digit-stats/{schedule_provider_id?}', [\App\Http\Controllers\Admin\SlotDigitStatsController::class, 'index'])->name('admin.slot-digit-stats');

    Route::delete('/admin/publish-results/{id}', [\App\Http\Controllers\Admin\PublishResultController::class, 'destroy'])->name('admin.publish-results.destroy');
    // Admin: view user's bank details (AJAX)
    Route::get('/admin/users/{id}/bank-details', [\App\Http\Controllers\Admin\UserController::class, 'bankDetails'])->name('admin.users.bank-details');

    Route::get('admin/whatsapplink', [App\Http\Controllers\Admin\WhatsAppController::class, 'index'])->name('admin.whatsapplink');
    Route::post('admin/whatsapplink/save', [App\Http\Controllers\Admin\WhatsAppController::class, 'save'])->name('admin.whatsapplink.save');
});


Route::get('customer-rules', [CustomerController::class, 'rules'])->name('customer.rules');

//user routes
Route::middleware(['auth', 'onlyCustomer'])->group(function () {
    // AJAX endpoint for wallet check before adding to cart
    Route::post('/lottery/cart/check-wallet', [\App\Http\Controllers\CartAjaxController::class, 'checkWallet'])->name('lottery.cart.check-wallet');
    Route::get('customer-dashboard', [CustomerController::class, 'index'])->name('customer.dashboard');
    Route::post('lottery/place-order', [CustomerController::class, 'placeOrder'])->name('lottery.place-order');
    Route::get('payment-history', [CustomerController::class, 'paymentHistory'])->name('payment.history');

    //route for cart view
    Route::get('lottery/view-cart', [CustomerController::class, 'viewCart'])->name('lottery.view.cart');

    Route::get('/lottery/cart', [CustomerController::class, 'getCart'])->name('lottery.get-cart');
    Route::post('/lottery/cart/add', [CustomerController::class, 'addToCart'])->name('lottery.add-to-cart');
    Route::delete('/lottery/cart/{index}', [CustomerController::class, 'removeFromCart'])->name('lottery.remove-from-cart');

    // Route::get('customer-rules', [CustomerController::class, 'rules'])->name('customer.rules');


    Route::get('customer-order-details', [CustomerController::class, 'customerOrderDetails'])->name('customer-order-details');

    Route::get('/withdraw', [\App\Http\Controllers\WithdrawController::class, 'showForm'])->name('customer.withdraw');
    Route::post('/withdraw', [\App\Http\Controllers\WithdrawController::class, 'submitRequest'])->name('customer.withdraw.submit');
    Route::get('/withdraw-history', [\App\Http\Controllers\WithdrawHistoryController::class, 'index'])->name('customer.withdraw.history');

    Route::get('/bank-details/create', [\App\Http\Controllers\BankDetailController::class, 'create'])->name('bank-details.create');
    Route::post('/bank-details', [\App\Http\Controllers\BankDetailController::class, 'store'])->name('bank-details.store');
});

//require __DIR__.'/auth.php';