<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\v1\AuthController;
use App\Http\Controllers\API\v1\ChatController;
use App\Http\Controllers\API\v1\ZegoController;
use App\Http\Controllers\API\v1\OrderController;
use App\Http\Controllers\API\v1\DisputeController;
use App\Http\Controllers\API\v1\SettingController;
use App\Http\Controllers\API\v1\CustomerController;
use App\Http\Controllers\API\v1\RazorpayController;
use App\Http\Controllers\API\v1\AstrologerController;
use App\Http\Controllers\API\v1\AppointmentController;
use App\Http\Controllers\API\v1\NotificationController;
use App\Http\Controllers\API\v1\HelpAndSupportController;


// API Version 1
Route::post('v1/login', [AuthController::class, 'login']);
Route::post('v1/verifyOtp', [AuthController::class, 'verifyOtp']);
Route::middleware('checkBearerToken')->post('v1/logout', [AuthController::class, 'logout']);
// Astrologer
Route::prefix('v1/astrologer')->group(function () {
    Route::middleware(['checkBearerToken'])->group(function () {
        // Dashboard
        Route::get('dashboard', [AstrologerController::class, 'dashboard']);
        // Profile
        Route::post('register-profile', [AstrologerController::class, 'registerProfile']);
        Route::post('update-profile', [AstrologerController::class, 'updateProfile']);
        Route::post('profile-picture', [AstrologerController::class, 'profilePicture']);
        Route::get('get-profile', [AstrologerController::class, 'getProfile']);
        Route::delete('delete-account', [AstrologerController::class, 'deleteAccount']);
        Route::post('store-bank-details', [AstrologerController::class, 'storeBankDetails']);
        Route::get('get-bank-details', [AstrologerController::class, 'getBankDetails']);
        Route::post('generate-share-link', [AstrologerController::class, 'generateShareLink']);
        // schedule
        Route::post('schedule', [AstrologerController::class, 'schedule']);
        Route::get('get-schedule', [AstrologerController::class, 'getSchedule']);
        Route::post('is-online', [AstrologerController::class, 'isOnline']);
        // Book Now Prices
        Route::post('book-now-prices', [AstrologerController::class, 'bookNowPrices']);
        // Settings
        Route::get('get-expert-in', [AstrologerController::class, 'getExpertIn']);
        Route::get('get-keywords', [AstrologerController::class, 'getKeywords']);
        Route::get('get-expertises', [AstrologerController::class, 'getExpertises']);
        Route::get('get-languages', [AstrologerController::class, 'getLanguages']);
        Route::get('get-report-prices', [AstrologerController::class, 'getReportPrices']);

        // Appointment
        Route::get('get-appointments', [AppointmentController::class, 'getAstroAppointments']);
        Route::get('get-completed-appointments', [AppointmentController::class, 'getAstroCompletedAppointments']);
        Route::get('get-assigned-customers', [AppointmentController::class, 'getAssignedCustomers']);
        Route::get('get-consultation-history', [AppointmentController::class, 'getConsultationHistory']);
        Route::post('extend-chat', [AppointmentController::class, 'extendTenMinChat']);
        
        // Wallet
        Route::get('get-earnings', [AstrologerController::class, 'getEarnings']);
        Route::post('withdrawal-request', [AstrologerController::class, 'withdrawalRequest']);
        Route::get('get-withdrawal-histories', [AstrologerController::class, 'getWithdrawalHistory']);
        Route::get('get-wallet', [AstrologerController::class, 'getWallet']);
        // Notification
        Route::get('get-notification', [NotificationController::class, 'getNotification']);
        Route::post('mark-as-seen', [NotificationController::class, 'markAsSeen']);
        Route::delete('delete-notification/{id}', [NotificationController::class, 'delete']);
        // Setting
        Route::get('get-min-max-prices', [SettingController::class, 'getPrices']);
        Route::get('get-config', [SettingController::class, 'getAstrologerConfig']);
    });
});

// Customer
Route::prefix('v1/customer')->group(function () {
    Route::post('register-profile', [CustomerController::class, 'registerProfile']);

    Route::middleware(['checkBearerToken'])->group(function () {
        // Profile
        Route::post('update-profile', [CustomerController::class, 'updateProfile']);
        Route::post('profile-picture', [CustomerController::class, 'profilePicture']);
        Route::get('get-profile', [CustomerController::class, 'getProfile']);
        Route::delete('delete-account', [CustomerController::class, 'deleteAccount']);
        // Astrologer
        Route::get('get-astrologers', [CustomerController::class, 'getAstrologers']);
        Route::get('get-online-astrologers', [CustomerController::class, 'getOnlineAstrologers']);
        Route::get('get-astrologer-detail/{id}', [CustomerController::class, 'getAstrologerDetail']);
        // Astrologer Review
        Route::post('astrologer-review', [AppointmentController::class, 'astrologerReview']);
        Route::get('get-astrologer-reviews', [AppointmentController::class, 'getAstrologerReviews']);
        // Appointment
        Route::get('get-astrologer-schedule/{id}', [AppointmentController::class, 'getAstrologerSchedule']);
        Route::get('get-price-settings/{id}', [AppointmentController::class, 'getPriceSettings']);
        Route::get('get-astrologer-availability/{id}', [AppointmentController::class, 'getAstrologerAvailability']);
        Route::post('book-appointment', [AppointmentController::class, 'bookAppointment']);
        Route::post('book-now', [AppointmentController::class, 'bookNow']);
        Route::post('booking-status-change-to-complete', [AppointmentController::class, 'bookingStatusChangetoComplete']);
        Route::get('get-appointments', [AppointmentController::class, 'getAppointments']);
        Route::get('get-completed-appointments', [AppointmentController::class, 'getCompletedAppointments']);
        Route::get('get-ongoing-chat', [AppointmentController::class, 'getOngoingChat']);
        Route::get('get-free-claim-chat', [AppointmentController::class, 'getFreeClaimChat']);
        // Appointment Review
        Route::post('appointment-review', [AppointmentController::class, 'appointmentReview']);
        Route::get('get-appointment-reviews', [AppointmentController::class, 'getAppointmentReviews']);
        // Orders
        Route::get('get-orders', [OrderController::class, 'getOrders']);
        Route::post('order-cancel', [OrderController::class, 'orderCancel']);
        Route::get('get-refund-requests', [OrderController::class, 'getRefundRequests']);
        // Coupon
        Route::post('apply-coupon', [OrderController::class, 'applyCoupon']);
        Route::get('get-coupon-available', [OrderController::class, 'getCouponAvailable']);
        // Notification
        Route::get('get-notification', [NotificationController::class, 'getNotification']);
        Route::post('mark-as-seen', [NotificationController::class, 'markAsSeen']);
        Route::delete('delete-notification/{id}', [NotificationController::class, 'delete']);
        // Dispute
        Route::post('raise-dispute', [DisputeController::class, 'raiseDispute']);
        Route::get('get-disputes', [DisputeController::class, 'getDisputes']);
        // Setting
        Route::get('get-config', [SettingController::class, 'getCustomerConfig']);
    });
});

Route::prefix('v1')->group(function () {
    Route::get('get-cities', [CustomerController::class, 'getCities']);
    Route::get('get-gst', [SettingController::class, 'getGst']);

    Route::get('get-is-ios-review', [SettingController::class, 'isIOSReview']);
    Route::get('get-branding', [SettingController::class, 'getBranding']);
    Route::get('get-features', [SettingController::class, 'getFeatures']);
    Route::middleware(['checkBearerToken'])->group(function () {
        // FAQ
        Route::get('get-faqs', [HelpAndSupportController::class, 'getFaqs']);
        Route::get('get-single-faq/{id}', [HelpAndSupportController::class, 'getSingleFaq']);
        // Zego
        Route::post('chat-log', [ZegoController::class, 'sendMessage']);
        Route::post('call-log', [ZegoController::class, 'callLog']);
        Route::get('get-call-log', [ZegoController::class, 'getCallLog']);
        Route::post('get-is-ended-chat', [ZegoController::class, 'getIsEndedChat']);
        Route::post('send-alert', [ZegoController::class, 'sendAlert']);
        Route::post('last-chat-session', [ZegoController::class, 'lastChatSession']);
        // Banner
        Route::get('get-banner', [SettingController::class, 'getBanner']);
        Route::post('banner-click', [SettingController::class, 'bannerClick']);

        Route::get('get-ongoing-appointments', [AppointmentController::class, 'getOngoingAppointments']);
    });
    // Zego Profile Picture
    Route::get('get-zego-profile-picture', [ZegoController::class, 'getZegoProfilePicture']);
});


// RazorPay
Route::prefix('v1/razorpay')->group(function () {
    Route::middleware(['checkBearerToken'])->group(function () {
        Route::post('create-order', [RazorpayController::class, 'createOrder']);
    });
});

// Chat
Route::prefix('v1/chat')->group(function () {
    Route::middleware(['checkBearerToken'])->group(function () {
        Route::post('/', [ChatController::class, 'store']);
        Route::get('/conversations', [ChatController::class, 'conversationList']);
        Route::get('messages/{userId}', [ChatController::class, 'index']);
        Route::post('/read/{id}', [ChatController::class, 'markAsRead']);
        Route::delete('delete/{id}', [ChatController::class, 'destroy']);
    });
});

// v2
Route::prefix('v2')->group(function () {
    require __DIR__.'/api/v2/api_v2.php';
});

// v3
Route::prefix('v3')->group(function () {
    require __DIR__.'/api/v3/api_v3.php';
});
