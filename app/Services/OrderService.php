<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Tryout;
use App\Models\User;
use App\Models\UserTryout;
use App\Notifications\AppNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class OrderService
{
    public function __construct(protected CartService $cart) {}

    /** Buat pesanan dari keranjang (dengan konversi paket otomatis). */
    public function checkout(User $user): Order
    {
        $summary = $this->cart->summary($user);
        if ($summary['lines']->isEmpty()) {
            throw new RuntimeException('Keranjang masih kosong.');
        }

        $order = DB::transaction(function () use ($user, $summary) {
            $match = $summary['match'];
            $order = Order::query()->create([
                'code' => $this->generateCode(),
                'user_id' => $user->id,
                'type' => ($summary['mode'] === CartService::MODE_PACKAGE || $match) ? 'package' : 'single',
                'subtotal' => $summary['subtotal'],
                'discount' => $summary['savings'],
                'total' => $summary['total'],
                'status' => Order::STATUS_PENDING,
                'converted_package_id' => $match?->id,
                'expires_at' => now()->addHours((int) setting('order_expiry_hours', 24)),
            ]);

            if ($match) {
                $this->addPackageItem($order, $match);
            } else {
                foreach ($summary['lines'] as $line) {
                    if ($line['type'] === 'package') {
                        $this->addPackageItem($order, $line['model']);
                    } else {
                        OrderItem::query()->create([
                            'order_id' => $order->id, 'item_type' => 'tryout', 'item_id' => $line['model']->id,
                            'name' => $line['model']->title, 'price' => $line['price'],
                        ]);
                    }
                }
            }

            $this->cart->clear($user);

            return $order;
        });

        $order->load('items');
        $user->notify(new AppNotification(
            'order_created',
            'Pesanan '.$order->code.' dibuat',
            'Segera lakukan pembayaran sebesar '.rupiah($order->total).' dan unggah bukti transfer sebelum '.$order->expires_at->translatedFormat('d M Y, H:i').' agar pesanan tidak kedaluwarsa.',
            route('orders.show', $order),
            'Bayar Sekarang',
        ));

        return $order;
    }

    protected function addPackageItem(Order $order, $package): void
    {
        $package->loadMissing('tryouts');
        OrderItem::query()->create([
            'order_id' => $order->id, 'item_type' => 'package', 'item_id' => $package->id,
            'name' => $package->name, 'price' => $package->price,
            'tryout_ids' => $package->tryouts->pluck('id')->all(),
        ]);
    }

    public function uploadProof(Order $order, UploadedFile $file, ?int $methodId, ?string $note): void
    {
        if (! $order->canUploadProof()) {
            throw new RuntimeException('Pesanan ini tidak dapat menerima bukti pembayaran.');
        }

        $path = \App\Support\ImageService::store($file, 'payment-proofs', 1800, 'jpg', 'local', 85);
        $old = $order->proof_path;
        $method = $methodId ? \App\Models\PaymentMethod::query()->find($methodId) : null;

        $order->update([
            'proof_path' => $path,
            'proof_uploaded_at' => now(),
            'status' => Order::STATUS_WAITING,
            'payment_method_id' => $method?->id,
            'payment_method_name' => $method?->name ?? ($methodId === 0 ? 'QRIS' : null),
            'payer_note' => $note ? Str::limit($note, 480) : null,
            'admin_note' => null,
        ]);
        if ($old && $old !== $path) {
            \App\Support\ImageService::delete($old, 'local');
        }

        $order->user->notify(new AppNotification(
            'proof_received',
            'Bukti pembayaran diterima',
            'Bukti pembayaran pesanan '.$order->code.' sudah kami terima dan akan diverifikasi paling lambat '.setting('verification_sla').'.',
            route('orders.show', $order),
            'Lihat Pesanan',
        ));

        User::query()->where('role', User::ROLE_ADMIN)->where('is_active', true)->get()
            ->each(fn (User $admin) => $admin->notify(new AppNotification(
                'admin_proof',
                'Bukti bayar baru: '.$order->code,
                $order->user->name.' mengunggah bukti pembayaran '.rupiah($order->total).'. Segera verifikasi.',
                route('admin.orders.show', $order),
                'Verifikasi',
            )));
    }

    public function approve(Order $order, User $admin, ?string $note = null): void
    {
        if ($order->status === Order::STATUS_PAID) {
            return;
        }

        DB::transaction(function () use ($order, $admin, $note) {
            $order->update([
                'status' => Order::STATUS_PAID,
                'paid_at' => now(),
                'verified_by' => $admin->id,
                'admin_note' => $note,
            ]);
            $this->grantTryouts($order->user, $order->tryoutIds(), $order->id, 'purchase', $admin->id);
        });

        $count = count($order->tryoutIds());
        $order->user->notify(new AppNotification(
            'order_approved',
            'Pembayaran disetujui 🎉',
            'Pesanan '.$order->code.' telah diverifikasi. '.$count.' tryout sudah aktif dan bisa langsung kamu kerjakan. Semangat!',
            route('my-tryouts.index'),
            'Mulai Tryout',
        ));
    }

    public function reject(Order $order, User $admin, string $reason): void
    {
        $order->update([
            'status' => Order::STATUS_REJECTED,
            'verified_by' => $admin->id,
            'admin_note' => $reason,
        ]);

        $order->user->notify(new AppNotification(
            'order_rejected',
            'Bukti pembayaran ditolak',
            'Bukti pembayaran pesanan '.$order->code.' belum dapat kami verifikasi. Alasan: '.$reason.'. Silakan unggah ulang bukti yang benar.',
            route('orders.show', $order),
            'Unggah Ulang',
        ));
    }

    public function cancel(Order $order): void
    {
        if (in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_REJECTED], true)) {
            $order->update(['status' => Order::STATUS_CANCELLED]);
        }
    }

    /** Berikan akses tryout (idempotent). */
    public function grantTryouts(User $user, array $tryoutIds, ?int $orderId = null, string $source = 'purchase', ?int $by = null): int
    {
        $granted = 0;
        foreach (Tryout::query()->whereIn('id', $tryoutIds)->pluck('id') as $id) {
            $row = UserTryout::query()->firstOrCreate(
                ['user_id' => $user->id, 'tryout_id' => $id],
                ['order_id' => $orderId, 'source' => $source, 'granted_by' => $by],
            );
            $granted += $row->wasRecentlyCreated ? 1 : 0;
        }

        return $granted;
    }

    public function expireStale(): int
    {
        $count = 0;
        Order::query()->where('status', Order::STATUS_PENDING)->where('expires_at', '<', now())
            ->with('user')->chunkById(100, function ($orders) use (&$count) {
                foreach ($orders as $order) {
                    $order->update(['status' => Order::STATUS_EXPIRED]);
                    $order->user?->notify(new AppNotification(
                        'order_expired',
                        'Pesanan '.$order->code.' kedaluwarsa',
                        'Batas waktu pembayaran telah lewat. Kamu bisa membuat pesanan baru kapan saja dari katalog.',
                        route('catalog.packages'),
                        'Lihat Katalog',
                    ));
                    $count++;
                }
            });

        return $count;
    }

    protected function generateCode(): string
    {
        do {
            $code = 'TO'.now()->format('ymd').strtoupper(Str::random(5));
        } while (Order::query()->where('code', $code)->exists());

        return $code;
    }
}
