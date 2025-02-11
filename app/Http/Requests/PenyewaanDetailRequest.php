<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use PHPUnit\Framework\Constraint\IsTrue;

class PenyewaanDetailRequest extends FormRequest
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
            'penyewaan_detail_penyewaan_id' => 'required|exists:penyewaan,penyewaan_id',
            'penyewaan_detail_alat_id' => 'required|exists:alat,alat_id',
            'penyewaan_detail_jumlah' => 'required|integer|min:1',
            'penyewaan_detail_subharga' => 'required|integer|min:0',
        ];
    }
        public function messages()
    {
        return [
            'penyewaan_detail_penyewaan_id.required' => 'ID penyewaan wajib diisi.',
            'penyewaan_detail_penyewaan_id.exists' => 'ID penyewaan tidak ditemukan.',
            'penyewaan_detail_alat_id.required' => 'ID alat wajib diisi.',
            'penyewaan_detail_alat_id.exists' => 'ID alat tidak ditemukan.',
            'penyewaan_detail_jumlah.required' => 'Jumlah alat wajib diisi.',
            'penyewaan_detail_jumlah.integer' => 'Jumlah alat harus berupa angka.',
            'penyewaan_detail_jumlah.min' => 'Jumlah alat minimal 1.',
            'penyewaan_detail_subharga.required' => 'Subharga wajib diisi.',
            'penyewaan_detail_subharga.integer' => 'Subharga harus berupa angka.',
            'penyewaan_detail_subharga.min' => 'Subharga tidak boleh kurang dari 0.',
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

