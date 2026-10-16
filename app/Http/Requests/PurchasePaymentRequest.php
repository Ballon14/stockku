<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchasePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_bayar' => 'required|date',
            'nominal' => 'required|numeric|min:1',
            'metode' => 'required|in:transfer,cash,giro',
            'bank_tujuan' => 'nullable|string|max:100',
            'nomor_referensi' => 'nullable|string|max:100',
            'bukti_bayar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'catatan' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_bayar.required' => 'Tanggal pembayaran wajib diisi.',
            'nominal.required' => 'Nominal pembayaran wajib diisi.',
            'nominal.numeric' => 'Nominal harus berupa angka.',
            'nominal.min' => 'Nominal pembayaran minimal Rp 1.',
            'metode.required' => 'Metode pembayaran wajib dipilih.',
            'bukti_bayar.image' => 'Bukti pembayaran harus berupa gambar.',
            'bukti_bayar.max' => 'Ukuran bukti pembayaran maksimal 2MB.',
        ];
    }
}
