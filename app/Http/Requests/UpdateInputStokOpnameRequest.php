<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class UpdateInputStokOpnameRequest extends FormRequest
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
            'produk' =>'required',
            'nomor_sku' =>'required',
            'jumlah_dilaporkan'=>'required|numeric|min:0'
        ];
    }

    #[Override]
    public function messages():array
    {
        return [
            'produk.required' => 'Produk wajib diisi',
            'nomor_sku.required' => 'Nomor SKU wajib diisi',
            'jumlah_dilaporkan.required'=> 'Jumlah dilaporkan haus diisi',
            'jumlah_dilaporkan.numeric' => 'Jumlah dilaporkan harus berupa angka',
            'Jumlah dilaporkan.min' => 'Jumlah dilaporkan minimal 0',
        ];
    }
}
