<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('no')->nullable();
            $table->string('month');
            $table->string('no_spp')->nullable();
            $table->string('nominatif')->nullable();
            $table->date('tgl_spp');
            $table->string('jenis_kegiatan')->nullable();
            $table->enum('kontraktual_type', ['Kontraktual', 'Non Kontraktual', 'GUP', 'TUP'])->nullable();
            $table->string('nomor_kontrak_spby')->nullable();
            $table->string('no_bast_kuitansi')->nullable();
            $table->string('id_e_perjadin')->nullable();
            $table->text('uraian_spp')->nullable();
            $table->enum('bagian', ['TU', 'Persija', 'MP', 'Pengelolaan', 'Perencanaan', 'Penat'])->nullable();
            $table->string('nama_pic')->nullable();
            $table->string('kode_kegiatan')->nullable();
            $table->string('kro')->nullable();
            $table->string('ro')->nullable();
            $table->string('sub_komponen')->nullable();
            $table->string('mak')->nullable();
            $table->string('nomor_surat_tugas_bast_sk')->nullable();
            $table->date('tanggal_st_sk')->nullable();
            $table->string('nomor_undangan')->nullable();
            $table->decimal('bruto', 15, 2)->default(0);
            $table->decimal('pajak_ppn', 15, 2)->default(0);
            $table->decimal('pajak_pph', 15, 2)->default(0);
            $table->decimal('netto', 15, 2)->default(0);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->enum('ls_bendahara', ['LS', 'Bendahara'])->nullable();
            $table->enum('staff_ppk', ['Diaz', 'Nomo'])->nullable();
            $table->string('no_sp2d')->nullable();
            $table->date('tgl_selesai_sp2d')->nullable();
            $table->date('tgl_sp2d')->nullable();
            $table->enum('status', [
                'Kegiatan Masih Berlangsung',
                'Tagihan Belum Disampaikan oleh Pihak Terkait',
                'Tagihan Telah Disampaikan oleh Pihak Terkait',
                'Tagihan Telah Diterbitkan SPP',
                'Tagihan Telah SP2D'
            ])->default('Kegiatan Masih Berlangsung');
            $table->string('coa')->nullable();
            $table->enum('posisi_uang', ['Bendahara', 'Penerima'])->nullable();

            // Relations
            $table->foreignId('budget_category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            // Indexes
            $table->index(['tgl_spp']);
            $table->index(['month']);
            $table->index(['status']);
            $table->index(['kode_kegiatan', 'kro', 'ro']);
            $table->index(['budget_category_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bills');
    }
};
