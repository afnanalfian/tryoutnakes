<x-layouts.auth title="Verifikasi Email">
    <div class="text-center">
        <span class="inline-flex h-16 w-16 items-center justify-center rounded-3xl bg-brand-50 text-brand-600"><x-icon name="mail" class="h-8 w-8" /></span>
        <h1 class="mt-5 font-display text-3xl font-semibold text-navy-900">Verifikasi emailmu</h1>
        <p class="mt-2 text-sm text-slate-500">Kami telah mengirim tautan verifikasi ke <strong>{{ auth()->user()->email }}</strong>. Klik tautan tersebut untuk melanjutkan pembelian dan pengerjaan tryout.</p>
        <form method="POST" action="{{ route('verification.send') }}" class="mt-6">@csrf<button class="btn-primary w-full">Kirim Ulang Email Verifikasi</button></form>
        <a href="{{ route('profile') }}" class="btn-ghost mt-2 w-full">Salah email? Ubah di profil</a>
    </div>
</x-layouts.auth>
