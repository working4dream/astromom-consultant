<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaqController;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeepLinkController;
use App\Http\Controllers\DropzoneController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AstrologerController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MediaLibraryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FreeChatUsageController;
use App\Http\Controllers\WithdrawRequestController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\File;
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
Route::get('/view-logs', function () {
   $logFile = storage_path('logs/laravel.log');
   if (File::exists($logFile)) {
      return '<pre>' . file_get_contents($logFile) . '</pre>';
   } else {
       return 'Log file not found!';
   }
});
Route::get('/', fn() => redirect()->route('admin.login'));
Route::get('/astrologer/signup', [AstrologerController::class, 'signup'])->name('astrologer.signup');
Route::get('/astrologer/success', [AstrologerController::class, 'success'])->name('astrologer.success');
Route::post('/astrologer/store', [AstrologerController::class, 'astrologer_store'])->name('astrologer.store');
Route::post('/upload-astrologer-file', [DropzoneController::class,'uploadFileAWS'])->name('admin.dropzone.astrologer-upload-file');
Route::post('/delete-uploaded-astrologer-file', [DropzoneController::class, 'deleteUploadedFile'])->name('admin.dropzone.astrologer-delete-file');
// Expert Profile
Route::get('/expert/profile-picture', [AstrologerController::class, 'profilePicture'])->name('expert.profile-picture');
Route::post('/expert/profile/store', [AstrologerController::class, 'expertProfileStore'])->name('expert-ptofile.store');

Route::get('/payment/success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/dropzone/get-media', [MediaLibraryController::class, 'getMedia'])->name('admin.dropzone.get-media');
Route::get('/get-media', [MediaLibraryController::class, 'getMediaLibrary'])->name('admin.get-media-library');

Route::prefix('admin')->group(function () {
   Route::get('/', function () {
      if (Auth::check()) {
         return redirect()->route('admin.dashboard');
      }
      return redirect()->route('admin.login');
   });
   Route::get('login', [LoginController::class, 'showLoginForm'])->name('admin.login');
   Route::post('login', [LoginController::class, 'login']);
   Route::post('logout', [LoginController::class, 'logout'])->name('admin.logout');
   Route::group(['middleware' => ['auth', \App\Http\Middleware\SetUserDisplayTimezone::class]], function () {
      Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('admin.dashboard');

      Route::prefix('products')->group(function () {
         Route::get('', [ProductController::class, 'index'])->name('admin.products.index');
         Route::get('/create', [ProductController::class, 'create'])->name('admin.products.create');
         Route::post('/store', [ProductController::class, 'store'])->name('admin.products.store');
         Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
         Route::post('/{id}', [ProductController::class, 'update'])->name('admin.products.update');
         Route::post('/{id}/destroy', [ProductController::class, 'destroy'])->name('admin.products.destroy');
      });
      Route::post('/update-product-status', [ProductController::class, 'updateStatus'])->name('admin.update-product-status');
      Route::get('/sales-data/{range}', [HomeController::class, 'getSalesData'])->name('admin.sales-data');
      Route::get('/orders/filter', [HomeController::class, 'filterOrders'])->name('orders.filter');
      Route::get('/free-chat-usage-data', [HomeController::class, 'freeChatUsage'])->name('orders.free-chat-usage-data');
      Route::get('/latest-customers', [HomeController::class, 'getLatestCustomers'])->name('latest-customers');
      Route::post('/dashboard/filter', [HomeController::class, 'filterData'])->name('admin.dashboard.filterData');
      Route::get('/edit-profile', [SettingController::class, 'editProfile'])->name('admin.edit-profile');
      Route::post('/edit-profile/store', [SettingController::class, 'editProfileStore'])->name('admin.edit-profile.store');
      Route::post('/change-password/store', [SettingController::class, 'changePasswordstore'])->name('admin.change-password.store');

      // Media Library
      Route::get('/media-library', [MediaLibraryController::class, 'index'])->name('admin.media-library');
      Route::post('multi/delete-existing-file', [MediaLibraryController::class, 'deleteMultipleFiles'])->name('admin.multi.delete-existing-file');


      // Expert Profile
      Route::prefix('faqs')->group(function () {
         Route::get('', [FaqController::class, 'index'])->name('admin.faqs.index');
         Route::get('/create', [FaqController::class, 'create'])->name('admin.faqs.create');
         Route::post('/store', [FaqController::class,'store'])->name('admin.faqs.store');
         Route::get('/{faq}/edit', [FaqController::class, 'edit'])->name('admin.faqs.edit');
         Route::post('/{faq}/destroy', [FaqController::class,'destroy'])->name('admin.faqs.destroy');
      });

      Route::prefix('experts')->group(function () {
         Route::get('', [AstrologerController::class, 'index'])->name('admin.experts.index');
         Route::get('/create', [AstrologerController::class, 'create'])->name('admin.experts.create');
         Route::post('/store', [AstrologerController::class, 'store'])->name('admin.experts.store');
         Route::get('/{id}/edit', [AstrologerController::class, 'edit'])->name('admin.experts.edit');
         Route::get('/{id}/show', [AstrologerController::class, 'show'])->name('admin.experts.show');
         Route::post('/{id}', [AstrologerController::class,'update'])->name('admin.experts.update');
         Route::post('/{id}/destroy', [AstrologerController::class,'destroy'])->name('admin.experts.destroy');
         Route::get('export', [AstrologerController::class, 'export'])->name('admin.experts.export');
         Route::get('/download/{id}', [AstrologerController::class, 'downloadImage'])->name('admin.experts.download');
         Route::get('/download-profile/{id}', [AstrologerController::class, 'downloadExpertProfileImage'])->name('admin.experts-profile.download');
         Route::delete('/delete-expert/{id}', [AstrologerController::class, 'deleteExpertProfile'])->name('admin.experts-profile.delete');
         Route::get('/{id}/ratings', [AstrologerController::class, 'expertRatings'])->name('admin.experts.ratings');
      });
      Route::post('/update-status', [AstrologerController::class, 'updateStatus'])->name('admin.experts.updateAstrologerStatus');
      Route::post('/update-approval-status', [AstrologerController::class, 'updateApprovalStatus'])->name('updateApprovalStatus');
      Route::post('/update-is-top-expert', [AstrologerController::class, 'updateIsTopExpert'])->name('admin.experts.updateIsTopExpert');

      Route::prefix('customers')->group(function () {
         Route::get('', [CustomerController::class, 'index'])->name('admin.customers.index');
         Route::get('/create', [CustomerController::class, 'create'])->name('admin.customers.create');
         Route::post('/store', [CustomerController::class, 'store'])->name('admin.customers.store');
         Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('admin.customers.edit');
         Route::get('/details/{customer}', [CustomerController::class, 'show'])->name('admin.customers.show');
         Route::post('/{customer}', [CustomerController::class,'update'])->name('admin.customers.update');
         Route::post('/{customer}/destroy', [CustomerController::class,'destroy'])->name('admin.customers.destroy');
         Route::get('export', [CustomerController::class, 'export'])->name('admin.customers.export');
      });
      Route::get('/expert-profile', [AstrologerController::class, 'expertProfileIndex'])->name('admin.expert.profile.index');
      Route::post('/appointment-reschedule', [CustomerController::class, 'appointmentReschedule'])->name('admin.appointment.reschedule');

      Route::prefix('coupons')->group(function () {
         Route::get('', [CouponController::class, 'index'])->name('admin.coupons.index');
         Route::get('/create', [CouponController::class, 'create'])->name('admin.coupons.create');
         Route::post('/store', [CouponController::class, 'store'])->name('admin.coupons.store');
         Route::get('/{id}/edit', [CouponController::class, 'edit'])->name('admin.coupons.edit');
         Route::post('/{id}', [CouponController::class,'update'])->name('admin.coupons.update');
         Route::post('/{id}/destroy', [CouponController::class,'destroy'])->name('admin.coupons.destroy');
      });
      Route::post('/check-coupon', [CouponController::class, 'checkCoupon'])->name('check.coupon');

      Route::prefix('orders')->group(function () {
         Route::get('', [OrderController::class, 'index'])->name('admin.orders.index');
         Route::get('show/{id}', [OrderController::class, 'show'])->name('admin.orders.show');
         Route::get('export', [OrderController::class, 'export'])->name('admin.orders.export');
      });

      Route::prefix('payments')->group(function () {
         Route::get('', [PaymentController::class, 'index'])->name('admin.payments.index');
         Route::get('show/{id}', [PaymentController::class, 'show'])->name('admin.payments.show');
      });

      Route::prefix('refunds')->group(function () {
         Route::get('', [PaymentController::class, 'refunds'])->name('admin.payments.refunds');
      });
      Route::prefix('disputes')->group(function () {
         Route::get('', [DisputeController::class, 'index'])->name('admin.disputes');
         Route::get('show/{id}', [DisputeController::class, 'show'])->name('admin.disputes.show');
         Route::post('/store-discussion-messages', [DisputeController::class, 'storeDiscussion'])->name('admin.disputes.storeDiscussionMessage');
         Route::get('/get-discussion-dispute-messages/{disputeId}', [DisputeController::class, 'getDiscussion']);

      });

      Route::post('/upload-file', [DropzoneController::class,'uploadFileAWS'])->name('admin.dropzone.upload-file');
      Route::post('/delete-uploaded-file', [DropzoneController::class, 'deleteUploadedFile'])->name('admin.dropzone.delete-file');
       Route::post('/delete-existing-file', [DropzoneController::class, 'deleteExistingFile'])->name('admin.dropzone.delete-existing-file');

      //Settings
      Route::prefix('settings')->group(function () {
         Route::get('', [SettingsController::class,'index'])->name('admin.settings.index');
         Route::post('update-price', [SettingsController::class,'updatePrice'])->name('admin.settings.update-price');
         Route::post('store/banner', [SettingsController::class,'storeBanner'])->name('admin.settings.store-banner');
         Route::delete('delete-banner/{id}', [SettingsController::class, 'deleteBanner'])->name('admin.settings.delete-banner');
         Route::post('update-banner', [SettingsController::class, 'updateBanner'])->name('admin.settings.update-banner');
         Route::post('update-app-settings', [SettingsController::class, 'updateAppSettings'])->name('admin.settings.update-app-settings');
         Route::post('update-branding', [SettingsController::class, 'updateBranding'])->name('admin.settings.update-branding');
         Route::post('delete-brand-logo', [SettingsController::class, 'deleteBrandLogo'])->name('admin.settings.delete-brand-logo');
      });
      // Notification
      Route::prefix('notification')->group(function () {
         Route::get('', [NotificationController::class, 'index'])->name('admin.notification.index');
         Route::post('', [NotificationController::class, 'sendNotifications'])->name('admin.notification.send');
      });
      // Withdraw
      Route::get('withdraw-requests', [WithdrawRequestController::class, 'index'])->name('admin.withdraw-request.index');
      Route::post('update-withdraw-approval-status', [WithdrawRequestController::class, 'updateWithdrawApprovalStatus'])->name('updateWithdrawApprovalStatus');
      Route::get('withdraw-requests/export', [WithdrawRequestController::class, 'export'])->name('withdraw.export');
      // Report
      Route::get('earning-report', [ReportController::class, 'earningReport'])->name('admin.earning-report.index');
      Route::get('earning-report/{earning}', [ReportController::class, 'earningReportShow'])->name('admin.earning-report.show');
      Route::get('account-report', [ReportController::class, 'accountReport'])->name('admin.account-report.index');
      // FreeChatUsage
      Route::get('free-chat-usage', [FreeChatUsageController::class, 'index'])->name('admin.freeChatUsage.index');
      Route::get('free-chat-usage/{id}', [FreeChatUsageController::class, 'show'])->name('admin.freeChatUsage.show');

      Route::post('create-appointment', [OrderController::class, 'createAppointment'])->name('admin.create-appointment');

   });
});
Route::get('/login', [DeepLinkController::class, 'handleDeepLink']);
// Mobile app API login: POST /login (GET above is deep-link only; without this, POST returns 405)
Route::post('/login', [\App\Http\Controllers\API\v1\AuthController::class, 'login'])
    ->middleware('throttle:api');
Route::get('/share-app', [DeepLinkController::class, 'handleDeepLink']);
Route::get('/expertInfoScreen', [AstrologerController::class, 'shareProfile']);

Route::get('/get-signed-url', function (Request $request) {
   $filePath = $request->path; // Frontend se file path milega

   if (!$filePath) {
       return response()->json(['error' => 'File path is required'], 400);
   }

   $signedUrl = Storage::disk('s3')->temporaryUrl($filePath, now()->addMinutes(30));

   return response()->json(['signedUrl' => $signedUrl]);
});
