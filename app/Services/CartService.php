<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Package;
use App\Models\Tryout;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Aturan keranjang:
 *  - Satu keranjang hanya boleh berisi PAKET saja atau SATUAN saja (tidak dicampur).
 *  - Tryout yang sudah dimiliki tidak bisa dimasukkan.
 *  - Saat checkout, jika kumpulan tryout satuan identik dengan isi sebuah paket aktif
 *    (dan paket lebih murah/sama), keranjang otomatis dikonversi menjadi paket tsb.
 */
class CartService
{
    public const MODE_PACKAGE = 'package';
    public const MODE_SINGLE = 'tryout';

    public function mode(User $user): ?string
    {
        return $user->cartItems()->value('item_type');
    }

    public function count(User $user): int
    {
        return $user->cartItems()->count();
    }

    /**
     * @return array{ok:bool, code:string, message:string}
     */
    public function add(User $user, string $type, int $id, bool $replace = false): array
    {
        $type = $type === 'package' ? self::MODE_PACKAGE : self::MODE_SINGLE;
        $mode = $this->mode($user);

        if ($mode && $mode !== $type) {
            if (! $replace) {
                return [
                    'ok' => false,
                    'code' => 'conflict',
                    'message' => $mode === self::MODE_PACKAGE
                        ? 'Keranjangmu berisi paket. Paket dan tryout satuan tidak bisa digabung dalam satu checkout.'
                        : 'Keranjangmu berisi tryout satuan. Paket dan tryout satuan tidak bisa digabung dalam satu checkout.',
                ];
            }
            $this->clear($user);
        }

        if ($type === self::MODE_SINGLE) {
            $tryout = Tryout::query()->published()->with('category')->find($id);
            if (! $tryout || $tryout->is_free) {
                return ['ok' => false, 'code' => 'invalid', 'message' => 'Tryout tidak tersedia untuk dibeli.'];
            }
            if ($user->ownedTryoutIds()->contains($tryout->id)) {
                return ['ok' => false, 'code' => 'owned', 'message' => 'Kamu sudah memiliki tryout ini.'];
            }
        } else {
            $package = Package::query()->active()->with('tryouts')->find($id);
            if (! $package) {
                return ['ok' => false, 'code' => 'invalid', 'message' => 'Paket tidak tersedia.'];
            }
            $owned = $user->ownedTryoutIds();
            if ($package->tryouts->isNotEmpty() && $package->tryouts->every(fn ($t) => $owned->contains($t->id))) {
                return ['ok' => false, 'code' => 'owned', 'message' => 'Semua tryout di paket ini sudah kamu miliki.'];
            }
        }

        $item = CartItem::query()->firstOrCreate(['user_id' => $user->id, 'item_type' => $type, 'item_id' => $id]);

        return [
            'ok' => true,
            'code' => $item->wasRecentlyCreated ? 'added' : 'exists',
            'message' => $item->wasRecentlyCreated ? 'Berhasil ditambahkan ke keranjang.' : 'Item sudah ada di keranjang.',
        ];
    }

    public function remove(User $user, int $cartItemId): void
    {
        $user->cartItems()->whereKey($cartItemId)->delete();
    }

    public function clear(User $user): void
    {
        $user->cartItems()->delete();
    }

    public function has(User $user, string $type, int $id): bool
    {
        return $user->cartItems()->where('item_type', $type)->where('item_id', $id)->exists();
    }

    /**
     * Ringkasan keranjang + deteksi konversi ke paket.
     */
    public function summary(User $user): array
    {
        $rows = $user->cartItems()->orderBy('id')->get();
        $mode = $rows->first()?->item_type;
        $owned = $user->ownedTryoutIds();
        $lines = collect();

        if ($mode === self::MODE_PACKAGE) {
            $packages = Package::query()->active()->with('tryouts.category')->whereIn('id', $rows->pluck('item_id'))->get()->keyBy('id');
            foreach ($rows as $row) {
                $p = $packages->get($row->item_id);
                if (! $p) {
                    $row->delete();
                    continue;
                }
                $lines->push([
                    'cart_id' => $row->id, 'type' => 'package', 'model' => $p, 'name' => $p->name,
                    'price' => $p->price, 'normal_price' => $p->normalPrice(),
                    'owned_count' => $p->tryouts->filter(fn ($t) => $owned->contains($t->id))->count(),
                ]);
            }
        } elseif ($mode === self::MODE_SINGLE) {
            $tryouts = Tryout::query()->published()->with('category')->whereIn('id', $rows->pluck('item_id'))->get()->keyBy('id');
            foreach ($rows as $row) {
                $t = $tryouts->get($row->item_id);
                if (! $t || $t->is_free || $owned->contains($row->item_id)) {
                    $row->delete();
                    continue;
                }
                $lines->push([
                    'cart_id' => $row->id, 'type' => 'tryout', 'model' => $t, 'name' => $t->title,
                    'price' => $t->price(), 'normal_price' => $t->price(), 'owned_count' => 0,
                ]);
            }
        }

        $subtotal = (int) $lines->sum('price');
        $match = $mode === self::MODE_SINGLE ? $this->matchingPackage($lines->pluck('model.id')) : null;

        return [
            'mode' => $mode,
            'lines' => $lines,
            'subtotal' => $subtotal,
            'match' => $match,                         // Package|null
            'total' => $match ? $match->price : $subtotal,
            'savings' => $match ? max(0, $subtotal - $match->price) : 0,
        ];
    }

    /** Paket aktif yang isi tryout-nya identik dengan kumpulan tryout satuan. */
    public function matchingPackage(Collection $tryoutIds): ?Package
    {
        $ids = $tryoutIds->filter()->map(fn ($i) => (int) $i)->unique()->sort()->values();
        if ($ids->count() < 2) {
            return null;
        }

        $sum = Tryout::query()->with('category')->whereIn('id', $ids)->get()->sum(fn ($t) => $t->price());

        return Package::query()->active()
            ->whereHas('tryouts', fn ($q) => $q->whereIn('tryouts.id', $ids), '=', $ids->count())
            ->withCount('tryouts')
            ->with('tryouts')
            ->get()
            ->filter(fn (Package $p) => $p->tryouts_count === $ids->count() && $p->price <= $sum)
            ->sortBy('price')
            ->first();
    }
}
