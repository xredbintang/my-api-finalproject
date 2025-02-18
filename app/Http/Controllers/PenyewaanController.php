<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenyewaanRequest;
use App\Models\Alat;
use App\Models\Penyewaan;
use App\Models\PenyewaanDetail;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

    // public function update(PenyewaanRequest $request, int $penyewaan_id) {
    //     try {
    //         $data = Penyewaan::find($penyewaan_id);
    //         if (!$data) return $this->jsonResponse(false, "Penyewaan dengan id {$penyewaan_id} tidak ditemukan", null, null, 400);
            
    //         $data->update($request->validated());
    //         Cache::forget('penyewaan');
    //         Cache::forget("penyewaan_{$penyewaan_id}");
    //         return $this->jsonResponse(true, 'Sukses mengupdate data penyewaan', $data);
    //     } catch (Exception $error) {
    //         return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
    //     }
    // }
    public function update(PenyewaanRequest $request, int $penyewaan_id)
    {
        DB::beginTransaction();
        try {
            // Cari data penyewaan
            $penyewaan = Penyewaan::find($penyewaan_id);
            if (!$penyewaan) {
                return $this->jsonResponse(false, "Penyewaan dengan id {$penyewaan_id} tidak ditemukan", null, null, 400);
            }

            // Simpan status sebelumnya
            $previousStatus = $penyewaan->penyewaan_sttskembali;

            // Update status penyewaan
            $penyewaan->update($request->validated());

            // Ambil status baru
            $newStatus = $request->input('penyewaan_sttskembali');

            // Jika status diubah menjadi "Sudah kembali"
            if ($newStatus === 'Sudah kembali' && $previousStatus === 'Belum kembali') {
                // Ambil semua detail penyewaan
                $details = PenyewaanDetail::where('penyewaan_detail_penyewaan_id', $penyewaan_id)->get();

                foreach ($details as $detail) {
                    $alat = Alat::find($detail->penyewaan_detail_alat_id);
                    if ($alat) {
                        // Tambahkan stok alat
                        $alat->increment('alat_stok', $detail->penyewaan_detail_jumlah);
                    }
                }
            }

            // Jika status diubah kembali menjadi "Belum kembali"
            if ($newStatus === 'Belum kembali' && $previousStatus === 'Sudah kembali') {
                // Ambil semua detail penyewaan
                $details = PenyewaanDetail::where('penyewaan_detail_penyewaan_id', $penyewaan_id)->get();

                foreach ($details as $detail) {
                    $alat = Alat::find($detail->penyewaan_detail_alat_id);
                    if ($alat) {
                        // Kurangi stok alat
                        $alat->decrement('alat_stok', $detail->penyewaan_detail_jumlah);
                    }
                }
            }

            DB::commit();
            Cache::forget('penyewaan');
            Cache::forget("penyewaan_{$penyewaan_id}");
            return $this->jsonResponse(true, 'Sukses mengupdate data penyewaan', $penyewaan);
        } catch (Exception $error) {
            DB::rollBack();
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
