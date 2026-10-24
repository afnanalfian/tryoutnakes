<x-layouts.app :title="$tryout->title">
    @php($cat = $tryout->category)
    <a href="{{ route('my-tryouts.index', ['kategori' => $cat->slug]) }}" wire:navigate class="btn-ghost -ml-3 mb-3"><x-icon name="arrow-left" class="h-4 w-4" /> Tryout Saya</a>
    <div class="grid gap-6 lg:grid-cols-[1.3fr_.9fr]">
        <div class="space-y-6">
            <div class="card overflow-hidden">
                <img src="{{ $tryout->imageUrl() }}" alt="{{ $tryout->title }}" class="aspect-[21/9] w-full object-cover" width="1200" height="514">
                <div class="p-6">
                    <x-category-badge :category="$cat" />
                    <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-navy-900 sm:text-3xl">{{ $tryout->title }}</h1>
                    @if($tryout->description)<p class="mt-2 text-slate-600">{{ $tryout->description }}</p>@endif
                    <dl class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach([['document', 'Jumlah soal', $tryout->questions_count], ['clock', 'Durasi', $tryout->duration_minutes.' menit'], ['chart', $cat->isSkd() ? 'Nilai maks' : 'Skala nilai', $cat->isSkd() ? '550' : score_format($tryout->scale_max ?? 100)], ['users', 'Peserta', $participants]] as [$i, $l, $v])
                            <div class="rounded-2xl bg-slate-50 p-3"><dt class="flex items-center gap-1.5 text-xs text-slate-500"><x-icon :name="$i" class="h-4 w-4" /> {{ $l }}</dt><dd class="mt-1 text-lg font-extrabold text-navy-900">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            </div>

            <div class="card card-pad">
                <h2 class="font-bold text-navy-900">Petunjuk pengerjaan</h2>
                <ul class="mt-4 space-y-3 text-sm text-slate-600">
                    @if($cat->isSkd())
                        <li class="flex gap-3"><x-icon name="check-circle" class="h-5 w-5 shrink-0 text-mint-600" /> <span>Urutan soal: TWK ({{ $composition['twk'] ?? 0 }}) → TIU ({{ $composition['tiu'] ?? 0 }}) → TKP ({{ $composition['tkp'] ?? 0 }}). Kamu bebas berpindah nomor.</span></li>
                        <li class="flex gap-3"><x-icon name="check-circle" class="h-5 w-5 shrink-0 text-mint-600" /> <span>TWK &amp; TIU: benar 5, salah/kosong 0. TKP: setiap opsi bernilai 1–5, tidak ada jawaban salah.</span></li>
                        <li class="flex gap-3"><x-icon name="check-circle" class="h-5 w-5 shrink-0 text-mint-600" /> <span>Ambang batas: TWK {{ $tryout->passingFor('twk') ?? 65 }}, TIU {{ $tryout->passingFor('tiu') ?? 80 }}, TKP {{ $tryout->passingFor('tkp') ?? 166 }}.</span></li>
                    @else
                        <li class="flex gap-3"><x-icon name="check-circle" class="h-5 w-5 shrink-0 text-mint-600" /> <span>Nilai = jumlah benar ÷ {{ $tryout->questions_count }} soal × skala {{ score_format($tryout->scale_max ?? 100) }}. Jawaban salah/kosong bernilai 0.</span></li>
                    @endif
                    <li class="flex gap-3"><x-icon name="check-circle" class="h-5 w-5 shrink-0 text-mint-600" /> <span>Waktu berjalan sejak tombol <strong>Mulai</strong> ditekan dan tidak berhenti walau halaman ditutup. Saat habis, jawaban dikumpulkan otomatis.</span></li>
                    <li class="flex gap-3"><x-icon name="check-circle" class="h-5 w-5 shrink-0 text-mint-600" /> <span>Jawaban tersimpan otomatis. Gunakan tombol <strong>Ragu-ragu</strong> untuk menandai soal yang ingin dicek ulang.</span></li>
                    <li class="flex gap-3"><x-icon name="check-circle" class="h-5 w-5 shrink-0 text-mint-600" /> <span>Pintasan keyboard: ← → pindah soal, A–E pilih jawaban, R tandai ragu.</span></li>
                </ul>
            </div>
        </div>

        <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start">
            <div class="card card-pad">
                @if(! $hasAccess)
                    <p class="font-bold text-navy-900">Kamu belum memiliki tryout ini</p>
                    <p class="mt-1 text-sm text-slate-500">Beli satuan seharga {{ rupiah($tryout->price()) }} atau pilih paket yang memuatnya.</p>
                    <a href="{{ route('catalog.single', ['jenis' => $cat->type, 'kategori' => $cat->slug]) }}" class="btn-brand btn-lg mt-5 w-full">Beli Tryout</a>
                @elseif($running)
                    <p class="font-bold text-navy-900">Tryout sedang berlangsung</p>
                    <p class="mt-1 text-sm text-slate-500">Sisa waktu ±{{ (int) ceil($running->remainingSeconds() / 60) }} menit.</p>
                    <a href="{{ route('exam.show', $running) }}" class="btn btn-lg mt-5 w-full bg-amber-500 text-white hover:bg-amber-600"><x-icon name="play" class="h-4 w-4" /> Lanjutkan</a>
                @elseif($left === 0)
                    <p class="font-bold text-navy-900">Kesempatan habis</p>
                    <p class="mt-1 text-sm text-slate-500">Kamu sudah menggunakan seluruh kesempatan mengerjakan tryout ini. Pelajari pembahasannya, ya!</p>
                @else
                    <p class="font-bold text-navy-900">Siap mulai?</p>
                    <p class="mt-1 text-sm text-slate-500">Pastikan koneksi stabil dan kamu punya waktu {{ $tryout->duration_minutes }} menit tanpa gangguan.</p>
                    <form method="POST" action="{{ route('exam.start', $tryout) }}" class="mt-5" x-data="{ busy: false }" @submit="busy = true">
                        @csrf
                        <button class="btn-brand btn-lg w-full" :disabled="busy"><x-icon name="play" class="h-4 w-4" /> <span x-text="busy ? 'Menyiapkan soal…' : '{{ $history->isEmpty() ? 'Mulai Tryout' : 'Kerjakan Ulang' }}'"></span></button>
                    </form>
                    <p class="mt-3 text-center text-xs text-slate-500">{{ $left === null ? 'Tanpa batas pengerjaan' : 'Sisa kesempatan: '.$left.'x' }} · Peringkat dihitung dari percobaan pertama</p>
                @endif
            </div>

            @if($history->isNotEmpty())
                <div class="card">
                    <p class="border-b border-slate-100 px-5 py-4 font-bold text-navy-900">Riwayat pengerjaan</p>
                    @foreach($history as $a)
                        <a href="{{ $a->isSubmitted() ? route('results.show', $a) : route('exam.show', $a) }}" class="flex items-center justify-between gap-3 px-5 py-3 text-sm hover:bg-slate-50">
                            <span><span class="font-semibold text-navy-900">Percobaan #{{ $a->attempt_no }}</span><span class="block text-xs text-slate-500">{{ $a->started_at->translatedFormat('d M Y, H:i') }}</span></span>
                            @if($a->isSubmitted())<span class="font-extrabold text-navy-900">{{ score_format($a->score) }}</span>@else<span class="badge bg-amber-100 text-amber-800">Berjalan</span>@endif
                        </a>
                    @endforeach
                </div>
            @endif
        </aside>
    </div>
</x-layouts.app>
