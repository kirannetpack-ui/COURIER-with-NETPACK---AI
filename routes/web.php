<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\CODSettlementController;
// =============================================
// AUTH CONTROLLERS
// =============================================
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DeliveryZoneController;
use App\Http\Controllers\Admin\DomesticPickupController as AdminDomesticPickupController;
// =============================================
// MAIN CONTROLLERS
// =============================================
use App\Http\Controllers\Admin\DomesticRateController;
use App\Http\Controllers\Admin\DomesticPartnerAssignmentController;
use App\Http\Controllers\Admin\DomesticShipmentController;
use App\Http\Controllers\Admin\LogisticsServiceController;
use App\Http\Controllers\Admin\OverseasPartnerController;
use App\Http\Controllers\Admin\PartnerChargeController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\PickupController as AdminPickupController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RateSheetController;
use App\Http\Controllers\Admin\InternationalRateController;
use App\Http\Controllers\RateInquiryController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RiderMonitoringController;
use App\Http\Controllers\Admin\ShipmentController as AdminShipmentController;
// =============================================
// ADMIN CONTROLLERS
// =============================================
use App\Http\Controllers\Api\CustomerPaymentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Domestic\AdminController as DomesticAdminController;
use App\Http\Controllers\Domestic\EcommerceController as DomesticEcommerceController;
use App\Http\Controllers\Domestic\ManifestController as DomesticManifestController;
use App\Http\Controllers\Domestic\PickupController as DomesticPickupRequestController;
use App\Http\Controllers\Ecommerce\AdminController as EcommerceAdminController;
use App\Http\Controllers\Ecommerce\EcommerceSellerController;
use App\Http\Controllers\Ecommerce\OrderController as EcommerceOrderController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\GroceryBoxController;
use App\Http\Controllers\HAWBController;
use App\Http\Controllers\HsCodeController;
use App\Http\Controllers\ShippingDocumentController;
// =============================================
// SELLER CONTROLLERS
// =============================================
use App\Http\Controllers\HomeController;
use App\Http\Controllers\International\AdminController as InternationalAdminController;
use App\Http\Controllers\International\HubController as InternationalHubController;
use App\Http\Controllers\International\AgencyController as InternationalAgencyController;
use App\Http\Controllers\International\LastMileCarrierController;
use App\Http\Controllers\International\MAWBController;
use App\Http\Controllers\International\InternationalManifestController;
use App\Http\Controllers\Agency\AgencyShipmentController;
use App\Http\Controllers\International\RateUploadController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Overseas\DashboardController as OverseasDashboardController;
use App\Http\Controllers\Overseas\HubController;
use App\Http\Controllers\Overseas\PartnerController as OverseasPartnerListController;
use App\Http\Controllers\Overseas\ScanController as OverseasScanController;
use App\Http\Controllers\Overseas\ShipmentController as OverseasShipmentController;
// =============================================
// CLIENT CONTROLLERS
// =============================================
use App\Http\Controllers\Overseas\StaffDashboardController;
// =============================================
// RIDER CONTROLLERS
// =============================================
use App\Http\Controllers\Overseas\StaffScanController;
use App\Http\Controllers\Overseas\TransitPointController;
use App\Http\Controllers\Partner\DashboardController as PartnerDashboardController;
use App\Http\Controllers\Partner\DeliveryController as PartnerDeliveryController;
use App\Http\Controllers\Partner\RateController as PartnerRateController;
use App\Http\Controllers\Partner\DomesticRateSubmissionController;
use App\Http\Controllers\Partner\ShipmentLegController;
use App\Http\Controllers\Partner\ScanController as PartnerScanController;
use App\Http\Controllers\Partner\StaffController as PartnerStaffController;
use App\Http\Controllers\Partner\ZoneController as PartnerZoneController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Rider\CODSettlementController as RiderCODSettlementController;
// =============================================
// DOMESTIC CONTROLLERS
// =============================================
use App\Http\Controllers\Rider\DashboardController as RiderDashboardController;
use App\Http\Controllers\Rider\DeliveryController;
use App\Http\Controllers\Rider\DepositController as RiderDepositController;
use App\Http\Controllers\Rider\EarningsController as RiderEarningsController;
// =============================================
// E-COMMERCE CONTROLLERS
// =============================================
use App\Http\Controllers\Rider\HistoryController;
use App\Http\Controllers\Rider\LocationController;
use App\Http\Controllers\Rider\OrderController as RiderOrderController;
// =============================================
// PARTNER CONTROLLERS
// =============================================
use App\Http\Controllers\Rider\PaymentMethodController as RiderPaymentMethodController;
use App\Http\Controllers\Rider\SettingsController as RiderSettingsController;
use App\Http\Controllers\Rider\WalletController as RiderWalletController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Seller\EarningsController as SellerEarningsController;
use App\Http\Controllers\Seller\OrderController as SellerOrderController;
// =============================================
// OVERSEAS CONTROLLERS
// =============================================
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Seller\SellerShipmentController;
use App\Http\Controllers\Seller\SettingsController as SellerSettingsController;
use App\Http\Controllers\Seller\SupportController as SellerSupportController;
use App\Http\Controllers\Seller\WalletController as SellerWalletController;
use App\Http\Controllers\Seller\WithdrawController as SellerWithdrawController;
use App\Http\Controllers\Domestic\EcommerceRiderController;
use App\Http\Controllers\Rider\RiderDeliveryDeskController;
use App\Http\Controllers\Rider\ServiceAreaController as RiderServiceAreaController;
use App\Http\Controllers\Seller\EcommerceBookingController;
use App\Http\Controllers\Seller\RiderRatingController as SellerRiderRatingController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\DomesticQuoteController;
use App\Http\Controllers\TrackingController;
// =============================================
// INTERNATIONAL CONTROLLERS
// =============================================
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
*/

// =============================================
// PUBLIC ROUTES
// =============================================
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/test', function () {
    return 'Laravel is working!';
});

// =============================================
// REGISTRATION ROUTES
// =============================================
Route::get('/register', [RegistrationController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegistrationController::class, 'register'])->name('register.submit');
Route::get('/registration-pending', [RegistrationController::class, 'showPendingPage'])->name('registration.pending');
Route::get('/registration-status/{id}', [RegistrationController::class, 'showApprovalStatus'])->name('registration.status');

// =============================================
// AUTH ROUTES
// =============================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/verification-pending', function () {
    return view('auth.verification-pending');
})->name('verification.pending');

// =============================================
// TRACKING ROUTES (Public)
// =============================================
Route::get('/track', function () {
    return view('tracking.lookup');
})->name('tracking.page');

Route::get('/tracking', function () {
    return redirect()->route('tracking.page');
});

Route::get('/track/search', function (Request $request) {
    $trackingNumber = $request->get('tracking');
    if ($trackingNumber) {
        return redirect()->route('tracking.show', $trackingNumber);
    }

    return redirect()->route('tracking.page');
})->name('tracking.search');

Route::get('/track/{trackingNumber}', [TrackingController::class, 'show'])->name('tracking.show');
Route::get('/tracking/{tracking_number}', [TrackingController::class, 'show'])->name('tracking.public');
Route::get('/tracking/{tracking_number}/hawb', [HAWBController::class, 'publicPrint'])->name('tracking.hawb.print');
Route::get('/tracking/{tracking_number}/hawb/print', [HAWBController::class, 'publicPrintPopup'])->name('tracking.hawb.popup');
Route::get('/tracking/{tracking_number}/hawb/download', [HAWBController::class, 'publicDownload'])->name('tracking.hawb.download');
Route::post('/track/subscribe', [TrackingController::class, 'subscribe'])->name('tracking.subscribe');
Route::post('/tracking/sync-carrier/{shipment}', [TrackingController::class, 'syncCarrier'])->name('tracking.sync-carrier');

// Universal Shipment Issue & Situation Reporting
Route::post('/shipments/{trackingNumberOrId}/issues', [\App\Http\Controllers\ShipmentIssueController::class, 'store'])->name('shipments.issues.store');
Route::get('/shipments/{trackingNumberOrId}/issues', [\App\Http\Controllers\ShipmentIssueController::class, 'getShipmentIssues'])->name('shipments.issues.list');
Route::post('/tracking/{tracking_number}/report-issue', [\App\Http\Controllers\ShipmentIssueController::class, 'store'])->name('tracking.report-issue');

// =============================================
// RATE INQUIRY & TARIFF CALCULATOR (Public & Clients)
// =============================================
Route::get('/rates/inquiry', [RateInquiryController::class, 'index'])->name('rates.inquiry');
Route::post('/rates/calculate', [RateInquiryController::class, 'calculate'])->name('rates.calculate');

// =============================================
// WCO HS CODES & CUSTOMS TARIFF ROUTES (PUBLIC & CLIENTS)
// =============================================
Route::get('/api/hs-codes/search', [HsCodeController::class, 'search'])->name('api.hs-codes.search');
Route::get('/api/hs-codes/categories', [HsCodeController::class, 'categories'])->name('api.hs-codes.categories');

// =============================================
// AUTH PROTECTED ROUTES
// =============================================
Route::middleware(['auth'])->group(function () {
    Route::post('/domestic/quote', [DomesticQuoteController::class, 'store'])->name('domestic.quote');

    // =============================================
    // PROFILE ROUTES
    // =============================================
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::put('/profile/update-address', [ProfileController::class, 'updateAddress'])->name('profile.update-address');
    Route::put('/profile/update-temporary', [ProfileController::class, 'updateTemporary'])->name('profile.update-temporary');

    // =============================================
    // DASHBOARD ROUTES
    // =============================================
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/seller/dashboard', [SellerDashboardController::class, 'index'])->name('seller.dashboard');
    Route::get('/rider/dashboard', [RiderDashboardController::class, 'index'])->name('rider.dashboard');
    Route::get('/client/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');
    Route::get('/partner/dashboard', [PartnerDashboardController::class, 'index'])->name('partner.dashboard');
    Route::get('/overseas/dashboard', [OverseasDashboardController::class, 'index'])->name('overseas.dashboard');
    Route::get('/customer/dashboard', function () {
        return view('dashboards.customer');
    })->name('customer.dashboard');

    // =============================================
    // GROCERY BOX ROUTES
    // =============================================
    Route::get('/grocery-box', [GroceryBoxController::class, 'index'])->name('grocery.box');

    // =============================================
    // SHIPMENT ROUTES
    // =============================================
    Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->whereNumber('shipment')->name('shipments.show');
    Route::resource('shipments', ShipmentController::class)->except(['create', 'store', 'show']);

    // =============================================
    // SHIPPING DOCUMENTS (COMMERCIAL INVOICE & PACKING LIST)
    // =============================================
    Route::get('/shipments/{id}/commercial-invoice', [ShippingDocumentController::class, 'commercialInvoice'])->name('shipments.invoice');
    Route::get('/shipments/{id}/packing-list', [ShippingDocumentController::class, 'packingList'])->name('shipments.packing-list');
    Route::get('/shipments/{id}/seller-bill', [ShippingDocumentController::class, 'sellerBill'])->name('shipments.seller-bill');

    // =============================================
    // HAWB ROUTES
    // =============================================
    Route::get('/hawb/international/{id}', [HAWBController::class, 'generate'])
        ->defaults('type', 'international')->name('hawb.international');
    Route::get('/hawb/domestic/{id}', [HAWBController::class, 'generate'])
        ->defaults('type', 'domestic')->name('hawb.domestic');
    Route::get('/hawb/print/{id}/{type?}', [HAWBController::class, 'printPopup'])->name('hawb.print');
    Route::get('/hawb/download/{id}/{type?}', [HAWBController::class, 'download'])->name('hawb.download');
    Route::view('/hawb/scanner', 'hawb.scan')->name('hawb.scanner');
    Route::post('/hawb/scan', [HAWBController::class, 'scan'])->name('hawb.scan');
    Route::post('/hawb/update-from-scan', [HAWBController::class, 'updateFromScan'])->name('hawb.update-from-scan');

    // =============================================
    // TRACKING UPDATE ROUTES
    // =============================================
    Route::get('/tracking/live/{shipmentId}', [TrackingController::class, 'getLiveLocation'])->name('tracking.live');
    Route::get('/tracking/orders/{order}/live', [TrackingController::class, 'getOrderLiveLocation'])->name('tracking.orders.live');
    Route::post('/tracking/update-location/{shipmentId}', [TrackingController::class, 'updateLocation'])->name('tracking.update-location');
    Route::post('/tracking/update-status/{shipmentId}', [TrackingController::class, 'updateStatus'])->name('tracking.update-status');
    Route::get('/tracking/update', function () {
        return view('tracking.update');
    })->name('tracking.update');

    // =============================================
    // PAYMENT ROUTES
    // =============================================
    Route::post('/payment/create/{shipment}', [PaymentController::class, 'createPaymentIntent'])->name('payment.create');
    Route::get('/payment/khalti/verify', [CustomerPaymentController::class, 'verifyKhaltiPayment'])->name('payment.khalti.verify');
    Route::get('/payment/esewa/verify', [CustomerPaymentController::class, 'verifyEsewaPayment'])->name('payment.esewa.verify');
    Route::get('/payment/esewa/failure', [CustomerPaymentController::class, 'paymentFailure'])->name('payment.esewa.failure');
    Route::get('/payment/success/{shipmentId}', [CustomerPaymentController::class, 'paymentSuccess'])->name('payment.success');
    Route::get('/payment/failure', [CustomerPaymentController::class, 'paymentFailure'])->name('payment.failure');

    // =============================================
    // NOTIFICATION ROUTES
    // =============================================
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // =============================================
    // CHAT & FEEDBACK ROUTES
    // =============================================
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/messages', [ChatController::class, 'messages'])->name('chat.messages');
    Route::get('/chat/conversations', [ChatController::class, 'conversations'])->name('chat.conversations');
    Route::post('/feedback/submit', [FeedbackController::class, 'submit'])->name('feedback.submit');

    // =============================================
    // E-COMMERCE CALCULATE ROUTE
    // =============================================
    Route::post('/ecommerce/calculate', [EcommerceSellerController::class, 'calculateEarnings'])->name('ecommerce.calculate');

    // =============================================
    // PASSWORD CHANGE
    // =============================================
    Route::get('/password-change', [PasswordChangeController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password-change', [PasswordChangeController::class, 'change'])->name('password.change.submit');

    // =============================================
    // DOMESTIC PICKUP ROUTES (For Customers)
    // =============================================
    Route::prefix('domestic')->name('domestic.')->group(function () {
        Route::get('/pickup/create', [DomesticPickupRequestController::class, 'create'])->name('pickup.create');
        Route::post('/pickup', [DomesticPickupRequestController::class, 'store'])->name('pickup.store');
        Route::get('/pickup/{pickupRequest}', [DomesticPickupRequestController::class, 'show'])->name('pickup.show');
        Route::get('/my-requests', [DomesticPickupRequestController::class, 'myRequests'])->name('pickup.my-requests');
        Route::post('/pickup/{pickupRequest}/cancel', [DomesticPickupRequestController::class, 'cancel'])->name('pickup.cancel');
    });

    // =============================================
    // DOMESTIC MANIFEST ROUTES
    // =============================================
    Route::prefix('domestic/manifests')->name('domestic.manifests.')->group(function () {
        Route::get('/', [DomesticManifestController::class, 'index'])->name('index');
        Route::get('/create', [DomesticManifestController::class, 'create'])->name('create');
        Route::post('/', [DomesticManifestController::class, 'store'])->name('store');
        Route::post('/scan-bag', [DomesticManifestController::class, 'scanBag'])->name('scan-bag');

        // IMPORTANT: POD routes must come BEFORE the {id} route
        Route::get('/pods', [DomesticManifestController::class, 'pods'])->name('pods');
        Route::get('/pods/{id}/file/{type}', [DomesticManifestController::class, 'downloadPodFile'])->name('pods.file');
        Route::get('/pods/{id}', [DomesticManifestController::class, 'showPod'])->name('pods.show');
        Route::put('/pods/{id}/status', [DomesticManifestController::class, 'updatePodStatus'])->name('pods.update-status');

        // 👇 ADD THESE ROUTES 👇
        Route::get('/pods/upload/{shipmentId}', [DomesticManifestController::class, 'showUploadForm'])->name('pods.upload.form');
        Route::post('/pods/upload', [DomesticManifestController::class, 'uploadPOD'])->name('pods.upload');

        // Scan Desk & Bulk Re-Manifest
        Route::get('/scan', [DomesticManifestController::class, 'scanDesk'])->name('scan');
        Route::post('/process-scan', [DomesticManifestController::class, 'processScan'])->name('process-scan');
        Route::post('/bulk-re-manifest', [DomesticManifestController::class, 'bulkRemanifest'])->name('bulk-re-manifest');

        // This {id} route must come AFTER the specific routes above
        Route::get('/{id}', [DomesticManifestController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [DomesticManifestController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DomesticManifestController::class, 'update'])->name('update');
        Route::get('/{manifest}/arrival-notice', [DomesticManifestController::class, 'arrivalNoticeForm'])->name('arrival-notice');
        Route::post('/{manifest}/arrival-notice', [DomesticManifestController::class, 'processArrivalNotice'])->name('process-arrival-notice');
        Route::post('/{manifest}/send-partner-reminder', [DomesticManifestController::class, 'sendPartnerReminder'])->name('send-partner-reminder');
        Route::put('/{manifest}/shipments/{manifestShipment}/status', [DomesticManifestController::class, 'updateShipmentStatus'])->name('shipments.update-status');
        Route::post('/{manifest}/shipments/{manifestShipment}/forward', [DomesticManifestController::class, 'forwardShipment'])->name('shipments.forward');

    });

});

// =============================================
// ADMIN ROUTES (Super Admin & Domestic Admin)
// =============================================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin,admin,domestic_admin'])->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/search-global', [ProductController::class, 'searchGlobal'])->name('products.search-global');

    // Users
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::put('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('/users/{user}/verify', [AdminUserController::class, 'verify'])->name('users.verify');
    Route::post('/users/{user}/approve', [AdminUserController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/reject', [AdminUserController::class, 'reject'])->name('users.reject');
    Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');

    // Partners
    Route::get('/partners', [PartnerController::class, 'index'])->name('partners.index');
    Route::get('/partners/create', [PartnerController::class, 'create'])->name('partners.create');
    Route::post('/partners', [PartnerController::class, 'store'])->name('partners.store');
    Route::get('/partners/{partner}', [PartnerController::class, 'show'])->name('partners.show');
    Route::get('/partners/{partner}/edit', [PartnerController::class, 'edit'])->name('partners.edit');
    Route::put('/partners/{partner}', [PartnerController::class, 'update'])->name('partners.update');
    Route::delete('/partners/{partner}', [PartnerController::class, 'destroy'])->name('partners.destroy');
    Route::get('/partners/{partner}/verify', [PartnerController::class, 'verify'])->name('partners.verify');
    Route::post('/partners/{partner}/approve', [PartnerController::class, 'approve'])->name('partners.approve');
    Route::post('/partners/{partner}/reject', [PartnerController::class, 'reject'])->name('partners.reject');

    // Overseas Partners
    Route::get('/overseas-partners', [OverseasPartnerController::class, 'index'])->name('overseas-partners.index');
    Route::get('/overseas-partners/create', [OverseasPartnerController::class, 'create'])->name('overseas-partners.create');
    Route::post('/overseas-partners', [OverseasPartnerController::class, 'store'])->name('overseas-partners.store');
    Route::get('/overseas-partners/{overseasPartner}', [OverseasPartnerController::class, 'show'])->name('overseas-partners.show');
    Route::get('/overseas-partners/{overseasPartner}/edit', [OverseasPartnerController::class, 'edit'])->name('overseas-partners.edit');
    Route::put('/overseas-partners/{overseasPartner}', [OverseasPartnerController::class, 'update'])->name('overseas-partners.update');
    Route::delete('/overseas-partners/{overseasPartner}', [OverseasPartnerController::class, 'destroy'])->name('overseas-partners.destroy');
    Route::post('/overseas-partners/{overseasPartner}/reset-password', [OverseasPartnerController::class, 'resetPassword'])->name('overseas-partners.reset-password');

    // Domestic Services (Admin)
    Route::prefix('domestic')->name('domestic.')->group(function () {
        Route::get('/rates', [DomesticRateController::class, 'index'])->name('rates');
        Route::get('/rates/create', [DomesticRateController::class, 'create'])->name('rates.create');
        Route::post('/rates', [DomesticRateController::class, 'store'])->name('rates.store');
        Route::get('/rates/{id}/edit', [DomesticRateController::class, 'edit'])->name('rates.edit');
        Route::put('/rates/{id}', [DomesticRateController::class, 'update'])->name('rates.update');
        Route::post('/rates/{rate}/approve', [DomesticRateController::class, 'approve'])->name('rates.approve');
        Route::post('/rates/{rate}/reject', [DomesticRateController::class, 'reject'])->name('rates.reject');
        Route::delete('/rates/{id}', [DomesticRateController::class, 'destroy'])->name('rates.destroy');

        Route::get('/assignments', [DomesticPartnerAssignmentController::class, 'index'])->name('assignments');
        Route::post('/assignments', [DomesticPartnerAssignmentController::class, 'store'])->name('assignments.store');
        Route::put('/assignments/{assignment}', [DomesticPartnerAssignmentController::class, 'update'])->name('assignments.update');
        Route::delete('/assignments/{assignment}', [DomesticPartnerAssignmentController::class, 'destroy'])->name('assignments.destroy');

        Route::get('/zones', [DeliveryZoneController::class, 'index'])->name('zones');
        Route::get('/zones/create', [DeliveryZoneController::class, 'create'])->name('zones.create');
        Route::post('/zones', [DeliveryZoneController::class, 'store'])->name('zones.store');
        Route::get('/zones/{id}/edit', [DeliveryZoneController::class, 'edit'])->name('zones.edit');
        Route::put('/zones/{id}', [DeliveryZoneController::class, 'update'])->name('zones.update');
        Route::post('/zones/{zone}/approve', [DeliveryZoneController::class, 'approve'])->name('zones.approve');
        Route::post('/zones/{zone}/reject', [DeliveryZoneController::class, 'reject'])->name('zones.reject');
        Route::delete('/zones/{id}', [DeliveryZoneController::class, 'destroy'])->name('zones.destroy');

        Route::get('/shipments', [DomesticShipmentController::class, 'index'])->name('shipments');
        Route::get('/shipments/{id}', [DomesticShipmentController::class, 'show'])->name('shipments.show');
        Route::put('/shipments/{id}/status', [DomesticShipmentController::class, 'updateStatus'])->name('shipments.update-status');

        Route::get('/pickups', [AdminDomesticPickupController::class, 'index'])->name('pickups');
        Route::get('/pickups/{id}', [AdminDomesticPickupController::class, 'show'])->name('pickups.show');
    });

    // Rate Management (International)
    Route::prefix('rates')->name('rates.')->group(function () {
        Route::get('/', [RateSheetController::class, 'index'])->name('index');
        Route::get('/create', [RateSheetController::class, 'create'])->name('create');
        Route::post('/', [RateSheetController::class, 'store'])->name('store');
        Route::get('/surcharges', [RateSheetController::class, 'surcharges'])->name('surcharges');
        Route::post('/surcharges', [RateSheetController::class, 'uploadRemoteSurcharges'])->name('upload-surcharges');
        Route::get('/charges', [RateSheetController::class, 'charges'])->name('charges');
        Route::post('/charges', [RateSheetController::class, 'storeCharge'])->name('store-charge');
        Route::delete('/{id}', [RateSheetController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle', [RateSheetController::class, 'toggle'])->name('toggle');
    });

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/shipments', [ReportController::class, 'shipments'])->name('reports.shipments');
    Route::get('/reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
    Route::get('/reports/partners', [ReportController::class, 'partners'])->name('reports.partners');
    Route::get('/reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');

    // Partner Charges
    Route::get('/partner-charges', [PartnerChargeController::class, 'index'])->name('partner-charges.index');
    Route::get('/partner-charges/{id}', [PartnerChargeController::class, 'show'])->name('partner-charges.show');
    Route::post('/partner-charges', [PartnerChargeController::class, 'store'])->name('partner-charges.store');
    Route::put('/partner-charges/{id}/status', [PartnerChargeController::class, 'updateStatus'])->name('partner-charges.update-status');
    Route::get('/partner-charges/export', [PartnerChargeController::class, 'export'])->name('partner-charges.export');

    // Rate Change Notifications

    // Rider Monitoring
    Route::get('/riders/dashboard', [RiderMonitoringController::class, 'dashboard'])->name('riders.dashboard');
    Route::get('/riders/locations', [RiderMonitoringController::class, 'getRiderLocations'])->name('riders.locations');
    Route::get('/riders/{id}/details', [RiderMonitoringController::class, 'riderDetails'])->name('riders.details');
    Route::get('/riders/track-delivery/{id}', [RiderMonitoringController::class, 'trackDelivery'])->name('riders.track-delivery');
    Route::get('/riders/export', [RiderMonitoringController::class, 'exportReport'])->name('riders.export');

    // COD Settlements (Admin)
    Route::get('/cod-settlements', [CODSettlementController::class, 'index'])->name('cod-settlements.index');
    Route::get('/cod-settlements/{id}', [CODSettlementController::class, 'show'])->name('cod-settlements.show');
    Route::put('/cod-settlements/{id}/status', [CODSettlementController::class, 'updateStatus'])->name('cod-settlements.update-status');
    Route::get('/cod-settlements/export', [CODSettlementController::class, 'export'])->name('cod-settlements.export');

    // Pickups & Shipments
    Route::get('/pickups', [AdminPickupController::class, 'index'])->name('pickups');
    Route::get('/shipments', [AdminShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/{id}', [AdminShipmentController::class, 'show'])->whereNumber('id')->name('shipments.show');
    Route::get('/analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/settlements', function () {
        return view('admin.settlements');
    })->name('settlements');
    Route::get('/settings', function () {
        return view('admin.settings');
    })->name('settings');

    // Communications & Delay Management
    Route::get('/communications', [\App\Http\Controllers\Admin\CommunicationController::class, 'index'])->name('communications');
    Route::post('/communications/send-delay', [\App\Http\Controllers\Admin\CommunicationController::class, 'sendDelayAlert'])->name('communications.send-delay');
    Route::post('/communications/resend/{id}', [\App\Http\Controllers\Admin\CommunicationController::class, 'resendReminder'])->name('communications.resend');

    // Dynamic Logistics Services & Transit Time SLA Config
    Route::get('/services', [LogisticsServiceController::class, 'index'])->name('services.index');
    Route::post('/services', [LogisticsServiceController::class, 'store'])->name('services.store');
    Route::put('/services/{id}', [LogisticsServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{id}', [LogisticsServiceController::class, 'destroy'])->name('services.destroy');
    Route::post('/services/{id}/toggle', [LogisticsServiceController::class, 'toggle'])->name('services.toggle');
});

// =============================================
// SELLER ROUTES
// =============================================
Route::prefix('seller')->name('seller.')->middleware(['auth', 'role:seller'])->group(function () {
    Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');

    // Rate Inquiry & Calculator
    Route::get('/rates', fn () => redirect()->route('rates.inquiry'))->name('rates');

    // Products (Removed from Seller Portal - clean redirects)
    Route::get('/products', fn () => redirect()->route('seller.dashboard'))->name('products.index');
    Route::get('/products/create', fn () => redirect()->route('seller.dashboard'))->name('products.create');
    Route::any('/products/{any?}', fn () => redirect()->route('seller.dashboard'))->where('any', '.*');

    // Orders
    Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders');
    Route::get('/orders/create', [EcommerceOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [EcommerceOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [SellerOrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}/status', [SellerOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/cancel', [SellerOrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{order}/invoice', [SellerOrderController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/export', [SellerOrderController::class, 'export'])->name('orders.export');

    // Earnings
    Route::get('/earnings', [SellerEarningsController::class, 'index'])->name('earnings');
    Route::get('/earnings/export', [SellerEarningsController::class, 'export'])->name('earnings.export');

    // Wallet
    Route::get('/wallet', [SellerWalletController::class, 'index'])->name('wallet');
    Route::get('/wallet/balance', [SellerWalletController::class, 'getBalance'])->name('wallet.balance');
    Route::post('/wallet/add-payment-method', [SellerWalletController::class, 'addPaymentMethod'])->name('wallet.add-payment-method');
    Route::delete('/wallet/delete-method/{id}', [SellerWalletController::class, 'deletePaymentMethod'])->name('wallet.delete-method');
    Route::post('/wallet/set-default/{id}', [SellerWalletController::class, 'setDefaultPaymentMethod'])->name('wallet.set-default');
    Route::post('/wallet/request-payout', [SellerWalletController::class, 'requestPayout'])->name('wallet.request-payout');

    // Withdraw
    Route::get('/withdraw', [SellerWithdrawController::class, 'index'])->name('withdraw');
    Route::post('/withdraw', [SellerWithdrawController::class, 'store'])->name('withdraw.store');
    Route::get('/withdraw/history', [SellerWithdrawController::class, 'history'])->name('withdraw.history');
    Route::post('/withdraw/{withdraw}/cancel', [SellerWithdrawController::class, 'cancel'])->name('withdraw.cancel');

    // Shipments
    Route::get('/shipments', [SellerShipmentController::class, 'index'])->name('shipments');
    Route::get('/shipments/create', [SellerShipmentController::class, 'create'])->name('shipments.create');
    Route::post('/shipments', [SellerShipmentController::class, 'store'])->name('shipments.store');
    Route::get('/shipments/{shipment}', [SellerShipmentController::class, 'show'])->name('shipments.show');
    Route::put('/shipments/{shipment}/tracking', [SellerShipmentController::class, 'updateTracking'])->name('shipments.update-tracking');
    Route::get('/shipments/{shipment}/label', [SellerShipmentController::class, 'printLabel'])->name('shipments.label');

    // Support
    Route::get('/support', [SellerSupportController::class, 'index'])->name('support');
    Route::get('/support/create', [SellerSupportController::class, 'create'])->name('support.create');
    Route::post('/support', [SellerSupportController::class, 'store'])->name('support.store');
    Route::get('/support/{support}', [SellerSupportController::class, 'show'])->name('support.show');
    Route::post('/support/{support}/reply', [SellerSupportController::class, 'reply'])->name('support.reply');
    Route::post('/support/{support}/close', [SellerSupportController::class, 'close'])->name('support.close');

    // Settings
    Route::get('/settings', [SellerSettingsController::class, 'index'])->name('settings');
    Route::put('/settings/profile', [SellerSettingsController::class, 'updateProfile'])->name('settings.update-profile');
    Route::put('/settings/password', [SellerSettingsController::class, 'updatePassword'])->name('settings.update-password');
    Route::put('/settings/bank', [SellerSettingsController::class, 'updateBank'])->name('settings.update-bank');
    Route::put('/settings/notifications', [SellerSettingsController::class, 'updateNotifications'])->name('settings.update-notifications');

    // E-Commerce Deliveries (Direct Rider Same-Day & Multi-Leg Courier)
    Route::get('/ecommerce', [EcommerceBookingController::class, 'index'])->name('ecommerce.index');
    Route::get('/ecommerce/direct', [EcommerceBookingController::class, 'createDirect'])->name('ecommerce.direct');
    Route::post('/ecommerce/direct', [EcommerceBookingController::class, 'storeDirect'])->name('ecommerce.direct.store');
    Route::get('/ecommerce/multileg', [EcommerceBookingController::class, 'createMultiLeg'])->name('ecommerce.multileg');
    Route::post('/ecommerce/multileg', [EcommerceBookingController::class, 'storeMultiLeg'])->name('ecommerce.multileg.store');
    Route::get('/ecommerce/{id}', [EcommerceBookingController::class, 'show'])->name('ecommerce.show');
    Route::post('/ecommerce/{id}/rate', [SellerRiderRatingController::class, 'store'])->name('ecommerce.rate');
});

// =============================================
// CLIENT ROUTES
// =============================================
Route::prefix('client')->name('client.')->middleware(['auth', 'role:client,customer'])->group(function () {
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/inquiries', [ClientDashboardController::class, 'inquiries'])->name('inquiries');
    Route::post('/inquiries', [ClientDashboardController::class, 'storeInquiry'])->name('inquiries.store');
    Route::get('/history', [ClientDashboardController::class, 'history'])->name('history');
    Route::get('/rates', function () {
        return redirect()->route('rates.inquiry');
    })->name('rates');
    
    // Legacy client redirects
    Route::get('/wallet', fn () => redirect()->route('client.dashboard'))->name('wallet');
    Route::get('/feedback', fn () => redirect()->route('client.dashboard'))->name('feedback');
    Route::get('/support', fn () => redirect()->route('client.dashboard'))->name('support');
    Route::get('/settings', fn () => redirect()->route('profile'))->name('settings');
});

// =============================================
// RIDER ROUTES (Uber-style)
// =============================================
Route::prefix('rider')->name('rider.')->middleware(['auth', 'role:rider'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [RiderDashboardController::class, 'index'])->name('dashboard');
    Route::post('/toggle-status', [RiderDashboardController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/update-location', [RiderDashboardController::class, 'updateLocation'])->name('update-location');
    Route::get('/stats', [RiderDashboardController::class, 'getStats'])->name('stats');

    // Deliveries
    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show'])->name('deliveries.show');
    Route::post('/deliveries/{delivery}/update-status', [DeliveryController::class, 'updateStatus'])->name('deliveries.update-status');
    Route::get('/deliveries/active', [DeliveryController::class, 'activeDeliveries'])->name('deliveries.active');
    Route::get('/deliveries/history', [DeliveryController::class, 'history'])->name('deliveries.history');

    // Orders (E-commerce - Uber-style)
    Route::get('/orders/available', [RiderOrderController::class, 'availableOrders'])->name('orders.available');
    Route::get('/orders/my', [RiderOrderController::class, 'myOrders'])->name('orders.my');
    Route::patch('/orders/accept/{id}', [RiderOrderController::class, 'accept'])->name('orders.accept');
    Route::patch('/orders/reject/{id}', [RiderOrderController::class, 'reject'])->name('orders.reject');
    Route::patch('/orders/pickup/{id}', [RiderOrderController::class, 'markPickedUp'])->name('orders.pickup');
    Route::patch('/orders/in-transit/{id}', [RiderOrderController::class, 'markInTransit'])->name('orders.in-transit');
    Route::patch('/orders/out-for-delivery/{id}', [RiderOrderController::class, 'markOutForDelivery'])->name('orders.out-for-delivery');
    Route::post('/orders/deliver/{id}', [RiderOrderController::class, 'markDelivered'])->name('orders.deliver');
    Route::get('/orders/track/{trackingNumber}', [RiderOrderController::class, 'trackOrder'])->name('orders.track');
    Route::get('/orders/tracking/{id}', [RiderOrderController::class, 'getTracking'])->name('orders.tracking');
    Route::post('/orders/update-location', [RiderOrderController::class, 'updateLocation'])->name('orders.update-location');

    // COD Settlement (Rider)
    Route::get('/cod/can-accept/{orderId}', [RiderCODSettlementController::class, 'canAcceptCOD'])->name('cod.can-accept');
    Route::post('/cod/settle/{orderId}', [RiderCODSettlementController::class, 'settleCOD'])->name('cod.settle');
    Route::get('/cod/deposit-balance', [RiderCODSettlementController::class, 'getDepositBalance'])->name('cod.deposit-balance');
    Route::get('/cod/deposit-history', [RiderCODSettlementController::class, 'depositHistory'])->name('cod.deposit-history');
    Route::get('/cod/settlement-summary', [RiderCODSettlementController::class, 'settlementSummary'])->name('cod.settlement-summary');

    // Earnings
    Route::get('/earnings', [RiderEarningsController::class, 'index'])->name('earnings');

    // History
    Route::get('/history', [HistoryController::class, 'index'])->name('history');

    // Wallet
    Route::get('/wallet', [RiderWalletController::class, 'index'])->name('wallet');
    Route::get('/wallet/balance', [RiderWalletController::class, 'getBalance'])->name('wallet.balance');
    Route::get('/wallet/transactions', [RiderWalletController::class, 'getTransactions'])->name('wallet.transactions');
    Route::get('/wallet/deposit-history', [RiderWalletController::class, 'getDepositHistory'])->name('wallet.deposit-history');

    // Payment Methods
    Route::get('/payment-methods', [RiderPaymentMethodController::class, 'index'])->name('payment-methods');
    Route::get('/payment-methods/add', [RiderPaymentMethodController::class, 'create'])->name('payment-methods.add');
    Route::post('/payment-methods', [RiderPaymentMethodController::class, 'store'])->name('payment-methods.store');
    Route::delete('/payment-methods/{id}', [RiderPaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');
    Route::post('/payment-methods/{id}/default', [RiderPaymentMethodController::class, 'setDefault'])->name('payment-methods.default');

    // Deposit
    Route::get('/deposit', [RiderDepositController::class, 'showDepositForm'])->name('deposit');
    Route::post('/deposit', [RiderDepositController::class, 'deposit'])->name('deposit.process');

    // Settings
    Route::get('/settings', [RiderSettingsController::class, 'index'])->name('settings');
    Route::put('/settings/update-profile', [RiderSettingsController::class, 'updateProfile'])->name('update-profile');
    Route::put('/settings/update-password', [RiderSettingsController::class, 'updatePassword'])->name('update-password');
    Route::put('/settings/update-availability', [RiderSettingsController::class, 'updateAvailability'])->name('update-availability');
    Route::put('/settings/update-bank', [RiderSettingsController::class, 'updateBank'])->name('update-bank');

    // Location
    Route::post('/update-location', [LocationController::class, 'update'])->name('update-location');

    // Live Delivery Desk & Available Jobs
    Route::get('/delivery/available', [RiderDeliveryDeskController::class, 'availableJobs'])->name('delivery.available');
    Route::post('/delivery/accept/{id}', [RiderDeliveryDeskController::class, 'acceptJob'])->name('delivery.accept');
    Route::get('/delivery/my', [RiderDeliveryDeskController::class, 'myDeliveries'])->name('delivery.my');
    Route::get('/delivery/show/{id}', [RiderDeliveryDeskController::class, 'show'])->name('delivery.show');
    Route::post('/delivery/{id}/arrive', [RiderDeliveryDeskController::class, 'arrivePickup'])->name('delivery.arrive');
    Route::post('/delivery/{id}/verify-pickup', [RiderDeliveryDeskController::class, 'verifyPickup'])->name('delivery.verify-pickup');
    Route::post('/delivery/{id}/complete', [RiderDeliveryDeskController::class, 'completeDelivery'])->name('delivery.complete');
    Route::post('/delivery/{id}/fail', [RiderDeliveryDeskController::class, 'failDelivery'])->name('delivery.fail');
    Route::get('/cod/desk', [RiderDeliveryDeskController::class, 'codLedger'])->name('delivery.cod');
    Route::post('/cod/deposit', [RiderDeliveryDeskController::class, 'depositCod'])->name('delivery.deposit');

    // Service Areas & Territorial Coverage
    Route::get('/service-areas', [RiderServiceAreaController::class, 'index'])->name('service-areas.index');
    Route::post('/service-areas', [RiderServiceAreaController::class, 'store'])->name('service-areas.store');
    Route::delete('/service-areas/{id}', [RiderServiceAreaController::class, 'destroy'])->name('service-areas.destroy');
    Route::post('/service-areas/{id}/toggle', [RiderServiceAreaController::class, 'toggle'])->name('service-areas.toggle');
});

// =============================================
// DOMESTIC ADMIN - E-COMMERCE ROUTES
// =============================================
Route::prefix('domestic/ecommerce')->name('domestic.ecommerce.')->middleware(['auth', 'role:domestic_admin,staff'])->group(function () {
    Route::get('/dashboard', [DomesticEcommerceController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [DomesticEcommerceController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}', [DomesticEcommerceController::class, 'showOrder'])->name('orders.show');
    Route::put('/orders/{id}/status', [DomesticEcommerceController::class, 'updateOrderStatus'])->name('orders.update-status');
    Route::post('/orders/bulk-update', [DomesticEcommerceController::class, 'bulkUpdate'])->name('orders.bulk-update');
    Route::get('/orders/export', [DomesticEcommerceController::class, 'exportOrders'])->name('export-orders');
    Route::get('/sellers', [DomesticEcommerceController::class, 'sellers'])->name('sellers');
    Route::get('/sellers/{id}', [DomesticEcommerceController::class, 'showSeller'])->name('sellers.show');
    Route::get('/products', [DomesticEcommerceController::class, 'products'])->name('products');
    Route::get('/products/{id}', [DomesticEcommerceController::class, 'showProduct'])->name('products.show');
    Route::get('/analytics', [DomesticEcommerceController::class, 'analytics'])->name('analytics');

    // Direct Rider Network Management, KYC & COD Settlement (under E-Commerce Delivery)
    Route::get('/riders', [EcommerceRiderController::class, 'index'])->name('riders.index');
    Route::get('/riders/rates', [EcommerceRiderController::class, 'rateRules'])->name('riders.rates');
    Route::post('/riders/rates', [EcommerceRiderController::class, 'updateRateRules'])->name('riders.rates.update');
    Route::get('/riders/cod-ledger', [EcommerceRiderController::class, 'codLedger'])->name('riders.cod');
    Route::post('/riders/cod-ledger/{id}/approve', [EcommerceRiderController::class, 'approveDeposit'])->name('riders.cod.approve');
    Route::get('/riders/{id}', [EcommerceRiderController::class, 'show'])->name('riders.show');
    Route::post('/riders/{id}/verify', [EcommerceRiderController::class, 'verify'])->name('riders.verify');
    Route::post('/riders/{id}/reject', [EcommerceRiderController::class, 'reject'])->name('riders.reject');
    Route::post('/riders/{id}/suspend', [EcommerceRiderController::class, 'suspend'])->name('riders.suspend');
    Route::post('/riders/direct-assign', [EcommerceRiderController::class, 'directAssign'])->name('riders.assign');
});

// =============================================
// DOMESTIC SERVICE ADMIN ROUTES (DOMESTIC ADMIN)
// =============================================
Route::prefix('domestic')->name('domestic.')->middleware(['auth', 'role:domestic_admin,staff'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DomesticAdminController::class, 'dashboard'])->name('dashboard');

    // Partners
    Route::get('/partners', [DomesticAdminController::class, 'partners'])->name('partners');
    Route::get('/partners/create', [DomesticAdminController::class, 'createPartner'])->name('partners.create');
    Route::post('/partners', [DomesticAdminController::class, 'storePartner'])->name('partners.store');
    Route::get('/partners/{id}', [DomesticAdminController::class, 'showPartner'])->name('partners.show');
    Route::get('/partners/{id}/edit', [DomesticAdminController::class, 'editPartner'])->name('partners.edit');
    Route::put('/partners/{id}', [DomesticAdminController::class, 'updatePartner'])->name('partners.update');
    Route::delete('/partners/{id}', [DomesticAdminController::class, 'deletePartner'])->name('partners.delete');
    Route::delete('/partners/{id}/destroy', [DomesticAdminController::class, 'deletePartner'])->name('partners.destroy');

    // Rates
    Route::get('/rates', [DomesticAdminController::class, 'rates'])->name('rates');
    Route::get('/rates/create', [DomesticAdminController::class, 'createRate'])->name('rates.create');
    Route::post('/rates', [DomesticAdminController::class, 'storeRate'])->name('rates.store');
    Route::get('/rates/{id}/edit', [DomesticAdminController::class, 'editRate'])->name('rates.edit');
    Route::put('/rates/{id}', [DomesticAdminController::class, 'updateRate'])->name('rates.update');
    Route::delete('/rates/{id}', [DomesticAdminController::class, 'deleteRate'])->name('rates.destroy');
    Route::delete('/rates/{id}/delete', [DomesticAdminController::class, 'deleteRate'])->name('rates.delete');

    // Zones
    Route::get('/zones', [DomesticAdminController::class, 'zones'])->name('zones');
    Route::get('/zones/create', [DomesticAdminController::class, 'createZone'])->name('zones.create');
    Route::post('/zones', [DomesticAdminController::class, 'storeZone'])->name('zones.store');
    Route::get('/zones/{id}/edit', [DomesticAdminController::class, 'editZone'])->name('zones.edit');
    Route::put('/zones/{id}', [DomesticAdminController::class, 'updateZone'])->name('zones.update');
    Route::delete('/zones/{id}', [DomesticAdminController::class, 'deleteZone'])->name('zones.destroy');
    Route::delete('/zones/{id}/delete', [DomesticAdminController::class, 'deleteZone'])->name('zones.delete');

    // Shipments
    Route::get('/shipments', [DomesticAdminController::class, 'shipments'])->name('shipments');
    Route::get('/shipments/{id}', [DomesticAdminController::class, 'showShipment'])->name('shipments.show');
    Route::put('/shipments/{id}/status', [DomesticAdminController::class, 'updateShipmentStatus'])->name('shipments.update-status');

    // Pickups
    Route::get('/pickups', [DomesticAdminController::class, 'pickups'])->name('pickups');

    // E-commerce
    Route::get('/sellers', [DomesticAdminController::class, 'sellers'])->name('sellers');
    Route::get('/sellers/{id}', [DomesticAdminController::class, 'showSeller'])->name('sellers.show');
    Route::get('/products', [DomesticAdminController::class, 'products'])->name('products');
    Route::get('/orders', [DomesticAdminController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}', [DomesticAdminController::class, 'showOrder'])->name('orders.show');
    Route::put('/orders/{id}/status', [DomesticAdminController::class, 'updateOrderStatus'])->name('orders.update-status');

    // Reports
    Route::get('/reports', [DomesticAdminController::class, 'reports'])->name('reports');

    // Operations Staff Management
    Route::get('/staff', [\App\Http\Controllers\Domestic\StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [\App\Http\Controllers\Domestic\StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [\App\Http\Controllers\Domestic\StaffController::class, 'store'])->name('staff.store');
    Route::delete('/staff/{id}', [\App\Http\Controllers\Domestic\StaffController::class, 'destroy'])->name('staff.destroy');
});

// =============================================
// PARTNER ROUTES
// =============================================
Route::prefix('partner')->name('partner.')->middleware(['auth', 'role:partner'])->group(function () {

    Route::get('/dashboard', [PartnerDashboardController::class, 'index'])->name('dashboard');

    // Deliveries
    Route::get('/deliveries/attention', [PartnerDeliveryController::class, 'attentionNeeded'])->name('deliveries.attention');
    Route::get('/deliveries/export', [PartnerDeliveryController::class, 'export'])->name('deliveries.export');
    Route::get('/deliveries', [PartnerDeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('/deliveries/{id}', [PartnerDeliveryController::class, 'show'])->name('deliveries.show');
    Route::post('/deliveries/{id}/update-status', [PartnerDeliveryController::class, 'updateStatus'])->name('deliveries.update-status');
    Route::get('/deliveries/{id}/report-delay', [PartnerDeliveryController::class, 'showDelayForm'])->name('deliveries.report-delay');
    Route::post('/deliveries/{id}/report-delay', [PartnerDeliveryController::class, 'reportDelay'])->name('deliveries.store-delay');

    // Zones
    Route::post('/zones/set-operating-district', [PartnerZoneController::class, 'setOperatingDistrict'])->name('zones.set-operating-district');
    Route::resource('/zones', PartnerZoneController::class);

    // Rates
    Route::get('/rates', [PartnerRateController::class, 'index'])->name('rates.index');
    Route::get('/rates/create', [DomesticRateSubmissionController::class, 'create'])->name('rates.create');
    Route::post('/rates', [DomesticRateSubmissionController::class, 'store'])->name('rates.store');
    Route::get('/rates/{id}/edit', [PartnerRateController::class, 'edit'])->name('rates.edit');
    Route::put('/rates/{id}', [PartnerRateController::class, 'update'])->name('rates.update');
    Route::post('/rates/custom-service', [PartnerRateController::class, 'storeCustomService'])->name('rates.custom-service');

    Route::get('/shipment-legs', [ShipmentLegController::class, 'index'])->name('shipment-legs.index');
    Route::patch('/shipment-legs/{leg}/status', [ShipmentLegController::class, 'updateStatus'])->name('shipment-legs.status');
    Route::get('/shipment-legs/{leg}/alternatives', [ShipmentLegController::class, 'alternatives'])->name('shipment-legs.alternatives');
    Route::post('/shipment-legs/{leg}/reassign', [ShipmentLegController::class, 'reassign'])->name('shipment-legs.reassign');

    // Scan
    Route::get('/scan', [PartnerScanController::class, 'scan'])->name('scan');
    Route::post('/process-scan', [PartnerScanController::class, 'processScan'])->name('process-scan');
    Route::get('/fetch/{identifier}', [PartnerScanController::class, 'fetchShipment'])->name('fetch');

    // Staff
    Route::resource('/staff', PartnerStaffController::class)->except('show');
});

// =============================================
// INTERNATIONAL SERVICE ADMIN ROUTES
// =============================================
Route::prefix('international')->name('international.')->middleware(['auth', 'role:international_admin,staff'])->group(function () {
    Route::get('/dashboard', [InternationalAdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/partners', [InternationalAdminController::class, 'partners'])->name('partners');
    Route::get('/partners/create', [InternationalAdminController::class, 'createPartner'])->name('partners.create');
    Route::post('/partners', [InternationalAdminController::class, 'storePartner'])->name('partners.store');
    Route::get('/partners/{id}', [InternationalAdminController::class, 'showPartner'])->name('partners.show');
    Route::get('/partners/{id}/edit', [InternationalAdminController::class, 'editPartner'])->name('partners.edit');
    Route::put('/partners/{id}', [InternationalAdminController::class, 'updatePartner'])->name('partners.update');
    Route::delete('/partners/{id}', [InternationalAdminController::class, 'deletePartner'])->name('partners.delete');
    Route::patch('/partners/{id}/toggle', [InternationalAdminController::class, 'togglePartnerStatus'])->name('partners.toggle');

    Route::get('/rates', [InternationalAdminController::class, 'rates'])->name('rates');
    Route::get('/rates/create', [InternationalAdminController::class, 'createRate'])->name('rates.create');
    Route::post('/rates', [InternationalAdminController::class, 'storeRate'])->name('rates.store');

    Route::get('/surcharges', [InternationalAdminController::class, 'surcharges'])->name('surcharges');
    Route::get('/surcharges/create', [InternationalAdminController::class, 'createSurcharge'])->name('surcharges.create');
    Route::post('/surcharges', [InternationalAdminController::class, 'storeSurcharge'])->name('surcharges.store');
    Route::delete('/surcharges/{id}', [InternationalAdminController::class, 'deleteSurcharge'])->name('surcharges.delete');
    Route::patch('/surcharges/{id}/toggle', [InternationalAdminController::class, 'toggleSurcharge'])->name('surcharges.toggle');

    Route::get('/shipments', [InternationalAdminController::class, 'shipments'])->name('shipments');
    Route::get('/shipments/create', [InternationalAdminController::class, 'createShipment'])->name('shipments.create');
    Route::post('/shipments', [InternationalAdminController::class, 'storeShipment'])->name('shipments.store');
    Route::get('/shipments/{id}', [InternationalAdminController::class, 'showShipment'])->name('shipments.show');
    Route::put('/shipments/{id}/status', [InternationalAdminController::class, 'updateShipmentStatus'])->name('shipments.update-status');
    Route::put('/shipments/{id}/routing', [InternationalAdminController::class, 'updateRouting'])->name('shipments.update-routing');

    Route::get('/reports', [InternationalAdminController::class, 'reports'])->name('reports');

    Route::get('/transit-points', [TransitPointController::class, 'index'])->name('transit-points.index');
    Route::get('/transit-points/create', [TransitPointController::class, 'create'])->name('transit-points.create');
    Route::post('/transit-points', [TransitPointController::class, 'store'])->name('transit-points.store');
    Route::get('/transit-points/{id}/edit', [TransitPointController::class, 'edit'])->name('transit-points.edit');
    Route::put('/transit-points/{id}', [TransitPointController::class, 'update'])->name('transit-points.update');
    Route::delete('/transit-points/{id}', [TransitPointController::class, 'destroy'])->name('transit-points.destroy');
    Route::patch('/transit-points/{id}/toggle', [TransitPointController::class, 'toggle'])->name('transit-points.toggle');

    // -------------------------------------------------------------
    // INTERNATIONAL AIR-CARGO, HUBS, AGENCIES, MAWBs & MANIFESTS
    // -------------------------------------------------------------
    Route::patch('hubs/{id}/toggle', [InternationalHubController::class, 'toggle'])->name('hubs.toggle');
    Route::resource('hubs', InternationalHubController::class)->except(['show']);

    Route::get('agencies/{agency}/format-settings', [InternationalAgencyController::class, 'formatSettings'])->name('agencies.format-settings');
    Route::post('agencies/{agency}/format-settings', [InternationalAgencyController::class, 'updateFormatSettings'])->name('agencies.update-format-settings');
    Route::post('agencies/{agency}/reset-password', [InternationalAgencyController::class, 'resetPassword'])->name('agencies.reset-password');
    Route::resource('agencies', InternationalAgencyController::class)->except(['show']);

    Route::resource('last-mile-carriers', LastMileCarrierController::class)->names('last-mile')->except(['create', 'show', 'edit']);
    Route::resource('mawbs', MAWBController::class)->except(['show']);

    Route::get('manifests', [InternationalManifestController::class, 'index'])->name('manifests.index');
    Route::get('manifests/create', [InternationalManifestController::class, 'create'])->name('manifests.create');
    Route::post('manifests', [InternationalManifestController::class, 'store'])->name('manifests.store');
    Route::get('manifests/{manifest}', [InternationalManifestController::class, 'show'])->name('manifests.show');
    Route::get('manifests/{manifest}/data-sheet', [InternationalManifestController::class, 'dataSheet'])->name('manifests.data-sheet');
    Route::post('manifests/{manifest}/send-agency-email', [InternationalManifestController::class, 'sendAgencyEmail'])->name('manifests.send-agency-email');

    // Operations Staff Management
    Route::get('/staff', [\App\Http\Controllers\International\StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [\App\Http\Controllers\International\StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [\App\Http\Controllers\International\StaffController::class, 'store'])->name('staff.store');
    Route::delete('/staff/{id}', [\App\Http\Controllers\International\StaffController::class, 'destroy'])->name('staff.destroy');

    // Sector Rate Matrices (0.5kg Slabs & Dynamic Per-KG Tiers)
    Route::get('/rates-matrix', [InternationalRateController::class, 'index'])->name('rates-matrix.index');
});

// =============================================
// ADMIN INTERNATIONAL RATE MATRICES (0.5kg Slabs & Dynamic Per-KG Tiers)
// =============================================
Route::prefix('admin/international-rates')->name('admin.international-rates.')->middleware(['auth', 'role:super_admin,admin,international_admin,staff'])->group(function () {
    Route::get('/', [InternationalRateController::class, 'index'])->name('index');
    Route::get('/create', [InternationalRateController::class, 'create'])->name('create');
    Route::post('/', [InternationalRateController::class, 'store'])->name('store');
    Route::get('/settings', [InternationalRateController::class, 'settings'])->name('settings');
    Route::post('/settings/tariff', [InternationalRateController::class, 'updateTariffSettings'])->name('settings.tariff');
    Route::post('/settings/packaging', [InternationalRateController::class, 'storePackaging'])->name('settings.packaging.store');
    Route::put('/settings/packaging/{id}', [InternationalRateController::class, 'updatePackaging'])->name('settings.packaging.update');
    Route::patch('/settings/packaging/{id}/toggle', [InternationalRateController::class, 'togglePackaging'])->name('settings.packaging.toggle');
    Route::delete('/settings/packaging/{id}', [InternationalRateController::class, 'destroyPackaging'])->name('settings.packaging.destroy');
    Route::get('/{id}/edit', [InternationalRateController::class, 'edit'])->name('edit');
    Route::put('/{id}', [InternationalRateController::class, 'update'])->name('update');
    Route::patch('/{id}/toggle', [InternationalRateController::class, 'toggle'])->name('toggle');
    Route::delete('/{id}', [InternationalRateController::class, 'destroy'])->name('destroy');
});

// =============================================
// AGENCY INBOUND DESK & BOX SCAN TELEMETRY
// =============================================
Route::prefix('agency')->name('agency.')->middleware(['auth'])->group(function () {
    Route::get('/scan', [AgencyShipmentController::class, 'scan'])->name('scan');
    Route::post('/process-scan', [AgencyShipmentController::class, 'processScan'])->name('process-scan');
    Route::get('/manifests', [AgencyShipmentController::class, 'inboundManifests'])->name('manifests.index');
    Route::get('/manifests/{manifest}/arrival-notice', [AgencyShipmentController::class, 'arrivalNoticeForm'])->name('manifests.arrival-notice');
    Route::post('/manifests/{manifest}/arrival-notice', [AgencyShipmentController::class, 'processArrivalNotice'])->name('manifests.process-arrival-notice');
    Route::get('/shipments', [AgencyShipmentController::class, 'shipments'])->name('shipments.index');
});

// =============================================
// OVERSEAS ROUTES
// =============================================
Route::prefix('overseas')->name('overseas.')->middleware(['auth', 'role:overseas'])->group(function () {
    Route::get('/dashboard', [OverseasDashboardController::class, 'index'])->name('dashboard');
    Route::get('/partners', [OverseasPartnerListController::class, 'index'])->name('partners.index');
    Route::get('/hubs', [HubController::class, 'index'])->name('hubs.index');
    Route::get('/shipments', [OverseasShipmentController::class, 'index'])->name('shipments');
    Route::get('/shipments/{shipment}', [OverseasShipmentController::class, 'show'])->name('shipments.show');
    Route::post('/shipments/{shipment}/update-status', [OverseasShipmentController::class, 'updateStatus'])->name('shipments.update-status');
    Route::get('/scan', [OverseasScanController::class, 'scan'])->name('scan');
    Route::post('/process-scan', [OverseasScanController::class, 'processScan'])->name('process-scan');
    Route::get('/documents', [OverseasShipmentController::class, 'documents'])->name('documents');

    Route::patch('transit-points/{id}/toggle', [TransitPointController::class, 'toggle'])->name('transit-points.toggle');
    Route::resource('transit-points', TransitPointController::class)->except('show');
});

// Overseas Staff Routes
Route::prefix('overseas/staff')->name('overseas.staff.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
    Route::get('/scan', [StaffScanController::class, 'scan'])->name('scan');
    Route::post('/process-scan', [StaffScanController::class, 'processScan'])->name('process-scan');
});

// =============================================
// E-COMMERCE ROUTES (Seller)
// =============================================
Route::prefix('seller/ecommerce')->name('ecommerce.seller.')->middleware(['auth', 'role:seller'])->group(function () {
    Route::get('/dashboard', [EcommerceSellerController::class, 'dashboard'])->name('dashboard');
    Route::get('/create', [EcommerceSellerController::class, 'create'])->name('create');
    Route::post('/orders', [EcommerceSellerController::class, 'store'])->name('orders.store');
    Route::get('/orders', [EcommerceSellerController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [EcommerceSellerController::class, 'show'])->name('order.show');
    Route::get('/orders/{order}/label', [EcommerceSellerController::class, 'generateLabel'])->name('order.label');
    Route::get('/orders/{order}/print', [EcommerceSellerController::class, 'printLabel'])->name('order.print');
    Route::post('/orders/{order}/cancel', [EcommerceSellerController::class, 'cancel'])->name('order.cancel');
});

// =============================================
// E-COMMERCE SERVICE ADMIN ROUTES
// =============================================
Route::prefix('ecommerce')->name('ecommerce.')->middleware(['auth', 'role:super_admin,domestic_admin'])->group(function () {
    Route::get('/dashboard', [EcommerceAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/sellers', [EcommerceAdminController::class, 'sellers'])->name('sellers');
    Route::get('/products', [EcommerceAdminController::class, 'products'])->name('products');
    Route::get('/orders', [EcommerceAdminController::class, 'orders'])->name('orders');
});

// =============================================
// RATE UPLOAD
// =============================================
Route::get('/rates/create', [RateUploadController::class, 'create'])->name('rates.create');
Route::post('/rates/parse', [RateUploadController::class, 'parse'])->name('rates.parse');
Route::post('/rates/import', [RateUploadController::class, 'import'])->name('rates.import');

// =============================================
// FALLBACK ROUTE
// =============================================
Route::fallback(function () {
    return redirect('/');
});
