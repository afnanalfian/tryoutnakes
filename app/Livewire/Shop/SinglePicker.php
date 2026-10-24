<?php

namespace App\Livewire\Shop;

use App\Models\Category;
use App\Models\Tryout;
use App\Services\CartService;
use App\Support\Seo;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Halaman beli satuan: pilih SKD/SKB → (jika SKB) pilih kategori → pilih tryout.
 */
#[Layout('components.layouts.public')]
class SinglePicker extends Component
{
    #[Url(as: 'jenis', history: true)]
    public string $type = '';

    #[Url(as: 'kategori', history: true)]
    public string $category = '';

    public ?string $conflict = null;
    public ?int $pendingId = null;

    public function chooseType(string $type): void
    {
        $this->type = in_array($type, ['skd', 'skb'], true) ? $type : '';
        $this->category = $this->type === 'skd' ? (Category::query()->where('type', 'skd')->value('slug') ?? '') : '';
    }

    public function chooseCategory(string $slug): void
    {
        $this->category = $slug;
    }

    public function back(): void
    {
        if ($this->type === 'skb' && $this->category) {
            $this->category = '';
        } else {
            $this->type = '';
            $this->category = '';
        }
    }

    public function add(int $tryoutId, CartService $cart, bool $replace = false)
    {
        if (! auth()->check()) {
            session()->put('url.intended', route('catalog.single', array_filter(['jenis' => $this->type, 'kategori' => $this->category])));
            session()->flash('info', 'Masuk atau daftar akun dulu ya, lalu lanjutkan memilih tryout.');

            return $this->redirect(route('login'));
        }

        $res = $cart->add(auth()->user(), 'tryout', $tryoutId, $replace);
        if ($res['code'] === 'conflict') {
            $this->conflict = $res['message'];
            $this->pendingId = $tryoutId;
            $this->dispatch('open-modal', 'single-conflict');

            return null;
        }
        $this->conflict = null;
        $this->pendingId = null;
        $this->dispatch('close-modal', 'single-conflict');
        $this->dispatch('cart-updated');
        $this->dispatch('toast', type: $res['ok'] ? 'success' : 'error', message: $res['message']);

        return null;
    }

    public function remove(int $tryoutId): void
    {
        if ($user = auth()->user()) {
            $user->cartItems()->where('item_type', 'tryout')->where('item_id', $tryoutId)->delete();
            $this->dispatch('cart-updated');
        }
    }

    public function render(CartService $cart, Seo $seo)
    {
        $categories = Category::query()->active()->ordered()->withCount(['tryouts' => fn ($q) => $q->published()->where('is_free', false)])->get();
        $current = $this->category ? $categories->firstWhere('slug', $this->category) : null;
        if ($current && $this->type === '') {
            $this->type = $current->type;
        }

        $tryouts = $current
            ? Tryout::query()->published()->where('is_free', false)->where('category_id', $current->id)->with('category')->withCount('questions')->ordered()->get()
            : collect();

        $user = auth()->user();
        $summary = $user ? $cart->summary($user) : null;
        $inCart = $summary && $summary['mode'] === 'tryout' ? $summary['lines']->pluck('model.id') : collect();

        $seo->title($current ? 'Beli Tryout '.$current->label().' Satuan' : 'Beli Tryout Satuan SKD & SKB CPNS Nakes')
            ->description('Pilih sendiri tryout SKD atau SKB CPNS tenaga kesehatan yang ingin kamu kerjakan. Harga satuan terjangkau, akses langsung setelah pembayaran diverifikasi.')
            ->canonical(route('catalog.single'))
            ->breadcrumbs([['Beli Satuan', route('catalog.single')]]);

        return view('livewire.shop.single-picker', [
            'categories' => $categories,
            'skd' => $categories->where('type', 'skd'),
            'skb' => $categories->where('type', 'skb'),
            'current' => $current,
            'tryouts' => $tryouts,
            'summary' => $summary,
            'inCart' => $inCart,
            'owned' => $user?->ownedTryoutIds() ?? collect(),
        ]);
    }
}
