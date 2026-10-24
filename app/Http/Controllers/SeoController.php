<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Package;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public function sitemap()
    {
        return response($this->buildSitemap(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function buildSitemap(bool $fresh = false): string
    {
        if ($fresh) {
            Cache::forget('sitemap.xml');
        }

        return Cache::remember('sitemap.xml', 3600, function () {
            $urls = [
                [route('home'), now(), 'daily', '1.0'],
                [route('catalog.packages'), now(), 'daily', '0.9'],
                [route('catalog.single'), now(), 'weekly', '0.8'],
                [route('pages.faq'), now()->startOfMonth(), 'monthly', '0.5'],
                [route('pages.about'), now()->startOfMonth(), 'monthly', '0.4'],
                [route('pages.terms'), now()->startOfYear(), 'yearly', '0.2'],
                [route('pages.privacy'), now()->startOfYear(), 'yearly', '0.2'],
            ];
            foreach (Category::query()->active()->ordered()->get() as $c) {
                $urls[] = [route('catalog.category', $c), $c->updated_at, 'weekly', '0.8'];
            }
            foreach (Package::query()->active()->ordered()->get() as $p) {
                $urls[] = [route('catalog.package', $p), $p->updated_at, 'weekly', '0.8'];
            }

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
            foreach ($urls as [$loc, $mod, $freq, $prio]) {
                $xml .= '  <url><loc>'.e($loc).'</loc><lastmod>'.($mod ?? now())->toAtomString().'</lastmod><changefreq>'.$freq.'</changefreq><priority>'.$prio.'</priority></url>'."\n";
            }

            return $xml.'</urlset>';
        });
    }

    public function robots()
    {
        $lines = app()->isProduction() ? [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /dashboard',
            'Disallow: /keranjang',
            'Disallow: /checkout',
            'Disallow: /pesanan',
            'Disallow: /ujian',
            'Disallow: /hasil',
            'Disallow: /riwayat',
            'Disallow: /progres',
            'Disallow: /notifikasi',
            'Disallow: /profil',
            'Disallow: /tryout-saya',
            'Disallow: /livewire',
            'Disallow: /*?*kategori=',
            '',
            'Sitemap: '.route('sitemap'),
        ] : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** Verifikasi kunci IndexNow (Bing/Yandex). */
    public function indexNowKey(string $key)
    {
        abort_unless(config('services.indexnow.key') && hash_equals((string) config('services.indexnow.key'), $key), 404);

        return response($key, 200, ['Content-Type' => 'text/plain']);
    }
}
