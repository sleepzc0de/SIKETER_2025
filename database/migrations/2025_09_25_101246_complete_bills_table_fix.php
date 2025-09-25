<?php
// database/migrations/2025_09_25_101246_complete_bills_table_fix.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing bills table and recreate with complete structure
        Schema::dropIfExists('bills');

        Schema::create('bills', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('no')->nullable();
            $table->integer('month');
            $table->integer('year')->default(2025);
            $table->string('no_spp')->nullable();
            $table->string('nominatif')->nullable();
            $table->date('tgl_spp');
            $table->string('jenis_kegiatan')->nullable();

            // Contract/Document Information
            $table->enum('kontraktual_type', ['Kontraktual', 'Non Kontraktual', 'GUP', 'TUP'])->nullable();
            $table->string('nomor_kontrak_spby')->nullable();
            $table->string('no_bast_kuitansi')->nullable();
            $table->string('id_e_perjadin')->nullable();
            $table->string('nomor_surat_tugas_bast_sk')->nullable();
            $table->date('tanggal_st_sk')->nullable();
            $table->string('nomor_undangan')->nullable();
            $table->text('uraian_spp')->nullable();

            // Organization & Coding
            $table->enum('bagian', [
                'Kepala Kantor', 'Kasubag TU', 'Bendahara', 'Operator SAKTI',
                'Bagian Umum', 'Bagian Kepegawaian', 'Bagian Keuangan',
                'Seksi Pengawasan I', 'Seksi Pengawasan II', 'Seksi Pengawasan III',
                'Seksi Investigasi', 'Seksi Pengembangan Pengawasan',
                'APIP', 'Pejabat Penandatangan SPM'
            ])->nullable();
            $table->string('nama_pic')->nullable();
            $table->string('kode_kegiatan')->nullable();
            $table->string('kro')->nullable();
            $table->string('ro')->nullable();
            $table->string('sub_komponen')->nullable();
            $table->string('mak')->nullable();
            $table->string('coa')->nullable();

            // Financial Information
            $table->decimal('bruto', 15, 2)->default(0);
            $table->decimal('pajak_ppn', 15, 2)->default(0);
            $table->decimal('pajak_pph', 15, 2)->default(0);
            $table->decimal('netto', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0); // Same as netto for compatibility
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();

            // Staff & Payment Information
            $table->enum('ls_bendahara', ['LS', 'Bendahara'])->nullable();
            $table->enum('staff_ppk', [
                'Kepala Kantor', 'Kasubag TU', 'Bendahara',
                'Pejabat Penandatangan SPM', 'Staff Keuangan'
            ])->nullable();
            $table->string('no_sp2d')->nullable();
            $table->date('tgl_selesai_sp2d')->nullable();
            $table->date('tgl_sp2d')->nullable();

            // Status & Position
            $table->enum('status', [
                'Kegiatan Masih Berlangsung',
                'SPP Sedang Diproses',
                'SPP Sudah Diserahkan ke KPPN',
                'Tagihan Telah SP2D',
                'Dibatalkan'
            ])->default('Kegiatan Masih Berlangsung');
            $table->enum('posisi_uang', [
                'Kas Negara',
                'Kas Daerah',
                'Bendahara Pengeluaran',
                'Rekening Pihak Ketiga'
            ])->nullable();

            // Approval Information
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('approval_notes')->nullable();

            // System Information
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['year', 'month']);
            $table->index(['status']);
            $table->index(['bagian']);
            $table->index(['tgl_spp']);
            $table->index(['approved_at']);
            $table->index(['created_at']);
            $table->index(['coa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
