<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Tryout;

class ExportController extends Controller
{
    public function questionTemplate()
    {
        $rows = [
            ['subtes', 'topik', 'soal', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'kunci', 'bobot_a', 'bobot_b', 'bobot_c', 'bobot_d', 'bobot_e', 'pembahasan', 'tingkat'],
            ['tiu', 'Deret Angka', 'Lanjutan deret 2, 4, 8, 16, ... adalah', '24', '30', '32', '36', '64', 'C', '', '', '', '', '', 'Setiap suku dikali 2, maka 16 × 2 = 32.', '1'],
            ['tiu', 'Aljabar', 'Nilai dari \\(\\frac{3}{4} + \\frac{1}{2}\\) adalah', '\\(\\frac{5}{4}\\)', '\\(\\frac{4}{6}\\)', '1', '\\(\\frac{3}{8}\\)', '2', 'A', '', '', '', '', '', 'Samakan penyebut: \\(\\frac{3}{4}+\\frac{2}{4}=\\frac{5}{4}\\).', '2'],
            ['tkp', 'Pelayanan Publik', 'Seorang pasien lansia kebingungan mencari loket. Sikap Anda...', 'Membiarkan karena bukan tugas saya', 'Menunjukkan arah dari kejauhan', 'Meminta satpam membantu', 'Mengantar langsung sambil menjelaskan alurnya', 'Menyuruhnya bertanya ke petugas lain', '', '1', '2', '3', '5', '4', 'Sikap paling melayani adalah membantu langsung.', ''],
            ['skb', 'Asuhan Keperawatan', 'Tindakan pertama pada pasien syok hipovolemik adalah...', 'Memberi makan', 'Memasang akses IV & resusitasi cairan', 'Memberi analgetik', 'Mengukur tinggi badan', 'Memulangkan pasien', 'B', '', '', '', '', '', 'Prioritas syok hipovolemik: perbaiki volume intravaskular.', '2'],
        ];

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            foreach ($rows as $r) {
                fputcsv($out, $r);
            }
            fclose($out);
        }, 'template-impor-soal.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function tryoutResults(Tryout $tryout)
    {
        $tryout->load('category');

        return response()->streamDownload(function () use ($tryout) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            $head = ['Nama', 'Email', 'Percobaan', 'Nilai', 'Maks', 'Persentase', 'Benar', 'Salah', 'Kosong', 'Durasi (detik)', 'Lulus', 'Selesai'];
            if ($tryout->isSkd()) {
                array_splice($head, 3, 0, ['TWK', 'TIU', 'TKP']);
            }
            fputcsv($out, $head);
            Attempt::query()->with('user:id,name,email')->where('tryout_id', $tryout->id)->where('status', Attempt::STATUS_SUBMITTED)
                ->orderByDesc('score')->chunk(500, function ($rows) use ($out, $tryout) {
                    foreach ($rows as $a) {
                        $r = [$a->user?->name, $a->user?->email, $a->attempt_no, $a->score, $a->max_score, $a->percentage, $a->correct_count, $a->wrong_count, $a->empty_count, $a->duration_seconds, $a->is_passed === null ? '-' : ($a->is_passed ? 'Ya' : 'Tidak'), $a->submitted_at?->format('Y-m-d H:i')];
                        if ($tryout->isSkd()) {
                            array_splice($r, 3, 0, [$a->score_twk, $a->score_tiu, $a->score_tkp]);
                        }
                        fputcsv($out, $r);
                    }
                });
            fclose($out);
        }, 'hasil-'.$tryout->slug.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
