<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'item_type', 'item_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
