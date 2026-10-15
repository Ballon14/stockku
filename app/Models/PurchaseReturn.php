<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'purchase_id',
        'supplier_id',
        'return_number',
        'tanggal',
        'total_nilai',
        'alasan',
        'status',
        'processed_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_nilai' => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public static function generateReturnNumber(): string
    {
        $prefix = 'RBP-'.date('Ymd');
        $last = static::where('return_number', 'like', $prefix.'%')
            ->orderBy('return_number', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->return_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix.'-'.str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
