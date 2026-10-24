<?php

use App\Http\Controllers;
use App\Livewire;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publik (dapat diindeks mesin pencari)
|--------------------------------------------------------------------------
*/
Route::get('/', [Controllers\HomeController::class, 'index'])->name('home');
Route::get('/paket', [Controllers\CatalogController::class, 'packages'])->name('catalog.packages');
Route::get('/paket/{package:slug}', [Controllers\CatalogController::class, 'package'])->name('catalog.package');
Route::get('/kategori/{category:slug}', [Controllers\CatalogController::class, 'category'])->name('catalog.category');
Route::get('/beli-satuan', Livewire\Shop\SinglePicker::class)->name('catalog.single');

Route::get('/tentang', [Controllers\PageController::class, 'about'])->name('pages.about');
Route::get('/faq', [Controllers\PageController::class, 'faq'])->name('pages.faq');
Route::get('/syarat-ketentuan', [Controllers\PageController::class, 'terms'])->name('pages.terms');
Route::get('/kebijakan-privasi', [Controllers\PageController::class, 'privacy'])->name('pages.privacy');

Route::get('/sitemap.xml', [Controllers\SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [Controllers\SeoController::class, 'robots']);
Route::get('/{key}.txt', [Controllers\SeoController::class, 'indexNowKey'])->where('key', '[a-f0-9]{32}');

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/masuk', Livewire\Auth\Login::class)->name('login');
    Route::get('/daftar', Livewire\Auth\Register::class)->name('register');
    Route::get('/lupa-password', Livewire\Auth\ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', Livewire\Auth\ResetPassword::class)->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [Controllers\AuthController::class, 'logout'])->name('logout');

    Route::get('/email/verifikasi', [Controllers\AuthController::class, 'verifyNotice'])->name('verification.notice');
    Route::get('/email/verifikasi/{id}/{hash}', [Controllers\AuthController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verifikasi/kirim', [Controllers\AuthController::class, 'resendVerification'])
        ->middleware('throttle:3,1')->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| Area Peserta
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Belanja
    Route::get('/keranjang', Livewire\Shop\Cart::class)->name('cart');
    Route::get('/checkout', Livewire\Shop\Checkout::class)->middleware('verified.optional')->name('checkout');
    Route::get('/pesanan', [Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order:code}', Livewire\Shop\OrderPayment::class)->name('orders.show');
    Route::get('/pesanan/{order:code}/bukti', [Controllers\OrderController::class, 'proof'])->name('orders.proof');
    Route::get('/pesanan/{order:code}/invoice', [Controllers\OrderController::class, 'invoice'])->name('orders.invoice');

    // Tryout
    Route::get('/tryout-saya', [Controllers\MyTryoutController::class, 'index'])->name('my-tryouts.index');
    Route::get('/tryout-saya/{tryout:slug}', [Controllers\MyTryoutController::class, 'show'])->name('my-tryouts.show');
    Route::post('/tryout-saya/{tryout:slug}/mulai', [Controllers\ExamController::class, 'start'])
        ->middleware(['verified.optional', 'throttle:10,1'])->name('exam.start');

    Route::get('/ujian/{attempt}', [Controllers\ExamController::class, 'show'])->name('exam.show');
    Route::post('/ujian/{attempt}/jawab', [Controllers\ExamController::class, 'answer'])->middleware('throttle:answers')->name('exam.answer');
    Route::post('/ujian/{attempt}/selesai', [Controllers\ExamController::class, 'submit'])->name('exam.submit');
    Route::get('/ujian/{attempt}/waktu', [Controllers\ExamController::class, 'sync'])->middleware('throttle:answers')->name('exam.sync');

    Route::get('/hasil/{attempt}', [Controllers\ResultController::class, 'show'])->name('results.show');
    Route::get('/riwayat', [Controllers\ResultController::class, 'index'])->name('results.index');
    Route::get('/progres', [Controllers\ProgressController::class, 'index'])->name('progress');

    Route::get('/notifikasi', Livewire\User\Notifications::class)->name('notifications');
    Route::get('/notifikasi/{id}/buka', [Controllers\NotificationController::class, 'open'])->name('notifications.open');
    Route::get('/profil', Livewire\User\Profile::class)->name('profile');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Livewire\Admin\Dashboard::class)->name('dashboard');

    Route::get('/pengguna', Livewire\Admin\Users\Index::class)->name('users');
    Route::get('/pengguna/{user}', Livewire\Admin\Users\Show::class)->name('users.show');

    Route::get('/kategori', Livewire\Admin\Categories::class)->name('categories');

    Route::get('/bank-soal', Livewire\Admin\Questions\Index::class)->name('questions');
    Route::get('/bank-soal/buat', Livewire\Admin\Questions\Form::class)->name('questions.create');
    Route::get('/bank-soal/impor', Livewire\Admin\Questions\Import::class)->name('questions.import');
    Route::get('/bank-soal/template.csv', [Controllers\Admin\ExportController::class, 'questionTemplate'])->name('questions.template');
    Route::get('/bank-soal/{question}/edit', Livewire\Admin\Questions\Form::class)->name('questions.edit');

    Route::get('/tryout', Livewire\Admin\Tryouts\Index::class)->name('tryouts');
    Route::get('/tryout/buat', Livewire\Admin\Tryouts\Form::class)->name('tryouts.create');
    Route::get('/tryout/{tryout:id}/edit', Livewire\Admin\Tryouts\Form::class)->name('tryouts.edit');
    Route::get('/tryout/{tryout:id}/soal', Livewire\Admin\Tryouts\Questions::class)->name('tryouts.questions');
    Route::get('/tryout/{tryout:id}/hasil', Livewire\Admin\Tryouts\Results::class)->name('tryouts.results');
    Route::get('/tryout/{tryout:id}/hasil.csv', [Controllers\Admin\ExportController::class, 'tryoutResults'])->name('tryouts.export');

    Route::get('/paket', Livewire\Admin\Packages\Index::class)->name('packages');
    Route::get('/paket/buat', Livewire\Admin\Packages\Form::class)->name('packages.create');
    Route::get('/paket/{package:id}/edit', Livewire\Admin\Packages\Form::class)->name('packages.edit');

    Route::get('/pesanan', Livewire\Admin\Orders\Index::class)->name('orders');
    Route::get('/pesanan/{order:code}', Livewire\Admin\Orders\Show::class)->name('orders.show');

    Route::get('/metode-pembayaran', Livewire\Admin\PaymentMethods::class)->name('payments');
    Route::get('/banner', Livewire\Admin\Banners::class)->name('banners');
    Route::get('/faq', Livewire\Admin\Faqs::class)->name('faqs');
    Route::get('/pengaturan', Livewire\Admin\Settings::class)->name('settings');
    Route::get('/broadcast', Livewire\Admin\Broadcast::class)->name('broadcast');
});
