<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Sanitizer HTML ringan berbasis whitelist (tanpa dependensi).
 * Dipakai untuk teks soal, opsi, dan pembahasan agar aman dari XSS,
 * sambil tetap mengizinkan format dasar & rumus LaTeX \( ... \).
 */
class HtmlSanitizer
{
    protected const ALLOWED = [
        'b' => [], 'strong' => [], 'i' => [], 'em' => [], 'u' => [], 's' => [], 'sub' => [], 'sup' => [],
        'br' => [], 'p' => [], 'span' => [], 'div' => [], 'small' => [], 'mark' => [], 'hr' => [],
        'ul' => [], 'ol' => [], 'li' => [], 'blockquote' => [],
        'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [], 'th' => ['colspan', 'rowspan'], 'td' => ['colspan', 'rowspan'],
        'img' => ['src', 'alt', 'width', 'height'], 'a' => ['href'],
    ];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }
        if (! str_contains($html, '<')) {
            return $html; // teks biasa, akan di-escape saat render
        }

        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();

        $root = $doc->getElementById('__root');
        if (! $root) {
            return e(strip_tags($html));
        }
        static::walk($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return $out;
    }

    protected static function walk(DOMNode $node): void
    {
        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $child = $node->childNodes->item($i);

            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);
                if (! array_key_exists($tag, self::ALLOWED)) {
                    if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'svg', 'math'], true)) {
                        $node->removeChild($child);
                        continue;
                    }
                    // Buang tag, pertahankan isi
                    static::walk($child);
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);
                    continue;
                }

                foreach (iterator_to_array($child->attributes) as $attr) {
                    $name = strtolower($attr->name);
                    if (! in_array($name, self::ALLOWED[$tag], true)) {
                        $child->removeAttribute($attr->name);
                        continue;
                    }
                    if (in_array($name, ['href', 'src'], true) && ! static::safeUrl($attr->value, $name === 'src')) {
                        $child->removeAttribute($attr->name);
                    }
                }
                if ($tag === 'a') {
                    $child->setAttribute('rel', 'nofollow noopener');
                    $child->setAttribute('target', '_blank');
                }
                if ($tag === 'img') {
                    $child->setAttribute('loading', 'lazy');
                }
                static::walk($child);
            } elseif ($child->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($child);
            }
        }
    }

    protected static function safeUrl(string $url, bool $isImage): bool
    {
        $url = trim($url);
        if (str_starts_with($url, '/')) {
            return ! str_starts_with($url, '//');
        }

        return (bool) preg_match('#^https?://#i', $url);
    }

    /** HTML siap tampil: plain text di-escape, newline jadi <br>. */
    public static function render(?string $value): string
    {
        $value = (string) $value;
        if ($value === '') {
            return '';
        }
        if (! str_contains($value, '<')) {
            return nl2br(e($value), false);
        }
        $clean = static::clean($value);

        // Jika admin menulis campuran teks & tag tanpa <p>/<br>, tetap hormati enter.
        if (! preg_match('/<(p|br|div|li|table)\b/i', $clean)) {
            $clean = nl2br($clean, false);
        }

        return $clean;
    }
}
