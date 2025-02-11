<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PelangganDataRequest extends FormRequest
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
            'pelanggan_data_pelanggan_id' => 'required|exists:pelanggan,pelanggan_id',
            'pelanggan_data_jenis'        => 'required|in:KTP,SIM',
            'pelanggan_data_file'         => 'required|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'pelanggan_data_pelanggan_id.required' => 'ID pelanggan wajib diisi.',
            'pelanggan_data_pelanggan_id.exists'   => 'ID pelanggan tidak ditemukan.',

            'pelanggan_data_jenis.required' => 'Jenis dokumen wajib diisi.',
            'pelanggan_data_jenis.in'       => 'Jenis dokumen harus KTP atau SIM.',

            'pelanggan_data_file.required' => 'File wajib diunggah.',
            'pelanggan_data_file.mimes'    => 'File harus memiliki format .jpg, .png, atau .jpeg.',
            'pelanggan_data_file.max'      => 'Ukuran file maksimal 2MB.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors'  => $validator->errors()
        ], 400));
    }
}
