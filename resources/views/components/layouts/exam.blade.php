@props(['title' => null])
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['title' => ($title ?? 'Ujian').' | '.setting('site_name'), 'noindex' => true])
</head>
<body class="min-h-screen bg-paper font-sans">
    {{ $slot }}
    @livewireScriptConfig
</body>
</html>
