<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class ForgotPassword extends Component
{
    public string $email = '';
    public bool $sent = false;

    public function send(): void
    {
        $this->validate(['email' => 'required|email']);
        $key = 'forgot:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Terlalu banyak permintaan. Coba lagi nanti.');

            return;
        }
        RateLimiter::hit($key, 600);

        Password::sendResetLink(['email' => strtolower(trim($this->email))]);
        // Selalu tampilkan pesan sama agar tidak membocorkan email terdaftar.
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.auth.forgot-password')->title('Lupa Kata Sandi');
    }
}
