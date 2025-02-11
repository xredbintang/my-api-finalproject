<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AlatRequest;
use App\Models\Alat;
use Exception;
use Illuminate\Support\Facades\Cache;

class AlatController extends Controller
{
    private function jsonResponse($success, $message, $data = null, $errors = null, $status = 200) {
        return response()->json(compact('success', 'message', 'data', 'errors'), $status);
    }

    public function index() {
        try {
            $data = Cache::remember('alat', 30, fn() => Alat::all());
            return $this->jsonResponse(true, 'Sukses mendapatkan data alat', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function show(int $alat_id) {
        try {
            $data = Cache::remember("alat_{$alat_id}", 30, fn() => Alat::find($alat_id));
            return $data ? $this->jsonResponse(true, 'Sukses mendapatkan data alat', $data)
                         : $this->jsonResponse(false, "Alat dengan id {$alat_id} tidak ditemukan", null, null, 400);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function store(AlatRequest $request) {
        try {
            $data = Alat::create($request->validated());
            Cache::forget('alat');
            return $this->jsonResponse(true, 'Sukses membuat data alat', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function update(AlatRequest $request, int $alat_id) {
        try {
            $data = Alat::find($alat_id);
            if (!$data) return $this->jsonResponse(false, "Alat dengan id {$alat_id} tidak ditemukan", null, null, 400);
            
            $data->update($request->validated());
            Cache::forget('alat');
            Cache::forget("alat_{$alat_id}");
            return $this->jsonResponse(true, 'Sukses mengupdate data alat', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function destroy(int $alat_id) {
        try {
            $data = Alat::find($alat_id);
            if (!$data) return $this->jsonResponse(false, 'Alat tidak ditemukan', null, null, 400);
            
            $data->delete();
            Cache::forget('alat');
            Cache::forget("alat_{$alat_id}");
            return $this->jsonResponse(true, 'Sukses menghapus data alat', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }
}
