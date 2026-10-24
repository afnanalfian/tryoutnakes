<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        if (Faq::query()->exists()) {
            return;
        }
        $faqs = [
            ['Apa bedanya membeli paket dan tryout satuan?', 'Paket berisi kombinasi beberapa tryout dengan harga lebih hemat. Tryout satuan memungkinkan kamu memilih sendiri tryout yang diinginkan. Satu checkout hanya boleh berisi paket saja atau satuan saja. Jika tryout satuan yang kamu pilih sama persis dengan isi sebuah paket, sistem otomatis mengubahnya menjadi paket yang lebih murah.'],
            ['Bagaimana cara pembayarannya?', 'Setelah checkout, bayar melalui QRIS atau rekening/e-wallet yang tertera di halaman pembayaran, lalu unggah bukti transfer. Admin akan memverifikasi paling lambat 1 × 3 jam. Ingin lebih cepat? Klik tombol konfirmasi WhatsApp.'],
            ['Bagaimana sistem penilaian SKD?', 'TWK dan TIU: jawaban benar bernilai 5, salah atau kosong bernilai 0. TKP: setiap pilihan memiliki bobot 1–5 sehingga tidak ada jawaban yang benar-benar salah. Kamu dinyatakan memenuhi passing grade bila TWK ≥ 65, TIU ≥ 80, dan TKP ≥ 166.'],
            ['Bagaimana sistem penilaian SKB?', 'Setiap tryout SKB memiliki skala nilai sendiri (misalnya 0–100 atau 0–400). Nilai dihitung dari jumlah jawaban benar dibagi jumlah soal lalu dikalikan skala. Contoh: skala 400, benar 50 dari 100 soal → nilai 200.'],
            ['Apakah tryout bisa dikerjakan ulang?', 'Bisa, sesuai batas kesempatan yang ditetapkan pada tiap tryout. Peringkat tetap dihitung dari percobaan pertama agar adil bagi semua peserta.'],
            ['Kapan peringkat ditampilkan?', 'Peringkat muncul setelah minimal 5 peserta menyelesaikan tryout yang sama. Kami hanya menampilkan posisimu, tanpa membuka data peserta lain.'],
            ['Bagaimana jika koneksi internet terputus saat ujian?', 'Jawaban disimpan otomatis ke server dan dicadangkan di perangkatmu. Saat online kembali, jawaban dikirim ulang otomatis. Waktu tetap berjalan, jadi segera sambungkan kembali internetmu.'],
            ['Apakah bisa dikerjakan di HP?', 'Tentu. Tampilan tryout dirancang responsif dan nyaman di HP, tablet, maupun laptop.'],
        ];
        foreach ($faqs as $i => [$q, $a]) {
            Faq::query()->create(['question' => $q, 'answer' => $a, 'sort_order' => $i, 'is_active' => true]);
        }
    }
}
