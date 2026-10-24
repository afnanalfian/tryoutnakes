<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Package;
use App\Models\Question;
use App\Models\Tryout;
use App\Models\User;
use App\Support\PublicCache;
use App\Support\Seo;

class HomeController extends Controller
{
    public function index(Seo $seo)
    {
        $data = PublicCache::remember('home', 600, function () {
            return [
                'categories' => Category::query()->active()->ordered()
                    ->withCount(['tryouts' => fn ($q) => $q->published()])->get(),
                'packages' => Package::query()->active()->ordered()->with('tryouts.category')->limit(6)->get(),
                'freeTryouts' => Tryout::query()->published()->where('is_free', true)->with('category')->withCount('questions')->ordered()->limit(3)->get(),
                'banners' => Banner::query()->live()->get(),
                'faqs' => Faq::query()->where('is_active', true)->orderBy('sort_order')->limit(8)->get(),
                'stats' => [
                    'questions' => Question::query()->count(),
                    'tryouts' => Tryout::query()->published()->count(),
                    'users' => User::query()->where('role', User::ROLE_USER)->count(),
                ],
            ];
        });

        $seo->title('Tryout SKD & SKB CPNS Tenaga Kesehatan Online')
            ->description(setting('site_description'))
            ->canonical(url('/'))
            ->schema([
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => setting('site_name'),
                'url' => url('/'),
                'inLanguage' => 'id-ID',
            ]);

        if ($data['faqs']->isNotEmpty()) {
            $seo->schema([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $data['faqs']->map(fn ($f) => [
                    '@type' => 'Question',
                    'name' => $f->question,
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($f->answer)],
                ])->all(),
            ]);
        }

        if ($data['packages']->isNotEmpty()) {
            $seo->schema([
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => 'Paket Tryout CPNS Nakes',
                'itemListElement' => $data['packages']->values()->map(fn ($p, $i) => [
                    '@type' => 'ListItem', 'position' => $i + 1, 'url' => route('catalog.package', $p), 'name' => $p->name,
                ])->all(),
            ]);
        }

        return view('pages.home', $data);
    }
}
