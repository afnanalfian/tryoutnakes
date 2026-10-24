<div>
    <a href="{{ route('admin.tryouts') }}" wire:navigate class="btn-ghost -ml-3 mb-3"><x-icon name="arrow-left" class="h-4 w-4" /> Tryout</a>
    <form wire:submit="save" class="grid gap-6 xl:grid-cols-[1fr_380px]">
        <div class="space-y-6">
            <div class="card card-pad grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="label">Kategori</label>
                    <select wire:model.live="category_id" class="input" @disabled($tryout && $tryout->attempts()->exists())>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->label() }}</option>@endforeach</select>
                    @if($tryout && $tryout->attempts()->exists())<p class="help">Kategori terkunci karena sudah ada peserta yang mengerjakan.</p>@endif
                </div>
                <div class="sm:col-span-2"><label class="label">Judul tryout</label><input wire:model.live.debounce.400ms="title" class="input" placeholder="mis. Tryout SKB Bidan #1">@error('title')<p class="error">{{ $message }}</p>@enderror</div>
                <div class="sm:col-span-2"><label class="label">Slug URL</label><input wire:model="slug" class="input">@error('slug')<p class="error">{{ $message }}</p>@enderror</div>
                <div class="sm:col-span-2"><label class="label">Deskripsi singkat</label><textarea wire:model="description" rows="3" class="input"></textarea></div>
                <div class="sm:col-span-2">
                    <label class="label">Gambar kartu (opsional — kosong = template kategori)</label>
                    <div class="flex items-center gap-4">
                        @if($image)<img src="{{ $image->temporaryUrl() }}" class="h-16 rounded-lg">@elseif($tryout?->image)<img src="{{ $tryout->imageUrl() }}" class="h-16 rounded-lg"><button type="button" wire:click="removeImage" class="btn-ghost btn-sm text-rose-600">Hapus</button>@elseif($cat)<img src="{{ $cat->imageUrl() }}" class="h-16 rounded-lg opacity-70">@endif
                        <input type="file" wire:model="image" accept="image/*" class="input p-2">
                    </div>
                    @error('image')<p class="error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="card card-pad">
                <p class="font-bold text-navy-900">Penilaian</p>
                @if($cat?->isSkd())
                    <p class="mt-1 text-sm text-slate-500">TWK & TIU benar = 5, salah/kosong = 0. TKP = bobot opsi (1–5). Atur nilai ambang batas (passing grade):</p>
                    <div class="mt-4 grid grid-cols-3 gap-3">
                        @foreach(['twk' => 'TWK', 'tiu' => 'TIU', 'tkp' => 'TKP'] as $k => $l)
                            <div><label class="label">{{ $l }}</label><input type="number" min="0" wire:model="pg.{{ $k }}" class="input">@error('pg.'.$k)<p class="error">{{ $message }}</p>@enderror</div>
                        @endforeach
                    </div>
                    <p class="help">Default resmi: TWK 65 · TIU 80 · TKP 166.</p>
                @else
                    <p class="mt-1 text-sm text-slate-500">Nilai = jumlah benar ÷ jumlah soal × <strong>skala</strong>. Contoh skala 400 dengan 50 benar dari 100 soal → nilai 200.</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div><label class="label">Skala nilai maksimum</label><input type="number" step="0.01" min="1" wire:model="scale_max" class="input text-lg font-bold">@error('scale_max')<p class="error">{{ $message }}</p>@enderror</div>
                        <div><label class="label">Nilai lulus (opsional)</label><input type="number" step="0.01" min="0" wire:model="pg.score" class="input" placeholder="kosongkan jika tidak ada"></div>
                    </div>
                @endif
            </div>
        </div>

        <aside class="space-y-4 xl:sticky xl:top-24 xl:self-start">
            <div class="card card-pad space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="label">Durasi (menit)</label><input type="number" min="1" wire:model="duration_minutes" class="input">@error('duration_minutes')<p class="error">{{ $message }}</p>@enderror</div>
                    <div><label class="label">Maks. percobaan</label><input type="number" min="0" wire:model="max_attempts" class="input"><p class="help">0 = tanpa batas</p></div>
                </div>
                <div><label class="label">Urutan tampil</label><input type="number" min="0" wire:model="sort_order" class="input"></div>
                <div class="space-y-2.5 rounded-xl bg-slate-50 p-3 text-sm">
                    <label class="flex items-center gap-2"><input type="checkbox" wire:model="is_published" class="checkbox"> Terbitkan</label>
                    <label class="flex items-center gap-2"><input type="checkbox" wire:model="is_free" class="checkbox"> Gratis untuk semua peserta</label>
                    <label class="flex items-center gap-2"><input type="checkbox" wire:model="shuffle_questions" class="checkbox"> Acak urutan soal {{ $cat?->isSkd() ? '(dalam tiap subtes)' : '' }}</label>
                    <label class="flex items-center gap-2"><input type="checkbox" wire:model="shuffle_options" class="checkbox"> Acak urutan opsi</label>
                    <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_explanation" class="checkbox"> Tampilkan pembahasan setelah selesai</label>
                </div>
                <button class="btn-primary btn-lg w-full" wire:loading.attr="disabled">Simpan</button>
                @if($tryout)<button type="button" wire:click="save(true)" class="btn-outline w-full">Simpan & Kelola Soal</button>@endif
            </div>
        </aside>
    </form>
</div>
