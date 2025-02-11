<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penyewaan extends Model
{
    use HasFactory;
    protected $table = 'penyewaan';
    protected $guarded = [];
    protected $primaryKey = 'penyewaan_id';
    public function pelanggan(){
        return $this->belongsTo(Pelanggan::class,'penyewaan_pelanggan_id','pelanggan_id');
    }
    public function penyewaandetail(){
        return $this->hasMany(PenyewaanDetail::class,'penyewaan_detail_penyewaan_id','penyewaan_id');
    }
}
