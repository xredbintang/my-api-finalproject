<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenyewaanDetailRequest;
use App\Models\Alat;
use App\Models\PenyewaanDetail;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PenyewaanDetailController extends Controller
{
    private function jsonResponse($success, $message, $data = null, $errors = null, $status = 200) {
        return response()->json(compact('success', 'message', 'data', 'errors'), $status);
    }

    public function index() {
        try {
            $data = Cache::remember('penyewaan_detail', 30, fn() => PenyewaanDetail::all());
            return $this->jsonResponse(true, 'Sukses mendapatkan data penyewaan detail', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function show(int $penyewaan_detail_id) {
        try {
            $data = Cache::remember("penyewaan_detail_{$penyewaan_detail_id}", 30, fn() => PenyewaanDetail::find($penyewaan_detail_id));
            return $data ? $this->jsonResponse(true, 'Sukses mendapatkan data penyewaan detail', $data)
                         : $this->jsonResponse(false, "Penyewaan detail dengan id {$penyewaan_detail_id} tidak ditemukan", null, null, 400);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function store(PenyewaanDetailRequest $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();

            $alat = Alat::find($validated['penyewaan_detail_alat_id']);

            if (!$alat || $alat->alat_stok < $validated['penyewaan_detail_jumlah']) {
                throw new Exception("Stok alat {$alat->alat_nama} tidak mencukupi.");
            }

            $alat->decrement('alat_stok', $validated['penyewaan_detail_jumlah']);

            $data = PenyewaanDetail::create($validated);

            DB::commit();
            Cache::forget('penyewaan_detail');
            return $this->jsonResponse(true, 'Sukses menambahkan data penyewaan detail', $data);
        } catch (Exception $error) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function update(PenyewaanDetailRequest $request, int $penyewaan_detail_id) {
        try {
            $data = PenyewaanDetail::find($penyewaan_detail_id);
            if (!$data) return $this->jsonResponse(false, "Penyewaan detail dengan id {$penyewaan_detail_id} tidak ditemukan", null, null, 400);
            
            $data->update($request->validated());
            Cache::forget('penyewaan_detail');
            Cache::forget("penyewaan_detail_{$penyewaan_detail_id}");
            return $this->jsonResponse(true, 'Sukses mengupdate data penyewaan detail', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function destroy(int $penyewaan_detail_id) {
        try {
            $data = PenyewaanDetail::find($penyewaan_detail_id);
            if (!$data) return $this->jsonResponse(false, 'Penyewaan detail tidak ditemukan', null, null, 400);
            
            $data->delete();
            Cache::forget('penyewaan_detail');
            Cache::forget("penyewaan_detail_{$penyewaan_detail_id}");
            return $this->jsonResponse(true, 'Sukses menghapus data penyewaan detail', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }
}
