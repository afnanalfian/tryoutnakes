# Optimasi Mesin Pencari (SEO)

Aplikasi sudah dibangun "SEO-ready". Dokumen ini menjelaskan apa yang sudah otomatis dan langkah yang perlu Anda lakukan setelah situs online di `https://tryoutnakes.bynama.id`.

## 1. Yang sudah otomatis di aplikasi

| Fitur | Keterangan |
|---|---|
| HTML dirender di server | Semua halaman publik (Blade/Livewire) langsung berisi konten — mudah dibaca Googlebot, tanpa menunggu JavaScript. |
| `<title>` & meta description unik | Per halaman: beranda, katalog paket, detail paket, 9 halaman kategori (`/kategori/skb-bidan`, dll.), beli satuan, FAQ, tentang. |
| Canonical URL | Mencegah konten ganda (mis. `/paket?kategori=...` → `noindex`). |
| Open Graph & Twitter Card | Pratinjau cantik saat link dibagikan di WhatsApp/IG/Telegram (gambar default `public/images/brand/og-default.jpg`). |
| Data terstruktur (JSON-LD) | `EducationalOrganization`, `WebSite`, `FAQPage` (beranda & /faq), `ItemList` paket, `Product + Offer` (harga paket), `BreadcrumbList`. |
| `sitemap.xml` dinamis | `/sitemap.xml` berisi semua halaman publik, kategori aktif, paket aktif; diperbarui tiap jam (cache). |
| `robots.txt` dinamis | Mengizinkan halaman publik, memblokir area privat (admin, ujian, keranjang, dll.). Di mode non-produksi otomatis `Disallow: /`. |
| Halaman privat `noindex` | Dashboard, ujian, hasil, admin tidak akan terindeks. |
| Kecepatan (Core Web Vitals) | CSS/JS diminify & di-cache 1 tahun, gambar unggahan dikonversi ke **WebP** & di-resize, hero memakai `srcset` + `fetchpriority`, gambar lain `loading="lazy"`, KaTeX dimuat hanya bila ada rumus, gzip di Nginx, OPcache + Redis di server. |
| Mobile-first | Seluruh tampilan responsif (Google memakai indeks mobile-first). |
| URL rapi berbahasa Indonesia | `/paket/paket-skd-only`, `/kategori/skb-perawat`, `/beli-satuan`, `/faq`. |
| IndexNow | Perintah `php artisan seo:indexnow` (terjadwal harian jika `INDEXNOW_KEY` diisi) memberi tahu Bing/Yandex/Naver tentang URL terbaru. |

## 2. Langkah setelah go-live

### 2.1 Google Search Console
1. Buka https://search.google.com/search-console → **Add property**.
2. Pilih **URL prefix** → `https://tryoutnakes.bynama.id` (atau properti **Domain** `bynama.id` via DNS TXT jika ingin mencakup semua subdomain).
3. Metode verifikasi **HTML tag**: salin nilai `content="..."` → tempel di **Admin → Pengaturan → SEO & Analitik → Google Search Console** → Simpan → klik *Verify*.
4. Menu **Sitemaps** → masukkan `sitemap.xml` → Submit.
5. **URL Inspection** → masukkan URL beranda & beberapa kategori → *Request indexing*.

### 2.2 Bing Webmaster Tools (juga memasok DuckDuckGo, Yahoo, ChatGPT search)
1. https://www.bing.com/webmasters → *Import from Google Search Console* (paling cepat), atau tambahkan manual dan isi kode `msvalidate.01` di Admin → Pengaturan → SEO.
2. Submit `https://tryoutnakes.bynama.id/sitemap.xml`.

### 2.3 IndexNow (opsional, mempercepat indeks Bing)
```bash
# buat kunci acak 32 karakter heksadesimal
php -r 'echo bin2hex(random_bytes(16)), PHP_EOL;'
```
Isi ke `.env` → `INDEXNOW_KEY=...` → `php artisan optimize` → cek `https://tryoutnakes.bynama.id/<kunci>.txt` menampilkan kunci → jalankan `php artisan seo:indexnow`.

### 2.4 Google Analytics 4 (opsional)
Buat properti GA4, salin *Measurement ID* `G-XXXX` → Admin → Pengaturan → SEO & Analitik. Skrip hanya dimuat di produksi.

### 2.5 Uji kualitas
- **Rich Results Test**: https://search.google.com/test/rich-results → uji beranda (FAQ) & halaman paket (Product).
- **PageSpeed Insights**: https://pagespeed.web.dev → target skor mobile ≥ 90.
- **Mobile-friendly**: lihat laporan *Page experience* di Search Console.

## 3. Strategi konten (paling berpengaruh)

1. **Deskripsi kategori**: di Admin → Kategori & Harga, tulis deskripsi 2–4 kalimat yang memuat kata kunci alami, mis. *"Tryout SKB CPNS Bidan online dengan soal asuhan kehamilan, persalinan, nifas..."*. Deskripsi ini menjadi teks halaman & meta description.
2. **Nama & deskripsi paket**: gunakan kata yang dicari orang: "Tryout SKB Perawat", "Paket SKD + SKB Nakes", "CPNS 2026".
3. **FAQ**: tambah pertanyaan yang sering ditanya calon pembeli (harga, cara bayar, kisi-kisi). Setiap FAQ aktif otomatis menjadi *rich result*.
4. **Tryout gratis**: sediakan minimal 1 tryout gratis per kategori — memancing tautan balik (backlink) dan pendaftar.
5. **Backlink**: bagikan halaman kategori (bukan hanya beranda) di grup Telegram/WA, IG bio, TikTok, forum nakes. Tautan dari situs lain yang relevan sangat membantu peringkat.
6. **Konsisten**: perbarui soal/paket berkala; halaman yang sering diperbarui cenderung lebih sering dirayapi.

## 4. Catatan teknis

- Wajib `APP_URL=https://tryoutnakes.bynama.id` di `.env`; canonical, sitemap, dan OG memakai nilai ini.
- Setelah mengubah paket/kategori, cache publik otomatis dibersihkan; sitemap diperbarui maksimal 1 jam (atau `php artisan sitemap:warm`).
- Ganti gambar `public/images/brand/og-default.jpg` (1200×630) dengan desain brand Anda bila perlu.
- Karena berupa subdomain, `tryoutnakes.bynama.id` dinilai Google sebagai situs terpisah dari `bynama.id`. Beri tautan dari `bynama.id` ke subdomain ini untuk membantu otoritas.
