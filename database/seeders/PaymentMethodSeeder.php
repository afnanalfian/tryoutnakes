<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

/** Contoh metode pembayaran (nonaktif). Ubah & aktifkan dari panel admin. */
class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        if (PaymentMethod::query()->exists()) {
            return;
        }
        PaymentMethod::query()->create(['name' => 'Bank BRI', 'account_number' => '0000-01-000000-00-0', 'account_holder' => 'Nama Pemilik Rekening', 'description' => 'Silakan transfer melalui rekening BRI berikut sesuai nominal tagihan.', 'is_active' => app()->environment('local'), 'sort_order' => 1]);
        PaymentMethod::query()->create(['name' => 'GoPay', 'account_number' => '0812-0000-0000', 'account_holder' => 'Nama Pemilik Akun', 'description' => 'Silakan kirimkan pembayaran sesuai nominal ke nomor GoPay yang tertera.', 'is_active' => app()->environment('local'), 'sort_order' => 2]);
    }
}
