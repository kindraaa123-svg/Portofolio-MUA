<?php

use App\Http\Controllers\Admin\AccessControlController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\OperationalHourController;
use App\Http\Controllers\Admin\PortfolioController as AdminPortfolioController;
use App\Http\Controllers\Admin\RecycleBinController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Frontend\BookingController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PortfolioController;
use App\Models\Addon;
use App\Models\Faq;
use App\Models\PortfolioImage;
use App\Models\ServiceCategory;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tentang-kami', [HomeController::class, 'about'])->name('about');
Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
Route::get('/portfolio/{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');

Route::get('/galeri', function () {
    return view('public.gallery', [
        'images' => PortfolioImage::with('portfolio')->latest()->paginate(24),
    ]);
})->name('gallery');

Route::get('/pricelist', function () {
    return view('public.pricelist', [
        'serviceCategories' => ServiceCategory::with('services')->orderBy('sort_order')->get(),
        'addons' => Addon::where('is_active', true)->orderBy('name')->get(),
    ]);
})->name('pricelist');

Route::get('/testimoni', function () {
    return view('public.testimonials', [
        'testimonials' => Testimonial::where('is_published', true)->latest()->paginate(9),
    ]);
})->name('testimonials');

Route::get('/faq', function () {
    return view('public.faq', [
        'faqs' => Faq::where('is_published', true)->orderBy('sort_order')->get(),
    ]);
})->name('faq');

Route::get('/kontak', [HomeController::class, 'contact'])->name('contact');
Route::get('/reservasi', [BookingController::class, 'create'])->name('booking.create');
Route::post('/reservasi', [BookingController::class, 'store'])->name('booking.store');
Route::get('/api/available-times', [BookingController::class, 'availableTimes'])->name('booking.available-times');

Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');

Route::prefix('admin')->middleware('auth403')->name('admin.')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::get('/portfolio', [AdminPortfolioController::class, 'index'])->middleware('permission:portfolio.view')->name('portfolios.index');
    Route::get('/portfolio/create', [AdminPortfolioController::class, 'create'])->middleware('permission:portfolio.create')->name('portfolios.create');
    Route::post('/portfolio', [AdminPortfolioController::class, 'store'])->middleware('permission:portfolio.create')->name('portfolios.store');
    Route::get('/portfolio/export/xlsx', [AdminPortfolioController::class, 'exportXlsx'])->middleware('permission:portfolio.view')->name('portfolios.export-xlsx');
    Route::post('/portfolio/import/xlsx', [AdminPortfolioController::class, 'importXlsx'])->middleware('permission:portfolio.create')->name('portfolios.import-xlsx');
    Route::get('/portfolio/{portfolio}/edit', [AdminPortfolioController::class, 'edit'])->middleware('permission:portfolio.update')->name('portfolios.edit');
    Route::put('/portfolio/{portfolio}', [AdminPortfolioController::class, 'update'])->middleware('permission:portfolio.update')->name('portfolios.update');
    Route::delete('/portfolio/{portfolio}', [AdminPortfolioController::class, 'destroy'])->middleware('permission:portfolio.delete')->name('portfolios.destroy');

    Route::get('/pricelist', [ServiceController::class, 'index'])->middleware('permission:service.view')->name('services.index');
    Route::get('/pricelist/services/export/xlsx', [ServiceController::class, 'exportServicesXlsx'])->middleware('permission:service.view')->name('services.export-services-xlsx');
    Route::post('/pricelist/services/import/xlsx', [ServiceController::class, 'importServicesXlsx'])->middleware('permission:service.create')->name('services.import-services-xlsx');
    Route::get('/pricelist/addons/export/xlsx', [ServiceController::class, 'exportAddonsXlsx'])->middleware('permission:service.view')->name('services.export-addons-xlsx');
    Route::post('/pricelist/addons/import/xlsx', [ServiceController::class, 'importAddonsXlsx'])->middleware('permission:service.create')->name('services.import-addons-xlsx');
    Route::post('/pricelist/service', [ServiceController::class, 'storeService'])->middleware('permission:service.create')->name('services.store');
    Route::put('/pricelist/service/{service}', [ServiceController::class, 'updateService'])->middleware('permission:service.create')->name('services.update');
    Route::post('/pricelist/addon', [ServiceController::class, 'storeAddon'])->middleware('permission:service.create')->name('addons.store');
    Route::put('/pricelist/addon/{addon}', [ServiceController::class, 'updateAddon'])->middleware('permission:service.create')->name('addons.update');
    Route::delete('/pricelist/service/{service}', [ServiceController::class, 'destroyService'])->middleware('permission:service.delete')->name('services.destroy');
    Route::delete('/pricelist/addon/{addon}', [ServiceController::class, 'destroyAddon'])->middleware('permission:service.delete')->name('addons.destroy');

    Route::get('/reservasi', [AdminBookingController::class, 'index'])->middleware('permission:booking.view')->name('bookings.index');
    Route::get('/validasi-pembayaran', [AdminBookingController::class, 'paymentValidations'])->middleware('permission:booking.verify-payment')->name('bookings.payment-validations');
    Route::post('/reservasi/{booking}/status', [AdminBookingController::class, 'updateStatus'])->middleware('permission:booking.update')->name('bookings.update-status');
    Route::post('/reservasi/payment/{payment}/verify', [AdminBookingController::class, 'verifyPayment'])->middleware('permission:booking.verify-payment')->name('bookings.verify-payment');
    Route::post('/reservasi/slot', [AdminBookingController::class, 'storeSlot'])->middleware('permission:booking.update')->name('bookings.store-slot');
    Route::post('/reservasi/blocked-schedule', [AdminBookingController::class, 'storeBlockedSchedule'])->middleware('permission:booking.update')->name('bookings.store-blocked');
    Route::get('/reservasi/export', [AdminBookingController::class, 'export'])->middleware('permission:booking.export')->name('bookings.export');

    Route::get('/laporan', [ReportController::class, 'index'])->middleware('permission:report.view')->name('reports.index');
    Route::get('/laporan/export/pdf', [ReportController::class, 'exportPdf'])->middleware('permission:report.view')->name('reports.export-pdf');
    Route::get('/laporan/export/excel', [ReportController::class, 'exportExcel'])->middleware('permission:report.view')->name('reports.export-excel');
    Route::get('/laporan/print', [ReportController::class, 'print'])->middleware('permission:report.view')->name('reports.print');

    Route::get('/backup-database', [BackupController::class, 'index'])->middleware('permission:backup.view')->name('backup.index');
    Route::post('/backup-database/export', [BackupController::class, 'export'])->middleware('permission:backup.create')->name('backup.export');
    Route::post('/backup-database/import', [BackupController::class, 'import'])->middleware('permission:backup.import')->name('backup.import');
    Route::get('/backup-database/{backupLog}/download', [BackupController::class, 'download'])->middleware('permission:backup.view')->name('backup.download');

    Route::get('/recycle-bin', [RecycleBinController::class, 'index'])->middleware('permission:recycle.view')->name('recycle-bin.index');
    Route::post('/recycle-bin/{item}/restore', [RecycleBinController::class, 'restore'])->middleware('permission:recycle.restore')->name('recycle-bin.restore');
    Route::delete('/recycle-bin/{item}', [RecycleBinController::class, 'destroy'])->middleware('permission:recycle.restore')->name('recycle-bin.destroy');

    Route::get('/hak-akses', [AccessControlController::class, 'index'])->middleware('permission:access.view')->name('access.index');
    Route::post('/hak-akses', [AccessControlController::class, 'update'])->middleware('permission:access.update')->name('access.update');

    Route::get('/user-data', [UserController::class, 'index'])->middleware('permission:user.view')->name('users.index');
    Route::get('/user-data/list', [UserController::class, 'list'])->middleware('permission:user.view')->name('users.list');
    Route::get('/user-data/export/xlsx', [UserController::class, 'exportXlsx'])->middleware('permission:user.view')->name('users.export-xlsx');
    Route::post('/user-data/import/xlsx', [UserController::class, 'importXlsx'])->middleware('permission:user.create')->name('users.import-xlsx');
    Route::post('/user-data', [UserController::class, 'store'])->middleware('permission:user.create')->name('users.store');
    Route::post('/user-data/{user}/reset-password', [UserController::class, 'resetPassword'])->middleware('permission:user.reset-password')->name('users.reset-password');
    Route::delete('/user-data/{user}', [UserController::class, 'destroy'])->middleware('permission:user.delete')->name('users.destroy');

    Route::get('/activity-log', [ActivityLogController::class, 'index'])->middleware('permission:activity.view')->name('activity-logs.index');

    Route::get('/testimoni', [AdminTestimonialController::class, 'index'])->middleware('permission:testimonial.view')->name('testimonials.index');
    Route::post('/testimoni', [AdminTestimonialController::class, 'store'])->middleware('permission:testimonial.create')->name('testimonials.store');
    Route::delete('/testimoni/{testimonial}', [AdminTestimonialController::class, 'destroy'])->middleware('permission:testimonial.delete')->name('testimonials.destroy');

    Route::get('/faq', [AdminFaqController::class, 'index'])->middleware('permission:faq.view')->name('faqs.index');
    Route::post('/faq', [AdminFaqController::class, 'store'])->middleware('permission:faq.create')->name('faqs.store');
    Route::delete('/faq/{faq}', [AdminFaqController::class, 'destroy'])->middleware('permission:faq.delete')->name('faqs.destroy');

    Route::get('/settings', [SettingController::class, 'index'])->middleware('permission:setting.view')->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->middleware('permission:setting.update')->name('settings.update');
    Route::get('/jam-operasional', [OperationalHourController::class, 'index'])->middleware('permission:setting.view')->name('operational-hours.index');
    Route::post('/jam-operasional', [OperationalHourController::class, 'update'])->middleware('permission:setting.update')->name('operational-hours.update');
});
