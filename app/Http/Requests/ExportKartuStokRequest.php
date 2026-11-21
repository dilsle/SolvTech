<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportKartuStokRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
            'nomor_sku' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_awal.required' => 'Tanggal awal wajib diisi.',
            'tanggal_awal.date' => 'Tanggal awal harus berupa tanggal yang valid.',

            'tanggal_akhir.required' => 'Tanggal akhir wajib diisi.',
            'tanggal_akhir.date' => 'Tanggal akhir harus berupa tanggal yang valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal awal.',

            'nomor_sku.required' => 'Nomor SKU wajib diisi.',
        ];
    }
}
