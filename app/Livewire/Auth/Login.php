<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = true;

    public function login()
    {
        $this->validate();
        $key = Str::lower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.');

            return null;
        }

        if (! Auth::attempt(['email' => Str::lower(trim($this->email)), 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key, 60);
            $this->addError('email', 'Email atau kata sandi salah.');

            return null;
        }

        RateLimiter::clear($key);
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $this->addError('email', 'Akunmu dinonaktifkan. Hubungi admin.');

            return null;
        }

        session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        $default = $user->isAdmin() ? route('admin.dashboard') : route('dashboard');

        return $this->redirectIntended($default);
    }

    public function render()
    {
        return view('livewire.auth.login')->title('Masuk');
    }
}
