<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelangganData extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $primaryKey = 'pelanggan_data_id';
    protected $table = 'pelanggan_data';

    public function pelanggan(){
        return $this->belongsTo(Pelanggan::class, 'pelanggan_data_pelanggan_id','pelanggan_id');
    }
    
}
