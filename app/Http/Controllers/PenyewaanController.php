<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenyewaanRequest;
use App\Models\Penyewaan;
use Exception;
use Illuminate\Support\Facades\Cache;

class PenyewaanController extends Controller
{
    
    private function jsonResponse($success, $message, $data = null, $errors = null, $status = 200) {
        return response()->json(compact('success', 'message', 'data', 'errors'), $status);
    }

    public function index() {
        try {
            $data = Cache::remember('penyewaan', 30, fn() => Penyewaan::all());
            return $this->jsonResponse(true, 'Sukses mendapatkan data penyewaan', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function show(int $penyewaan_id) {
        try {
            $data = Cache::remember("penyewaan_{$penyewaan_id}", 30, fn() => Penyewaan::find($penyewaan_id));
            return $data ? $this->jsonResponse(true, 'Sukses mendapatkan data penyewaan', $data)
                         : $this->jsonResponse(false, "Penyewaan dengan id {$penyewaan_id} tidak ditemukan", null, null, 400);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function store(PenyewaanRequest $request) {
        try {
            $data = Penyewaan::create($request->validated());
            Cache::forget('penyewaan');
            return $this->jsonResponse(true, 'Sukses membuat data penyewaan', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function update(PenyewaanRequest $request, int $penyewaan_id) {
        try {
            $data = Penyewaan::find($penyewaan_id);
            if (!$data) return $this->jsonResponse(false, "Penyewaan dengan id {$penyewaan_id} tidak ditemukan", null, null, 400);
            
            $data->update($request->validated());
            Cache::forget('penyewaan');
            Cache::forget("penyewaan_{$penyewaan_id}");
            return $this->jsonResponse(true, 'Sukses mengupdate data penyewaan', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function destroy(int $penyewaan_id) {
        try {
            $data = Penyewaan::find($penyewaan_id);
            if (!$data) return $this->jsonResponse(false, 'Penyewaan tidak ditemukan', null, null, 400);
            
            $data->delete();
            Cache::forget('penyewaan');
            Cache::forget("penyewaan_{$penyewaan_id}");
            return $this->jsonResponse(true, 'Sukses menghapus data penyewaan', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }
}
