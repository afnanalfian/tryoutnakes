@php($compact = $compact ?? false)
<div class="flex items-center gap-0.5 rounded-xl bg-slate-100 p-0.5 text-xs">
    @unless($compact)
        <button type="button" @click="wrap('{{ $target }}', '<b>', '</b>')" class="h-7 w-7 rounded-lg font-bold text-slate-700 hover:bg-white" title="Tebal">B</button>
        <button type="button" @click="wrap('{{ $target }}', '<i>', '</i>')" class="h-7 w-7 rounded-lg italic text-slate-700 hover:bg-white" title="Miring">I</button>
        <button type="button" @click="wrap('{{ $target }}', '<u>', '</u>')" class="h-7 w-7 rounded-lg text-slate-700 underline hover:bg-white" title="Garis bawah">U</button>
        <button type="button" @click="wrap('{{ $target }}', '<sup>', '</sup>')" class="h-7 w-7 rounded-lg text-slate-700 hover:bg-white" title="Superskrip">x²</button>
        <button type="button" @click="wrap('{{ $target }}', '<sub>', '</sub>')" class="h-7 w-7 rounded-lg text-slate-700 hover:bg-white" title="Subskrip">x₂</button>
        <button type="button" @click="wrap('{{ $target }}', '<table>\n<tr><th>Kolom 1</th><th>Kolom 2</th></tr>\n<tr><td>a</td><td>b</td></tr>\n</table>')" class="h-7 rounded-lg px-1.5 text-slate-700 hover:bg-white" title="Sisipkan tabel">Tabel</button>
    @endunless
    <button type="button" @click="openMath('{{ $target }}')" class="h-7 rounded-lg bg-white px-2 font-bold text-brand-700 shadow-sm hover:bg-brand-50" title="Sisipkan rumus (MathQuill)">∑ Rumus</button>
</div>
