<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PelangganRequest extends FormRequest
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
            'pelanggan_nama' => 'required|regex:/^[a-zA-Z\s]+$/|max:150',
            'pelanggan_alamat' => 'required|regex:/^[a-zA-Z0-9\s,.-]+$/|max:200',
            'pelanggan_notelp' => 'required|regex:/^[0-9()+-]+$/|min:10|max:13',
            'pelanggan_email' => 'required|email|max:100',
        ];  
    }

    public function messages():array
    {
        return [
            'pelanggan_nama.required'   => 'Nama pelanggan wajib diisi.',
            'pelanggan_nama.regex'      => 'Nama pelanggan hanya boleh mengandung huruf dan spasi.',
            'pelanggan_nama.max'        => 'Nama pelanggan maksimal 150 karakter.',

            'pelanggan_alamat.required' => 'Alamat pelanggan wajib diisi.',
            'pelanggan_alamat.regex'    => 'Alamat pelanggan hanya boleh mengandung huruf, angka, spasi, koma, titik, dan strip.',
            'pelanggan_alamat.max'      => 'Alamat pelanggan maksimal 200 karakter.',

            'pelanggan_notelp.required' => 'Nomor telepon pelanggan wajib diisi.',
            'pelanggan_notelp.regex'    => 'Nomor telepon hanya boleh mengandung angka, +, -, dan tanda kurung.',
            'pelanggan_notelp.min'      => 'Nomor telepon minimal 10 digit.',
            'pelanggan_notelp.max'      => 'Nomor telepon maksimal 13 digit.',

            'pelanggan_email.required'  => 'Email pelanggan wajib diisi.',
            'pelanggan_email.email'     => 'Format email tidak valid.',
            'pelanggan_email.max'       => 'Email pelanggan maksimal 100 karakter.',
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
