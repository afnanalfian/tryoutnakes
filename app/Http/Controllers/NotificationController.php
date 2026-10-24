<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function open(Request $request, string $id)
    {
        $n = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $n->markAsRead();
        $url = $n->data['url'] ?? route('notifications');

        // Hanya izinkan redirect ke domain sendiri.
        return str_starts_with($url, url('/')) ? redirect()->to($url) : redirect()->route('notifications');
    }
}
