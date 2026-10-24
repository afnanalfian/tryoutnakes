# Panduan Admin

Masuk di `/masuk` dengan akun admin → otomatis diarahkan ke **Panel Admin** (`/admin`).

## Urutan setup pertama kali

1. **Profil** (menu akun kanan atas) → ganti kata sandi.
2. **Pengaturan**
   - *Umum & Brand*: nama situs, tagline, logo (opsional), teks berjalan (topbar), catatan penting.
   - *Kontak & WhatsApp*: nomor WA admin (dipakai tombol "Konfirmasi via WhatsApp" & bantuan), template pesan WA (`{nama}`, `{kode}`, `{total}`).
   - *Pembayaran & Akun*: teks batas verifikasi (default **1 × 3 jam**), lama pesanan kedaluwarsa, wajib verifikasi email (opsional).
   - *SEO & Analitik*: kode Search Console/Bing, GA4.
   - *Kata Motivasi*: pesan per rentang nilai (100%, 90–99%, 80–89%, 70–79%, 50–69%, 0–49%).
   - *Email*: aktif/nonaktif salinan email, tombol kirim email tes.
3. **Metode Pembayaran**: unggah gambar **QRIS** (wajib), lalu tambah metode lain (Metode, No. Rek/No. HP, Atas nama, Keterangan). Semua metode aktif tampil di halaman pembayaran pembeli. Dua metode contoh (BRI, GoPay) dibuat **nonaktif** — edit lalu aktifkan, atau hapus.
4. **Kategori & Harga**: 9 kategori sudah tersedia (SKD + 8 profesi nakes). Atur **harga satuan** per kategori, deskripsi (dipakai SEO), warna, ikon, dan gambar (kosong = template bawaan).

## Bank Soal

- Pilih kategori (chip di atas) → subtes (khusus SKD: TWK/TIU/TKP).
- **Soal Baru**: teks soal + rumus (lihat `docs/MATHQUILL.md`), gambar opsional, 2–6 opsi (default 5: A–E).
  - TWK/TIU/SKB → pilih **Kunci jawaban** (benar = poin, salah/kosong = 0).
  - TKP → isi **Bobot 1–5** tiap opsi.
  - Topik/materi dipakai untuk filter & grafik akurasi peserta.
  - *Simpan & Buat Lagi* untuk input cepat beruntun.
- **Impor CSV**: unduh template → isi di Excel/Google Sheets → *Save as CSV* (koma atau titik-koma) → unggah → periksa pratinjau & baris bermasalah → Impor. Bisa langsung dimasukkan ke tryout tertentu.
- Aksi per soal: pratinjau, edit, duplikat, hapus (soal otomatis dikeluarkan dari tryout). Centang beberapa soal untuk hapus massal.

## Tryout

1. **Tryout Baru**: kategori, judul, deskripsi, gambar kartu (opsional), durasi, maks. percobaan (0 = tanpa batas), urutan.
   - **SKD**: passing grade TWK/TIU/TKP (default 65/80/166).
   - **SKB**: **skala nilai** bebas (100, 400, dst.) + nilai lulus opsional. Nilai = benar ÷ jumlah soal × skala.
   - Opsi: gratis, acak soal (SKD diacak dalam tiap subtes), acak opsi, tampilkan pembahasan.
2. **Kelola Soal**: pilih dari bank (filter subtes/topik/kata kunci, centang lalu *Tambahkan*), atau **Isi Otomatis** acak sesuai jumlah per subtes (default SKD 30/35/45). Seret untuk mengurutkan. Kartu komposisi menunjukkan jumlah vs target.
3. Centang **Terbitkan** (hanya bisa jika sudah ada soal). Tombol status *Terbit/Draft* & *Gratis/Berbayar* juga ada di daftar tryout.
4. **Hasil**: statistik (rata-rata, tertinggi, % lulus), daftar peringkat (percobaan pertama), lihat jawaban peserta, ekspor **CSV**.

## Paket

- **Paket Baru**: nama, label (mis. "Terlaris"), tagline, deskripsi, gambar (opsional — kosong = template kategori/paket), **harga**, lalu centang tryout dari kategori mana pun (ada tombol *Pilih semua* per kategori).
- Panel kanan menghitung harga normal (total satuan) dan persentase hemat.
- *Unggulan* = tampil di landing page. Paket nonaktif tidak bisa dibeli.
- **Konversi otomatis**: jika peserta memasukkan tryout satuan yang sama persis dengan isi sebuah paket aktif yang lebih murah, checkout otomatis menjadi paket tersebut.

## Pesanan & verifikasi pembayaran

- Tab **Menunggu Verifikasi** diurutkan dari yang paling lama menunggu (target ≤ 1 × 3 jam). Badge kuning di menu menunjukkan jumlahnya.
- Buka pesanan → lihat bukti (disimpan privat) → **Setujui & Aktifkan Akses** (semua tryout di pesanan langsung aktif + notifikasi in-app & email) atau **Tolak Bukti** dengan alasan (peserta bisa unggah ulang).
- Pesanan yang tidak dibayar melewati batas waktu otomatis *Kedaluwarsa*.

## Pengguna

- Cari/filter, tambah admin/peserta, edit, nonaktifkan (pengguna langsung keluar), reset kata sandi (sistem membuat sandi acak), hapus.
- **Detail pengguna** → *Beri Akses Tryout*: pilih paket dan/atau tryout satuan (mis. hadiah, kompensasi, pembayaran manual). Akses bisa dicabut.

## Banner, FAQ, Broadcast

- **Banner**: gambar desktop (1600×533) + mobile opsional (900×506), judul/subjudul, tautan, tombol, jadwal tayang. Tampil sebagai slider di bawah hero.
- **FAQ**: tampil di landing & `/faq` (juga *rich result* Google). Seret untuk mengurutkan.
- **Broadcast**: kirim pengumuman ke semua peserta / pemilik tryout / pemilik kategori tertentu. Email opsional — perhatikan kuota Brevo gratis 300/hari.

## Aturan penilaian (ringkas)

| Tes | Benar | Salah | Kosong | Maks | Lulus |
|---|---|---|---|---|---|
| TWK | 5 | 0 | 0 | 5 × jumlah soal | ≥ passing TWK |
| TIU | 5 | 0 | 0 | 5 × jumlah soal | ≥ passing TIU |
| TKP | bobot 1–5 | — | 0 | Σ bobot tertinggi | ≥ passing TKP |
| SKB | skala ÷ n | 0 | 0 | skala | ≥ nilai lulus (opsional) |

- **Peringkat**: dari percobaan pertama tiap peserta, urut nilai total → (SKD) TKP → TIU → TWK → durasi tercepat. Hanya tampil jika peserta ≥ 5.
- **Kata motivasi**: berdasarkan persentase (nilai ÷ maks × 100).
