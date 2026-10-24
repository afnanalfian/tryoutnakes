<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
    <title>Invoice {{ $order->code }} — {{ setting('site_name') }}</title>
    @vite(['resources/css/app.css'])
    <style>@media print { .no-print { display: none !important } body { background: #fff } }</style>
</head>
<body class="bg-paper p-4 font-sans sm:p-10">
    <div class="mx-auto max-w-2xl rounded-3xl bg-white p-6 shadow-soft sm:p-10">
        <div class="flex items-start justify-between gap-4">
            <x-logo />
            <div class="text-right">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Invoice</p>
                <p class="font-mono font-bold text-navy-900">{{ $order->code }}</p>
                <span class="badge mt-1 bg-{{ $order->statusColor() }}-100 text-{{ $order->statusColor() }}-800">{{ $order->statusLabel() }}</span>
            </div>
        </div>
        <div class="mt-8 grid grid-cols-2 gap-4 text-sm">
            <div><p class="text-xs text-slate-400">Ditagihkan kepada</p><p class="font-semibold text-navy-900">{{ $order->user->name }}</p><p class="text-slate-500">{{ $order->user->email }}</p></div>
            <div class="text-right"><p class="text-xs text-slate-400">Tanggal</p><p class="font-semibold text-navy-900">{{ $order->created_at->translatedFormat('d F Y') }}</p>@if($order->paid_at)<p class="text-slate-500">Lunas {{ $order->paid_at->translatedFormat('d F Y') }}</p>@endif</div>
        </div>
        <table class="mt-8 w-full text-sm">
            <thead><tr class="border-b border-slate-200 text-left text-xs uppercase text-slate-400"><th class="py-2">Item</th><th class="py-2 text-right">Harga</th></tr></thead>
            <tbody>
                @foreach($order->items as $i)<tr class="border-b border-slate-100"><td class="py-3">{{ $i->name }} <span class="text-xs text-slate-400">({{ $i->item_type === 'package' ? 'Paket' : 'Tryout' }})</span></td><td class="py-3 text-right">{{ rupiah($i->price) }}</td></tr>@endforeach
            </tbody>
            <tfoot>
                @if($order->discount)<tr><td class="pt-3 text-slate-500">Subtotal</td><td class="pt-3 text-right">{{ rupiah($order->subtotal) }}</td></tr><tr><td class="text-mint-700">Konversi paket</td><td class="text-right text-mint-700">−{{ rupiah($order->discount) }}</td></tr>@endif
                <tr><td class="pt-3 font-bold text-navy-900">Total</td><td class="pt-3 text-right text-lg font-extrabold text-navy-900">{{ rupiah($order->total) }}</td></tr>
            </tfoot>
        </table>
        @if($order->payment_method_name)<p class="mt-6 text-sm text-slate-500">Metode: {{ $order->payment_method_name }}</p>@endif
        <button onclick="window.print()" class="no-print btn-primary mt-8 w-full">Cetak / Simpan PDF</button>
    </div>
</body>
</html>
