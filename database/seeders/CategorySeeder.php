<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public static function data(): array
    {
        return [
            ['skd', 'skd', 'SKD CPNS', 'skd', '#1d55f0', 15000, 'TWK, TIU & TKP dengan penilaian resmi', 'Tryout SKD CPNS online dengan sistem CAT: Tes Wawasan Kebangsaan (TWK), Tes Intelegensia Umum (TIU), dan Tes Karakteristik Pribadi (TKP). Penilaian mengikuti aturan resmi — TWK & TIU 5 poin per jawaban benar, TKP berbobot 1–5 — lengkap dengan passing grade, pembahasan, dan peringkat.'],
            ['skb', 'fisioterapi', 'Fisioterapi', 'fisioterapi', '#2563eb', 20000, 'SKB CPNS Fisioterapis', 'Tryout SKB CPNS Fisioterapi: asesmen dan intervensi muskuloskeletal, neuromuskular, kardiopulmonal, tumbuh kembang, modalitas fisioterapi, serta etika profesi fisioterapis.'],
            ['skb', 'okupasi-terapi', 'Okupasi Terapi', 'okupasi-terapi', '#7c3aed', 20000, 'SKB CPNS Okupasi Terapis', 'Tryout SKB CPNS Okupasi Terapi: aktivitas kehidupan sehari-hari (ADL), sensori integrasi, terapi pada anak berkebutuhan khusus, kesehatan jiwa, dan proses okupasi terapi.'],
            ['skb', 'terapi-wicara', 'Terapi Wicara', 'terapi-wicara', '#0d9488', 20000, 'SKB CPNS Terapis Wicara', 'Tryout SKB CPNS Terapi Wicara: gangguan bahasa, artikulasi, suara, kelancaran (gagap), disfagia, serta asesmen dan intervensi terapi wicara.'],
            ['skb', 'teknik-gigi', 'Teknik Gigi', 'teknik-gigi', '#0284c7', 20000, 'SKB CPNS Teknisi Gigi', 'Tryout SKB CPNS Teknik Gigi: anatomi gigi, bahan kedokteran gigi, gigi tiruan lepasan & cekat, ortodonti, dan prosedur laboratorium teknik gigi.'],
            ['skb', 'tenaga-gizi', 'Tenaga Gizi', 'tenaga-gizi', '#16a34a', 20000, 'SKB CPNS Nutrisionis & Dietisien', 'Tryout SKB CPNS Tenaga Gizi (Nutrisionis/Dietisien): asuhan gizi terstandar (PAGT), gizi masyarakat, stunting, penyelenggaraan makanan, dan dietetik penyakit.'],
            ['skb', 'tenaga-farmasi', 'Tenaga Farmasi', 'tenaga-farmasi', '#9333ea', 20000, 'SKB CPNS Apoteker & TTK', 'Tryout SKB CPNS Tenaga Farmasi (Apoteker & Tenaga Teknis Kefarmasian): farmakologi, farmasetika, pelayanan kefarmasian, manajemen sediaan, dan regulasi obat.'],
            ['skb', 'bidan', 'Bidan', 'bidan', '#e11d48', 20000, 'SKB CPNS Bidan', 'Tryout SKB CPNS Bidan: asuhan kehamilan, persalinan, nifas, bayi baru lahir, KB, kesehatan reproduksi, kegawatdaruratan maternal-neonatal, dan regulasi kebidanan.'],
            ['skb', 'perawat', 'Perawat', 'perawat', '#059669', 20000, 'SKB CPNS Perawat', 'Tryout SKB CPNS Perawat: keperawatan medikal bedah, gawat darurat, maternitas, anak, jiwa, komunitas, manajemen keperawatan, dan etika keperawatan berbasis SDKI–SLKI–SIKI.'],
        ];
    }

    public function run(): void
    {
        foreach (static::data() as $i => [$type, $code, $name, $icon, $color, $price, $tagline, $desc]) {
            Category::query()->updateOrCreate(['code' => $code], [
                'type' => $type,
                'name' => $name,
                'slug' => $type === 'skd' ? 'skd-cpns' : 'skb-'.$code,
                'icon' => $icon,
                'color' => $color,
                'price' => $price,
                'tagline' => $tagline,
                'description' => $desc,
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }
    }
}
