<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'invoice_number',
        'supplier_id',
        'user_id',
        'tanggal',
        'payment_type',
        'due_date',
        'supplier_invoice',
        'payment_status',
        'paid_amount',
        'total',
        'status',
        'keterangan',
        'foto_nota',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'due_date' => 'date',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class)->orderBy('tanggal_bayar', 'desc')->orderBy('created_at', 'desc');
    }

    public function getTotalReturnsAmountAttribute(): float
    {
        return (float) $this->returns->sum('total_nilai');
    }

    public function getRemainingBillAttribute(): float
    {
        $totalReturns = (float) $this->returns->sum('total_nilai');
        $sisa = (float) $this->total - $totalReturns - (float) $this->paid_amount;
        return max(0, round($sisa, 2));
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->payment_status === 'paid') {
            return false;
        }

        return $this->due_date && $this->due_date->isPast();
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'PUR-'.date('Ymd');
        $last = static::where('invoice_number', 'like', $prefix.'%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix.'-'.str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
