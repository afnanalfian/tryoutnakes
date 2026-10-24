<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $cfg = config('tryout.admin');
        $email = strtolower((string) $cfg['email']);
        if (! $cfg['password']) {
            $this->command?->warn('ADMIN_PASSWORD kosong — akun admin tidak dibuat. Gunakan: php artisan app:create-admin email@domain');

            return;
        }
        if (User::query()->where('email', $email)->exists()) {
            return;
        }
        $user = User::query()->create([
            'name' => $cfg['name'],
            'email' => $email,
            'password' => $cfg['password'],
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
    }
}
