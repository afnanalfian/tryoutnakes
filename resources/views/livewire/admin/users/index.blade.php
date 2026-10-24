<div>
    <x-page-head title="Manajemen Pengguna" subtitle="Kelola akun peserta & admin, reset kata sandi, dan beri akses tryout.">
        <button wire:click="create" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> Pengguna</button>
    </x-page-head>
    <div class="card mb-4 grid gap-3 p-3 sm:grid-cols-[1fr_auto_auto]">
        <div class="relative"><x-icon name="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input wire:model.live.debounce.400ms="search" class="input pl-9" placeholder="Cari nama, email, atau no. HP"></div>
        <select wire:model.live="role" class="input"><option value="">Semua peran</option><option value="user">Peserta</option><option value="admin">Admin</option></select>
        <select wire:model.live="status" class="input"><option value="">Semua status</option><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select>
    </div>
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Pengguna</th><th>Kontak</th><th>Peran</th><th>Tryout</th><th>Ujian</th><th>Terdaftar</th><th></th></tr></thead>
                <tbody>
                    @foreach($users as $u)
                        <tr wire:key="u-{{ $u->id }}">
                            <td><a href="{{ route('admin.users.show', $u) }}" wire:navigate class="font-semibold text-navy-900 hover:text-brand-700">{{ $u->name }}</a> @unless($u->is_active)<span class="badge bg-rose-50 text-rose-600">Nonaktif</span>@endunless<p class="text-xs text-slate-500">{{ $u->email }}</p></td>
                            <td class="text-xs">@if($u->phone)<a href="{{ wa_link($u->phone) }}" target="_blank" class="text-mint-700 hover:underline">+{{ $u->phone }}</a>@else – @endif</td>
                            <td><span class="badge {{ $u->isAdmin() ? 'bg-navy-900 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $u->isAdmin() ? 'Admin' : 'Peserta' }}</span></td>
                            <td>{{ $u->entitlements_count }}</td>
                            <td>{{ $u->attempts_count }}</td>
                            <td class="whitespace-nowrap text-xs text-slate-500">{{ $u->created_at->format('d/m/Y') }}</td>
                            <td class="whitespace-nowrap text-right">
                                <div x-data="{ o: false }" class="relative inline-block" @click.outside="o = false">
                                    <button @click="o = !o" class="btn-ghost btn-sm">Aksi <x-icon name="chevron-down" class="h-3.5 w-3.5" /></button>
                                    <div x-show="o" x-cloak class="absolute right-0 z-20 mt-1 w-48 rounded-xl border border-slate-200 bg-white p-1 text-left shadow-lift">
                                        <a href="{{ route('admin.users.show', $u) }}" wire:navigate class="block rounded-lg px-3 py-2 text-sm hover:bg-slate-50">Detail & akses</a>
                                        <button wire:click="edit({{ $u->id }})" @click="o = false" class="block w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-slate-50">Edit</button>
                                        <button wire:click="resetPassword({{ $u->id }})" wire:confirm="Buat kata sandi baru untuk {{ $u->name }}?" @click="o = false" class="block w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-slate-50">Reset kata sandi</button>
                                        @if($u->id !== auth()->id())
                                            <button wire:click="toggleActive({{ $u->id }})" @click="o = false" class="block w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-slate-50">{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                            <button wire:click="delete({{ $u->id }})" wire:confirm="Hapus {{ $u->name }} beserta seluruh datanya? Tindakan ini tidak bisa dibatalkan." @click="o = false" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-rose-600 hover:bg-rose-50">Hapus</button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($users->isEmpty())<p class="p-10 text-center text-sm text-slate-500">Tidak ada pengguna ditemukan.</p>@endif
    </div>
    <div class="mt-4">{{ $users->links() }}</div>

    <x-modal name="user-form" :title="$editingId ? 'Edit Pengguna' : 'Pengguna Baru'" maxWidth="xl">
        <form wire:submit="save" class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2"><label class="label">Nama</label><input wire:model="form.name" class="input">@error('form.name')<p class="error">{{ $message }}</p>@enderror</div>
            <div><label class="label">Email</label><input type="email" wire:model="form.email" class="input">@error('form.email')<p class="error">{{ $message }}</p>@enderror</div>
            <div><label class="label">No. WhatsApp</label><input wire:model="form.phone" class="input"></div>
            <div><label class="label">Peran</label><select wire:model="form.role" class="input"><option value="user">Peserta</option><option value="admin">Admin</option></select>@error('form.role')<p class="error">{{ $message }}</p>@enderror</div>
            <div><label class="label">Kata sandi {{ $editingId ? '(kosongkan jika tidak diubah)' : '' }}</label><input type="text" wire:model="form.password" class="input" autocomplete="new-password">@error('form.password')<p class="error">{{ $message }}</p>@enderror</div>
            <label class="flex items-center gap-2 text-sm sm:col-span-2"><input type="checkbox" wire:model="form.is_active" class="checkbox"> Akun aktif</label>
            <div class="flex justify-end gap-2 sm:col-span-2"><button type="button" @click="$dispatch('close-modal', 'user-form')" class="btn-outline">Batal</button><button class="btn-primary">Simpan</button></div>
        </form>
    </x-modal>
    <x-modal name="password-result" title="Kata sandi baru" maxWidth="md">
        <p class="text-sm text-slate-600">Sampaikan kata sandi ini kepada pengguna secara pribadi. Sarankan untuk menggantinya setelah masuk.</p>
        <p class="mt-4 rounded-xl bg-slate-50 p-4 font-mono text-sm font-bold text-navy-900">{{ $generatedPassword }}</p>
    </x-modal>
</div>
