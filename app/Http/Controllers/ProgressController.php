<?php

namespace App\Http\Controllers;

use App\Services\ProgressService;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function index(Request $request, ProgressService $progress)
    {
        $user = $request->user();
        $categories = $progress->categoriesWithAttempts($user);
        $current = $categories->firstWhere('slug', $request->string('kategori')->toString()) ?? $categories->first();

        return view('user.progress', [
            'categories' => $categories,
            'current' => $current,
            'data' => $current ? $progress->forCategory($user, $current) : null,
        ]);
    }
}
