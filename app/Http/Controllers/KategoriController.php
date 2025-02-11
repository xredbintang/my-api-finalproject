<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\KategoriRequest;
use App\Models\Kategori;
use Exception;
use Illuminate\Support\Facades\Cache;

class KategoriController extends Controller
{
    private function jsonResponse($success, $message, $data = null, $errors = null, $status = 200) {
        return response()->json(compact('success', 'message', 'data', 'errors'), $status);
    }

    public function index() {
        try {
            $data = Cache::remember('kategori', 10, fn() => Kategori::all());
            return $this->jsonResponse(true, 'Berhasil mengambil data kategori.', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Maaf, terjadi kesalahan pada server.', null, $error->getMessage(), 500);
        }
    }

    public function show(int $kategori_id) {
        try {
            $data = Cache::remember("kategori_{$kategori_id}", 10, fn() => Kategori::find($kategori_id));
            return $data ? $this->jsonResponse(true, 'Berhasil mengambil data kategori', $data)
                         : $this->jsonResponse(false, "Kategori dengan id: {$kategori_id} tidak ditemukan", null, null, 400);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Maaf, terjadi kesalahan pada server.', null, $error->getMessage(), 500);
        }
    }

    public function store(KategoriRequest $request) {
        try {
            $data = Kategori::create($request->validated());
            Cache::forget('kategori');
            return $this->jsonResponse(true, 'Berhasil membuat data kategori', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Kesalahan pada server internal', null, $error->getMessage(), 500);
        }
    }

    public function update(KategoriRequest $request, int $kategori_id) {
        try {
            $data = Kategori::find($kategori_id);
            if (!$data) return $this->jsonResponse(false, "Kategori dengan id {$kategori_id} tidak ditemukan", null, null, 400);
            
            $data->update($request->validated());
            Cache::forget('kategori');
            Cache::forget("kategori_{$kategori_id}");
            return $this->jsonResponse(true, 'Sukses mengupdate data kategori', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function destroy(int $kategori_id) {
        try {
            $data = Kategori::find($kategori_id);
            if (!$data) return $this->jsonResponse(false, 'Data kategori tidak ditemukan', null, null, 400);
            
            $data->delete();
            Cache::forget('kategori');
            Cache::forget("kategori_{$kategori_id}");
            return $this->jsonResponse(true, 'Berhasil menghapus data kategori', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Maaf, terjadi kesalahan pada server.', null, $error->getMessage(), 500);
        }
    }
}
