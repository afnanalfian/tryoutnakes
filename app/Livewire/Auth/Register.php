<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $agree = false;

    /** Honeypot anti-bot: harus tetap kosong. */
    public string $website = '';
    public int $startedAt = 0;

    public function mount(): void
    {
        $this->startedAt = time();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'agree' => ['accepted'],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk.',
            'phone.regex' => 'Masukkan nomor WhatsApp yang valid, mis. 081234567890.',
            'agree.accepted' => 'Kamu perlu menyetujui syarat & ketentuan.',
        ];
    }

    public function register()
    {
        $this->email = Str::lower(trim($this->email));
        $this->validate();

        // Bot biasanya mengisi honeypot atau submit terlalu cepat.
        if ($this->website !== '' || time() - $this->startedAt < 3) {
            $this->addError('email', 'Pendaftaran gagal. Silakan coba lagi.');

            return null;
        }

        $key = 'register:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->addError('email', 'Terlalu banyak pendaftaran dari jaringan ini. Coba lagi nanti.');

            return null;
        }
        RateLimiter::hit($key, 3600);

        $user = User::query()->create([
            'name' => strip_tags(trim($this->name)),
            'email' => $this->email,
            'phone' => normalize_phone($this->phone),
            'password' => $this->password,
            'role' => User::ROLE_USER,
            'is_active' => true,
        ]);

        Auth::login($user, true);
        session()->regenerate();

        if (setting('require_email_verification') === '1') {
            $user->sendEmailVerificationNotification();
        }

        $user->notify(new AppNotification(
            'welcome',
            'Selamat datang di '.setting('site_name').'! 👋',
            'Akunmu sudah aktif. Coba tryout gratis atau pilih paket sesuai profesimu, lalu pantau progresmu dari dashboard.',
            route('dashboard'),
            'Buka Dashboard',
        ));

        return $this->redirectIntended(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.register')->title('Daftar Akun');
    }
}
