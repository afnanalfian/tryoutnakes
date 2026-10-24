<?php

use App\Support\Settings;
use Illuminate\Support\Facades\Storage;

if (! function_exists('setting')) {
    /** Ambil nilai pengaturan situs (di-cache). */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(Settings::class)->get($key, $default);
    }
}

if (! function_exists('rupiah')) {
    function rupiah(int|float|null $amount, bool $prefix = true): string
    {
        $formatted = number_format((float) $amount, 0, ',', '.');

        return $prefix ? 'Rp'.$formatted : $formatted;
    }
}

if (! function_exists('media_url')) {
    /** URL file pada disk public (gambar soal, paket, banner, dsb). */
    function media_url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (str_starts_with($path, 'http') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}

if (! function_exists('normalize_phone')) {
    /** 0812xxxx / +62812xxxx / 62812xxxx -> 62812xxxx */
    function normalize_phone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        return $digits ?: null;
    }
}

if (! function_exists('wa_link')) {
    function wa_link(?string $phone, string $text = ''): ?string
    {
        $number = normalize_phone($phone);
        if (! $number) {
            return null;
        }

        return 'https://wa.me/'.$number.($text !== '' ? '?text='.rawurlencode($text) : '');
    }
}

if (! function_exists('rich')) {
    /** Render teks soal/opsi/pembahasan: HTML disanitasi, newline -> <br>. */
    function rich(?string $html): string
    {
        return \App\Support\HtmlSanitizer::render($html);
    }
}

if (! function_exists('score_format')) {
    function score_format(float|int|null $score): string
    {
        if ($score === null) {
            return '-';
        }

        return fmod((float) $score, 1.0) === 0.0
            ? number_format($score, 0, ',', '.')
            : number_format($score, 2, ',', '.');
    }
}
