<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class storeTransaksiMasukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pengirim' => 'required',
            'kontak' => 'required',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
        ];
    }

    public function messages()
    {
        return [
            'pengirim.required' => 'Pengirim wajib diisi',
            'kontak.required' => 'Kontak wajib diisi',
            'items.required' => 'Item wajib diisi',
            'items.min' => 'Item minimal 1',
        ];
    }
}
