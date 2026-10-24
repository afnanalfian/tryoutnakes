{{-- Cheat sheet MathQuill (klik contoh untuk menyisipkan ke editor rumus) --}}
@php
$groups = [
    'Akar & Pangkat' => [['√x', '\\sqrt{x}', 'ketik: sqrt'], ['ⁿ√x', '\\sqrt[n]{x}', 'ketik: nthroot'], ['x²', 'x^2', 'ketik: x^2'], ['xⁿ', 'x^{n}', 'ketik: x^n'], ['xᵢ', 'x_i', 'ketik: x_i'], ['xᵢⱼ', 'x_{i,j}', 'ketik: x_{i,j}']],
    'Pecahan & Kurung' => [['a/b', '\\frac{a}{b}', 'ketik: a/b'], ['(x)', '\\left(x\\right)', 'ketik: ('], ['[x]', '\\left[x\\right]', 'ketik: ['], ['{x}', '\\left\\{x\\right\\}', 'ketik: \\{'], ['|x|', '\\left|x\\right|', 'ketik: |'], ['a·b', 'a\\cdot b', 'ketik: \\cdot'], ['a×b', 'a\\times b', 'ketik: \\times'], ['a÷b', 'a\\div b', 'ketik: \\div'], ['±', '\\pm', 'ketik: \\pm']],
    'Kalkulus & Deret' => [['∑', '\\sum_{i=1}^{n}', 'ketik: sum'], ['∫', '\\int_{a}^{b}', 'ketik: int'], ['lim', '\\lim_{x\\to0}', 'ketik: lim'], ['∞', '\\infty', 'ketik: infty'], ['Δ', '\\Delta', 'ketik: \\Delta']],
    'Trigonometri & Log' => [['sin x', '\\sin x', 'ketik: sin'], ['cos x', '\\cos x', 'ketik: cos'], ['tan x', '\\tan x', 'ketik: tan'], ['log x', '\\log x', 'ketik: log'], ['ᵃlog b', '{}^{a}\\log b', 'ketik: ^a lalu log'], ['ln x', '\\ln x', 'ketik: ln'], ['30°', '30^{\\circ}', 'ketik: 30^\\circ']],
    'Relasi' => [['≠', '\\ne', 'ketik: \\ne'], ['≤', '\\le', 'ketik: <='], ['≥', '\\ge', 'ketik: >='], ['≈', '\\approx', 'ketik: \\approx'], ['→', '\\to', 'ketik: \\to'], ['⇒', '\\Rightarrow', 'ketik: \\Rightarrow'], ['∴', '\\therefore', 'ketik: \\therefore']],
    'Himpunan' => [['∈', '\\in', 'ketik: \\in'], ['∉', '\\notin', 'ketik: \\notin'], ['⊂', '\\subset', 'ketik: \\subset'], ['∪', '\\cup', 'ketik: \\cup'], ['∩', '\\cap', 'ketik: \\cap'], ['∅', '\\emptyset', 'ketik: \\emptyset'], ['ℝ', '\\mathbb{R}', 'ketik: \\mathbb{R}']],
    'Huruf Yunani' => [['α', '\\alpha', 'alpha'], ['β', '\\beta', 'beta'], ['γ', '\\gamma', 'gamma'], ['θ', '\\theta', 'theta'], ['π', '\\pi', 'pi'], ['μ', '\\mu', '\\mu'], ['σ', '\\sigma', '\\sigma'], ['λ', '\\lambda', '\\lambda']],
    'Statistik & Lainnya' => [['x̄', '\\overline{x}', 'ketik: \\overline'], ['%', '\\%', 'ketik: %'], ['nCr', 'C_{r}^{n}', 'kombinasi'], ['n!', 'n!', 'faktorial']],
];
@endphp
<div class="flex h-full flex-col">
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
        <p class="flex items-center gap-2 font-bold text-navy-900"><x-icon name="bolt" class="h-5 w-5 text-brand-600" /> MathQuill · Cheat Sheet</p>
        <button type="button" @click="docsOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100"><x-icon name="x" /></button>
    </div>
    <div class="flex-1 space-y-3 overflow-y-auto p-4 text-sm">
        <div class="rounded-xl bg-brand-50 p-3 text-xs leading-relaxed text-brand-900">
            <p class="font-bold">Cara cepat</p>
            <ul class="mt-1 list-disc space-y-0.5 pl-4">
                <li>Ketik <code>/</code> untuk pecahan, <code>^</code> pangkat, <code>_</code> indeks.</li>
                <li>Ketik <code>sqrt</code>, <code>nthroot</code>, <code>sum</code>, <code>int</code>, <code>pi</code> — otomatis jadi simbol.</li>
                <li>Perintah lain: ketik <code>\</code> + nama (mis. <code>\times</code>) lalu spasi.</li>
                <li>Gunakan tombol panah / Tab untuk keluar dari pangkat atau pecahan.</li>
                <li>Tekan <strong>Enter</strong> atau tombol <em>Sisipkan</em> untuk memasukkan rumus.</li>
                <li><strong>Jangan</strong> mengetik <code>\sqrt[n]{x}</code> manual di editor — pakai <code>nthroot</code>.</li>
            </ul>
        </div>
        @foreach($groups as $title => $items)
            <details class="group rounded-xl border border-slate-200" @if($loop->first) open @endif>
                <summary class="flex cursor-pointer items-center justify-between px-4 py-2.5 font-semibold text-navy-900">{{ $title }} <x-icon name="chevron-down" class="h-4 w-4 text-slate-400 transition group-open:rotate-180" /></summary>
                <div class="grid grid-cols-2 gap-1.5 px-3 pb-3">
                    @foreach($items as [$label, $latex, $hint])
                        <button type="button" @click="write(@js($latex))" class="flex flex-col items-start rounded-lg border border-slate-100 bg-slate-50 px-2.5 py-1.5 text-left hover:border-brand-300 hover:bg-brand-50" title="Klik untuk menyisipkan">
                            <span class="text-base text-navy-900">{{ $label }}</span>
                            <span class="font-mono text-[10.5px] text-slate-500">{{ $hint }}</span>
                        </button>
                    @endforeach
                </div>
            </details>
        @endforeach
        <div class="rounded-xl border border-slate-200 p-3 text-xs text-slate-600">
            <p class="font-bold text-navy-900">Menulis rumus manual (tanpa editor)</p>
            <p class="mt-1">Apit LaTeX dengan <code>\( ... \)</code> untuk rumus di dalam kalimat, atau <code>\[ ... \]</code> untuk rumus di baris sendiri. Contoh: <code>\(\frac{3}{4}\)</code>.</p>
        </div>
    </div>
</div>
