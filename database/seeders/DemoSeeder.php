<?php

namespace Database\Seeders;

use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Category;
use App\Models\Package;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTopic;
use App\Models\Tryout;
use App\Models\User;
use App\Services\ExamService;
use App\Services\OrderService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Data contoh untuk mencoba aplikasi: bank soal, tryout, paket, dan peserta demo.
 * Jalankan: php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Question::query()->exists()) {
            $this->command?->warn('Bank soal sudah berisi data, DemoSeeder dilewati.');

            return;
        }

        \App\Notifications\AppNotification::$muteMail = true; // jangan habiskan kuota email untuk data contoh
        $cats = Category::query()->get()->keyBy('code');
        $skd = $cats['skd'];

        // ---------- SKD ----------
        foreach ($this->twk() as $q) {
            $this->makeQuestion($skd, 'twk', ...$q);
        }
        foreach ($this->tiu() as $q) {
            $this->makeQuestion($skd, 'tiu', ...$q);
        }
        foreach ($this->generatedTiu(24) as $q) {
            $this->makeQuestion($skd, 'tiu', ...$q);
        }
        foreach ($this->tkp() as $q) {
            $this->makeTkp($skd, ...$q);
        }

        // ---------- SKB ----------
        foreach ($this->skb() as $code => $questions) {
            foreach ($questions as $q) {
                $this->makeQuestion($cats[$code], 'skb', ...$q);
            }
        }

        // ---------- Tryout ----------
        $twk = Question::query()->where('subtest', 'twk')->pluck('id');
        $tkp = Question::query()->where('subtest', 'tkp')->pluck('id');
        $tiuAll = Question::query()->where('subtest', 'tiu')->pluck('id');

        $skdTryouts = collect();
        foreach ([1, 2, 3] as $n) {
            $t = Tryout::query()->create([
                'category_id' => $skd->id,
                'title' => "Tryout SKD CPNS #$n",
                'slug' => "tryout-skd-cpns-$n",
                'description' => 'Simulasi SKD lengkap TWK, TIU, dan TKP dengan sistem CAT (versi demo).',
                'duration_minutes' => 30,
                'passing_grade' => ['twk' => 30, 'tiu' => 40, 'tkp' => 35],
                'is_published' => true,
                'published_at' => now(),
                'max_attempts' => 3,
                'sort_order' => $n,
            ]);
            $tiu = $tiuAll->shuffle()->take(12);
            $this->attach($t, $twk->merge($tiu)->merge($tkp));
            $skdTryouts->push($t);
        }

        $free = Tryout::query()->create([
            'category_id' => $skd->id,
            'title' => 'Tryout SKD Gratis — Pemanasan',
            'slug' => 'tryout-skd-gratis',
            'description' => 'Coba sistem CAT kami tanpa biaya.',
            'duration_minutes' => 15,
            'passing_grade' => ['twk' => 15, 'tiu' => 15, 'tkp' => 20],
            'is_free' => true,
            'is_published' => true,
            'published_at' => now(),
            'max_attempts' => 0,
            'sort_order' => 0,
        ]);
        $this->attach($free, $twk->take(5)->merge($tiuAll->take(5))->merge($tkp->take(5)));

        $skbTryouts = [];
        foreach ($cats->where('type', 'skb') as $code => $cat) {
            $ids = Question::query()->where('category_id', $cat->id)->pluck('id');
            foreach ([1, 2, 3] as $n) {
                $t = Tryout::query()->create([
                    'category_id' => $cat->id,
                    'title' => "Tryout SKB {$cat->name} #$n",
                    'slug' => 'tryout-skb-'.$code.'-'.$n,
                    'description' => "Simulasi SKB CPNS {$cat->name} (versi demo).",
                    'duration_minutes' => 15,
                    'scale_max' => $n === 2 ? 400 : 100,
                    'is_published' => true,
                    'published_at' => now(),
                    'shuffle_options' => true,
                    'max_attempts' => 3,
                    'sort_order' => $n,
                ]);
                $this->attach($t, $ids->shuffle());
                $skbTryouts[$code][] = $t;
            }
        }

        // ---------- Paket ----------
        $pSkd = $this->package('Paket SKD Only', 'paket-skd-only', 'Tiga tryout SKD lengkap TWK, TIU, TKP.', 39000, $skdTryouts->pluck('id'), true, 'Terlaris');
        foreach (['perawat', 'bidan', 'tenaga-farmasi', 'tenaga-gizi'] as $i => $code) {
            $cat = $cats[$code];
            $this->package("Paket SKB {$cat->name}", 'paket-skb-'.$code, "Tiga tryout SKB khusus {$cat->name}.", 49000, collect($skbTryouts[$code])->pluck('id'), false, null, $i + 2);
        }
        $this->package('Paket Lengkap Perawat (SKD + SKB)', 'paket-lengkap-perawat', 'Persiapan total: 3 SKD + 3 SKB Perawat.', 79000, $skdTryouts->pluck('id')->merge(collect($skbTryouts['perawat'])->pluck('id')), true, 'Paling Hemat', 1);
        $this->package('Paket Lengkap Bidan (SKD + SKB)', 'paket-lengkap-bidan', 'Persiapan total: 3 SKD + 3 SKB Bidan.', 79000, $skdTryouts->pluck('id')->merge(collect($skbTryouts['bidan'])->pluck('id')), true, null, 6);

        // ---------- Peserta demo ----------
        $orders = app(OrderService::class);
        $exam = app(ExamService::class);
        $admin = User::query()->where('role', 'admin')->first();

        $demo = User::query()->firstOrCreate(['email' => 'peserta@demo.test'], [
            'name' => 'Nadia Rahmawati', 'phone' => '6281234500001', 'password' => 'password123', 'role' => 'user', 'is_active' => true,
        ]);
        $orders->grantTryouts($demo, $pSkd->tryouts->pluck('id')->merge(collect($skbTryouts['perawat'])->pluck('id'))->all(), null, 'grant', $admin?->id);

        $names = ['Andi Pratama', 'Siti Aminah', 'Rizky Maulana', 'Dewi Lestari', 'Fajar Nugroho', 'Putri Ayu', 'Bagas Saputra'];
        foreach ($names as $i => $name) {
            $u = User::query()->firstOrCreate(['email' => 'peserta'.($i + 2).'@demo.test'], [
                'name' => $name, 'phone' => '62812345000'.($i + 2), 'password' => 'password123', 'role' => 'user', 'is_active' => true,
            ]);
            $orders->grantTryouts($u, [$skdTryouts[0]->id, $skbTryouts['perawat'][0]->id], null, 'grant', $admin?->id);
            $this->simulate($exam, $u, $skdTryouts[0], 0.45 + $i * 0.06);
            $this->simulate($exam, $u, $skbTryouts['perawat'][0], 0.4 + $i * 0.07);
        }

        // Riwayat demo user agar grafik progres terisi
        $this->simulate($exam, $demo, $skdTryouts[0], 0.55, now()->subDays(9));
        $this->simulate($exam, $demo, $skdTryouts[1], 0.68, now()->subDays(5));
        $this->simulate($exam, $demo, $skdTryouts[0], 0.8, now()->subDays(2));
        $this->simulate($exam, $demo, $skbTryouts['perawat'][0], 0.6, now()->subDays(7));
        $this->simulate($exam, $demo, $skbTryouts['perawat'][1], 0.75, now()->subDays(3));
    }

    /* ================= Helpers ================= */

    protected function topic(Category $cat, string $subtest, ?string $name): ?int
    {
        return $name ? QuestionTopic::query()->firstOrCreate(['category_id' => $cat->id, 'subtest' => $subtest, 'name' => $name])->id : null;
    }

    /** @param  array<int,string>  $options  key = index jawaban benar */
    protected function makeQuestion(Category $cat, string $subtest, string $topic, string $text, array $options, int $correct, string $explanation = '', int $difficulty = 2): void
    {
        $q = Question::query()->create([
            'category_id' => $cat->id, 'subtest' => $subtest, 'topic_id' => $this->topic($cat, $subtest, $topic),
            'question_text' => $text, 'explanation' => $explanation, 'difficulty' => $difficulty,
        ]);
        foreach (array_values($options) as $i => $o) {
            QuestionOption::query()->create(['question_id' => $q->id, 'order' => $i + 1, 'option_text' => $o, 'is_correct' => $i === $correct, 'weight' => 0]);
        }
    }

    protected function makeTkp(Category $cat, string $topic, string $text, array $optionsWithWeight, string $explanation = ''): void
    {
        $q = Question::query()->create([
            'category_id' => $cat->id, 'subtest' => 'tkp', 'topic_id' => $this->topic($cat, 'tkp', $topic),
            'question_text' => $text, 'explanation' => $explanation, 'difficulty' => 2,
        ]);
        foreach ($optionsWithWeight as $i => [$o, $w]) {
            QuestionOption::query()->create(['question_id' => $q->id, 'order' => $i + 1, 'option_text' => $o, 'is_correct' => false, 'weight' => $w]);
        }
    }

    protected function attach(Tryout $t, $ids): void
    {
        $t->questions()->sync(collect($ids)->unique()->values()->mapWithKeys(fn ($id, $i) => [$id => ['order' => $i + 1]])->all());
    }

    protected function package(string $name, string $slug, string $tagline, int $price, $tryoutIds, bool $featured = false, ?string $badge = null, int $sort = 0): Package
    {
        $p = Package::query()->create([
            'name' => $name, 'slug' => $slug, 'tagline' => $tagline, 'price' => $price, 'badge' => $badge,
            'description' => $tagline."\nSetiap tryout dilengkapi pembahasan, peringkat, dan grafik progres.",
            'is_featured' => $featured, 'is_active' => true, 'sort_order' => $sort,
        ]);
        $p->tryouts()->sync(collect($tryoutIds)->unique()->all());

        return $p->load('tryouts');
    }

    /** Simulasikan pengerjaan dengan peluang benar tertentu. */
    protected function simulate(ExamService $exam, User $user, Tryout $tryout, float $skill, $when = null): void
    {
        $attempt = $exam->start($user, $tryout);
        $questions = Question::query()->with('options')->whereIn('id', $attempt->question_order)->get();
        foreach ($questions as $q) {
            if (mt_rand(1, 100) <= 8) {
                continue; // kosong
            }
            if ($q->subtest === 'tkp') {
                $sorted = $q->options->sortByDesc('weight')->values();
                $opt = mt_rand(1, 100) <= $skill * 100 ? $sorted[0] : $sorted[mt_rand(1, $sorted->count() - 1)];
            } else {
                $correct = $q->options->firstWhere('is_correct', true);
                $opt = mt_rand(1, 100) <= $skill * 100 ? $correct : $q->options->where('is_correct', false)->random();
            }
            AttemptAnswer::query()->create(['attempt_id' => $attempt->id, 'question_id' => $q->id, 'option_id' => $opt->id, 'answered_at' => now()]);
        }
        $start = ($when ?? now())->copy()->subMinutes(mt_rand(8, 14));
        $attempt->update(['started_at' => $start, 'deadline_at' => $start->copy()->addMinutes($tryout->duration_minutes)]);
        \Illuminate\Support\Carbon::setTestNow($start->copy()->addMinutes(mt_rand(6, 12)));
        $exam->submit($attempt->fresh());
        \Illuminate\Support\Carbon::setTestNow();
    }

    /* ================= Bank soal contoh ================= */

    protected function twk(): array
    {
        return [
            ['Pancasila', 'Rumusan Pancasila yang sah dan resmi sebagai dasar negara tercantum dalam ...', ['Piagam Jakarta 22 Juni 1945', 'Pembukaan UUD NRI Tahun 1945 alinea keempat', 'Pidato Ir. Soekarno 1 Juni 1945', 'Dekrit Presiden 5 Juli 1959', 'Ketetapan MPRS No. XX/MPRS/1966'], 1, 'Rumusan Pancasila yang sah adalah yang tercantum dalam Pembukaan UUD 1945 alinea IV, ditetapkan PPKI 18 Agustus 1945.'],
            ['UUD 1945', 'Lembaga negara yang berwenang mengubah dan menetapkan Undang-Undang Dasar adalah ...', ['DPR', 'Presiden', 'MPR', 'Mahkamah Konstitusi', 'DPD'], 2, 'Pasal 3 ayat (1) UUD 1945: MPR berwenang mengubah dan menetapkan UUD.'],
            ['Sejarah', 'Ikrar Sumpah Pemuda dibacakan pada tanggal ...', ['20 Mei 1908', '28 Oktober 1928', '17 Agustus 1945', '1 Juni 1945', '10 November 1945'], 1, 'Sumpah Pemuda diikrarkan pada Kongres Pemuda II, 28 Oktober 1928.'],
            ['Bhinneka Tunggal Ika', 'Semboyan "Bhinneka Tunggal Ika" dikutip dari kitab ...', ['Negarakertagama karya Mpu Prapanca', 'Arjunawiwaha karya Mpu Kanwa', 'Sutasoma karya Mpu Tantular', 'Pararaton', 'Bharatayudha karya Mpu Sedah'], 2, 'Semboyan tersebut berasal dari Kakawin Sutasoma karya Mpu Tantular (abad ke-14).'],
            ['UUD 1945', 'Hak setiap warga negara untuk mendapatkan pendidikan diatur dalam UUD NRI 1945 Pasal ...', ['27 ayat (1)', '28', '29 ayat (2)', '31 ayat (1)', '33 ayat (3)'], 3, 'Pasal 31 ayat (1): Setiap warga negara berhak mendapat pendidikan.'],
            ['Pancasila', 'Hari Lahir Pancasila diperingati setiap tanggal ...', ['1 Juni', '22 Juni', '18 Agustus', '1 Oktober', '17 Agustus'], 0, 'Ditetapkan melalui Keppres No. 24 Tahun 2016.'],
            ['Wawasan Nusantara', 'Deklarasi Djuanda tahun 1957 pada dasarnya menegaskan ...', ['Batas laut teritorial 3 mil dari garis pantai', 'Indonesia sebagai negara kepulauan dengan laut sebagai pemersatu wilayah', 'Pembentukan Zona Ekonomi Eksklusif 200 mil', 'Kerja sama maritim ASEAN', 'Penggabungan Irian Barat'], 1, 'Deklarasi Djuanda menegaskan konsep negara kepulauan (archipelagic state).'],
            ['UUD 1945', 'Ketentuan "Kedaulatan berada di tangan rakyat dan dilaksanakan menurut Undang-Undang Dasar" terdapat dalam Pasal ...', ['1 ayat (1)', '1 ayat (2)', '1 ayat (3)', '2 ayat (1)', '3 ayat (2)'], 1, 'Pasal 1 ayat (2) UUD NRI 1945 hasil amandemen ketiga.'],
            ['Lambang Negara', 'Jumlah bulu pada masing-masing sayap Garuda Pancasila adalah ...', ['8', '17', '19', '45', '5'], 1, 'Sayap 17, ekor 8, pangkal ekor 19, leher 45 → 17-8-1945.'],
            ['Integritas', 'Seorang ASN menolak pemberian bingkisan dari pihak yang sedang mengurus izin di instansinya. Sikap ini paling mencerminkan nilai ...', ['Nasionalisme', 'Integritas', 'Bela negara', 'Kesetiakawanan', 'Toleransi'], 1, 'Menolak gratifikasi adalah wujud integritas ASN.'],
        ];
    }

    protected function tiu(): array
    {
        return [
            ['Deret Angka', 'Lanjutan deret 3, 7, 15, 31, 63, ... adalah', ['95', '111', '125', '127', '131'], 3, 'Pola ×2 + 1: 63 × 2 + 1 = 127.', 1],
            ['Pecahan', 'Hasil dari \(\frac{2}{5} + \frac{3}{10}\) adalah ...', ['\(\frac{5}{15}\)', '\(\frac{1}{2}\)', '\(\frac{7}{10}\)', '\(\frac{5}{10}\)', '\(\frac{3}{5}\)'], 2, 'Samakan penyebut: \(\frac{4}{10} + \frac{3}{10} = \frac{7}{10}\).', 1],
            ['Hitung Cepat', 'Nilai dari \(\sqrt{144} + 3^{2}\) adalah ...', ['15', '18', '21', '24', '27'], 2, '\(\sqrt{144} = 12\) dan \(3^2 = 9\), jumlahnya 21.', 1],
            ['Analogi', 'DOKTER : PASIEN = GURU : ...', ['Sekolah', 'Murid', 'Buku', 'Kelas', 'Kepala sekolah'], 1, 'Dokter melayani pasien; guru melayani murid.', 1],
            ['Aljabar', 'Jika \(x + 2y = 10\) dan \(x - y = 1\), maka nilai \(x\) adalah ...', ['2', '3', '4', '5', '6'], 2, 'Kurangkan kedua persamaan: \(3y = 9 \Rightarrow y = 3\), sehingga \(x = 4\).', 2],
            ['Silogisme', 'Semua perawat memakai seragam. Sebagian pegawai rumah sakit adalah perawat. Kesimpulan yang tepat adalah ...', ['Semua pegawai rumah sakit memakai seragam', 'Sebagian pegawai rumah sakit memakai seragam', 'Semua yang memakai seragam adalah perawat', 'Sebagian perawat tidak memakai seragam', 'Tidak dapat disimpulkan'], 1, 'Pola silogisme: Semua A adalah B, sebagian C adalah A → sebagian C adalah B.', 2],
            ['Persentase', '25% dari 480 adalah ...', ['100', '110', '120', '125', '140'], 2, '\(\frac{25}{100} \times 480 = 120\).', 1],
            ['Sinonim', 'Sinonim kata EFISIEN adalah ...', ['Tepat waktu', 'Berdaya guna', 'Hemat biaya', 'Cepat', 'Rapi'], 1, 'Efisien = tepat atau sesuai untuk mengerjakan sesuatu dengan tidak membuang waktu/tenaga (berdaya guna).', 1],
            ['Antonim', 'Lawan kata SKEPTIS adalah ...', ['Ragu', 'Apatis', 'Yakin', 'Kritis', 'Pesimis'], 2, 'Skeptis = kurang percaya/ragu-ragu; lawannya yakin.', 2],
            ['Soal Cerita', 'Sebuah ambulans menempuh jarak 90 km dalam waktu 1,5 jam. Kecepatan rata-ratanya adalah ...', ['45 km/jam', '50 km/jam', '55 km/jam', '60 km/jam', '75 km/jam'], 3, '\(v = \frac{s}{t} = \frac{90}{1{,}5} = 60\) km/jam.', 1],
            ['Hitung Cepat', 'Nilai dari \(2^{5} - 4^{2}\) adalah ...', ['8', '12', '16', '20', '24'], 2, '\(32 - 16 = 16\).', 1],
        ];
    }

    protected function generatedTiu(int $n): array
    {
        $out = [];
        mt_srand(2026);
        for ($i = 0; $i < $n; $i++) {
            if ($i % 2 === 0) {
                $a = mt_rand(2, 9);
                $d = mt_rand(2, 7);
                $seq = [$a, $a + $d, $a + 3 * $d, $a + 6 * $d, $a + 10 * $d];
                $ans = $a + 15 * $d;
                $opts = collect([$ans, $ans + $d, $ans - $d, $ans + 2, $ans - 3])->unique()->values();
                while ($opts->count() < 5) {
                    $opts->push($ans + $opts->count() * 4);
                }
                $opts = $opts->shuffle()->values();
                $out[] = ['Deret Angka', 'Lanjutan deret '.implode(', ', $seq).', ... adalah', $opts->map(fn ($v) => (string) $v)->all(), $opts->search($ans), "Selisih bertambah: {$d}, ".(2 * $d).', '.(3 * $d).', '.(4 * $d).', berikutnya '.(5 * $d).". Jadi {$seq[4]} + ".(5 * $d)." = {$ans}.", 2];
            } else {
                $p = mt_rand(2, 9) * 5;
                $x = mt_rand(4, 20) * 20;
                $ans = $p * $x / 100;
                $opts = collect([$ans, $ans + 5, $ans - 5, $ans * 2, $ans + 10])->unique()->values()->shuffle()->values();
                $out[] = ['Persentase', "Nilai \\({$p}\\%\\) dari {$x} adalah ...", $opts->map(fn ($v) => (string) $v)->all(), $opts->search($ans), "\\(\\frac{{$p}}{100} \\times {$x} = {$ans}\\).", 1];
            }
        }

        return $out;
    }

    protected function tkp(): array
    {
        return [
            ['Pelayanan Publik', 'Seorang pasien lansia tampak kebingungan mencari loket pendaftaran, padahal Anda sedang menuju ruang rapat. Sikap Anda ...', [['Mengabaikannya karena bukan tugas saya', 1], ['Menunjukkan arah loket dari kejauhan', 3], ['Meminta satpam terdekat membantunya', 4], ['Mengantarnya ke loket sambil menjelaskan alurnya, lalu bergegas ke rapat', 5], ['Menyuruhnya bertanya kepada pasien lain', 2]], 'Pelayanan prima: membantu langsung tanpa mengabaikan tanggung jawab lain.'],
            ['Kerja Sama', 'Rekan satu tim Anda sering terlambat menyerahkan laporan sehingga pekerjaan tim tertunda. Anda ...', [['Melaporkannya langsung kepada atasan', 3], ['Membicarakan secara baik-baik untuk mencari solusi bersama', 5], ['Mengerjakan bagiannya diam-diam', 2], ['Menyindirnya di grup kantor', 1], ['Membiarkan saja, yang penting bagian saya selesai', 2]], 'Komunikasi asertif dan solusi bersama adalah pilihan terbaik.'],
            ['Profesionalisme', 'Atasan memberi tugas mendadak di luar bidang Anda dengan tenggat yang singkat. Anda ...', [['Menolak karena bukan bidang saya', 1], ['Menerima tetapi mengerjakan seadanya', 2], ['Menerima dan segera mempelajari serta berkoordinasi dengan pihak yang memahami', 5], ['Meminta rekan lain mengerjakannya', 2], ['Menerima sambil meminta tambahan waktu sejak awal', 4]], 'Profesional: menerima tantangan dan proaktif mencari sumber daya.'],
            ['Anti Radikalisme', 'Anda menerima pesan berantai di grup WhatsApp yang berisi ajakan membenci kelompok tertentu. Anda ...', [['Meneruskan ke grup lain agar waspada', 1], ['Mendiamkan saja', 2], ['Mengingatkan anggota grup agar tidak menyebarkan dan melaporkan konten tersebut', 5], ['Keluar dari grup', 3], ['Menanyakan kebenarannya kepada pengirim', 4]], 'Mencegah penyebaran ujaran kebencian dan melaporkannya.'],
            ['Jejaring Kerja', 'Anda baru dipindahkan ke unit kerja baru dan belum mengenal siapa pun. Anda ...', [['Menunggu rekan kerja menyapa lebih dulu', 2], ['Memperkenalkan diri dan aktif mengikuti kegiatan unit', 5], ['Fokus bekerja tanpa perlu berinteraksi', 1], ['Hanya berinteraksi dengan atasan', 3], ['Bergabung dengan kelompok yang terlihat paling akrab', 4]], 'Membangun jejaring secara aktif.'],
            ['Sosial Budaya', 'Di tempat tugas baru, masyarakat memiliki kebiasaan yang berbeda dengan daerah asal Anda. Anda ...', [['Mempertahankan kebiasaan sendiri', 2], ['Mempelajari dan menghormati kebiasaan setempat', 5], ['Meminta masyarakat menyesuaikan diri', 1], ['Menghindari interaksi', 1], ['Mengikuti sebagian kebiasaan yang saya sukai saja', 3]], 'Adaptif dan menghargai keberagaman.'],
            ['Teknologi Informasi', 'Instansi Anda menerapkan aplikasi pelaporan digital baru yang belum Anda kuasai. Anda ...', [['Tetap memakai cara manual', 1], ['Meminta rekan mengisikan laporan saya', 2], ['Mempelajari panduan dan berlatih menggunakannya', 5], ['Menunggu pelatihan resmi', 3], ['Mengeluhkan kebijakan tersebut', 1]], 'Adaptif terhadap teknologi untuk efektivitas kerja.'],
            ['Pelayanan Publik', 'Keluarga pasien marah-marah karena antrean pelayanan sangat panjang. Anda ...', [['Membalas dengan nada tinggi', 1], ['Menenangkan, meminta maaf, dan menjelaskan estimasi waktu tunggu', 5], ['Memintanya menunggu tanpa penjelasan', 2], ['Mendahulukan pelayanannya agar tidak ribut', 3], ['Memanggil petugas keamanan', 2]], 'Empati dan komunikasi efektif dalam pelayanan.'],
            ['Integritas', 'Anda melihat rekan kerja memanipulasi data kehadiran. Anda ...', [['Ikut melakukannya', 1], ['Menegurnya dan mengingatkan konsekuensinya', 5], ['Pura-pura tidak tahu', 2], ['Menyebarkan ke rekan lain', 1], ['Langsung melapor tanpa menegur', 4]], 'Integritas: mengingatkan secara langsung.'],
            ['Pengendalian Diri', 'Hasil kerja Anda dikritik keras oleh atasan di depan rekan-rekan. Anda ...', [['Membela diri dengan emosi', 1], ['Menerima kritik dengan tenang dan memperbaiki pekerjaan', 5], ['Diam lalu menceritakan ke rekan lain', 2], ['Meminta pindah unit', 1], ['Menerima tetapi merasa tersinggung', 3]], 'Pengendalian diri dan orientasi perbaikan.'],
        ];
    }

    protected function skb(): array
    {
        return [
            'perawat' => [
                ['Gawat Darurat', 'Pasien datang dengan perdarahan hebat, TD 80/50 mmHg, nadi 124×/menit lemah, akral dingin. Tindakan prioritas perawat adalah ...', ['Memberikan analgetik', 'Memasang akses IV dan memberikan resusitasi cairan', 'Mengukur tinggi badan', 'Memberikan makan dan minum', 'Mengajarkan teknik relaksasi'], 1, 'Tanda syok hipovolemik; prioritas sirkulasi dengan resusitasi cairan.'],
                ['KMB', 'Posisi yang paling tepat untuk pasien PPOK yang mengalami sesak napas adalah ...', ['Supinasi', 'Trendelenburg', 'Fowler tinggi / semi Fowler', 'Pronasi', 'Sims'], 2, 'Posisi Fowler memaksimalkan ekspansi paru.'],
                ['Farmakologi Keperawatan', 'Berikut yang BUKAN termasuk prinsip benar dalam pemberian obat adalah ...', ['Benar pasien', 'Benar dosis', 'Benar rute', 'Benar harga', 'Benar waktu'], 3, 'Prinsip benar: pasien, obat, dosis, rute, waktu, dokumentasi — bukan harga.'],
                ['Neurologi', 'Pasien membuka mata spontan, orientasi baik, dan mengikuti perintah. Nilai GCS pasien adalah ...', ['12', '13', '14', '15', '16'], 3, 'E4 V5 M6 = 15 (compos mentis).'],
                ['Endokrin', 'Tanda awal hipoglikemia pada pasien diabetes adalah ...', ['Poliuria dan polidipsia', 'Keringat dingin, gemetar, dan berdebar', 'Napas Kussmaul', 'Kulit kering dan panas', 'Bau napas aseton'], 1, 'Respons adrenergik: diaforesis, tremor, takikardia.'],
            ],
            'bidan' => [
                ['Kehamilan', 'Yang termasuk tanda pasti kehamilan adalah ...', ['Amenore', 'Mual muntah', 'Terdengar denyut jantung janin', 'Tes HCG positif', 'Pembesaran perut'], 2, 'Tanda pasti: DJJ terdengar, gerakan janin teraba pemeriksa, janin terlihat pada USG.'],
                ['ANC', 'Menurut standar pelayanan antenatal terbaru Kemenkes, kunjungan ANC minimal selama kehamilan adalah ...', ['4 kali', '5 kali', '6 kali', '7 kali', '8 kali'], 2, 'Minimal 6 kali: 2× TM I, 1× TM II, 3× TM III (minimal 2× diperiksa dokter).'],
                ['Persalinan', 'Langkah pertama manajemen aktif kala III adalah ...', ['Masase fundus uteri', 'Penegangan tali pusat terkendali', 'Pemberian oksitosin 10 IU IM dalam 1 menit setelah bayi lahir', 'Melahirkan plasenta', 'Inisiasi menyusu dini'], 2, 'Urutan MAK III: oksitosin, PTT, masase fundus.'],
                ['Bayi Baru Lahir', 'Komponen penilaian APGAR berikut, KECUALI ...', ['Warna kulit', 'Frekuensi jantung', 'Tonus otot', 'Suhu tubuh', 'Usaha napas'], 3, 'APGAR: Appearance, Pulse, Grimace, Activity, Respiration.'],
                ['Nifas', 'Masa nifas berlangsung selama ...', ['7 hari', '14 hari', '30 hari', '42 hari (6 minggu)', '3 bulan'], 3, 'Masa nifas dimulai setelah plasenta lahir hingga 6 minggu.'],
            ],
            'tenaga-farmasi' => [
                ['Penggolongan Obat', 'Logo lingkaran merah dengan huruf K berwarna hitam pada kemasan menandakan ...', ['Obat bebas', 'Obat bebas terbatas', 'Obat keras', 'Narkotika', 'Jamu'], 2, 'Obat keras hanya dapat diperoleh dengan resep dokter.'],
                ['Toksikologi', 'Antidotum untuk keracunan parasetamol adalah ...', ['Nalokson', 'Atropin', 'N-asetilsistein', 'Flumazenil', 'Vitamin K'], 2, 'N-asetilsistein mengembalikan cadangan glutation hati.'],
                ['Resep', 'Singkatan "a.c." pada resep berarti ...', ['Sesudah makan', 'Sebelum makan', 'Saat makan', 'Malam hari', 'Bila perlu'], 1, 'a.c. = ante coenam (sebelum makan); p.c. = post coenam.'],
                ['Farmakologi', 'Efek samping khas obat golongan ACE inhibitor seperti kaptopril adalah ...', ['Batuk kering', 'Hipoglikemia', 'Konstipasi', 'Ruam fotosensitif', 'Bradikardia berat'], 0, 'Akumulasi bradikinin menyebabkan batuk kering.'],
                ['Manajemen Sediaan', 'Sebagian besar vaksin disimpan pada rentang suhu ...', ['-25 s.d. -15 °C', '0 s.d. 2 °C', '2 s.d. 8 °C', '8 s.d. 15 °C', 'Suhu ruang'], 2, 'Rantai dingin vaksin umumnya 2–8 °C.'],
            ],
            'tenaga-gizi' => [
                ['Antropometri', 'Indikator status gizi yang digunakan untuk menentukan stunting adalah ...', ['BB/U', 'TB/U atau PB/U', 'BB/TB', 'IMT/U', 'LILA'], 1, 'Stunting: TB/U < −2 SD.'],
                ['Zat Gizi', 'Satu gram lemak menghasilkan energi sebesar ...', ['4 kkal', '5 kkal', '7 kkal', '9 kkal', '12 kkal'], 3, 'Lemak 9 kkal/g; karbohidrat dan protein 4 kkal/g.'],
                ['PAGT', 'Urutan langkah Proses Asuhan Gizi Terstandar (PAGT) yang benar adalah ...', ['Diagnosis – asesmen – intervensi – monitoring', 'Asesmen – diagnosis – intervensi – monitoring & evaluasi', 'Intervensi – asesmen – diagnosis – evaluasi', 'Skrining – intervensi – diagnosis – evaluasi', 'Asesmen – intervensi – diagnosis – evaluasi'], 1, 'PAGT: asesmen, diagnosis, intervensi, monitoring & evaluasi gizi.'],
                ['Defisiensi', 'Kekurangan vitamin A dapat menyebabkan ...', ['Skorbut', 'Rabun senja', 'Beri-beri', 'Rakitis', 'Pelagra'], 1, 'Defisiensi vitamin A → xeroftalmia, diawali rabun senja.'],
                ['Antropometri', 'Seseorang dengan berat badan 60 kg dan tinggi 150 cm memiliki IMT sekitar ... (\(IMT = \frac{BB}{TB^2}\))', ['22,5', '24,0', '26,7', '28,3', '30,1'], 2, '\(\frac{60}{1{,}5^2} = \frac{60}{2{,}25} \approx 26{,}7\) kg/m².'],
            ],
            'fisioterapi' => [
                ['Elektroterapi', 'Modalitas TENS terutama digunakan untuk ...', ['Meningkatkan kekuatan otot', 'Mengurangi nyeri', 'Mempercepat penyembuhan tulang', 'Meningkatkan lingkup gerak sendi', 'Mengurangi spastisitas'], 1, 'TENS bekerja melalui gate control theory untuk modulasi nyeri.'],
                ['Pemeriksaan', 'Nilai 3 pada Manual Muscle Testing (MMT) berarti ...', ['Tidak ada kontraksi', 'Kontraksi tanpa gerakan', 'Gerak penuh tanpa melawan gravitasi', 'Gerak penuh melawan gravitasi tanpa tahanan', 'Gerak penuh melawan tahanan maksimal'], 3, 'MMT 3 (fair): full ROM melawan gravitasi.'],
                ['Cedera Olahraga', 'Pada prinsip PRICE untuk cedera akut, huruf "I" adalah ...', ['Immobilization', 'Ice', 'Injection', 'Isometric', 'Inspection'], 1, 'PRICE: Protection, Rest, Ice, Compression, Elevation.'],
                ['Muskuloskeletal', 'Lachman test digunakan untuk menilai integritas ...', ['Ligamen kolateral medial', 'Meniskus lateral', 'Ligamen krusiatum anterior (ACL)', 'Tendon patella', 'Ligamen krusiatum posterior'], 2, 'Lachman test adalah tes paling sensitif untuk ruptur ACL.'],
                ['Kinesiologi', 'Gerakan menjauhi garis tengah tubuh disebut ...', ['Adduksi', 'Abduksi', 'Fleksi', 'Ekstensi', 'Rotasi'], 1, 'Abduksi menjauhi midline; adduksi mendekati.'],
            ],
            'okupasi-terapi' => [
                ['ADL', 'Yang termasuk aktivitas kehidupan sehari-hari dasar (basic ADL) adalah ...', ['Berbelanja', 'Mengelola keuangan', 'Berpakaian', 'Menggunakan transportasi umum', 'Memasak'], 2, 'BADL: mandi, berpakaian, makan, toileting, berpindah; lainnya termasuk IADL.'],
                ['Teori', 'Teori Sensory Integration dikembangkan oleh ...', ['Mary Reilly', 'A. Jean Ayres', 'Gary Kielhofner', 'Anne Fisher', 'Mary Law'], 1, 'A. Jean Ayres mengembangkan teori integrasi sensori.'],
                ['Model', 'Model PEO dalam okupasi terapi terdiri atas ...', ['Person, Environment, Occupation', 'Practice, Evaluation, Outcome', 'Planning, Execution, Observation', 'Physical, Emotional, Occupational', 'Patient, Education, Orientation'], 0, 'PEO (Law dkk., 1996): interaksi Person–Environment–Occupation.'],
                ['Konsep Dasar', 'Tujuan utama intervensi okupasi terapi adalah ...', ['Menyembuhkan penyakit', 'Meningkatkan kemandirian dalam okupasi sehari-hari', 'Memberikan obat', 'Mendiagnosis gangguan', 'Melakukan operasi'], 1, 'OT berfokus pada partisipasi dan kemandirian okupasi.'],
                ['Splinting', 'Splint yang digunakan untuk mempertahankan tangan dalam posisi istirahat fungsional adalah ...', ['Dynamic splint', 'Resting hand splint', 'Thumb spica', 'Cock-up splint', 'Finger extension splint'], 1, 'Resting hand splint mempertahankan posisi fungsional istirahat.'],
            ],
            'terapi-wicara' => [
                ['Disfagia', 'Gangguan menelan disebut ...', ['Disartria', 'Disfagia', 'Disfonia', 'Disleksia', 'Afasia'], 1, 'Disfagia = kesulitan menelan.'],
                ['Afasia', 'Afasia Broca ditandai dengan ...', ['Bicara lancar tetapi tidak bermakna', 'Bicara tidak lancar dengan pemahaman relatif baik', 'Tidak mampu memahami ucapan', 'Gangguan suara serak', 'Bicara terlalu cepat'], 1, 'Afasia Broca (ekspresif): non-fluent, pemahaman relatif baik.'],
                ['Kelancaran', 'Gagap termasuk ke dalam gangguan ...', ['Artikulasi', 'Suara', 'Kelancaran (fluency)', 'Bahasa reseptif', 'Menelan'], 2, 'Stuttering adalah gangguan kelancaran bicara.'],
                ['Artikulasi', 'Anak mengucapkan "lumah" untuk kata "rumah". Gangguan ini termasuk ...', ['Omisi', 'Substitusi', 'Distorsi', 'Adisi', 'Afasia'], 1, 'Mengganti fonem /r/ dengan /l/ adalah substitusi.'],
                ['Anatomi', 'Organ utama yang menghasilkan suara (fonasi) adalah ...', ['Lidah', 'Langit-langit', 'Pita suara di laring', 'Bibir', 'Faring'], 2, 'Fonasi dihasilkan oleh getaran pita suara di laring.'],
            ],
            'teknik-gigi' => [
                ['Anatomi Gigi', 'Jumlah gigi permanen lengkap pada orang dewasa adalah ...', ['20', '24', '28', '32', '36'], 3, 'Gigi permanen lengkap berjumlah 32 termasuk molar ketiga.'],
                ['Bahan', 'Bahan cetak elastis irreversibel yang umum digunakan untuk mencetak rahang adalah ...', ['Gips', 'Alginat', 'Malam (wax)', 'Resin akrilik', 'Kompomer'], 1, 'Alginat adalah hidrokoloid irreversibel.'],
                ['Prostodonsia', 'Gigi tiruan yang dapat dilepas pasang dan menggantikan sebagian gigi disebut ...', ['GTL', 'GTSL', 'Mahkota jaket', 'Bridge', 'Implan'], 1, 'GTSL = Gigi Tiruan Sebagian Lepasan.'],
                ['Bahan', 'Bahan basis gigi tiruan yang paling umum digunakan adalah ...', ['Porselen', 'Resin akrilik polimetil metakrilat (PMMA)', 'Amalgam', 'Semen ionomer kaca', 'Emas'], 1, 'PMMA heat-cured paling umum untuk basis gigi tiruan.'],
                ['Penomoran', 'Dalam sistem FDI, gigi kaninus rahang atas kanan permanen ditulis ...', ['11', '13', '23', '33', '43'], 1, 'Kuadran 1 (rahang atas kanan), gigi ke-3 (kaninus) → 13.'],
            ],
        ];
    }
}
