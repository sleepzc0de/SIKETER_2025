@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto" x-data="billEditManager()" x-init="init()">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Edit Tagihan</h1>
            <p class="mt-2 text-sm text-gray-700">Ubah informasi tagihan dalam sistem anggaran.</p>
            <div class="mt-2 flex items-center space-x-2">
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $bill->status_color }}">
                    {{ $bill->status }}
                </span>
                @if($bill->no_spp)
                    <span class="text-gray-400">•</span>
                    <span class="text-xs text-gray-600">No SPP: {{ $bill->no_spp }}</span>
                @endif
            </div>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('bills.show', $bill->id) }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('bills.update', $bill->id) }}" method="POST" id="billEditForm">
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Informasi Dasar</h3>
            </div>
            <div class="px-6 py-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- No -->
                <div>
                    <label for="no" class="block text-sm font-medium text-gray-700">No</label>
                    <input type="text" name="no" id="no" value="{{ old('no', $bill->no) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('no') border-red-300 @enderror">
                    @error('no')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Bulan -->
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700">Bulan <span class="text-red-500">*</span></label>
                    <select name="month" id="month" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('month') border-red-300 @enderror">
                        <option value="">Pilih Bulan</option>
                        @foreach($months as $value => $label)
                            <option value="{{ $value }}" {{ old('month', $bill->month) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('month')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No SPP -->
                <div>
                    <label for="no_spp" class="block text-sm font-medium text-gray-700">No SPP</label>
                    <input type="text" name="no_spp" id="no_spp" value="{{ old('no_spp', $bill->no_spp) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('no_spp') border-red-300 @enderror">
                    @error('no_spp')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nominatif -->
                <div>
                    <label for="nominatif" class="block text-sm font-medium text-gray-700">Nominatif</label>
                    <input type="text" name="nominatif" id="nominatif" value="{{ old('nominatif', $bill->nominatif) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('nominatif') border-red-300 @enderror">
                    @error('nominatif')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal SPP -->
                <div>
                    <label for="tgl_spp" class="block text-sm font-medium text-gray-700">Tanggal SPP <span class="text-red-500">*</span></label>
                    <input type="date" name="tgl_spp" id="tgl_spp" value="{{ old('tgl_spp', $bill->tgl_spp->format('Y-m-d')) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('tgl_spp') border-red-300 @enderror">
                    @error('tgl_spp')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenis Kegiatan -->
                <div>
                    <label for="jenis_kegiatan" class="block text-sm font-medium text-gray-700">Jenis Kegiatan</label>
                    <input type="text" name="jenis_kegiatan" id="jenis_kegiatan" value="{{ old('jenis_kegiatan', $bill->jenis_kegiatan) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('jenis_kegiatan') border-red-300 @enderror">
                    @error('jenis_kegiatan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Contract/Document Information -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Informasi Kontrak & Dokumen</h3>
            </div>
            <div class="px-6 py-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Kontraktual Type -->
                <div>
                    <label for="kontraktual_type" class="block text-sm font-medium text-gray-700">Kontraktual/Non Kontraktual/GUP/TUP</label>
                    <select name="kontraktual_type" id="kontraktual_type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('kontraktual_type') border-red-300 @enderror">
                        <option value="">Pilih Jenis</option>
                        @foreach($kontraktualTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('kontraktual_type', $bill->kontraktual_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('kontraktual_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nomor Kontrak/SPBy -->
                <div>
                    <label for="nomor_kontrak_spby" class="block text-sm font-medium text-gray-700">Nomor Kontrak/Nomor SPBy</label>
                    <input type="text" name="nomor_kontrak_spby" id="nomor_kontrak_spby" value="{{ old('nomor_kontrak_spby', $bill->nomor_kontrak_spby) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('nomor_kontrak_spby') border-red-300 @enderror">
                    @error('nomor_kontrak_spby')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Continue with other contract fields... -->
                <!-- (Similar pattern as create form, but with old values from $bill) -->

                <!-- No BAST/Kuitansi -->
                <div>
                    <label for="no_bast_kuitansi" class="block text-sm font-medium text-gray-700">No BAST/Kuitansi</label>
                    <input type="text" name="no_bast_kuitansi" id="no_bast_kuitansi" value="{{ old('no_bast_kuitansi', $bill->no_bast_kuitansi) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('no_bast_kuitansi') border-red-300 @enderror">
                    @error('no_bast_kuitansi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- ID e-Perjadin -->
                <div>
                    <label for="id_e_perjadin" class="block text-sm font-medium text-gray-700">ID e-Perjadin</label>
                    <input type="text" name="id_e_perjadin" id="id_e_perjadin" value="{{ old('id_e_perjadin', $bill->id_e_perjadin) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('id_e_perjadin') border-red-300 @enderror">
                    @error('id_e_perjadin')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nomor Surat Tugas/BAST/SK -->
                <div>
                    <label for="nomor_surat_tugas_bast_sk" class="block text-sm font-medium text-gray-700">Nomor Surat Tugas/Nomor BAST/Nomor SK</label>
                    <input type="text" name="nomor_surat_tugas_bast_sk" id="nomor_surat_tugas_bast_sk" value="{{ old('nomor_surat_tugas_bast_sk', $bill->nomor_surat_tugas_bast_sk) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('nomor_surat_tugas_bast_sk') border-red-300 @enderror">
                    @error('nomor_surat_tugas_bast_sk')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal ST/SK -->
                <div>
                    <label for="tanggal_st_sk" class="block text-sm font-medium text-gray-700">Tanggal ST/SK</label>
                    <input type="date" name="tanggal_st_sk" id="tanggal_st_sk" value="{{ old('tanggal_st_sk', $bill->tanggal_st_sk ? $bill->tanggal_st_sk->format('Y-m-d') : '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('tanggal_st_sk') border-red-300 @enderror">
                    @error('tanggal_st_sk')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nomor Undangan -->
                <div>
                    <label for="nomor_undangan" class="block text-sm font-medium text-gray-700">Nomor Undangan (Jika Ada)</label>
                    <input type="text" name="nomor_undangan" id="nomor_undangan" value="{{ old('nomor_undangan', $bill->nomor_undangan) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('nomor_undangan') border-red-300 @enderror">
                    @error('nomor_undangan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Uraian SPP -->
            <div class="px-6 pb-6">
                <label for="uraian_spp" class="block text-sm font-medium text-gray-700">Uraian SPP</label>
                <textarea name="uraian_spp" id="uraian_spp" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('uraian_spp') border-red-300 @enderror"
                          placeholder="Masukkan uraian lengkap SPP...">{{ old('uraian_spp', $bill->uraian_spp) }}</textarea>
                @error('uraian_spp')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Organization & Coding -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Informasi Bagian & Koding</h3>
            </div>
            <div class="px-6 py-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Bagian -->
                <div>
                    <label for="bagian" class="block text-sm font-medium text-gray-700">Bagian</label>
                    <select name="bagian" id="bagian"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('bagian') border-red-300 @enderror">
                        <option value="">Pilih Bagian</option>
                        @foreach($bagians as $value => $label)
                            <option value="{{ $value }}" {{ old('bagian', $bill->bagian) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('bagian')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama PIC -->
                <div>
                    <label for="nama_pic" class="block text-sm font-medium text-gray-700">Nama PIC</label>
                    <input type="text" name="nama_pic" id="nama_pic" value="{{ old('nama_pic', $bill->nama_pic) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('nama_pic') border-red-300 @enderror">
                    @error('nama_pic')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cascading Dropdowns for Coding -->
                <!-- Similar to create form but with existing values -->
                <!-- Kode Kegiatan -->
                <div>
                    <label for="kode_kegiatan" class="block text-sm font-medium text-gray-700">Kode Kegiatan</label>
                    <select name="kode_kegiatan" id="kode_kegiatan" @change="updateKros()"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('kode_kegiatan') border-red-300 @enderror">
                        <option value="">Pilih Kode Kegiatan</option>
                        @foreach($kodeKegiatans as $kegiatan)
                            <option value="{{ $kegiatan }}" {{ old('kode_kegiatan', $bill->kode_kegiatan) == $kegiatan ? 'selected' : '' }}>{{ $kegiatan }}</option>
                        @endforeach
                    </select>
                    @error('kode_kegiatan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- KRO -->
                <div>
                    <label for="kro" class="block text-sm font-medium text-gray-700">KRO</label>
                    <select name="kro" id="kro" @change="updateRos()" :disabled="!kodeKegiatan"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('kro') border-red-300 @enderror">
                        <option value="">Pilih KRO</option>
                    </select>
                    @error('kro')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- RO -->
                <div>
                    <label for="ro" class="block text-sm font-medium text-gray-700">RO</label>
                    <select name="ro" id="ro" @change="updateSubKomponens()" :disabled="!kro"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('ro') border-red-300 @enderror">
                        <option value="">Pilih RO</option>
                    </select>
                    @error('ro')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sub Komponen -->
                <div>
                    <label for="sub_komponen" class="block text-sm font-medium text-gray-700">Sub Komponen</label>
                    <select name="sub_komponen" id="sub_komponen" @change="updateMaks()" :disabled="!ro"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('sub_komponen') border-red-300 @enderror">
                        <option value="">Pilih Sub Komponen</option>
                    </select>
                    @error('sub_komponen')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- MAK -->
                <div>
                    <label for="mak" class="block text-sm font-medium text-gray-700">MAK</label>
                    <select name="mak" id="mak" @change="updateCoa()" :disabled="!subKomponen"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('mak') border-red-300 @enderror">
                        <option value="">Pilih MAK</option>
                    </select>
                    @error('mak')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- COA (Auto-generated) -->
                <div>
                    <label for="coa" class="block text-sm font-medium text-gray-700">COA (Auto-generated)</label>
                    <input type="text" name="coa" id="coa" x-model="coa" readonly
                           class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Otomatis: Kode Kegiatan + KRO + RO + Sub Komponen + MAK</p>
                </div>
            </div>
        </div>

        <!-- Financial Information -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Informasi Keuangan</h3>
            </div>
            <div class="px-6 py-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Bruto -->
                <div>
                    <label for="bruto" class="block text-sm font-medium text-gray-700">Bruto (Rp)</label>
                    <input type="number" name="bruto" id="bruto" value="{{ old('bruto', $bill->bruto) }}" step="0.01" min="0"
                           @input="calculateNetto()"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('bruto') border-red-300 @enderror">
                    @error('bruto')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pajak PPN -->
                <div>
                    <label for="pajak_ppn" class="block text-sm font-medium text-gray-700">Pajak PPN (Rp)</label>
                    <input type="number" name="pajak_ppn" id="pajak_ppn" value="{{ old('pajak_ppn', $bill->pajak_ppn) }}" step="0.01" min="0"
                           @input="calculateNetto()"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('pajak_ppn') border-red-300 @enderror">
                    @error('pajak_ppn')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pajak PPH -->
                <div>
                    <label for="pajak_pph" class="block text-sm font-medium text-gray-700">Pajak PPH (Rp)</label>
                    <input type="number" name="pajak_pph" id="pajak_pph" value="{{ old('pajak_pph', $bill->pajak_pph) }}" step="0.01" min="0"
                           @input="calculateNetto()"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('pajak_pph') border-red-300 @enderror">
                    @error('pajak_pph')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Netto (Auto-calculated) -->
                <div>
                    <label for="netto" class="block text-sm font-medium text-gray-700">Netto (Auto-calculated)</label>
                    <input type="number" name="netto" id="netto" x-model="netto" readonly step="0.01"
                           class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Bruto - PPN - PPH</p>
                </div>

                <!-- Continue with other financial fields... -->
                <!-- (Similar pattern, using existing values from $bill) -->
            </div>
        </div>

        <!-- Payment Information & Status -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Informasi Pembayaran & Status</h3>
            </div>
            <div class="px-6 py-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                    <select name="status" id="status" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('status') border-red-300 @enderror">
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $bill->status) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No SP2D -->
                <div>
                    <label for="no_sp2d" class="block text-sm font-medium text-gray-700">No SP2D</label>
                    <input type="text" name="no_sp2d" id="no_sp2d" value="{{ old('no_sp2d', $bill->no_sp2d) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('no_sp2d') border-red-300 @enderror">
                    @error('no_sp2d')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tgl Selesai SP2D -->
                <div>
                    <label for="tgl_selesai_sp2d" class="block text-sm font-medium text-gray-700">Tgl Selesai SP2D</label>
                    <input type="date" name="tgl_selesai_sp2d" id="tgl_selesai_sp2d" value="{{ old('tgl_selesai_sp2d', $bill->tgl_selesai_sp2d ? $bill->tgl_selesai_sp2d->format('Y-m-d') : '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('tgl_selesai_sp2d') border-red-300 @enderror">
                    @error('tgl_selesai_sp2d')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tgl SP2D -->
                <div>
                    <label for="tgl_sp2d" class="block text-sm font-medium text-gray-700">Tgl SP2D</label>
                    <input type="date" name="tgl_sp2d" id="tgl_sp2d" value="{{ old('tgl_sp2d', $bill->tgl_sp2d ? $bill->tgl_sp2d->format('Y-m-d') : '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('tgl_sp2d') border-red-300 @enderror">
                    @error('tgl_sp2d')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- LS/Bendahara -->
                <div>
                    <label for="ls_bendahara" class="block text-sm font-medium text-gray-700">LS/Bendahara</label>
                    <select name="ls_bendahara" id="ls_bendahara"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('ls_bendahara') border-red-300 @enderror">
                        <option value="">Pilih LS/Bendahara</option>
                        @foreach($lsBendaharaOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('ls_bendahara', $bill->ls_bendahara) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('ls_bendahara')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Staff PPK -->
                <div>
                    <label for="staff_ppk" class="block text-sm font-medium text-gray-700">Staff PPK</label>
                    <select name="staff_ppk" id="staff_ppk"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('staff_ppk') border-red-300 @enderror">
                        <option value="">Pilih Staff PPK</option>
                        @foreach($staffPpkOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('staff_ppk', $bill->staff_ppk) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('staff_ppk')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Posisi Uang -->
                <div>
                    <label for="posisi_uang" class="block text-sm font-medium text-gray-700">Posisi Uang</label>
                    <select name="posisi_uang" id="posisi_uang"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('posisi_uang') border-red-300 @enderror">
                        <option value="">Pilih Posisi Uang</option>
                        @foreach($posisiUangOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('posisi_uang', $bill->posisi_uang) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('posisi_uang')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Mulai -->
                <div>
                    <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ old('tanggal_mulai', $bill->tanggal_mulai ? $bill->tanggal_mulai->format('Y-m-d') : '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('tanggal_mulai') border-red-300 @enderror">
                    @error('tanggal_mulai')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Selesai -->
                <div>
                    <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="{{ old('tanggal_selesai', $bill->tanggal_selesai ? $bill->tanggal_selesai->format('Y-m-d') : '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('tanggal_selesai') border-red-300 @enderror">
                    @error('tanggal_selesai')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Warning for SP2D Status -->
        @if($bill->status === 'Tagihan Telah SP2D')
        <div class="mb-6 rounded-md bg-yellow-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Perhatian</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>Tagihan ini sudah berstatus SP2D. Perubahan yang dilakukan akan mempengaruhi realisasi anggaran. Pastikan data yang diubah sudah benar.</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Error Messages -->
        @if($errors->any())
        <div class="mb-6 rounded-md bg-red-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan dalam form:</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Form Actions -->
        <div class="flex items-center justify-end space-x-3 mb-8">
            <a href="{{ route('bills.show', $bill->id) }}"
               class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Batal
            </a>
            <button type="submit"
                    class="inline-flex items-center rounded-md bg-navy-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-navy-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-navy-600">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Update Tagihan
            </button>
        </div>
    </form>
</div>

<script>
function billEditManager() {
    return {
        kodeKegiatan: '{{ old("kode_kegiatan", $bill->kode_kegiatan) }}',
        kro: '{{ old("kro", $bill->kro) }}',
        ro: '{{ old("ro", $bill->ro) }}',
        subKomponen: '{{ old("sub_komponen", $bill->sub_komponen) }}',
        mak: '{{ old("mak", $bill->mak) }}',
        coa: '{{ old("coa", $bill->coa) }}',
        netto: {{ old('netto', $bill->netto) }},

        init() {
            this.calculateNetto();
            if (this.kodeKegiatan) {
                this.updateKros();
            }
        },

        async updateKros() {
            this.kodeKegiatan = document.getElementById('kode_kegiatan').value;
            const oldKro = this.kro;

            if (!this.kodeKegiatan) {
                this.clearSelect('kro');
                this.clearSelect('ro');
                this.clearSelect('sub_komponen');
                this.clearSelect('mak');
                this.kro = '';
                this.ro = '';
                this.subKomponen = '';
                this.mak = '';
                this.updateCoa();
                return;
            }

            try {
                const response = await fetch(`{{ route('bills.ajax.kros') }}?kegiatan=${encodeURIComponent(this.kodeKegiatan)}`);
                const kros = await response.json();
                this.populateSelect('kro', kros, oldKro);

                if (oldKro && kros.includes(oldKro)) {
                    this.kro = oldKro;
                    await this.updateRos();
                } else {
                    this.clearSelect('ro');
                    this.clearSelect('sub_komponen');
                    this.clearSelect('mak');
                    this.kro = '';
                    this.ro = '';
                    this.subKomponen = '';
                    this.mak = '';
                    this.updateCoa();
                }
            } catch (error) {
                console.error('Error fetching KROs:', error);
            }
        },

        async updateRos() {
            this.kro = document.getElementById('kro').value;
            const oldRo = this.ro;

            if (!this.kro) {
                this.clearSelect('ro');
                this.clearSelect('sub_komponen');
                this.clearSelect('mak');
                this.ro = '';
                this.subKomponen = '';
                this.mak = '';
                this.updateCoa();
                return;
            }

            try {
                const response = await fetch(`{{ route('bills.ajax.ros') }}?kegiatan=${encodeURIComponent(this.kodeKegiatan)}&kro=${encodeURIComponent(this.kro)}`);
                const ros = await response.json();
                this.populateSelect('ro', ros, oldRo);

                if (oldRo && ros.includes(oldRo)) {
                    this.ro = oldRo;
                    await this.updateSubKomponens();
                } else {
                    this.clearSelect('sub_komponen');
                    this.clearSelect('mak');
                    this.ro = '';
                    this.subKomponen = '';
                    this.mak = '';
                    this.updateCoa();
                }
            } catch (error) {
                console.error('Error fetching ROs:', error);
            }
        },

        async updateSubKomponens() {
            this.ro = document.getElementById('ro').value;
            const oldSubKomponen = this.subKomponen;

            if (!this.ro) {
                this.clearSelect('sub_komponen');
                this.clearSelect('mak');
                this.subKomponen = '';
                this.mak = '';
                this.updateCoa();
                return;
            }

            try {
                const response = await fetch(`{{ route('bills.ajax.sub-komponens') }}?kegiatan=${encodeURIComponent(this.kodeKegiatan)}&kro=${encodeURIComponent(this.kro)}&ro=${encodeURIComponent(this.ro)}`);
                const subKomponens = await response.json();
                this.populateSelect('sub_komponen', subKomponens, oldSubKomponen);

                if (oldSubKomponen && subKomponens.includes(oldSubKomponen)) {
                    this.subKomponen = oldSubKomponen;
                    await this.updateMaks();
                } else {
                    this.clearSelect('mak');
                    this.subKomponen = '';
                    this.mak = '';
                    this.updateCoa();
                }
            } catch (error) {
                console.error('Error fetching Sub Komponens:', error);
            }
        },

        async updateMaks() {
            this.subKomponen = document.getElementById('sub_komponen').value;
            const oldMak = this.mak;

            if (!this.subKomponen) {
                this.clearSelect('mak');
                this.mak = '';
                this.updateCoa();
                return;
            }

            try {
                const response = await fetch(`{{ route('bills.ajax.maks') }}?kegiatan=${encodeURIComponent(this.kodeKegiatan)}&kro=${encodeURIComponent(this.kro)}&ro=${encodeURIComponent(this.ro)}&sub_komponen=${encodeURIComponent(this.subKomponen)}`);
                const maks = await response.json();
                this.populateSelect('mak', maks, oldMak);

                if (oldMak && maks.includes(oldMak)) {
                    this.mak = oldMak;
                } else {
                    this.mak = '';
                }
                this.updateCoa();
            } catch (error) {
                console.error('Error fetching MAKs:', error);
            }
        },

        updateCoa() {
            this.mak = document.getElementById('mak')?.value || this.mak;
            this.coa = this.kodeKegiatan + this.kro + this.ro + this.subKomponen + this.mak;
        },

        calculateNetto() {
            const bruto = parseFloat(document.getElementById('bruto').value) || 0;
            const ppn = parseFloat(document.getElementById('pajak_ppn').value) || 0;
            const pph = parseFloat(document.getElementById('pajak_pph').value) || 0;
            this.netto = bruto - ppn - pph;
        },

        populateSelect(selectId, options, selectedValue = '') {
            const select = document.getElementById(selectId);
            select.innerHTML = `<option value="">Pilih ${selectId.replace('_', ' ').toUpperCase()}</option>`;

            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option;
                optionElement.textContent = option;
                if (option === selectedValue) {
                    optionElement.selected = true;
                }
                select.appendChild(optionElement);
            });
        },

        clearSelect(selectId) {
            const select = document.getElementById(selectId);
            select.innerHTML = `<option value="">Pilih ${selectId.replace('_', ' ').toUpperCase()}</option>`;
        }
    }
}
</script>
@endsection
