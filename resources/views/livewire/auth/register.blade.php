<div>
    <p class="eyebrow">Gratis, 1 menit</p>
    <h1 class="mt-2 font-display text-4xl font-semibold tracking-tight text-navy-900">Buat akun baru</h1>
    <p class="mt-2 text-sm text-slate-500">Mulai perjalananmu menuju ASN tenaga kesehatan.</p>

    <form wire:submit="register" class="mt-8 space-y-4" x-data="{ show: false }">
        <div class="hidden" aria-hidden="true"><label>Website <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label></div>
        <div>
            <label class="label" for="name">Nama lengkap</label>
            <input id="name" type="text" wire:model="name" autocomplete="name" class="input @error('name') input-error @enderror" placeholder="Nama sesuai KTP">
            @error('name')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label" for="email">Email</label>
                <input id="email" type="email" wire:model="email" autocomplete="email" class="input @error('email') input-error @enderror" placeholder="nama@email.com">
                @error('email')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="phone">No. WhatsApp</label>
                <input id="phone" type="tel" inputmode="tel" wire:model="phone" autocomplete="tel" class="input @error('phone') input-error @enderror" placeholder="0812xxxxxxx">
                @error('phone')<p class="error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="label" for="password">Kata sandi</label>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" wire:model="password" autocomplete="new-password" class="input pr-11 @error('password') input-error @enderror" placeholder="Min. 8 karakter, huruf & angka">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400"><x-icon name="eye" x-show="!show" class="h-5 w-5" /><x-icon name="eye-off" x-show="show" x-cloak class="h-5 w-5" /></button>
            </div>
            @error('password')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="password_confirmation">Ulangi kata sandi</label>
            <input id="password_confirmation" :type="show ? 'text' : 'password'" wire:model="password_confirmation" autocomplete="new-password" class="input">
        </div>
        <label class="flex items-start gap-2.5 text-sm text-slate-600">
            <input type="checkbox" wire:model="agree" class="checkbox mt-0.5">
            <span>Saya setuju dengan <a href="{{ route('pages.terms') }}" target="_blank" class="font-semibold text-brand-700 underline">Syarat &amp; Ketentuan</a> serta <a href="{{ route('pages.privacy') }}" target="_blank" class="font-semibold text-brand-700 underline">Kebijakan Privasi</a>.</span>
        </label>
        @error('agree')<p class="error -mt-2">{{ $message }}</p>@enderror
        <button type="submit" class="btn-primary btn-lg w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="register">Daftar Sekarang</span><span wire:loading wire:target="register">Membuat akun…</span>
        </button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500">Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:underline">Masuk</a></p>
</div>
