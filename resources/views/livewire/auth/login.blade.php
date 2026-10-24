<div>
    <p class="eyebrow">Selamat datang kembali</p>
    <h1 class="mt-2 font-display text-4xl font-semibold tracking-tight text-navy-900">Masuk ke akunmu</h1>
    <p class="mt-2 text-sm text-slate-500">Lanjutkan latihan dan pantau progresmu.</p>

    <form wire:submit="login" class="mt-8 space-y-4">
        <div>
            <label class="label" for="email">Email</label>
            <input id="email" type="email" wire:model="email" autocomplete="email" autofocus class="input @error('email') input-error @enderror" placeholder="nama@email.com">
            @error('email')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div x-data="{ show: false }">
            <div class="flex items-center justify-between">
                <label class="label" for="password">Kata sandi</label>
                <a href="{{ route('password.request') }}" class="mb-1.5 text-xs font-semibold text-brand-700 hover:underline">Lupa kata sandi?</a>
            </div>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" wire:model="password" autocomplete="current-password" class="input pr-11" placeholder="••••••••">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 hover:text-slate-600" :aria-label="show ? 'Sembunyikan' : 'Tampilkan'">
                    <x-icon name="eye" x-show="!show" class="h-5 w-5" /><x-icon name="eye-off" x-show="show" x-cloak class="h-5 w-5" />
                </button>
            </div>
            @error('password')<p class="error">{{ $message }}</p>@enderror
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" wire:model="remember" class="checkbox"> Ingat saya</label>
        <button type="submit" class="btn-primary btn-lg w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login">Masuk</span><span wire:loading wire:target="login">Memeriksa…</span>
        </button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500">Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-brand-700 hover:underline">Daftar gratis</a></p>
</div>
