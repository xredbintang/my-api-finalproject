<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class KategoriRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kategori_nama' => 'required|max:100|regex:/^[a-zA-Z\s]+$/'
        ];
    }
    public function messages(): array
    {
        return [
            'kategori_nama.required' => 'Nama Kategori wajib diisi',
            'kategori_nama.max' => 'Nama Kategori tidak boleh lebih dari 100 karakter',
            'kategori_nama.regex' => 'Nama Kategori hanya boleh mengandung huruf dan spasi',
        ];
    }
    public function failedValidation(Validator $validator)
    {
       throw new HttpResponseException(response()->json([
         'success'   => false,
         'message'   => 'Validation errors',
         'errors'      => $validator->errors()
       ],400));
    }
}
