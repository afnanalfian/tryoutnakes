<div>
    <h1 class="font-display text-4xl font-semibold tracking-tight text-navy-900">Kata sandi baru</h1>
    <p class="mt-2 text-sm text-slate-500">Buat kata sandi baru yang kuat untuk akunmu.</p>
    <form wire:submit="resetPassword" class="mt-8 space-y-4">
        <div>
            <label class="label">Email</label>
            <input type="email" wire:model="email" class="input @error('email') input-error @enderror">
            @error('email')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label">Kata sandi baru</label>
            <input type="password" wire:model="password" autocomplete="new-password" class="input @error('password') input-error @enderror">
            @error('password')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label">Ulangi kata sandi</label>
            <input type="password" wire:model="password_confirmation" autocomplete="new-password" class="input">
        </div>
        <button type="submit" class="btn-primary btn-lg w-full">Simpan Kata Sandi</button>
    </form>
</div>
