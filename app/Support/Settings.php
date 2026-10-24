<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Settings
{
    protected ?array $values = null;

    /** Nilai bawaan jika admin belum mengisi. */
    public static function defaults(): array
    {
        return [
            'site_name' => config('app.name', 'NakesPrep'),
            'site_tagline' => 'Tryout SKD & SKB CPNS Tenaga Kesehatan',
            'site_description' => 'Platform tryout online SKD (TWK, TIU, TKP) dan SKB CPNS Tenaga Kesehatan: Perawat, Bidan, Farmasi, Gizi, Fisioterapi, Okupasi Terapi, Terapi Wicara, dan Teknik Gigi. Sistem CAT, pembahasan lengkap, peringkat, dan grafik progres.',
            'site_keywords' => 'tryout skb nakes, tryout skd cpns, tryout cpns tenaga kesehatan, soal skb perawat, soal skb bidan, tryout cat online',
            'logo' => null,
            'contact_whatsapp' => '6281234567890',
            'contact_email' => 'halo@bynama.id',
            'contact_instagram' => '',
            'contact_tiktok' => '',
            'whatsapp_message' => 'Halo Admin, saya {nama} ingin konfirmasi pembayaran pesanan {kode} sebesar {total}.',
            'verification_sla' => '1 × 3 jam',
            'order_expiry_hours' => 24,
            'qris_image' => null,
            'qris_name' => '',
            'qris_note' => 'Scan QRIS menggunakan aplikasi m-banking / e-wallet apa pun, lalu bayar sesuai nominal yang tertera.',
            'require_email_verification' => '0',
            'mail_enabled' => '1',
            'google_site_verification' => '',
            'bing_site_verification' => '',
            'ga_measurement_id' => '',
            'topbar_items' => "Tryout SKD & SKB CPNS Nakes\nSistem CAT seperti aslinya\nPembahasan lengkap\nPeringkat & grafik progres\nAkses digital 24 jam",
            'motivation_100' => 'SEMPURNA! 💯 Kamu menjawab semuanya dengan tepat. Pertahankan ritme ini — kursi CPNS sudah di depan mata!',
            'motivation_90' => 'Luar biasa! 🔥 Nilaimu sudah di level juara. Tinggal poles sedikit bagian yang terlewat, kamu siap tempur.',
            'motivation_80' => 'Keren banget! 🚀 Fondasimu kuat. Fokus ke soal yang salah dan nilaimu bisa tembus 90+.',
            'motivation_70' => 'Bagus! 💪 Kamu sudah di jalur yang benar. Konsisten latihan dan pelajari pembahasan, peningkatanmu akan terasa.',
            'motivation_50' => 'Lumayan, progresnya ada! 🌱 Pelajari pembahasan pelan-pelan, tandai materi yang sering keliru, lalu coba lagi.',
            'motivation_0' => 'Jangan menyerah! 🤍 Setiap ahli dulunya pemula. Baca pembahasan dengan teliti — tryout berikutnya pasti lebih baik.',
            'disclaimer' => 'Soal pada platform ini adalah latihan dan simulasi untuk membantu persiapan seleksi CPNS. Soal bukan merupakan soal resmi dari instansi pemerintah dan tidak menjamin soal yang sama akan muncul pada seleksi sebenarnya.',
        ];
    }

    public function all(): array
    {
        if ($this->values !== null) {
            return $this->values;
        }

        $stored = [];
        try {
            $stored = Cache::rememberForever('settings:all', function () {
                return Schema::hasTable('settings') ? Setting::query()->pluck('value', 'key')->all() : [];
            });
        } catch (\Throwable) {
            // DB belum siap (mis. saat instalasi awal)
        }

        return $this->values = array_merge(static::defaults(), array_filter($stored, fn ($v) => $v !== null));
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->all()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public function set(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => is_array($value) ? json_encode($value) : $value]);
        }
        $this->forget();
    }

    public function forget(): void
    {
        Cache::forget('settings:all');
        $this->values = null;
        PublicCache::flush();
    }

    public function bool(string $key): bool
    {
        return filter_var($this->get($key), FILTER_VALIDATE_BOOLEAN);
    }
}
