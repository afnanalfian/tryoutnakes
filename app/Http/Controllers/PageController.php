<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Support\Seo;

class PageController extends Controller
{
    public function about(Seo $seo)
    {
        $seo->title('Tentang Kami')->description('Kenali '.setting('site_name').': platform tryout CPNS khusus tenaga kesehatan dengan simulasi CAT, pembahasan, dan analitik progres.')
            ->breadcrumbs([['Tentang Kami', route('pages.about')]]);

        return view('pages.about');
    }

    public function faq(Seo $seo)
    {
        $faqs = Faq::query()->where('is_active', true)->orderBy('sort_order')->get();
        $seo->title('Pertanyaan yang Sering Diajukan (FAQ)')->description('Jawaban seputar tryout SKD & SKB CPNS nakes: cara membeli, pembayaran, sistem penilaian, peringkat, dan akses tryout.')
            ->breadcrumbs([['FAQ', route('pages.faq')]]);
        if ($faqs->isNotEmpty()) {
            $seo->schema([
                '@context' => 'https://schema.org', '@type' => 'FAQPage',
                'mainEntity' => $faqs->map(fn ($f) => ['@type' => 'Question', 'name' => $f->question, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($f->answer)]])->all(),
            ]);
        }

        return view('pages.faq', compact('faqs'));
    }

    public function terms(Seo $seo)
    {
        $seo->title('Syarat & Ketentuan')->breadcrumbs([['Syarat & Ketentuan', route('pages.terms')]]);

        return view('pages.terms');
    }

    public function privacy(Seo $seo)
    {
        $seo->title('Kebijakan Privasi')->breadcrumbs([['Kebijakan Privasi', route('pages.privacy')]]);

        return view('pages.privacy');
    }
}
