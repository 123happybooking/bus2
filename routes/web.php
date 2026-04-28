<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Masters\AuthController;
use App\Http\Controllers\Masters\HomeController;
use App\Http\Controllers\Masters\BasicInfoController;
use App\Http\Controllers\Masters\StaffController;
use App\Http\Controllers\Masters\VehicleController;
use App\Http\Controllers\Masters\GuideController;
use App\Http\Controllers\Masters\AgencyController;
use App\Http\Controllers\Masters\CustomerController;
use App\Http\Controllers\Masters\BranchController;
use App\Http\Controllers\Masters\DriverController;
use App\Http\Controllers\Masters\PartnerController;
use App\Http\Controllers\Masters\LoginHistoryController;
use App\Http\Controllers\Masters\ItineraryController;
use App\Http\Controllers\Masters\FacilityController;
use App\Http\Controllers\Masters\LocationController;
use App\Http\Controllers\Masters\PurposeController;
use App\Http\Controllers\Masters\ReservationCategoryController;
use App\Http\Controllers\Masters\AttendanceCategoryController;
use App\Http\Controllers\Masters\RemarkController;
use App\Http\Controllers\Masters\FeeController;
use App\Http\Controllers\Masters\BankController;
use App\Http\Controllers\Masters\VehicleTypeController;
use App\Http\Controllers\Masters\VehicleModelController;
use App\Http\Controllers\Masters\VehicleGradeController;
use App\Http\Controllers\Masters\UserCompanyInfoController;

use App\Http\Controllers\Masters\DailyItineraryController;
use App\Http\Controllers\Masters\GroupInfoController;
use App\Http\Controllers\Masters\GroupInfoDateRemarkController;
use App\Http\Controllers\Masters\BusAssignmentController;
use App\Http\Controllers\Masters\OperationLedgerController;
use App\Http\Controllers\Masters\DriverLedgerController;
use App\Http\Controllers\Masters\DriverAttendanceController;
use App\Http\Controllers\Masters\DailyReportController;
use App\Http\Controllers\Masters\DriverOperationStatusController;
use App\Http\Controllers\Masters\OptionController;

use App\Http\Controllers\Driver\DriverDashboardController;
use App\Http\Controllers\Driver\DriverItineraryController;
use App\Http\Controllers\Driver\DriverProfileController;
use App\Http\Controllers\Driver\DriverDailyReportController;
use App\Http\Controllers\Driver\DriverOperationController;
use App\Http\Controllers\Driver\DriverAuthController;

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;

use App\Http\Controllers\Masters\CurrencyController;
use App\Http\Controllers\Masters\InvoiceController;
use App\Http\Controllers\Masters\PaymentController;
use App\Http\Controllers\Masters\ProductController;
use App\Http\Controllers\Masters\AccountCategoryController;
use App\Http\Controllers\Masters\AccountTaxController;
use App\Http\Controllers\Masters\AccountDepartmentController;
use App\Http\Controllers\Masters\AccountPartnerController;
use App\Http\Controllers\Masters\AccountController;
use App\Http\Controllers\Masters\AccountSubController;
use App\Http\Controllers\Masters\AccountJournalEntryController;
use App\Http\Controllers\Masters\AccountLedgerController;
use App\Http\Controllers\Masters\AccountPlController;
use App\Http\Controllers\Masters\AccountBsController;
use App\Http\Controllers\Masters\AccountPeriodController;
use App\Http\Controllers\Masters\AccountMonthSumController;
use App\Http\Controllers\Masters\AccountSumController;

Route::get('/', function() {
    return redirect('/masters');
})->name('home');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.post');
    
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.direct');
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::resource('users', UserController::class)->names('users');
    });
});

Route::prefix('masters')->name('masters.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    
    Route::middleware(['auth:masters', \App\Http\Middleware\SetUserDatabase::class])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::resource('basicinfo', BasicInfoController::class)->names('basicinfo');
        Route::resource('branches', BranchController::class)->names('branches');
        Route::resource('staffs', StaffController::class)->names('staffs');
        Route::resource('vehicles', VehicleController::class)->names('vehicles');
        Route::resource('drivers', DriverController::class)->names('drivers');
        Route::resource('guides', GuideController::class)->names('guides');
        Route::resource('agencies', AgencyController::class)->names('agencies');
        Route::resource('customers', CustomerController::class)->names('customers');
        Route::resource('partners', PartnerController::class)->names('partners');
        Route::resource('itineraries', ItineraryController::class)->names('itineraries');
        Route::resource('facilities', FacilityController::class)->names('facilities');
        Route::resource('locations', LocationController::class)->names('locations');
        Route::resource('purposes', PurposeController::class)->names('purposes');
        Route::resource('reservation-categories', ReservationCategoryController::class)->names('reservation-categories');
        Route::resource('attendance-categories', AttendanceCategoryController::class)->names('attendance-categories');
        Route::resource('remarks', RemarkController::class)->names('remarks');
        Route::resource('fees', FeeController::class)->names('fees');
        Route::resource('banks', BankController::class)->names('banks');
        Route::resource('vehicle-types', VehicleTypeController::class)->names('vehicle-types');
        Route::resource('vehicle-models', VehicleModelController::class)->names('vehicle-models');
        Route::resource('vehicle-grades', VehicleGradeController::class)->names('vehicle-grades');
        Route::resource('user-company-info', UserCompanyInfoController::class)->names('user-company-info');
        Route::get('login-histories', [LoginHistoryController::class, 'index'])->name('login-histories.index');
        
        
        

        
        Route::get('/group-infos/{id}/files', [GroupInfoController::class, 'getFiles'])->name('group-infos.files');
        Route::post('/group-infos/{id}/upload-file', [GroupInfoController::class, 'uploadFile'])->name('group-infos.upload-file');
        Route::delete('/group-files/{id}', [GroupInfoController::class, 'deleteFile'])->name('group-files.delete');
        Route::get('/group-files/{id}/download', [GroupInfoController::class, 'downloadFile'])->name('group-files.download');
        
        
        
        Route::resource('group-infos', GroupInfoController::class)->names('group-infos');
        Route::post('group-infos/{id}/split-itineraries', [GroupInfoController::class, 'splitItineraries'])->name('group-infos.split-itineraries');
        Route::post('group-infos/batch-destroy', [GroupInfoController::class, 'batchDestroy'])->name('group-infos.batch-destroy');
        Route::get('group-infos/uuid/{uuid}', [GroupInfoController::class, 'getByUuid'])->name('group-infos.by-uuid');
        Route::post('group-infos/{id}/merge-by-id', [GroupInfoController::class, 'mergeItinerariesById'])->name('group-infos.merge-by-id');
        Route::post('group-infos/{id}/update-bus-assignment', [GroupInfoController::class, 'updateBusAssignment'])->name('group-infos.update-bus-assignment');
        Route::put('group-infos/{id}', [GroupInfoController::class, 'update'])->name('group-infos.update');
        Route::post('group-infos/{id}/delete-itinerary', [GroupInfoController::class, 'deleteItinerary'])->name('group-infos.delete-itinerary');
        Route::post('/group-infos/{id}/copy', [GroupInfoController::class, 'copy'])->name('group-infos.copy');
        
        
        Route::get('operation-ledger', [OperationLedgerController::class, 'index'])->name('operation-ledger.index');
        
        Route::get('driver-ledger', [DriverLedgerController::class, 'index'])->name('driver-ledger.index');
        
        Route::prefix('driver-attendance')->name('driver-attendance.')->group(function () {
            Route::get('/edit', [DriverAttendanceController::class, 'edit'])->name('edit');
            Route::post('/', [DriverAttendanceController::class, 'store'])->name('store');
            Route::delete('/{driverId}/{date}', [DriverAttendanceController::class, 'destroy'])->name('destroy');
        });
        
        Route::prefix('group-info-date-remarks')->name('group-info-date-remarks.')->group(function () {
            Route::get('/{date}', [GroupInfoDateRemarkController::class, 'show'])->name('show');
            Route::post('', [GroupInfoDateRemarkController::class, 'store'])->name('store');
            Route::delete('/{date}', [GroupInfoDateRemarkController::class, 'destroy'])->name('destroy');
        });
        
        Route::prefix('daily-itineraries')->name('daily-itineraries.')->group(function () {
            Route::get('/', [DailyItineraryController::class, 'index'])->name('index');
            Route::get('/create', [DailyItineraryController::class, 'create'])->name('create');
            Route::post('/', [DailyItineraryController::class, 'store'])->name('store');
            Route::get('/{id}', [DailyItineraryController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [DailyItineraryController::class, 'edit'])->name('edit');
            Route::put('/{id}', [DailyItineraryController::class, 'update'])->name('update');
            Route::delete('/{id}', [DailyItineraryController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/copy', [DailyItineraryController::class, 'copy'])->name('copy');
            Route::patch('/{id}/status', [DailyItineraryController::class, 'updateStatus'])->name('update-status');
            Route::post('/bulk-update-status', [DailyItineraryController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
            Route::get('/export/csv', [DailyItineraryController::class, 'export'])->name('export');
            Route::get('by-group/{keyUuid}', [DailyItineraryController::class, 'byGroup'])
                 ->name('by-group');
        });
        
        Route::prefix('bus-assignments')->name('bus-assignments.')->group(function () {
            Route::get('/', [BusAssignmentController::class, 'index'])->name('index');
            Route::get('/create', [BusAssignmentController::class, 'create'])->name('create');
            Route::post('/', [BusAssignmentController::class, 'store'])->name('store');
            Route::get('/{id}', [BusAssignmentController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [BusAssignmentController::class, 'edit'])->name('edit');
            Route::put('/{id}', [BusAssignmentController::class, 'update'])->name('update');
            Route::delete('/{id}', [BusAssignmentController::class, 'destroy'])->name('destroy');
        });
        
        Route::prefix('daily-reports')->name('daily-reports.')->group(function () {
            Route::get('/', [DailyReportController::class, 'index'])->name('index');
            Route::get('/{id}/edit', [DailyReportController::class, 'edit'])->name('edit');
            Route::put('/{id}', [DailyReportController::class, 'update'])->name('update');
            Route::get('/{id}/pdf', [DailyReportController::class, 'exportPdf'])->name('export-pdf');
            Route::put('/operation-log/{id}', [DailyReportController::class, 'updateOperationLog'])->name('update-operation-log');
        });
        
        Route::resource('driver-operation-status', DriverOperationStatusController::class)->names('driver-operation-status');
        
        Route::resource('options', OptionController::class)->names('options');
        

        Route::resource('currencies', CurrencyController::class)->names('currencies');
        Route::resource('invoices', InvoiceController::class)->names('invoices');
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'generatePdf'])->name('invoices.pdf');
        Route::post('invoices/{invoice}/toggle-lock', [InvoiceController::class, 'toggleLock'])->name('invoices.toggle-lock');
        Route::post('invoices/bulk-toggle-lock', [InvoiceController::class, 'bulkToggleLock'])->name('invoices.bulk-toggle-lock');
        Route::post('invoices/bulk-pdf', [InvoiceController::class, 'bulkPdf'])->name('invoices.bulk-pdf');
        Route::get('/invoices/{invoice}/pdf-status', [InvoiceController::class, 'checkPdfStatus']);
        Route::post('reconcile/batch', [PaymentController::class, 'storeBatch'])->name('invoices.reconcile.batch.store');
        Route::get('invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoices.duplicate');
        Route::resource('payments', PaymentController::class)->names('payments');
        Route::resource('products', ProductController::class)->names('products');

        Route::resource('account-categories', AccountCategoryController::class)->names('account-categories');//财务类别
        Route::resource('account-taxs', AccountTaxController::class)->names('account-taxs');//财务类别
        Route::resource('account-departments', AccountDepartmentController::class)->names('account-departments');//部门
        Route::resource('account_partners', AccountPartnerController::class)->names('account_partners');//取引先
        Route::resource('accounts', AccountController::class)->names('accounts');//勘定科目
        Route::resource('account-subs', AccountSubController::class)->names('account-subs');//勘定科目
        Route::resource('journal_entries', AccountJournalEntryController::class)->names('journal_entries');
        Route::get('/account/account-subs/{accountId}', [AccountJournalEntryController::class, 'getAccountSubs'])->name('account.account-subs');
        Route::get('/account/journal-entries/{id}', [AccountJournalEntryController::class, 'show'])->name('journal_entries.show');
        Route::get('/account-ledgers/index', [AccountLedgerController::class, 'index'])->name('account-ledgers.index');
        Route::get('/account-ledgers/generate/{id}', [AccountLedgerController::class, 'generate'])->name('account-ledgers.generate');
        Route::get('/account-ledgers/pdf', [AccountLedgerController::class, 'generatePdf'])->name('account-ledgers.pdf');
        Route::get('/account-pl/index', [AccountPlController::class, 'index'])->name('account-pl.index');
        Route::get('/account-bs/index', [AccountBsController::class, 'index'])->name('account-bs.index');
        Route::resource('account-month-sums', AccountMonthSumController::class)->names('account-month-sums');//月次決算

        Route::resource('account-periods', AccountPeriodController::class)->names('account-periods');//周期
        Route::resource('account-sums', AccountSumController::class)->names('account-sums');//月次決算
        
    });
});


Route::prefix('driver')->name('driver.')->middleware(['auth:masters', \App\Http\Middleware\SetUserDatabase::class])->group(function () {
    // Route::get('dashboard', [DriverDashboardController::class, 'index'])->name('dashboard');
    // Route::get('calendar-data', [DriverDashboardController::class, 'getCalendarData'])->name('calendar-data');
    // Route::get('itineraries/{date}', [DriverDashboardController::class, 'getItineraries'])->name('itineraries');
    // Route::get('search', [DriverDashboardController::class, 'search'])->name('search');
    // Route::get('itinerary/{id}', [DriverDashboardController::class, 'showItinerary'])->name('itinerary.show');
    // Route::get('daily-itineraries/{date}', [DriverDashboardController::class, 'dailyItineraries'])->name('daily-itineraries');
    // Route::get('tab-itineraries', [DriverDashboardController::class, 'getTabItineraries'])->name('tab-itineraries');
    // Route::get('files/{id}/download', [DriverDashboardController::class, 'downloadFile'])->name('files.download');
    
    Route::get('dashboard', [DriverDashboardController::class, 'index'])->name('dashboard');
    Route::get('calendar-data', [DriverDashboardController::class, 'getCalendarData'])->name('calendar-data');
    Route::get('tab-itineraries', [DriverDashboardController::class, 'getTabItineraries'])->name('tab-itineraries');
    
    Route::get('itineraries/{date}', [DriverItineraryController::class, 'getItineraries'])->name('itineraries');
    Route::get('search', [DriverItineraryController::class, 'search'])->name('search');
    Route::get('itinerary/{id}', [DriverItineraryController::class, 'showItinerary'])->name('itinerary.show');
    Route::get('daily-itineraries/{date}', [DriverItineraryController::class, 'dailyItineraries'])->name('daily-itineraries');
    Route::get('files/{id}/download', [DriverItineraryController::class, 'downloadFile'])->name('files.download');
    
    Route::post('logout', [DriverAuthController::class, 'logout'])->name('logout');
    Route::get('settings', [DriverAuthController::class, 'settings'])->name('settings');
    
    Route::get('password', [DriverProfileController::class, 'showChangePasswordForm'])->name('password');
    Route::post('password', [DriverProfileController::class, 'updatePassword'])->name('update-password');
    Route::get('profile', [DriverProfileController::class, 'editProfile'])->name('profile');
    Route::post('profile', [DriverProfileController::class, 'updateProfile'])->name('update-profile');
    
    Route::get('daily-reports/{date}/{vehicleId?}', [DriverDailyReportController::class, 'index'])->name('daily-reports');
    Route::post('daily-reports/create', [DriverDailyReportController::class, 'create'])->name('daily-reports.create');
    Route::put('daily-reports/{id}', [DriverDailyReportController::class, 'update'])->name('daily-reports.update');
    
    Route::get('operation/run/{id}', [DriverOperationController::class, 'runOperation'])->name('operation.run');
    Route::post('operation/log/{id}', [DriverOperationController::class, 'logAction'])->name('operation.log');
    Route::get('operation/logs/{id}', [DriverOperationController::class, 'getLogs'])->name('operation.logs');
    Route::put('operation/log/{id}', [DriverOperationController::class, 'updateLog'])->name('operation.log.update');
    Route::delete('operation/log/{id}', [DriverOperationController::class, 'deleteLog'])->name('operation.log.delete');
});


Route::get('/login', function() {
    return redirect()->route('masters.login');
})->name('login');