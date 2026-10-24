@php($seo = app(\App\Support\Seo::class)->data())
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
<meta name="robots" content="{{ ($noindex ?? false) ? 'noindex, nofollow' : $seo['robots'] }}">
<link rel="canonical" href="{{ $seo['canonical'] }}">
<meta name="theme-color" content="#172a4d">
<meta name="format-detection" content="telephone=no">

{{-- Open Graph / Twitter --}}
<meta property="og:site_name" content="{{ setting('site_name') }}">
<meta property="og:locale" content="id_ID">
<meta property="og:type" content="{{ $seo['type'] }}">
<meta property="og:title" content="{{ $title ?? $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
<meta property="og:image" content="{{ $seo['image'] }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title ?? $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
<meta name="twitter:image" content="{{ $seo['image'] }}">

@if(setting('google_site_verification'))<meta name="google-site-verification" content="{{ setting('google_site_verification') }}">@endif
@if(setting('bing_site_verification'))<meta name="msvalidate.01" content="{{ setting('bing_site_verification') }}">@endif

<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('images/brand/apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">

<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
<link rel="stylesheet" href="https://fonts.bunny.net/css?family=fraunces:500,600,700|plus-jakarta-sans:400,500,600,700,800&display=swap">

@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles

<script>
    window.APP_MATH = {
        katexCss: @js(config('tryout.katex_css')),
        katexJs: @js(config('tryout.katex_js')),
        autoRenderJs: @js(config('tryout.katex_autorender_js')),
        jquery: @js(config('tryout.jquery_js')),
        mathquillCss: @js(config('tryout.mathquill_css')),
        mathquillJs: @js(config('tryout.mathquill_js')),
    };
</script>

@php($org = ['@context' => 'https://schema.org', '@type' => 'EducationalOrganization', 'name' => setting('site_name'), 'url' => url('/'), 'logo' => asset('images/brand/logo-512.png'), 'description' => setting('site_description')])
<script type="application/ld+json">{!! json_encode($org, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@foreach($seo['schemas'] as $schema)
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endforeach

@if(setting('ga_measurement_id') && app()->isProduction())
<script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('ga_measurement_id') }}"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config',@js(setting('ga_measurement_id')));</script>
@endif
