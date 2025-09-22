@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto" x-data="budgetManager()">
    <!-- Header -->
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Data Anggaran</h1>
            <p class="mt-2 text-sm text-gray-700">Kelola data anggaran dan alokasi dana untuk berbagai kegiatan.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            @if(auth()->user()->canManageBudget())
            <div class="flex space-x-3">
                <button @click="showBulkDeleteModal = selectedBudgets.length > 0"
                        x-show="selectedBudgets.length > 0"
                        class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Terpilih (<span x-text="selectedBudgets.length"></span>)
                </button>
                <a href="{{ route('budget.create') }}" class="inline-flex items-center rounded-md bg-navy-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-navy-500">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tambah Anggaran
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Filters -->
    <div class="mt-6 bg-white shadow rounded-lg p-6">
        <form method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:space-x-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700">Pencarian</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm"
                       placeholder="Cari berdasarkan deskripsi, kode, atau PIC...">
            </div>
            <div class="min-w-0 flex-1 sm:max-w-xs">
                <label for="pic" class="block text-sm font-medium text-gray-700">PIC</label>
                <select name="pic" id="pic" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm">
                    <option value="">Semua PIC</option>
                    @foreach($pics as $pic)
                        <option value="{{ $pic }}" {{ request('pic') == $pic ? 'selected' : '' }}>{{ $pic }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-0 flex-1 sm:max-w-xs">
                <label for="kegiatan" class="block text-sm font-medium text-gray-700">Kegiatan</label>
                <select name="kegiatan" id="kegiatan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm">
                    <option value="">Semua Kegiatan</option>
                    @foreach($kegiatans as $kegiatan)
                        <option value="{{ $kegiatan }}" {{ request('kegiatan') == $kegiatan ? 'selected' : '' }}>{{ $kegiatan }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="inline-flex items-center rounded-md bg-navy-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-navy-500">
                    Filter
                </button>
                <a href="{{ route('budget.index') }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                @if(auth()->user()->canManageBudget())
                                <th scope="col" class="relative px-6 py-3">
                                    <input type="checkbox" @change="toggleAll" class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-navy-600 focus:ring-navy-500">
                                </th>
                                @endif
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kategori Anggaran
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pagu Anggaran
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Realisasi
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Progress
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($budgets as $budget)
                            <tr class="hover:bg-gray-50">
                                @if(auth()->user()->canManageBudget())
                                <td class="relative px-6 py-4">
                                    <input type="checkbox" x-model="selectedBudgets" value="{{ $budget->id }}" class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-navy-600 focus:ring-navy-500">
                                </td>
                                @endif
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $budget->full_code }}
                                        </div>
                                        <div class="text-sm text-gray-500 max-w-xs truncate">
                                            {{ $budget->program_kegiatan_output }}
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1">
                                            PIC: {{ $budget->pic }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($budget->budget_allocation, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        Rp {{ number_format($budget->total_penyerapan, 0, ',', '.') }}
                                    </div>
                                    @if($budget->tagihan_outstanding > 0)
                                    <div class="text-xs text-yellow-600">
                                        Outstanding: Rp {{ number_format($budget->tagihan_outstanding, 0, ',', '.') }}
                                    </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-gradient-to-r from-green-500 to-green-600 h-2 rounded-full"
                                                 style="width: {{ min($budget->realization_percentage, 100) }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-500">{{ number_format($budget->realization_percentage, 1) }}%</span>
                                    </div>
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('budget.show', $budget->id) }}" class="text-navy-600 hover:text-navy-900 tooltip" title="Lihat Detail">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        @if(auth()->user()->canManageBudget())
                                        <a href="{{ route('budget.edit', $budget->id) }}" class="text-yellow-600 hover:text-yellow-900 tooltip" title="Edit">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <button @click="confirmDelete({{ $budget->id }}, '{{ $budget->full_code }}', '{{ addslashes($budget->program_kegiatan_output) }}')"
                                                class="text-red-600 hover:text-red-900 tooltip" title="Hapus">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ auth()->user()->canManageBudget() ? '6' : '5' }}" class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center py-8">
                                        <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500">Tidak ada data anggaran</p>
                                        @if(auth()->user()->canManageBudget())
                                        <div class="mt-6">
                                            <a href="{{ route('budget.create') }}" class="inline-flex items-center rounded-md bg-navy-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-navy-500">
                                                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                                Tambah Anggaran Pertama
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($budgets->hasPages())
    <div class="mt-6">
        {{ $budgets->links() }}
    </div>
    @endif

    <!-- Single Delete Modal -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Konfirmasi Hapus Data Anggaran
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" x-text="deleteMessage"></p>

                                <!-- Loading state for deletion preview -->
                                <div x-show="loadingPreview" class="mt-3 flex items-center text-sm text-gray-500">
                                    <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Memuat informasi...
                                </div>

                                <!-- Deletion preview -->
                                <div x-show="!loadingPreview && deletionPreview" class="mt-3 space-y-2">
                                    <div x-show="deletionPreview && !deletionPreview.can_delete" class="p-3 bg-red-50 border border-red-200 rounded-md">
                                        <div class="flex">
                                            <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                            </svg>
                                            <div class="ml-3">
                                                <h4 class="text-sm font-medium text-red-800">Tidak dapat menghapus!</h4>
                                                <div class="mt-1 text-sm text-red-700">
                                                    <template x-for="warning in deletionPreview.warnings" :key="warning">
                                                        <p x-text="warning"></p>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div x-show="deletionPreview && deletionPreview.can_delete && deletionPreview.warnings.length > 0" class="p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                        <div class="flex">
                                            <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                            </svg>
                                            <div class="ml-3">
                                                <h4 class="text-sm font-medium text-yellow-800">Peringatan:</h4>
                                                <div class="mt-1 text-sm text-yellow-700">
                                                    <template x-for="warning in deletionPreview.warnings" :key="warning">
                                                        <p x-text="warning"></p>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div x-show="deletionPreview && deletionPreview.bills.cancelled > 0" class="p-3 bg-blue-50 border border-blue-200 rounded-md">
                                        <div class="text-sm text-blue-700">
                                            <p x-text="`${deletionPreview.bills.cancelled} tagihan yang dibatalkan akan ikut terhapus.`"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button x-show="!loadingPreview && deletionPreview && deletionPreview.can_delete" @click="executeDelete" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Ya, Hapus
                    </button>
                    <button @click="closeDeleteModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Modal -->
    <div x-show="showBulkDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showBulkDeleteModal" x-transition class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="showBulkDeleteModal" x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('budget.bulk-destroy') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Hapus Multiple Data Anggaran</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500" x-text="`Apakah Anda yakin ingin menghapus ${selectedBudgets.length} data anggaran yang dipilih? Tindakan ini tidak dapat dibatalkan.`"></p>

                                    <!-- Hidden inputs for selected budgets -->
                                    <template x-for="budgetId in selectedBudgets" :key="budgetId">
                                        <input type="hidden" name="budget_ids[]" :value="budgetId">
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Ya, Hapus Semua
                        </button>
                        <button @click="showBulkDeleteModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function budgetManager() {
    return {
        selectedBudgets: [],
        showDeleteModal: false,
        showBulkDeleteModal: false,
        deleteTargetId: null,
        deleteMessage: '',
        deletionPreview: null,
        loadingPreview: false,

        toggleAll(event) {
            if (event.target.checked) {
                this.selectedBudgets = Array.from(document.querySelectorAll('input[type="checkbox"][value]')).map(cb => cb.value);
            } else {
                this.selectedBudgets = [];
            }
        },

        async confirmDelete(budgetId, budgetCode, budgetName) {
            this.deleteTargetId = budgetId;
            this.deleteMessage = `Apakah Anda yakin ingin menghapus data anggaran "${budgetCode}"?\n\n${budgetName}`;
            this.showDeleteModal = true;
            this.loadingPreview = true;
            this.deletionPreview = null;

            // Load deletion preview
            try {
                const response = await fetch(`/budget/${budgetId}/deletion-preview`);
                const data = await response.json();
                this.deletionPreview = data;
            } catch (error) {
                console.error('Failed to load deletion preview:', error);
                this.deletionPreview = {
                    can_delete: true,
                    warnings: ['Gagal memuat informasi detail. Silakan lanjutkan dengan hati-hati.']
                };
            } finally {
                this.loadingPreview = false;
            }
        },

        executeDelete() {
            if (this.deleteTargetId && this.deletionPreview && this.deletionPreview.can_delete) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/budget/${this.deleteTargetId}`;

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
        },

        closeDeleteModal() {
            this.showDeleteModal = false;
            this.deleteTargetId = null;
            this.deleteMessage = '';
            this.deletionPreview = null;
            this.loadingPreview = false;
        }
    }
}
</script>
@endsection
