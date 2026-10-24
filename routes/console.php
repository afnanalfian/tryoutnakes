<?php

use App\Models\User;
use App\Services\ExamService;
use App\Services\OrderService;
use Illuminate\Support\Facades\Artisan;

Artisan::command('attempts:finalize-expired', function (ExamService $exam) {
    $n = $exam->finalizeExpired();
    $n && $this->info("$n attempt kedaluwarsa dikumpulkan otomatis.");
})->purpose('Kumpulkan otomatis ujian yang waktunya habis');

Artisan::command('orders:expire', function (OrderService $orders) {
    $n = $orders->expireStale();
    $n && $this->info("$n pesanan ditandai kedaluwarsa.");
})->purpose('Tandai pesanan yang melewati batas waktu pembayaran');

Artisan::command('sitemap:warm', function () {
    app(\App\Http\Controllers\SeoController::class)->buildSitemap(true);
    $this->info('Sitemap diperbarui.');
})->purpose('Bangun ulang cache sitemap.xml');

Artisan::command('app:create-admin {email} {--name=Administrator} {--password=}', function (string $email) {
    $password = $this->option('password') ?: $this->secret('Password admin (min. 8 karakter)');
    if (strlen((string) $password) < 8) {
        $this->error('Password minimal 8 karakter.');

        return 1;
    }
    $user = User::query()->updateOrCreate(['email' => strtolower($email)], [
        'name' => $this->option('name'),
        'password' => $password,
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);
    $user->forceFill(['email_verified_at' => now()])->save();
    $this->info("Admin {$user->email} siap digunakan.");
})->purpose('Buat / reset akun admin');

Artisan::command('seo:indexnow', function () {
    $key = config('services.indexnow.key');
    if (! $key) {
        $this->warn('INDEXNOW_KEY belum diisi di .env — dilewati.');

        return 0;
    }
    $xml = app(\App\Http\Controllers\SeoController::class)->buildSitemap(true);
    preg_match_all('#<loc>(.*?)</loc>#', $xml, $m);
    $urls = array_map('html_entity_decode', $m[1]);
    $res = \Illuminate\Support\Facades\Http::timeout(20)->post('https://api.indexnow.org/indexnow', [
        'host' => parse_url(config('app.url'), PHP_URL_HOST),
        'key' => $key,
        'keyLocation' => rtrim(config('app.url'), '/').'/'.$key.'.txt',
        'urlList' => $urls,
    ]);
    $this->info('IndexNow: '.count($urls).' URL dikirim, status '.$res->status());
})->purpose('Kirim daftar URL publik ke IndexNow (Bing, Yandex, Seznam, Naver)');
