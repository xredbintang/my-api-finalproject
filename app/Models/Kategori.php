<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $primaryKey = 'kategori_id';
    protected $table = 'kategori';

    public function alat(){
        return $this->hasMany(Alat::class, 'alat_kategori_id','kategori_id');
    }
    
}
