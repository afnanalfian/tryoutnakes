<?php

namespace App\Livewire\Admin\Questions;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionTopic;
use App\Models\Tryout;
use App\Support\HtmlSanitizer;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Impor soal massal dari CSV (bisa dibuat dari Excel / Google Sheets).
 * Kolom: subtes, topik, soal, opsi_a..opsi_e, kunci, bobot_a..bobot_e, pembahasan, tingkat
 */
#[Layout('components.layouts.admin')]
class Import extends Component
{
    use WithFileUploads;

    public $category_id = '';
    public $file;
    public $tryout_id = '';
    public array $rows = [];
    public array $problems = [];
    public bool $parsed = false;

    public function mount(): void
    {
        $this->category_id = (string) (request('kategori') ?: Category::query()->ordered()->value('id'));
    }

    public function updatedFile(): void
    {
        $this->parse();
    }

    public function updatedCategoryId(): void
    {
        $this->tryout_id = '';
        if ($this->file) {
            $this->parse();
        }
    }

    public function parse(): void
    {
        $this->validate(['file' => 'required|file|mimes:csv,txt|max:5120', 'category_id' => 'required|exists:categories,id']);
        $cat = Category::query()->findOrFail($this->category_id);
        $allowed = $cat->subtests();

        $path = $this->file->getRealPath();
        $first = (string) fgets(fopen($path, 'r'));
        $delimiter = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';

        $fh = fopen($path, 'r');
        $header = null;
        $this->rows = [];
        $this->problems = [];
        $line = 0;
        while (($data = fgetcsv($fh, 0, $delimiter)) !== false) {
            $line++;
            if ($header === null) {
                $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0] ?? '');
                $header = array_map(fn ($h) => strtolower(trim($h)), $data);
                continue;
            }
            if (count(array_filter($data, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $r = [];
            foreach ($header as $i => $h) {
                $r[$h] = trim((string) ($data[$i] ?? ''));
            }

            $sub = strtolower($r['subtes'] ?? '') ?: $allowed[0];
            $opts = [];
            foreach (['a', 'b', 'c', 'd', 'e'] as $k) {
                if (($r['opsi_'.$k] ?? '') !== '') {
                    $opts[] = ['text' => $r['opsi_'.$k], 'weight' => (int) ($r['bobot_'.$k] ?? 0)];
                }
            }
            $err = [];
            if (! in_array($sub, $allowed, true)) {
                $err[] = "subtes \"$sub\" tidak sesuai kategori (".implode('/', $allowed).')';
            }
            if (($r['soal'] ?? '') === '') {
                $err[] = 'kolom soal kosong';
            }
            if (count($opts) < 2) {
                $err[] = 'minimal 2 opsi';
            }
            $correct = null;
            if ($sub === 'tkp') {
                foreach ($opts as $o) {
                    if ($o['weight'] < 1 || $o['weight'] > 5) {
                        $err[] = 'bobot TKP harus 1–5 untuk setiap opsi';
                        break;
                    }
                }
            } else {
                $key = strtoupper($r['kunci'] ?? '');
                $correct = $key !== '' ? ord($key) - 65 : -1;
                if ($correct < 0 || $correct >= count($opts)) {
                    $err[] = 'kunci jawaban tidak valid';
                }
            }
            if ($err) {
                $this->problems[] = "Baris $line: ".implode('; ', $err);
                continue;
            }
            $this->rows[] = [
                'subtest' => $sub, 'topic' => $r['topik'] ?? '', 'text' => $r['soal'], 'options' => $opts, 'correct' => $correct,
                'explanation' => $r['pembahasan'] ?? '', 'difficulty' => in_array($r['tingkat'] ?? '', ['1', '2', '3'], true) ? (int) $r['tingkat'] : null,
            ];
        }
        fclose($fh);
        $this->parsed = true;
    }

    public function import()
    {
        if (! $this->rows) {
            return null;
        }
        $created = [];
        DB::transaction(function () use (&$created) {
            foreach ($this->rows as $r) {
                $topicId = $r['topic'] !== '' ? QuestionTopic::query()->firstOrCreate(['category_id' => $this->category_id, 'subtest' => $r['subtest'], 'name' => $r['topic']])->id : null;
                $q = Question::query()->create([
                    'category_id' => $this->category_id, 'subtest' => $r['subtest'], 'topic_id' => $topicId,
                    'question_text' => HtmlSanitizer::clean($r['text']), 'explanation' => HtmlSanitizer::clean($r['explanation']) ?: null,
                    'difficulty' => $r['difficulty'], 'created_by' => auth()->id(),
                ]);
                foreach ($r['options'] as $i => $o) {
                    $q->options()->create([
                        'order' => $i + 1, 'option_text' => HtmlSanitizer::clean($o['text']),
                        'is_correct' => $r['subtest'] !== 'tkp' && $r['correct'] === $i,
                        'weight' => $r['subtest'] === 'tkp' ? $o['weight'] : 0,
                    ]);
                }
                $created[] = $q->id;
            }
            if ($this->tryout_id) {
                $t = Tryout::query()->where('category_id', $this->category_id)->findOrFail($this->tryout_id);
                $start = (int) $t->questions()->max('question_tryout.order');
                $t->questions()->syncWithoutDetaching(collect($created)->mapWithKeys(fn ($id, $i) => [$id => ['order' => $start + $i + 1]])->all());
            }
        });

        session()->flash('success', count($created).' soal berhasil diimpor.');

        return $this->redirect(route('admin.questions', ['kategori' => $this->category_id]), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.questions.import', [
            'categories' => Category::query()->ordered()->get(),
            'tryouts' => Tryout::query()->where('category_id', $this->category_id)->ordered()->get(['id', 'title']),
        ])->title('Impor Soal');
    }
}
