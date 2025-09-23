@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Detail Tagihan
            </h2>
            <div class="mt-2 flex items-center text-sm text-gray-500">
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $bill->status_color }}">
                    {{ $bill->status }}
                </span>
                @if($bill->no_spp)
                    <span class="ml-2 text-gray-400">•</span>
                    <span class="ml-2">No SPP: {{ $bill->no_spp }}</span>
                @endif
                <span class="ml-2 text-gray-400">•</span>
                <span class="ml-2">{{ $bill->tgl_spp_formatted }}</span>
            </div>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0 space-x-3">
            <a href="{{ route('bills.edit', $bill->id) }}"
               class="inline-flex items-center rounded-md bg-navy-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-navy-700">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
            @if($bill->status !== 'Tagihan Telah SP2D')
            <button onclick="confirmDelete()" class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Hapus
            </button>
            @endif
            <a href="{{ route('bills.index') }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="mb-6 rounded-md bg-green-50 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Basic Information -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Informasi Dasar</h3>
        </div>
        <div class="px-6 py-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">No</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->no ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Bulan</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->month }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">No SPP</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->no_spp ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nominatif</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->nominatif ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Tanggal SPP</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->tgl_spp_formatted }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Jenis Kegiatan</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->jenis_kegiatan ?: '-' }}</dd>
                </div>
            </dl>

            @if($bill->uraian_spp)
            <div class="mt-6">
                <dt class="text-sm font-medium text-gray-500">Uraian SPP</dt>
                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $bill->uraian_spp }}</dd>
            </div>
            @endif
        </div>
    </div>

    <!-- Contract & Document Information -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Informasi Kontrak & Dokumen</h3>
        </div>
        <div class="px-6 py-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Jenis Kontraktual</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->kontraktual_type ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nomor Kontrak/SPBy</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->nomor_kontrak_spby ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">No BAST/Kuitansi</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->no_bast_kuitansi ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">ID e-Perjadin</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->id_e_perjadin ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nomor Surat Tugas/BAST/SK</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->nomor_surat_tugas_bast_sk ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Tanggal ST/SK</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->tanggal_st_sk ? $bill->tanggal_st_sk->format('d F Y') : '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nomor Undangan</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->nomor_undangan ?: '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Organization & Coding -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Informasi Bagian & Koding</h3>
        </div>
        <div class="px-6 py-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Bagian</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->bagian ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nama PIC</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->nama_pic ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Kode Kegiatan</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $bill->kode_kegiatan ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">KRO</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $bill->kro ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">RO</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $bill->ro ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Sub Komponen</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $bill->sub_komponen ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">MAK</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $bill->mak ?: '-' }}</dd>
                </div>
                <div class="lg:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">COA (Auto-generated)</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono bg-gray-50 px-3 py-2 rounded">{{ $bill->coa ?: '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Financial Information -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Informasi Keuangan</h3>
        </div>
        <div class="px-6 py-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Bruto</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900">Rp {{ number_format($bill->bruto, 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Pajak PPN</dt>
                    <dd class="mt-1 text-lg font-semibold text-red-600">Rp {{ number_format($bill->pajak_ppn, 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Pajak PPH</dt>
                    <dd class="mt-1 text-lg font-semibold text-red-600">Rp {{ number_format($bill->pajak_pph, 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Netto</dt>
                    <dd class="mt-1 text-xl font-bold text-green-600">Rp {{ number_format($bill->netto, 0, ',', '.') }}</dd>
                </div>
            </dl>

            <div class="mt-6 grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Tanggal Mulai</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->tanggal_mulai ? $bill->tanggal_mulai->format('d F Y') : '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Tanggal Selesai</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->tanggal_selesai ? $bill->tanggal_selesai->format('d F Y') : '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">LS/Bendahara</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->ls_bendahara ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Staff PPK</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->staff_ppk ?: '-' }}</dd>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Information -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Informasi Pembayaran</h3>
        </div>
        <div class="px-6 py-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">No SP2D</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->no_sp2d ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Tgl Selesai SP2D</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->tgl_selesai_sp2d_formatted ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Tgl SP2D</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->tgl_sp2d_formatted ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $bill->status_color }}">
                            {{ $bill->status }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Posisi Uang</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $bill->posisi_uang ?: '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Budget Category Information -->
    @if($bill->budgetCategory)
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Kategori Anggaran Terkait</h3>
        </div>
        <div class="px-6 py-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-blue-800">{{ $bill->budgetCategory->full_code }}</h4>
                        <p class="text-sm text-blue-700">{{ $bill->budgetCategory->program_kegiatan_output }}</p>
                        <p class="text-xs text-blue-600 mt-1">PIC: {{ $bill->budgetCategory->pic }}</p>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('budget.show', $bill->budgetCategory->id) }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                        Lihat Detail Anggaran →
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Audit Information -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Informasi Audit</h3>
        </div>
        <div class="px-6 py-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
               <div>
                    <dt class="text-sm font-medium text-gray-500">Dibuat oleh</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $bill->creator->name ?? 'Unknown' }}
                        <div class="text-xs text-gray-500">{{ $bill->created_at->format('d F Y H:i') }}</div>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Terakhir diubah</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($bill->updater)
                            {{ $bill->updater->name }}
                            <div class="text-xs text-gray-500">{{ $bill->updated_at->format('d F Y H:i') }}</div>
                        @else
                            <span class="text-gray-400">Belum pernah diubah</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
async function confirmDelete() {
    const result = await Swal.fire({
        title: 'Konfirmasi Hapus',
        html: `
            <div class="text-left">
                <p class="mb-2">Apakah Anda yakin ingin menghapus tagihan ini?</p>
                <div class="bg-gray-50 p-3 rounded-md">
                    <p class="font-semibold text-gray-900">{{ $bill->no_spp ?: 'No SPP: -' }}</p>
                    <p class="text-sm text-gray-600">{{ $bill->nominatif ?: 'Nominatif: -' }}</p>
                    <p class="text-sm text-gray-600">Netto: Rp {{ number_format($bill->netto, 0, ',', '.') }}</p>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    });

    if (result.isConfirmed) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("bills.destroy", $bill->id) }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
