<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExportLaporanTransaksiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'jenis_transaksi'   => 'required|in:pemasukan,pengeluaran',
            'tanggal_awal'      => 'required|date',
            'tanggal_akhir'     => 'required|date|after_or_equal:tanggal_awal',
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_transaksi.required' => 'Jenis transaksi wajib diisi.',
            'jenis_transaksi.in' => 'Jenis transaksi harus berupa "pemasukan" atau "pengeluaran".',
            'tanggal_awal.required' => 'Tanggal awal wajib diisi.',
            'tanggal_awal.date' => 'Tanggal awal harus berupa tanggal yang valid.',
            'tanggal_akhir.required' => 'Tanggal akhir wajib diisi.',
            'tanggal_akhir.date' => 'Tanggal akhir harus berupa tanggal yang valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal awal.',
        ];
    }
}
