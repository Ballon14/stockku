<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasePayment extends Model
{
    protected $fillable = [
        'purchase_id',
        'supplier_id',
        'payment_number',
        'tanggal_bayar',
        'nominal',
        'metode',
        'bank_tujuan',
        'nomor_referensi',
        'bukti_bayar',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generatePaymentNumber(): string
    {
        $prefix = 'PAY-'.date('Ymd');
        $last = static::where('payment_number', 'like', $prefix.'%')
            ->orderBy('payment_number', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->payment_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix.'-'.str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
