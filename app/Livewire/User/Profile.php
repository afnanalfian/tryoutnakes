<?php

namespace App\Livewire\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Profile extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $u = auth()->user();
        $this->name = $u->name;
        $this->email = $u->email;
        $this->phone = (string) $u->phone;
    }

    public function saveProfile(): void
    {
        $u = auth()->user();
        $this->email = strtolower(trim($this->email));
        $data = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($u->id)],
            'phone' => ['nullable', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
        ], ['phone.regex' => 'Nomor WhatsApp tidak valid.']);

        $emailChanged = $data['email'] !== $u->email;
        $u->fill(['name' => strip_tags($data['name']), 'email' => $data['email'], 'phone' => normalize_phone($data['phone'])]);
        if ($emailChanged) {
            $u->email_verified_at = null;
        }
        $u->save();
        if ($emailChanged && setting('require_email_verification') === '1') {
            $u->sendEmailVerificationNotification();
        }
        $this->dispatch('toast', type: 'success', message: 'Profil berhasil diperbarui.');
    }

    public function savePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], ['current_password.current_password' => 'Kata sandi saat ini salah.']);

        auth()->user()->update(['password' => $this->password]);
        $this->reset('current_password', 'password', 'password_confirmation');
        $this->dispatch('toast', type: 'success', message: 'Kata sandi berhasil diganti.');
    }

    public function render()
    {
        return view('livewire.user.profile')->title('Profil');
    }
}
