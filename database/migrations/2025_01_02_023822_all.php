<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggan', function(Blueprint $table){
        $table->id('pelanggan_id')->primary(); 
		$table->string('pelanggan_nama', 150)->nullable(false);
        $table->string('pelanggan_alamat', 200)->nullable(false);
        $table->char('pelanggan_notelp', 13)->nullable(false);
        $table->string('pelanggan_email', 100)->nullable(false)->unique(); 
		$table->timestamps();
        });
        
        Schema::create('admin', function(Blueprint $table){
        $table->id('admin_id')->primary(); 
		$table->string('admin_username', 50)->nullable(false);
        $table->string('admin_password', 255)->nullable(false);
        $table->timestamps();
        });

        Schema::create('kategori', function(Blueprint $table){
        $table->id('kategori_id')->primary(); 
		$table->string('kategori_nama', 100)->nullable(false);
        $table->timestamps();
        });

        Schema::create('pelanggan_data', function(Blueprint $table){
        $table->id('pelanggan_data_id')->primary(); 
		$table->foreignId('pelanggan_data_pelanggan_id')->nullable(false)->constrained('pelanggan','pelanggan_id')
        ->cascadeOnUpdate()->cascadeOnDelete();
        $table->enum('pelanggan_data_jenis',['KTP','SIM'])->nullable(false);
        $table->string('pelanggan_data_file',255)->nullable(false); 
		$table->timestamps();
        });

        Schema::create('penyewaan', function(Blueprint $table){
        $table->id('penyewaan_id')->primary(); 
		$table->foreignId('penyewaan_pelanggan_id')->constrained('pelanggan','pelanggan_id')
        ->cascadeOnUpdate()->cascadeOnDelete();
        $table->date('penyewaan_tglsewa')->nullable (false);
        $table->date('penyewaan_tglkembali')->nullable (false);
        $table->enum('penyewaan_sttspembayaran',['Lunas','Belum dibayar','DP'])->default('Belum dibayar')->nullable(false);
        $table->enum('penyewaan_sttskembali',['Sudah kembali','Belum kembali','DP'])->default('Belum kembali')->nullable(false);
        $table->integer('penyewaan_totalharga')->nullable (false);
        $table->timestamps();
        });

        Schema::create('alat', function(Blueprint $table){
            $table->id('alat_id')->primary(); 
            $table->foreignId('alat_kategori_id')->nullable(false)
            ->constrained('kategori','kategori_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('alat_nama',150)->nullable(false);
            $table->string('alat_deskripsi',255)->nullable(false);
            $table->integer('alat_hargaperhari')->nullable(false);
            $table->integer('alat_stok')->nullable(false);
            $table->timestamps();
            });

        Schema::create('penyewaan_detail', function(Blueprint $table){
            $table->id('penyewaan_detail_id')->primary(); 
            $table->foreignId('penyewaan_detail_penyewaan_id')->nullable(false)
            ->constrained('penyewaan','penyewaan_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('penyewaan_detail_alat_id')->nullable(false)
            ->constrained('alat','alat_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('penyewaan_detail_jumlah')->nullable(false);
            $table->integer('penyewaan_detail_subharga')->nullable(false);
            $table->timestamps();
            });
    }

    
    public function down(): void
{
    Schema::dropIfExists('pelanggan_data');
    Schema::dropIfExists('penyewaan');
    Schema::dropIfExists('alat');
    Schema::dropIfExists('penyewaan_detail');
    Schema::dropIfExists('kategori');
    Schema::dropIfExists('admin');
    Schema::dropIfExists('pelanggan');
}

};
