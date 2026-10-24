<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Aset matematika (MathQuill untuk editor admin, KaTeX untuk render)
    |--------------------------------------------------------------------------
    | KaTeX sudah di-self-host di public/vendor/katex (cepat & tanpa CDN).
    | MathQuill + jQuery (hanya dipakai di editor admin) memakai CDN cdnjs;
    | bisa di-self-host juga lewat .env (lihat docs/MATHQUILL.md).
    */
    'katex_css' => env('KATEX_CSS', '/vendor/katex/katex.min.css'),
    'katex_js' => env('KATEX_JS', '/vendor/katex/katex.min.js'),
    'katex_autorender_js' => env('KATEX_AUTORENDER_JS', '/vendor/katex/contrib/auto-render.min.js'),
    'jquery_js' => env('JQUERY_JS', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js'),
    'mathquill_css' => env('MATHQUILL_CSS', 'https://cdnjs.cloudflare.com/ajax/libs/mathquill/0.10.1/mathquill.min.css'),
    'mathquill_js' => env('MATHQUILL_JS', 'https://cdnjs.cloudflare.com/ajax/libs/mathquill/0.10.1/mathquill.min.js'),

    /*
    | Peringkat hanya ditampilkan jika peserta (percobaan pertama) >= nilai ini.
    */
    'rank_min_participants' => (int) env('RANK_MIN_PARTICIPANTS', 5),

    /*
    | Toleransi (detik) jawaban yang masuk setelah waktu habis (latensi jaringan).
    */
    'answer_grace_seconds' => 20,

    /*
    | Komposisi & passing grade SKD (Kepmenpan-RB). Bisa diubah per tryout.
    */
    'skd' => [
        'composition' => ['twk' => 30, 'tiu' => 35, 'tkp' => 45],
        'duration' => 100,
        'passing_grade' => ['twk' => 65, 'tiu' => 80, 'tkp' => 166],
        'correct_point' => 5,
    ],

    'subtests' => [
        'twk' => 'TWK — Tes Wawasan Kebangsaan',
        'tiu' => 'TIU — Tes Intelegensia Umum',
        'tkp' => 'TKP — Tes Karakteristik Pribadi',
        'skb' => 'SKB — Kompetensi Bidang',
    ],

    'option_labels' => ['A', 'B', 'C', 'D', 'E'],

    /*
    | Akun admin pertama (dipakai AdminSeeder). Ganti password setelah login!
    */
    'admin' => [
        'name' => env('ADMIN_NAME', 'Administrator'),
        'email' => env('ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];
