<div class="mx-auto max-w-3xl space-y-6">
    <x-page-head title="Profil & Keamanan" subtitle="Kelola data akun dan kata sandimu." />
    <form wire:submit="saveProfile" class="card card-pad">
        <p class="font-bold text-navy-900">Data Diri</p>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="label">Nama lengkap</label>
                <input type="text" wire:model="name" class="input">
                @error('name')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label">Email</label>
                <input type="email" wire:model="email" class="input">
                @error('email')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label">No. WhatsApp</label>
                <input type="tel" wire:model="phone" class="input" placeholder="0812xxxxxxx">
                @error('phone')<p class="error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="mt-5 flex justify-end"><button class="btn-primary">Simpan Profil</button></div>
    </form>
    <form wire:submit="savePassword" class="card card-pad">
        <p class="font-bold text-navy-900">Ganti Kata Sandi</p>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="label">Kata sandi saat ini</label>
                <input type="password" wire:model="current_password" class="input" autocomplete="current-password">
                @error('current_password')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label">Kata sandi baru</label>
                <input type="password" wire:model="password" class="input" autocomplete="new-password">
                @error('password')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label">Ulangi kata sandi baru</label>
                <input type="password" wire:model="password_confirmation" class="input" autocomplete="new-password">
            </div>
        </div>
        <div class="mt-5 flex justify-end"><button class="btn-primary">Ganti Kata Sandi</button></div>
    </form>
</div>
