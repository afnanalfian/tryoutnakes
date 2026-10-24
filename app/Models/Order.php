<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';     // menunggu pembayaran / upload bukti
    public const STATUS_WAITING = 'waiting';     // bukti diunggah, menunggu verifikasi
    public const STATUS_PAID = 'paid';           // disetujui admin
    public const STATUS_REJECTED = 'rejected';   // bukti ditolak (boleh unggah ulang)
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING => 'Menunggu Pembayaran',
        self::STATUS_WAITING => 'Menunggu Verifikasi',
        self::STATUS_PAID => 'Lunas',
        self::STATUS_REJECTED => 'Bukti Ditolak',
        self::STATUS_EXPIRED => 'Kedaluwarsa',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    protected $fillable = [
        'code', 'user_id', 'type', 'subtotal', 'discount', 'total', 'status', 'payment_method_id',
        'payment_method_name', 'proof_path', 'proof_uploaded_at', 'payer_note', 'converted_package_id',
        'expires_at', 'paid_at', 'verified_by', 'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'proof_uploaded_at' => 'datetime',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function convertedPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'converted_package_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'emerald',
            self::STATUS_WAITING => 'amber',
            self::STATUS_PENDING => 'sky',
            self::STATUS_REJECTED => 'rose',
            default => 'slate',
        };
    }

    public function canUploadProof(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_REJECTED, self::STATUS_WAITING], true);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_WAITING, self::STATUS_REJECTED], true);
    }

    /** Semua tryout_id yang akan diberikan saat pesanan disetujui. */
    public function tryoutIds(): array
    {
        return $this->items->flatMap(function (OrderItem $item) {
            return $item->item_type === 'package' ? ($item->tryout_ids ?? []) : [$item->item_id];
        })->unique()->values()->all();
    }
}
