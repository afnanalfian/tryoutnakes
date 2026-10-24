<x-layouts.public>
    <section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:py-16">
        <x-breadcrumbs :items="[['Tentang Kami', route('pages.about')]]" />
        <h1 class="mt-4 font-display text-4xl font-semibold tracking-tight text-navy-900 sm:text-5xl">Tentang {{ setting('site_name') }}</h1>
        <div class="rich mt-6 space-y-4 text-lg text-slate-700">
            <p>{{ setting('site_name') }} adalah platform tryout online yang fokus membantu tenaga kesehatan Indonesia mempersiapkan seleksi CPNS — baik Seleksi Kompetensi Dasar (SKD) maupun Seleksi Kompetensi Bidang (SKB).</p>
            <p>Kami percaya latihan yang efektif bukan sekadar mengerjakan banyak soal, tetapi memahami <em>mengapa</em> sebuah jawaban benar. Karena itu setiap tryout dilengkapi pembahasan, analisis per subtes, peringkat, dan grafik perkembangan.</p>
        </div>
        <div class="mt-10 grid gap-4 sm:grid-cols-3">
            @foreach([['cap', 'Fokus Nakes', '8 profesi kesehatan dengan SKB masing-masing.'], ['shield', 'Penilaian Resmi', 'Mengikuti aturan TWK, TIU, TKP dan ambang batas.'], ['trending', 'Berbasis Data', 'Grafik progres & akurasi per topik.']] as [$i, $t, $d])
                <div class="card card-pad"><span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><x-icon :name="$i" /></span><p class="mt-3 font-bold text-navy-900">{{ $t }}</p><p class="mt-1 text-sm text-slate-600">{{ $d }}</p></div>
            @endforeach
        </div>
        <div class="mt-10 rounded-2xl bg-slate-50 p-5 text-sm text-slate-600">{{ setting('disclaimer') }}</div>
    </section>
</x-layouts.public>
