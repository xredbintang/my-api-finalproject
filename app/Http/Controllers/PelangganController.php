<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Middleware\VerifyRefreshToken;
use App\Http\Requests\PelangganRequest;
use App\Models\Pelanggan;
use App\Traits\TokenValidator;
use Exception;
use Illuminate\Support\Facades\Cache;

class PelangganController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(VerifyRefreshToken::class);
    // }
    private function jsonResponse($success, $message, $data = null, $errors = null, $status = 200) {
        return response()->json(compact('success', 'message', 'data', 'errors'), $status);
    }

    public function index() {
        try {
            $data = Cache::remember('pelanggan', 30, fn() => Pelanggan::all());
            return $this->jsonResponse(true, 'Sukses mendapatkan data pelanggan', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function show(int $pelanggan_id) {
        try {
            $data = Cache::remember("pelanggan_{$pelanggan_id}", 30, fn() => Pelanggan::find($pelanggan_id));
            return $data ? $this->jsonResponse(true, 'Sukses mendapatkan data pelanggan', $data)
                         : $this->jsonResponse(false, "Pelanggan dengan id {$pelanggan_id} tidak ditemukan", null, null, 400);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function store(PelangganRequest $request) {
        try {
            $data = Pelanggan::create($request->validated());
            Cache::forget('pelanggan');
            return $this->jsonResponse(true, 'Sukses membuat data pelanggan', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function update(PelangganRequest $request, int $pelanggan_id) {
        try {
            $data = Pelanggan::find($pelanggan_id);
            if (!$data) return $this->jsonResponse(false, "Pelanggan dengan id {$pelanggan_id} tidak ditemukan", null, null, 400);
            
            $data->update($request->validated());
            Cache::forget('pelanggan');
            Cache::forget("pelanggan_{$pelanggan_id}");
            return $this->jsonResponse(true, 'Sukses mengupdate data pelanggan', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function destroy(int $pelanggan_id) {
        try {
            $data = Pelanggan::find($pelanggan_id);
            if (!$data) return $this->jsonResponse(false, 'Pelanggan tidak ditemukan', null, null, 400);
            
            $data->delete();
            Cache::forget('pelanggan');
            Cache::forget("pelanggan_{$pelanggan_id}");
            return $this->jsonResponse(true, 'Sukses menghapus data pelanggan', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }
}
