<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $primaryKey = 'pelanggan_id';
    protected $table = 'pelanggan';
    public function penyewaan(){
        return $this->hasMany(Penyewaan::class, 'penyewaan_pelanggan_id','pelanggan_id');
    }
    public function pelanggandata(){
        return $this->hasOne(PelangganData::class, 'pelanggan_data_pelanggan_id','pelanggan_id');
    }
}
