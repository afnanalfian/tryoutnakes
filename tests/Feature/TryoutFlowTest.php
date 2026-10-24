<?php

namespace Tests\Feature;

use App\Livewire\Admin\Questions\Form as QuestionForm;
use App\Livewire\Admin\Questions\Import;
use App\Livewire\Admin\Tryouts\Questions as TryoutQuestions;
use App\Livewire\Shop\Checkout;
use App\Models\Attempt;
use App\Models\Category;
use App\Models\Order;
use App\Models\Package;
use App\Models\Question;
use App\Models\Tryout;
use App\Models\User;
use App\Services\CartService;
use App\Services\ExamService;
use App\Services\OrderService;
use App\Services\RankingService;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class TryoutFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    protected function user(string $role = 'user'): User
    {
        return User::query()->create(['name' => 'Uji '.$role, 'email' => uniqid().'@t.id', 'password' => 'password123', 'role' => $role]);
    }

    protected function question(Category $c, string $sub, int $correct = 0, ?array $weights = null): Question
    {
        $q = Question::query()->create(['category_id' => $c->id, 'subtest' => $sub, 'question_text' => 'Soal '.$sub.' '.uniqid()]);
        foreach (range(0, 4) as $i) {
            $q->options()->create(['order' => $i + 1, 'option_text' => 'Opsi '.$i, 'is_correct' => ! $weights && $i === $correct, 'weight' => $weights[$i] ?? 0]);
        }

        return $q->load('options');
    }

    protected function tryout(Category $c, array $questions, array $extra = []): Tryout
    {
        $t = Tryout::query()->create(array_merge(['category_id' => $c->id, 'title' => 'TO '.uniqid(), 'slug' => 'to-'.uniqid(), 'duration_minutes' => 60, 'is_published' => true], $extra));
        $t->questions()->sync(collect($questions)->mapWithKeys(fn ($q, $i) => [$q->id => ['order' => $i + 1]])->all());

        return $t;
    }

    public function test_skd_scoring_with_tkp_weights_and_passing_grade(): void
    {
        $skd = Category::query()->where('code', 'skd')->first();
        $twk = $this->question($skd, 'twk', 1);
        $tiu = $this->question($skd, 'tiu', 2);
        $tkp = $this->question($skd, 'tkp', weights: [1, 2, 3, 4, 5]);
        $t = $this->tryout($skd, [$twk, $tiu, $tkp], ['passing_grade' => ['twk' => 5, 'tiu' => 5, 'tkp' => 4]]);
        $u = $this->user();
        app(OrderService::class)->grantTryouts($u, [$t->id]);

        $exam = app(ExamService::class);
        $a = $exam->start($u, $t);
        $exam->saveAnswer($a, $twk->id, $twk->options[1]->id, false); // benar +5
        $exam->saveAnswer($a, $tiu->id, $tiu->options[0]->id, true);  // salah 0
        $exam->saveAnswer($a, $tkp->id, $tkp->options[3]->id, false); // bobot 4
        $a = $exam->submit($a);

        $this->assertSame(9.0, $a->score);
        $this->assertSame(15.0, $a->max_score);
        $this->assertSame([5, 0, 4], [$a->score_twk, $a->score_tiu, $a->score_tkp]);
        $this->assertFalse($a->is_passed); // TIU 0 < 5
        $this->assertSame(3, $a->answers()->count());
    }

    public function test_skb_scale_score(): void
    {
        $cat = Category::query()->where('code', 'bidan')->first();
        $qs = collect(range(1, 4))->map(fn () => $this->question($cat, 'skb', 0));
        $t = $this->tryout($cat, $qs->all(), ['scale_max' => 400]);
        $u = $this->user();
        app(OrderService::class)->grantTryouts($u, [$t->id]);
        $exam = app(ExamService::class);
        $a = $exam->start($u, $t);
        $exam->saveAnswer($a, $qs[0]->id, $qs[0]->options[0]->id, false);
        $exam->saveAnswer($a, $qs[1]->id, $qs[1]->options[0]->id, false);
        $exam->saveAnswer($a, $qs[2]->id, $qs[2]->options[3]->id, false);
        $a = $exam->submit($a);
        $this->assertSame(200.0, $a->score);     // 2/4 × 400
        $this->assertSame(50.0, $a->percentage);
        $this->assertSame([2, 1, 1], [$a->correct_count, $a->wrong_count, $a->empty_count]);
    }

    public function test_cart_cannot_mix_and_converts_to_matching_package(): void
    {
        $cat = Category::query()->where('code', 'perawat')->first();
        $q = $this->question($cat, 'skb');
        $t1 = $this->tryout($cat, [$q]);
        $t2 = $this->tryout($cat, [$q]);
        $p = Package::query()->create(['name' => 'Paket', 'slug' => 'paket', 'price' => 30000]);
        $p->tryouts()->sync([$t1->id, $t2->id]);
        $u = $this->user();
        $cart = app(CartService::class);

        $this->assertTrue($cart->add($u, 'tryout', $t1->id)['ok']);
        $this->assertSame('conflict', $cart->add($u, 'package', $p->id)['code']);
        $this->assertTrue($cart->add($u, 'tryout', $t2->id)['ok']);

        $summary = $cart->summary($u);
        $this->assertSame($p->id, $summary['match']->id);
        $this->assertSame(30000, $summary['total']);

        $order = app(OrderService::class)->checkout($u);
        $this->assertSame('package', $order->type);
        $this->assertSame(10000, $order->discount);
        $this->assertSame(0, $u->cartItems()->count());

        app(OrderService::class)->approve($order, $this->user('admin'));
        $this->assertTrue($u->fresh()->canAccessTryout($t1) && $u->fresh()->canAccessTryout($t2));
        $this->assertSame('owned', $cart->add($u->fresh(), 'tryout', $t1->id)['code']);
    }

    public function test_checkout_livewire_and_proof_upload_flow(): void
    {
        Storage::fake('local');
        $cat = Category::query()->where('code', 'gizi')->first() ?? Category::query()->where('code', 'tenaga-gizi')->first();
        $t = $this->tryout($cat, [$this->question($cat, 'skb')]);
        $u = $this->user();
        app(CartService::class)->add($u, 'tryout', $t->id);

        Livewire::actingAs($u)->test(Checkout::class)->call('place')->assertHasErrors('agree');
        Livewire::actingAs($u)->test(Checkout::class)->set('agree', true)->call('place')->assertRedirect();
        $order = Order::query()->firstOrFail();
        $this->assertSame(20000, $order->total);

        Livewire::actingAs($u)->test(\App\Livewire\Shop\OrderPayment::class, ['order' => $order])
            ->set('proof', UploadedFile::fake()->image('bukti.jpg', 400, 600))
            ->call('submitProof')->assertHasNoErrors();
        $this->assertSame(Order::STATUS_WAITING, $order->fresh()->status);

        // Bukti hanya untuk pemilik/admin
        $this->actingAs($this->user())->get(route('orders.proof', $order))->assertForbidden();
        $this->actingAs($u)->get(route('orders.proof', $order))->assertOk();
    }

    public function test_ranking_requires_five_participants(): void
    {
        $cat = Category::query()->where('code', 'bidan')->first();
        $q = $this->question($cat, 'skb');
        $t = $this->tryout($cat, [$q], ['is_free' => true]);
        $exam = app(ExamService::class);
        $attempts = [];
        foreach (range(1, 5) as $i) {
            $u = $this->user();
            $a = $exam->start($u, $t);
            if ($i % 2) {
                $exam->saveAnswer($a, $q->id, $q->options[0]->id, false);
            }
            $attempts[] = $exam->submit($a);
            if ($i === 4) {
                $this->assertFalse(app(RankingService::class)->rankFor($attempts[0])['eligible']);
            }
        }
        $r = app(RankingService::class)->rankFor($attempts[4]);
        $this->assertTrue($r['eligible']);
        $this->assertSame(5, $r['participants']);
    }

    public function test_answers_rejected_after_deadline_and_auto_finalize(): void
    {
        $cat = Category::query()->where('code', 'bidan')->first();
        $q = $this->question($cat, 'skb');
        $t = $this->tryout($cat, [$q], ['is_free' => true, 'duration_minutes' => 1]);
        $u = $this->user();
        $a = app(ExamService::class)->start($u, $t);
        $this->travel(5)->minutes();
        $this->actingAs($u)->postJson(route('exam.answer', $a), ['question_id' => $q->id, 'option_id' => $q->options[0]->id])->assertStatus(410);
        $this->artisan('attempts:finalize-expired')->assertSuccessful();
        $this->assertSame(Attempt::STATUS_SUBMITTED, $a->fresh()->status);
    }

    public function test_access_control(): void
    {
        $cat = Category::query()->where('code', 'bidan')->first();
        $t = $this->tryout($cat, [$this->question($cat, 'skb')]);
        $u = $this->user();
        $this->actingAs($u)->get('/admin')->assertForbidden();
        $this->actingAs($u)->post(route('exam.start', $t))->assertSessionHas('error');
        auth()->logout();
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_admin_question_form_tryout_autofill_and_import(): void
    {
        $admin = $this->user('admin');
        $skd = Category::query()->where('code', 'skd')->first();

        Livewire::actingAs($admin)->test(QuestionForm::class)
            ->set('category_id', (string) $skd->id)->set('subtest', 'tkp')
            ->set('question_text', 'Sikap terbaik <script>alert(1)</script> \(x^2\)')
            ->set('options.0.text', 'a')->set('options.1.text', 'b')->set('options.2.text', 'c')->set('options.3.text', 'd')->set('options.4.text', 'e')
            ->call('save')->assertHasErrors('options.0.weight')
            ->set('options.0.weight', 1)->set('options.1.weight', 2)->set('options.2.weight', 3)->set('options.3.weight', 4)->set('options.4.weight', 5)
            ->set('newTopic', 'Pelayanan')
            ->call('save')->assertHasNoErrors();
        $q = Question::query()->latest('id')->first();
        $this->assertStringNotContainsString('<script', $q->question_text);
        $this->assertSame([1, 2, 3, 4, 5], $q->options->pluck('weight')->all());
        $this->assertNotNull($q->topic_id);

        $csv = "subtes;topik;soal;opsi_a;opsi_b;opsi_c;opsi_d;opsi_e;kunci\ntiu;Deret;1,2,3,...;4;5;6;7;8;A\ntwk;;Pasal?;a;b;c;d;e;Z\n";
        $file = UploadedFile::fake()->createWithContent('soal.csv', $csv);
        Livewire::actingAs($admin)->test(Import::class)->set('category_id', (string) $skd->id)->set('file', $file)
            ->assertSet('rows', fn ($r) => count($r) === 1)->assertSet('problems', fn ($p) => count($p) === 1)
            ->call('import');
        $this->assertSame(2, Question::query()->count());

        $t = Tryout::query()->create(['category_id' => $skd->id, 'title' => 'SKD X', 'slug' => 'skd-x', 'duration_minutes' => 100]);
        Livewire::actingAs($admin)->test(TryoutQuestions::class, ['tryout' => $t])
            ->set('auto', ['twk' => 0, 'tiu' => 5, 'tkp' => 5])->call('autoFill');
        $this->assertSame(2, $t->questions()->count());
    }
}
