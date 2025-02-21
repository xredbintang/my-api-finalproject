<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenyewaanDetailRequest;
use App\Models\Alat;
use App\Models\Penyewaan;
use App\Models\PenyewaanDetail;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    public function store(PenyewaanDetailRequest $request) {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $alat = Alat::find($validated['penyewaan_detail_alat_id']);
            if (!$alat || $alat->alat_stok < $validated['penyewaan_detail_jumlah']) {
                throw new Exception("Stok alat tidak mencukupi.");
            }
            if ($alat->alat_hargaperhari <= 0) {
                throw new Exception("Harga alat tidak valid.");
            }
            $penyewaan = Penyewaan::find($validated['penyewaan_detail_penyewaan_id']);
            $tglSewa = Carbon::parse($penyewaan->penyewaan_tglsewa);
            $tglKembali = Carbon::parse($penyewaan->penyewaan_tglkembali);
            if ($tglKembali->lt($tglSewa)) {
                throw new Exception("Tanggal kembali harus sama atau setelah tanggal sewa.");
            }
            $durasiSewa = $tglSewa->diffInDays($tglSewa) + 1;
            if ($durasiSewa <= 0) {
                throw new Exception("Durasi sewa tidak valid.");
            }
            $subtotalHarga = $validated['penyewaan_detail_jumlah'] * $alat->alat_hargaperhari * $durasiSewa;
            if ($subtotalHarga < 0) {
                throw new Exception("Subtotal harga tidak valid.");
            }
            $validated['penyewaan_detail_subharga'] = $subtotalHarga;
            $alat->decrement('alat_stok', $validated['penyewaan_detail_jumlah']);
            $data = PenyewaanDetail::create($validated);
            $totalHarga = PenyewaanDetail::where('penyewaan_detail_penyewaan_id', $validated['penyewaan_detail_penyewaan_id'])
                ->sum('penyewaan_detail_subharga');
            Penyewaan::where('penyewaan_id', $validated['penyewaan_detail_penyewaan_id'])
                ->update(['penyewaan_totalharga' => $totalHarga]);
            DB::commit();
            Cache::forget('penyewaan_detail');
            Cache::forget("penyewaan_{$validated['penyewaan_detail_penyewaan_id']}");
            return $this->jsonResponse(true, 'Sukses menambahkan data penyewaan detail', $data);
        } catch (Exception $error) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function update(PenyewaanDetailRequest $request, int $penyewaan_detail_id) {
        DB::beginTransaction();
        try {
            $data = PenyewaanDetail::find($penyewaan_detail_id);
            if (!$data) return $this->jsonResponse(false, "Penyewaan detail dengan id {$penyewaan_detail_id} tidak ditemukan", null, null, 400);

            $alatSebelumnya = Alat::find($data->penyewaan_detail_alat_id);

            if ($alatSebelumnya) {
                $alatSebelumnya->increment('alat_stok', $data->penyewaan_detail_jumlah);
            }

            $validated = $request->validated();

            $alatBaru = Alat::find($validated['penyewaan_detail_alat_id']);
            if (!$alatBaru || $alatBaru->alat_stok < $validated['penyewaan_detail_jumlah']) {
                throw new Exception("Stok alat {$alatBaru->alat_nama} tidak mencukupi.");
            }

            $penyewaan = Penyewaan::find($validated['penyewaan_detail_penyewaan_id']);
            $tglSewa = Carbon::parse($penyewaan->penyewaan_tglsewa);
            $tglKembali = Carbon::parse($penyewaan->penyewaan_tglkembali);
            if ($tglKembali->lt($tglSewa)) {
                throw new Exception("Tanggal kembali harus sama atau setelah tanggal sewa.");
            }
            $durasiSewa = $tglSewa->diffInDays($tglSewa)+1;
            $subtotalHarga = $validated['penyewaan_detail_jumlah'] * $alatBaru->alat_hargaperhari * $durasiSewa;
            $validated['penyewaan_detail_subharga'] = $subtotalHarga;
            $alatBaru->decrement('alat_stok', $validated['penyewaan_detail_jumlah']);
            $data->update($validated);
            $totalHarga = PenyewaanDetail::where('penyewaan_detail_penyewaan_id', $validated['penyewaan_detail_penyewaan_id'])
                ->sum('penyewaan_detail_subharga');

            Penyewaan::where('penyewaan_id', $data->penyewaan_detail_penyewaan_id)
    ->update(['penyewaan_totalharga' => $totalHarga ?: 0]);
DB::commit();

            Cache::forget('penyewaan_detail');
            Cache::forget("penyewaan_detail_{$penyewaan_detail_id}");
            Cache::forget("penyewaan_{$validated['penyewaan_detail_penyewaan_id']}");

            return $this->jsonResponse(true, 'Sukses mengupdate data penyewaan detail', $data);
        } catch (Exception $error) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function destroy(int $penyewaan_detail_id) {
        DB::beginTransaction();
        try {
            $data = PenyewaanDetail::find($penyewaan_detail_id);
            if (!$data) return $this->jsonResponse(false, 'Penyewaan detail tidak ditemukan', null, null, 400);

            $alat = Alat::find($data->penyewaan_detail_alat_id);
            if ($alat) {
                $alat->increment('alat_stok', $data->penyewaan_detail_jumlah);
            }

            $data->delete();

            $totalHarga = PenyewaanDetail::where('penyewaan_detail_penyewaan_id', $data->penyewaan_detail_penyewaan_id)
                ->sum('penyewaan_detail_subharga');

    
            Penyewaan::where('penyewaan_id', $data->penyewaan_detail_penyewaan_id)
                ->update(['penyewaan_totalharga' => $totalHarga]);

         
            DB::commit();


            Cache::forget('penyewaan_detail');
            Cache::forget("penyewaan_detail_{$penyewaan_detail_id}");
            Cache::forget("penyewaan_{$data->penyewaan_detail_penyewaan_id}");

            return $this->jsonResponse(true, 'Sukses menghapus data penyewaan detail', $data);
        } catch (Exception $error) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }
}