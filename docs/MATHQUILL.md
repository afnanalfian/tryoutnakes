# Rumus Matematika — MathQuill (editor) & KaTeX (tampilan)

Mekanisme mengikuti Satya Naratama: admin mengetik soal biasa di textarea, lalu menekan **∑ Rumus** untuk membuka editor visual **MathQuill**. Rumus disisipkan sebagai LaTeX `\( ... \)` pada posisi kursor, dan pratinjau langsung dirender. Peserta melihat rumus yang sudah dirender rapi oleh **KaTeX** (lebih ringan & cepat dari MathJax).

## 1. Cara membuat soal bergambar/berumus

1. **Admin → Bank Soal → Soal Baru**.
2. Pilih kategori, subtes (TWK/TIU/TKP untuk SKD), topik, tingkat kesulitan.
3. Ketik teks soal. Letakkan kursor di posisi rumus → klik **∑ Rumus**.
4. Di jendela **Editor Rumus**:
   - ketik langsung (`1/2` otomatis jadi pecahan, `x^2` jadi pangkat, `sqrt` jadi akar), atau
   - klik tombol palet (pecahan, akar, ⁿ√, pangkat, indeks, ×, ÷, ±, ≤, ≥, π, °, ∑, ∫, log, θ), atau
   - buka **📘 Dokumentasi** dan klik contoh untuk menyisipkannya.
   - centang **Baris sendiri** untuk rumus besar di baris tersendiri (`\[ ... \]`).
5. Tekan **Enter** atau **Sisipkan**. Pratinjau di bawah textarea langsung menampilkan hasilnya.
6. Lakukan hal yang sama untuk opsi A–E dan pembahasan (tiap kolom punya tombol **∑ Rumus**).
7. Toolbar lain: **B / I / U / x² / x₂ / Tabel** untuk format teks.
8. Gambar soal dan gambar per-opsi bisa diunggah (otomatis dikompres ke WebP).
9. **TWK/TIU/SKB**: pilih satu *Kunci jawaban*. **TKP**: isi *Bobot* 1–5 di setiap opsi (tanpa kunci).

## 2. Cheat sheet

| Hasil | Ketik di editor | LaTeX yang tersimpan |
|---|---|---|
| a/b (pecahan) | `a/b` | `\frac{a}{b}` |
| √x | `sqrt` | `\sqrt{x}` |
| ⁿ√x | `nthroot` | `\sqrt[n]{x}` |
| x² | `x^2` | `x^2` |
| xᵢ | `x_i` | `x_i` |
| ∑ dari i=1 s.d. n | `sum` | `\sum_{i=1}^{n}` |
| ∫ dari a s.d. b | `int` | `\int_{a}^{b}` |
| lim | `lim` | `\lim_{x\to0}` |
| sin, cos, tan, log, ln | ketik namanya | `\sin x` dst. |
| ≤ ≥ ≠ ≈ | `<=` `>=` `\ne` `\approx` | `\le` `\ge` `\ne` `\approx` |
| × ÷ ± · | `\times` `\div` `\pm` `\cdot` | sama |
| π θ α β | `pi` `theta` `\alpha` `\beta` | sama |
| 30° | `30^\circ` | `30^{\circ}` |
| ∈ ∪ ∩ ℝ | `\in` `\cup` `\cap` `\mathbb{R}` | sama |

Tips:
- Gunakan panah ← → atau **Tab** untuk keluar dari pangkat/penyebut.
- **Jangan** mengetik `\sqrt[n]{x}` manual di editor visual; pakai `nthroot`.
- Teks biasa tetap ditulis di textarea, editor rumus hanya untuk persamaan.

## 3. Menulis rumus tanpa editor (mis. impor CSV)

Apit LaTeX dengan `\( ... \)` (dalam kalimat) atau `\[ ... \]` (baris sendiri). Contoh kolom CSV:

```
tiu;Pecahan;Hasil dari \(\frac{2}{5} + \frac{3}{10}\) adalah;\(\frac{7}{10}\);\(\frac{1}{2}\);...;A
```

`$$ ... $$` juga dikenali sebagai rumus display.

## 4. Aset & self-hosting

| Aset | Dipakai di | Lokasi default |
|---|---|---|
| KaTeX 0.16 (JS, CSS, font woff2) | Ujian, hasil, pratinjau admin | **Self-host** di `public/vendor/katex` (tanpa CDN) |
| MathQuill 0.10.1 + jQuery 3.7.1 | Hanya editor admin | CDN cdnjs (dimuat saat tombol ∑ Rumus ditekan) |

Ingin MathQuill self-host juga (mis. server tanpa akses CDN)?

```bash
cd /var/www/tryoutnakes/public && mkdir -p vendor/mathquill
curl -L -o vendor/mathquill/jquery.min.js     https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js
curl -L -o vendor/mathquill/mathquill.min.js  https://cdnjs.cloudflare.com/ajax/libs/mathquill/0.10.1/mathquill.min.js
curl -L -o vendor/mathquill/mathquill.min.css https://cdnjs.cloudflare.com/ajax/libs/mathquill/0.10.1/mathquill.min.css
mkdir -p vendor/mathquill/fonts && cd vendor/mathquill/fonts
for f in Symbola.eot Symbola.woff2 Symbola.woff Symbola.ttf Symbola.svg; do curl -L -o $f https://cdnjs.cloudflare.com/ajax/libs/mathquill/0.10.1/fonts/$f; done
```

Lalu di `.env`:

```dotenv
JQUERY_JS=/vendor/mathquill/jquery.min.js
MATHQUILL_JS=/vendor/mathquill/mathquill.min.js
MATHQUILL_CSS=/vendor/mathquill/mathquill.min.css
```

`php artisan optimize` untuk menerapkan. Konfigurasi lengkap ada di `config/tryout.php`.

## 5. Keamanan konten soal

Teks soal/opsi/pembahasan boleh berisi HTML dasar (`<b> <i> <u> <sup> <sub> <br> <p> <ul> <ol> <table> <img> <a>`). Semua HTML dibersihkan oleh `App\Support\HtmlSanitizer` saat disimpan dan saat ditampilkan, sehingga skrip berbahaya (`<script>`, atribut `onclick`, `javascript:`) otomatis dibuang.
