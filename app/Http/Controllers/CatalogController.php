<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Package;
use App\Models\Tryout;
use App\Support\PublicCache;
use App\Support\Seo;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function packages(Request $request, Seo $seo)
    {
        $filter = $request->string('kategori')->toString();
        $categories = PublicCache::remember('catalog:categories', 3600, fn () => Category::query()->active()->ordered()->get());

        $packages = PublicCache::remember('catalog:packages', 600, fn () => Package::query()->active()->ordered()
            ->with(['tryouts' => fn ($q) => $q->published()->with('category')])->get());

        if ($filter) {
            $packages = $packages->filter(fn ($p) => $p->tryouts->contains(fn ($t) => $t->category?->slug === $filter))->values();
        }

        $seo->title('Paket Tryout SKD & SKB CPNS Nakes')
            ->description('Pilih paket tryout CPNS tenaga kesehatan: SKD (TWK, TIU, TKP), SKB Perawat, Bidan, Farmasi, Gizi, Fisioterapi, Okupasi Terapi, Terapi Wicara, Teknik Gigi, dan paket kombinasi SKD + SKB yang lebih hemat.')
            ->canonical(route('catalog.packages'))
            ->breadcrumbs([['Paket Tryout', route('catalog.packages')]]);
        if ($filter) {
            $seo->noindex();
        }

        return view('catalog.packages', compact('packages', 'categories', 'filter'));
    }

    public function package(Package $package, Seo $seo)
    {
        abort_unless($package->is_active, 404);
        $package->load(['tryouts' => fn ($q) => $q->published()->with('category')->withCount('questions')]);
        $owned = auth()->user()?->ownedTryoutIds() ?? collect();

        $related = PublicCache::remember('package:related:'.$package->id, 600, fn () => Package::query()->active()->whereKeyNot($package->id)
            ->ordered()->with('tryouts.category')->limit(3)->get());

        $seo->title($package->name)
            ->description($package->tagline ?: $package->description ?: 'Paket '.$package->name.' berisi '.$package->tryouts->count().' tryout CPNS nakes lengkap dengan pembahasan.')
            ->canonical(route('catalog.package', $package))
            ->image($package->image ? $package->imageUrl() : null)
            ->type('product')
            ->breadcrumbs([['Paket Tryout', route('catalog.packages')], [$package->name, route('catalog.package', $package)]])
            ->schema([
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $package->name,
                'description' => strip_tags($package->description ?: $package->tagline ?: $package->name),
                'image' => $package->imageUrl(),
                'brand' => ['@type' => 'Brand', 'name' => setting('site_name')],
                'category' => 'Tryout CPNS',
                'offers' => [
                    '@type' => 'Offer',
                    'price' => $package->price,
                    'priceCurrency' => 'IDR',
                    'availability' => 'https://schema.org/InStock',
                    'url' => route('catalog.package', $package),
                ],
            ]);

        return view('catalog.package', compact('package', 'owned', 'related'));
    }

    public function category(Category $category, Seo $seo)
    {
        abort_unless($category->is_active, 404);

        $tryouts = Tryout::query()->published()->where('category_id', $category->id)->with('category')
            ->withCount('questions')->ordered()->get();
        $packages = Package::query()->active()->ordered()
            ->whereHas('tryouts', fn ($q) => $q->where('category_id', $category->id))
            ->with('tryouts.category')->get();
        $owned = auth()->user()?->ownedTryoutIds() ?? collect();

        $name = $category->isSkd() ? 'SKD CPNS (TWK, TIU, TKP)' : 'SKB CPNS '.$category->name;
        $seo->title('Tryout '.$name.' Online')
            ->description($category->description ?: 'Latihan dan tryout '.$name.' dengan sistem CAT, pembahasan lengkap, dan peringkat peserta.')
            ->canonical(route('catalog.category', $category))
            ->image($category->image ? $category->imageUrl() : null)
            ->breadcrumbs([['Kategori', route('catalog.packages')], [$category->label(), route('catalog.category', $category)]]);

        return view('catalog.category', compact('category', 'tryouts', 'packages', 'owned', 'name'));
    }
}
