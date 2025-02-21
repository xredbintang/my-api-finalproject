<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PenyewaanRequest extends FormRequest
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
            'penyewaan_pelanggan_id' => 'required|exists:pelanggan,pelanggan_id',
            'penyewaan_tglsewa'      => 'required|date_format:Y-m-d',
        'penyewaan_tglkembali'   => 'required|date_format:Y-m-d|after_or_equal:penyewaan_tglsewa',
            'penyewaan_sttspembayaran' => 'required|in:Lunas,Belum dibayar,DP',
            'penyewaan_sttskembali' => 'required|in:Sudah kembali,Belum kembali,DP'
        ];
    }
    public function messages()
    {
        return [
            'penyewaan_pelanggan_id.required' => 'ID pelanggan wajib diisi.',
            'penyewaan_pelanggan_id.exists' => 'ID pelanggan tidak ditemukan.',
            'penyewaan_tglsewa.required' => 'Tanggal sewa wajib diisi.',
            'penyewaan_tglsewa.date_format' => 'Format tanggal sewa tidak valid. Gunakan format YYYY-MM-DD.',
            'penyewaan_tglkembali.required' => 'Tanggal kembali wajib diisi.',
        'penyewaan_tglkembali.date_format' => 'Format tanggal kembali tidak valid. Gunakan format YYYY-MM-DD.',
        'penyewaan_tglkembali.after_or_equal' => 'Tanggal kembali harus sama atau setelah tanggal sewa.',
            'penyewaan_sttspembayaran.required' => 'Status pembayaran wajib diisi.',
            'penyewaan_sttspembayaran.in' => 'Status pembayaran harus salah satu dari: Lunas, Belum dibayar, atau DP.',
            'penyewaan_sttskembali.required' => 'Status kembali wajib diisi.',
            'penyewaan_sttskembali.in' => 'Status kembali harus salah satu dari: Sudah kembali, Belum kembali, atau DP.'
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
