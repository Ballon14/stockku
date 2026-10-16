<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('payment_type')) {
            $this->merge(['payment_type' => 'cash']);
        }
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'tanggal' => 'required|date',
            'payment_type' => 'required|in:cash,credit',
            'due_date' => 'nullable|required_if:payment_type,credit|date',
            'supplier_invoice' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string|max:1000',
            'foto_nota' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id|distinct',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric|min:0',
            'items.*.update_harga_beli' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'due_date.required_if' => 'Tanggal jatuh tempo wajib diisi jika tipe pembayaran tempo/kredit.',
            'items.required' => 'Minimal harus ada 1 item.',
            'items.min' => 'Minimal harus ada 1 item.',
            'items.*.product_id.distinct' => 'Produk tidak boleh diisi ganda. Gabungkan qty pada satu baris.',
        ];
    }
}
