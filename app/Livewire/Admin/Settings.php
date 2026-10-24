<?php

namespace App\Livewire\Admin;

use App\Services\MotivationService;
use App\Support\ImageService;
use App\Support\Settings as SettingStore;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class Settings extends Component
{
    use WithFileUploads;

    public string $tab = 'umum';
    public array $s = [];
    public $logo;

    protected array $keys = [
        'site_name', 'site_tagline', 'site_description', 'site_keywords', 'topbar_items', 'disclaimer',
        'contact_whatsapp', 'contact_email', 'contact_instagram', 'contact_tiktok', 'whatsapp_message',
        'verification_sla', 'order_expiry_hours', 'require_email_verification', 'mail_enabled',
        'google_site_verification', 'bing_site_verification', 'ga_measurement_id',
        'motivation_100', 'motivation_90', 'motivation_80', 'motivation_70', 'motivation_50', 'motivation_0',
    ];

    public function mount(SettingStore $store): void
    {
        $all = $store->all();
        foreach ($this->keys as $k) {
            $this->s[$k] = (string) ($all[$k] ?? '');
        }
    }

    public function save(SettingStore $store): void
    {
        $this->validate([
            's.site_name' => 'required|string|max:60',
            's.site_tagline' => 'required|string|max:120',
            's.site_description' => 'required|string|max:320',
            's.contact_whatsapp' => ['nullable', 'regex:/^(\+?62|0)8[0-9]{7,12}$/'],
            's.contact_email' => 'nullable|email',
            's.verification_sla' => 'required|string|max:40',
            's.order_expiry_hours' => 'required|integer|min:1|max:720',
            's.ga_measurement_id' => ['nullable', 'regex:/^G-[A-Z0-9]+$/'],
            'logo' => 'nullable|image|max:2048',
        ], ['s.contact_whatsapp.regex' => 'Format nomor WhatsApp tidak valid (contoh 081234567890).']);

        $values = $this->s;
        $values['contact_whatsapp'] = normalize_phone($values['contact_whatsapp']) ?? '';
        $values['require_email_verification'] = ($values['require_email_verification'] ?? '0') === '1' ? '1' : '0';
        $values['mail_enabled'] = ($values['mail_enabled'] ?? '1') === '1' ? '1' : '0';
        if ($this->logo) {
            ImageService::delete(setting('logo'));
            $values['logo'] = ImageService::store($this->logo, 'brand', 400, 'png');
        }
        $store->set($values);
        $this->reset('logo');
        $this->dispatch('toast', type: 'success', message: 'Pengaturan disimpan.');
    }

    public function removeLogo(SettingStore $store): void
    {
        ImageService::delete(setting('logo'));
        $store->set(['logo' => null]);
    }

    public function sendTestMail(): void
    {
        try {
            auth()->user()->notifyNow(new \App\Notifications\AppNotification('test', 'Tes email berhasil ✅', 'Konfigurasi SMTP (Brevo) sudah berfungsi dengan baik.', url('/'), 'Buka Situs'), ['mail']);
            $this->dispatch('toast', type: 'success', message: 'Email tes dikirim ke '.auth()->user()->email);
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Gagal mengirim: '.\Illuminate\Support\Str::limit($e->getMessage(), 160), timeout: 9000);
        }
    }

    public function render()
    {
        return view('livewire.admin.settings', ['ranges' => MotivationService::ranges()])->title('Pengaturan');
    }
}
