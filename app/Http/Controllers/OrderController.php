<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()->where('user_id', $request->user()->id)->with('items')->latest()->paginate(10);

        return view('user.orders', compact('orders'));
    }

    /** Bukti bayar disimpan di disk privat; hanya pemilik & admin yang bisa melihat. */
    public function proof(Request $request, Order $order)
    {
        abort_unless($request->user()->isAdmin() || $order->user_id === $request->user()->id, 403);
        abort_unless($order->proof_path && Storage::disk('local')->exists($order->proof_path), 404);

        return response()->file(Storage::disk('local')->path($order->proof_path), [
            'Cache-Control' => 'private, max-age=600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function invoice(Request $request, Order $order)
    {
        abort_unless($request->user()->isAdmin() || $order->user_id === $request->user()->id, 403);
        $order->load('items', 'user', 'paymentMethod');

        return view('user.invoice', compact('order'));
    }
}
