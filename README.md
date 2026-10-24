# NakesPrep — Tryout SKD & SKB CPNS Tenaga Kesehatan

Platform **tryout online** khusus persiapan CPNS tenaga kesehatan, dibangun ulang dari konsep Satya Naratama dengan **TALL Stack** (Tailwind CSS · Alpine.js · Laravel 12 · Livewire 3) dan siap SEO.

Target produksi: `https://tryoutnakes.bynama.id`

## Dokumentasi

| Dokumen | Isi |
|---|---|
| [`docs/INSTALL-VPS.md`](docs/INSTALL-VPS.md) | Instalasi lengkap di VPS Ubuntu 24.04: DNS subdomain, Nginx, PHP 8.3-FPM, MySQL, Redis, HTTPS Let's Encrypt, Brevo, Supervisor, cron, backup, tuning, deploy ulang |
| [`docs/SEO.md`](docs/SEO.md) | Fitur SEO bawaan + langkah Search Console, Bing, IndexNow, GA4, strategi konten |
| [`docs/MATHQUILL.md`](docs/MATHQUILL.md) | Cara membuat soal berumus (MathQuill + KaTeX), cheat sheet, self-hosting |
| [`docs/PANDUAN-ADMIN.md`](docs/PANDUAN-ADMIN.md) | Cara memakai panel admin dari setup awal sampai verifikasi pesanan |
| `docs/server/*` | File konfigurasi siap pakai: Nginx, PHP-FPM pool, OPcache, Supervisor, MySQL |
| `deploy.sh` | Script update aplikasi di server |

## Fitur

**Publik & SEO**
- Landing page (topbar berjalan, hero, banner slider dari admin, pilih profesi, paket populer, simulasi CAT, tryout gratis, sistem penilaian, FAQ, CTA).
- Katalog paket, detail paket, halaman per kategori (`/kategori/skb-bidan`, dst.), halaman beli satuan bertahap (SKD/SKB → kategori → tryout).
- Meta tag, canonical, Open Graph, JSON-LD (Organization, WebSite, FAQPage, Product, BreadcrumbList), `sitemap.xml` & `robots.txt` dinamis, IndexNow.

**Peserta**
- Daftar sederhana (nama, email, WA, sandi) dengan anti-bot, login, lupa sandi, verifikasi email opsional.
- Tamu bebas melihat katalog; saat menambah ke keranjang diminta login/daftar lalu kembali ke halaman semula.
- Keranjang: **paket dan satuan tidak bisa dicampur**; tryout satuan yang identik dengan isi paket **otomatis dikonversi** ke paket saat checkout.
- Pembayaran manual: QRIS + metode tambahan dari admin, unggah bukti, tombol konfirmasi WhatsApp, info batas verifikasi 1 × 3 jam, invoice cetak.
- Tryout Saya: hanya kategori yang dimiliki, kartu bergambar (template otomatis per kategori).
- Ujian CAT: timer server, navigasi nomor berwarna, ragu-ragu, urutan SKD TWK→TIU→TKP, pintasan keyboard, zoom teks, layar penuh, auto-save + cadangan offline, auto-submit saat waktu habis, lanjutkan ujian yang terputus (banner & notifikasi).
- Hasil: skor, persentase, lulus passing grade (SKD), rincian subtes, **kata penyemangat** per rentang nilai, **peringkat** (min. 5 peserta), pembahasan lengkap dengan filter benar/salah/kosong.
- **Progres**: grafik nilai per kategori, per subtes SKD, tren, tingkat lulus, akurasi per topik.
- Notifikasi in-app + **email (Brevo)** untuk pesanan, verifikasi, hasil, pengumuman.

**Admin**
- Dashboard (pendapatan, antrean verifikasi, peserta baru, tryout terpopuler).
- Manajemen pengguna (peran, aktif/nonaktif, reset sandi, beri/cabut akses tryout).
- Bank soal: TWK/TIU/TKP + SKB 8 profesi, pilihan ganda, TKP berbobot 1–5, topik, tingkat kesulitan, gambar soal & opsi, **editor rumus MathQuill + dokumentasi**, pratinjau KaTeX, impor CSV, duplikat.
- Tryout: SKD (passing grade) & SKB (skala nilai bebas), isi soal manual/acak otomatis, urutkan seret-lepas, acak soal/opsi, batas percobaan, gratis/berbayar, hasil & ekspor CSV.
- Paket bebas (kombinasi tryout apa pun), harga satuan per kategori, metode pembayaran & QRIS, banner landing, FAQ, pengaturan situs, broadcast notifikasi.

## Teknologi

- PHP 8.2+ (disarankan 8.3), Laravel 12, Livewire 3, Alpine.js (bawaan Livewire), Tailwind CSS 3, Vite 5.
- MySQL 8 / MariaDB 10.6+ (lokal bisa SQLite), Redis (cache, sesi, antrean).
- KaTeX (self-host di `public/vendor/katex`), MathQuill 0.10.1 (editor admin).
- Tanpa paket pihak ketiga tambahan — sanitasi HTML, kompresi gambar WebP, sitemap, dan grafik SVG dibuat sendiri agar ringan & mudah dirawat.

## Menjalankan di komputer lokal

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed        # di APP_ENV=local otomatis mengisi data contoh
php artisan storage:link
npm install && npm run build      # atau: npm run dev
php artisan serve
```

Akun contoh (lokal):

| Peran | Email | Sandi |
|---|---|---|
| Admin | nilai `ADMIN_EMAIL` di `.env` (default `admin@bynama.id`) | nilai `ADMIN_PASSWORD` |
| Peserta | `peserta@demo.test` | `password123` |

Jalankan antrean & penjadwal saat pengembangan (opsional):

```bash
php artisan queue:work
php artisan schedule:work
```

## Pengujian

```bash
php artisan test
```

Mencakup: penilaian SKD (TWK/TIU/TKP + passing grade) & SKB (skala), aturan keranjang & konversi paket, checkout + unggah bukti, persetujuan & hak akses, peringkat minimal 5 peserta, batas waktu ujian & auto-submit, kontrol akses admin, form soal (sanitasi HTML, bobot TKP), impor CSV, isi otomatis soal tryout.

## Struktur penting

```
app/
  Livewire/        Komponen halaman (Shop, Auth, User, Admin/*)
  Http/Controllers Halaman publik/SEO, ujian (JSON), hasil, progres
  Services/        CartService, OrderService, ExamService, ScoringService,
                   RankingService, ProgressService, MotivationService
  Support/         Settings, Seo, HtmlSanitizer, ImageService, PublicCache, helpers
config/tryout.php  Passing grade, komposisi SKD, aset KaTeX/MathQuill, min. peserta peringkat
resources/js/      app.js (Livewire+Alpine), exam.js (mesin ujian), question-editor.js (MathQuill), math.js (KaTeX)
resources/views/   components/layouts (public, app, admin, auth, exam), pages, catalog, user, exam, results, livewire/*
```

## Catatan keputusan desain

- **Rentang kata motivasi**: 0–49%, 50–69%, **70–79% (ditambahkan agar tidak ada celah)**, 80–89%, 90–99%, 100%. Semua teks bisa diubah di Pengaturan.
- **Hak akses disimpan per tryout** saat pesanan disetujui. Jika admin menambah tryout ke paket di kemudian hari, pembeli lama tidak otomatis mendapatkannya (bisa diberikan lewat *Beri Akses*).
- **Peringkat** memakai percobaan pertama agar adil; akun admin tidak ikut dihitung.
- **Pengerjaan ulang** dibatasi per tryout (default 3×, bisa 0 = tanpa batas).
- Soal **TKP** dianggap "benar" pada statistik bila memilih opsi berbobot tertinggi.
