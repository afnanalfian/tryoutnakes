<div>
    <h1 class="font-display text-4xl font-semibold tracking-tight text-navy-900">Lupa kata sandi?</h1>
    <p class="mt-2 text-sm text-slate-500">Masukkan email akunmu. Kami akan mengirim tautan untuk mengatur ulang kata sandi.</p>
    @if($sent)
        <div class="mt-8 rounded-2xl border border-mint-200 bg-mint-50 p-5 text-sm text-mint-900">
            <p class="font-bold">Cek email kamu 📬</p>
            <p class="mt-1">Jika email terdaftar, tautan reset sudah dikirim. Periksa juga folder Spam/Promosi.</p>
        </div>
    @else
        <form wire:submit="send" class="mt-8 space-y-4">
            <div>
                <label class="label" for="email">Email</label>
                <input id="email" type="email" wire:model="email" class="input @error('email') input-error @enderror" placeholder="nama@email.com" autofocus>
                @error('email')<p class="error">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn-primary btn-lg w-full" wire:loading.attr="disabled">Kirim Tautan Reset</button>
        </form>
    @endif
    <p class="mt-6 text-center text-sm text-slate-500"><a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:underline">← Kembali ke halaman masuk</a></p>
</div>
