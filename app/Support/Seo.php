<?php

namespace App\Support;

/**
 * Pengelola meta SEO per halaman (title, description, canonical, OG, JSON-LD).
 * Dipakai di controller: app(Seo::class)->title('...')->description('...');
 */
class Seo
{
    protected ?string $title = null;
    protected ?string $description = null;
    protected ?string $canonical = null;
    protected ?string $image = null;
    protected string $type = 'website';
    protected bool $noindex = false;
    protected array $schemas = [];
    protected array $breadcrumbs = [];

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function description(?string $description): static
    {
        if ($description) {
            $this->description = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($description))), 158, '…');
        }

        return $this;
    }

    public function canonical(string $url): static
    {
        $this->canonical = $url;

        return $this;
    }

    public function image(?string $url): static
    {
        if ($url) {
            $this->image = $url;
        }

        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function noindex(bool $value = true): static
    {
        $this->noindex = $value;

        return $this;
    }

    public function schema(array $schema): static
    {
        $this->schemas[] = $schema;

        return $this;
    }

    /** @param array<int, array{0:string,1:string}> $items [label, url] */
    public function breadcrumbs(array $items): static
    {
        $this->breadcrumbs = $items;

        return $this;
    }

    public function data(): array
    {
        $site = setting('site_name');
        $title = $this->title ? $this->title.' | '.$site : $site.' — '.setting('site_tagline');
        $schemas = $this->schemas;

        if ($this->breadcrumbs) {
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => collect([['Beranda', url('/')], ...$this->breadcrumbs])->values()->map(fn ($b, $i) => [
                    '@type' => 'ListItem', 'position' => $i + 1, 'name' => $b[0], 'item' => $b[1],
                ])->all(),
            ];
        }

        return [
            'title' => $title,
            'description' => $this->description ?? setting('site_description'),
            'canonical' => $this->canonical ?? url()->current(),
            'image' => $this->image ?? asset('images/brand/og-default.jpg'),
            'type' => $this->type,
            'robots' => $this->noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large',
            'schemas' => $schemas,
            'breadcrumbs' => $this->breadcrumbs,
        ];
    }
}
