<div class="mx-auto max-w-4xl">
    <a href="{{ route('admin.questions', ['kategori' => $category_id]) }}" wire:navigate class="btn-ghost -ml-3 mb-3"><x-icon name="arrow-left" class="h-4 w-4" /> Bank Soal</a>
    <x-page-head title="Impor Soal dari CSV" subtitle="Siapkan soal di Excel / Google Sheets, simpan sebagai CSV, lalu unggah di sini.">
        <a href="{{ route('admin.questions.template') }}" class="btn-outline"><x-icon name="download" class="h-4 w-4" /> Unduh Template</a>
    </x-page-head>

    <div class="card card-pad space-y-5">
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="label">Kategori tujuan</label><select wire:model.live="category_id" class="input">@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->label() }}</option>@endforeach</select></div>
            <div><label class="label">Tambahkan juga ke tryout (opsional)</label><select wire:model="tryout_id" class="input"><option value="">— Tidak —</option>@foreach($tryouts as $t)<option value="{{ $t->id }}">{{ $t->title }}</option>@endforeach</select></div>
        </div>
        <div>
            <label class="label">File CSV (pemisah koma atau titik koma, UTF-8)</label>
            <input type="file" wire:model="file" accept=".csv,text/csv" class="input p-2">
            <div wire:loading wire:target="file" class="help">Membaca file…</div>
            @error('file')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="rounded-2xl bg-slate-50 p-4 text-xs leading-relaxed text-slate-600">
            <p class="font-bold text-navy-900">Format kolom</p>
            <p class="mt-1"><code>subtes</code> (twk/tiu/tkp untuk SKD, skb untuk SKB) · <code>topik</code> · <code>soal</code> · <code>opsi_a</code> s.d. <code>opsi_e</code> · <code>kunci</code> (A–E, untuk selain TKP) · <code>bobot_a</code> s.d. <code>bobot_e</code> (1–5, khusus TKP) · <code>pembahasan</code> · <code>tingkat</code> (1/2/3).</p>
            <p class="mt-1">Rumus boleh ditulis langsung dengan LaTeX: <code>\(\frac{1}{2}\)</code>. Gambar dapat ditambahkan setelah impor lewat menu edit soal.</p>
        </div>
    </div>

    @if($parsed)
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <x-stat label="Siap diimpor" :value="count($rows)" icon="check-circle" tone="mint" />
            <x-stat label="Baris bermasalah" :value="count($problems)" icon="warning" :tone="count($problems) ? 'rose' : 'navy'" />
        </div>
        @if($problems)
            <div class="card mt-4 max-h-64 overflow-y-auto p-4 text-sm text-rose-700">
                @foreach($problems as $p)<p>• {{ $p }}</p>@endforeach
            </div>
        @endif
        @if($rows)
            <div class="card mt-4 overflow-hidden">
                <p class="border-b border-slate-100 px-5 py-3 text-sm font-bold text-navy-900">Pratinjau (5 baris pertama)</p>
                @foreach(array_slice($rows, 0, 5) as $r)
                    <div class="border-b border-slate-100 px-5 py-3 text-sm last:border-0" data-math>
                        <p class="text-xs font-bold uppercase text-brand-700">{{ $r['subtest'] }} @if($r['topic'])· {{ $r['topic'] }}@endif</p>
                        <div class="rich mt-1">{!! rich($r['text']) !!}</div>
                        <p class="mt-1 text-xs text-slate-500">{{ count($r['options']) }} opsi · {{ $r['subtest'] === 'tkp' ? 'bobot '.collect($r['options'])->pluck('weight')->implode('-') : 'kunci '.chr(65 + $r['correct']) }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 flex justify-end"><button wire:click="import" wire:confirm="Impor {{ count($rows) }} soal?" class="btn-primary btn-lg" wire:loading.attr="disabled"><x-icon name="upload" class="h-4 w-4" /> Impor {{ count($rows) }} Soal</button></div>
        @endif
    @endif
</div>
