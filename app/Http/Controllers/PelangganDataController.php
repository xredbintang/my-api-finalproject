<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PelangganDataRequest;
use App\Models\PelangganData;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PelangganDataController extends Controller
{
    private function jsonResponse($success, $message, $data = null, $errors = null, $status = 200) {
        return response()->json(compact('success', 'message', 'data', 'errors'), $status);
    }

    public function index() {
        try {
            $data = Cache::remember('pelanggandata', 30, fn() => PelangganData::all());
            return $this->jsonResponse(true, 'Sukses mendapatkan data pelanggan', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function show(int $pelanggan_data_id) {
        try {
            $data = Cache::remember("pelanggandata_{$pelanggan_data_id}", 30, fn() => PelangganData::find($pelanggan_data_id));
            return $data ? $this->jsonResponse(true, 'Sukses mendapatkan data pelanggan', $data)
                         : $this->jsonResponse(false, "Pelanggan dengan id {$pelanggan_data_id} tidak ditemukan", null, null, 400);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function store(PelangganDataRequest $request) {
        try {
            $data = $request->validated();
            $existed =  PelangganData::where('pelanggan_data_pelanggan_id', $request->pelanggan_data_pelanggan_id)->first();
            if ($existed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pelanggan yang anda masukkan sudah terdaftar dengan data ',
                    'data' => null,
                    'errors' => null
                ], 400);
            };
            
            if ($request->hasFile('pelanggan_data_file')) {
                $file = $request->file('pelanggan_data_file');
                $data['pelanggan_data_file'] = $file->storeAs('pelanggan_files', Str::random(40) . '.' . $file->getClientOriginalExtension(), 'public');
            }
            $createdData = PelangganData::create($data);
            Cache::forget('pelanggandata');
            return $this->jsonResponse(true, 'Sukses membuat data pelanggan', $createdData);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function update(PelangganDataRequest $request, int $pelanggan_data_id) {
        try {
            $data = PelangganData::find($pelanggan_data_id);
            if (!$data) return $this->jsonResponse(false, "Pelanggan dengan id {$pelanggan_data_id} tidak ditemukan", null, null, 400);
            
            $data->update($request->validated());
            Cache::forget('pelanggandata');
            Cache::forget("pelanggandata_{$pelanggan_data_id}");
            return $this->jsonResponse(true, 'Sukses mengupdate data pelanggan', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }

    public function destroy(int $pelanggan_data_id) {
        try {
            $data = PelangganData::find($pelanggan_data_id);
            if (!$data) return $this->jsonResponse(false, 'Pelanggan tidak ditemukan', null, null, 400);
            
            $data->delete();
            Cache::forget('pelanggandata');
            Cache::forget("pelanggandata_{$pelanggan_data_id}");
            return $this->jsonResponse(true, 'Sukses menghapus data pelanggan', $data);
        } catch (Exception $error) {
            return $this->jsonResponse(false, 'Terjadi kesalahan pada server', null, $error->getMessage(), 500);
        }
    }
}
