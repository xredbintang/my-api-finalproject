<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alat extends Model
{
    use HasFactory;
    protected $table = 'alat';
    protected $guarded = [];
    protected $primaryKey = 'alat_id';
    public function kategori(){
        return $this->belongsTo(Kategori::class,'alat_kategori_id','kategori_id');
    }
    public function penyewaandetail(){
        return $this->hasMany(PenyewaanDetail::class,'penyewaan_detail_alat_id','alat_id');
    }
}
