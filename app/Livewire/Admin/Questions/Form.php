<?php

namespace App\Livewire\Admin\Questions;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionTopic;
use App\Support\HtmlSanitizer;
use App\Support\ImageService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Form soal (mirip Satya): teks + sisip rumus MathQuill + pratinjau KaTeX,
 * gambar soal/opsi, kunci jawaban (TWK/TIU/SKB) atau bobot 1–5 (TKP).
 */
#[Layout('components.layouts.admin')]
class Form extends Component
{
    use WithFileUploads;

    public ?Question $question = null;
    public $category_id = '';
    public string $subtest = '';
    public $topic_id = '';
    public string $newTopic = '';
    public $difficulty = 2;
    public string $question_text = '';
    public string $explanation = '';
    public ?string $image = null;
    public $imageUpload;
    public bool $removeImage = false;
    public array $options = [];
    public array $optionUploads = [];
    public $correct = 0;

    public function mount(?Question $question = null): void
    {
        if ($question?->exists) {
            $this->question = $question->load('options');
            $this->category_id = (string) $question->category_id;
            $this->subtest = $question->subtest;
            $this->topic_id = (string) $question->topic_id;
            $this->difficulty = $question->difficulty ?? 2;
            $this->question_text = $question->question_text;
            $this->explanation = (string) $question->explanation;
            $this->image = $question->image;
            $this->options = $question->options->map(fn ($o) => [
                'key' => 'o'.$o->id, 'id' => $o->id, 'text' => (string) $o->option_text, 'image' => $o->image, 'weight' => $o->weight ?: '', 'remove_image' => false,
            ])->values()->all();
            $this->correct = (int) $question->options->search(fn ($o) => $o->is_correct);
        } else {
            $this->category_id = (string) (request('kategori') ?: Category::query()->ordered()->value('id'));
            $this->subtest = (string) request('subtest', '');
            $this->topic_id = (string) request('topik', '');
            $this->options = array_map(fn () => $this->blankOption(), range(1, 5));
        }
        $this->ensureSubtest();
    }

    protected function blankOption(): array
    {
        return ['key' => \Illuminate\Support\Str::random(8), 'id' => null, 'text' => '', 'image' => null, 'weight' => '', 'remove_image' => false];
    }

    protected function cat(): ?Category
    {
        return Category::query()->find($this->category_id);
    }

    protected function ensureSubtest(): void
    {
        $subs = $this->cat()?->subtests() ?? ['skb'];
        if (! in_array($this->subtest, $subs, true)) {
            $this->subtest = $subs[0];
        }
    }

    public function updatedCategoryId(): void
    {
        $this->topic_id = '';
        $this->ensureSubtest();
    }

    public function updatedSubtest(): void
    {
        $this->topic_id = '';
    }

    public function addOption(): void
    {
        if (count($this->options) < 6) {
            $this->options[] = $this->blankOption();
        }
    }

    public function removeOption(int $i): void
    {
        if (count($this->options) <= 2) {
            return;
        }
        array_splice($this->options, $i, 1);
        $uploads = $this->optionUploads;
        unset($uploads[$i]);
        $this->optionUploads = array_values($uploads);
        if ((int) $this->correct === $i) {
            $this->correct = 0;
        } elseif ((int) $this->correct > $i) {
            $this->correct = (int) $this->correct - 1;
        }
    }

    public function save(bool $another = false)
    {
        $isTkp = $this->subtest === 'tkp';
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'subtest' => 'required|in:twk,tiu,tkp,skb',
            'difficulty' => 'nullable|integer|in:1,2,3',
            'question_text' => 'required|string|max:20000',
            'explanation' => 'nullable|string|max:20000',
            'imageUpload' => 'nullable|image|max:3072',
            'options' => 'required|array|min:2|max:6',
            'optionUploads.*' => 'nullable|image|max:2048',
            'newTopic' => 'nullable|string|max:100',
        ];
        foreach ($this->options as $i => $o) {
            $hasImage = ($o['image'] && ! $o['remove_image']) || ! empty($this->optionUploads[$i]);
            $rules["options.$i.text"] = ($hasImage ? 'nullable' : 'required').'|string|max:5000';
            if ($isTkp) {
                $rules["options.$i.weight"] = 'required|integer|min:1|max:5';
            }
        }
        if (! $isTkp) {
            $rules['correct'] = 'required|integer|min:0|max:'.(count($this->options) - 1);
        }
        $this->validate($rules, [
            'question_text.required' => 'Teks soal wajib diisi.',
            'options.*.text.required' => 'Isi teks opsi (atau unggah gambar opsi).',
            'options.*.weight.required' => 'Bobot TKP wajib (1–5).',
        ]);

        DB::transaction(function () use ($isTkp) {
            $topicId = $this->topic_id ?: null;
            if (trim($this->newTopic) !== '') {
                $topicId = QuestionTopic::query()->firstOrCreate(['category_id' => $this->category_id, 'subtest' => $this->subtest, 'name' => trim($this->newTopic)])->id;
            }

            $q = $this->question ?? new Question(['created_by' => auth()->id()]);
            $image = $this->image;
            if ($this->removeImage || $this->imageUpload) {
                ImageService::delete($image);
                $image = null;
            }
            if ($this->imageUpload) {
                $image = ImageService::store($this->imageUpload, 'questions', 1400);
            }
            $q->fill([
                'category_id' => $this->category_id,
                'subtest' => $this->subtest,
                'topic_id' => $topicId,
                'difficulty' => $this->difficulty ?: null,
                'question_text' => HtmlSanitizer::clean($this->question_text),
                'explanation' => HtmlSanitizer::clean($this->explanation) ?: null,
                'image' => $image,
            ])->save();

            $keep = [];
            foreach ($this->options as $i => $o) {
                $opt = $o['id'] ? $q->options()->find($o['id']) : null;
                $img = $opt?->image;
                if (($o['remove_image'] ?? false) || ! empty($this->optionUploads[$i])) {
                    ImageService::delete($img);
                    $img = null;
                }
                if (! empty($this->optionUploads[$i])) {
                    $img = ImageService::store($this->optionUploads[$i], 'options', 900);
                }
                $data = [
                    'order' => $i + 1,
                    'option_text' => HtmlSanitizer::clean($o['text'] ?? ''),
                    'image' => $img,
                    'is_correct' => ! $isTkp && (int) $this->correct === $i,
                    'weight' => $isTkp ? (int) $o['weight'] : 0,
                ];
                $opt = $opt ? tap($opt)->update($data) : $q->options()->create($data);
                $keep[] = $opt->id;
            }
            $q->options()->whereNotIn('id', $keep)->get()->each(function ($o) {
                ImageService::delete($o->image);
                $o->delete();
            });
            $this->question = $q;
        });

        session()->flash('success', 'Soal disimpan.');
        if ($another) {
            return $this->redirect(route('admin.questions.create', ['kategori' => $this->category_id, 'subtest' => $this->subtest, 'topik' => $this->question->topic_id]), navigate: true);
        }

        return $this->redirect(route('admin.questions', ['kategori' => $this->category_id, 'subtest' => $this->subtest]), navigate: true);
    }

    public function render()
    {
        $cat = $this->cat();

        return view('livewire.admin.questions.form', [
            'categories' => Category::query()->ordered()->get(),
            'cat' => $cat,
            'topics' => QuestionTopic::query()->where('category_id', $this->category_id)->where('subtest', $this->subtest)->orderBy('name')->get(),
            'usedIn' => $this->question ? $this->question->tryouts()->pluck('title') : collect(),
        ])->title($this->question ? 'Edit Soal' : 'Tambah Soal');
    }
}
