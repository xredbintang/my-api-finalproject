<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class AlatRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'alat_kategori_id' => 'required|exists:kategori,kategori_id',
            'alat_nama'        => 'required|max:150', 
            'alat_deskripsi'   => 'required|max:255',
            'alat_hargaperhari'=> 'required|numeric|min:1',
            'alat_stok'        => 'required|integer|min:1',
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'alat_kategori_id.required' => 'Kategori alat wajib diisi.',
            'alat_kategori_id.exists'   => 'Kategori alat yang dipilih tidak ditemukan.',

            'alat_nama.required' => 'Nama alat wajib diisi.',
            'alat_nama.max'      => 'Nama alat tidak boleh lebih dari 150 karakter.',

            'alat_deskripsi.required' => 'Deskripsi alat wajib diisi.',
            'alat_deskripsi.max'      => 'Deskripsi alat tidak boleh lebih dari 255 karakter.',

            'alat_hargaperhari.required' => 'Harga per hari alat wajib diisi.',
            'alat_hargaperhari.numeric'  => 'Harga per hari harus berupa angka (boleh desimal).',
            'alat_hargaperhari.min'      => 'Harga per hari alat minimal 1.',

            'alat_stok.required' => 'Stok alat wajib diisi.',
            'alat_stok.integer'  => 'Stok alat harus berupa angka bulat.',
            'alat_stok.min'      => 'Stok alat minimal 1.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors'  => $validator->errors()
        ], 400));
    }
}
