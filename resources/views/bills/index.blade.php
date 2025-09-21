@extends('layouts.app')

@section('content')
<div x-data="billsIndex()">
    <!-- Header -->
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Manajemen Tagihan</h1>
            <p class="mt-2 text-sm text-gray-700">Kelola tagihan dan realisasi anggaran per bulan.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none space-x-3">
            @can('create bills')
            <a href="{{ route('bills.create') }}" class="block rounded-md bg-gradient-to-r from-navy-600 to-navy-700 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:from-navy-700 hover:to-navy-800">
                Tambah Tagihan
            </a>
            @endcan
        </div>
    </div>

    <!-- Filters -->
    <div class="mt-6 bg-white shadow rounded-lg p-6">
        <form method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:space-x-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700">Pencarian</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm"
                       placeholder="Cari berdasarkan nomor tagihan atau deskripsi...">
            </div>
            <div class="min-w-0 flex-1 sm:max-w-xs">
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="sp2d" {{ request('status') == 'sp2d' ? 'selected' : '' }}>SP2D</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="min-w-0 flex-1 sm:max-w-xs">
                <label for="month" class="block text-sm font-medium text-gray-700">Bulan</label>
                <select name="month" id="month" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm">
                    <option value="">Semua Bulan</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="min-w-0 flex-1 sm:max-w-xs">
                <label for="year" class="block text-sm font-medium text-gray-700">Tahun</label>
                <select name="year" id="year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm">
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="inline-flex items-center rounded-md bg-navy-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-navy-500">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                    </svg>
                    Filter
                </button>
                <a href="{{ route('bills.index') }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Bulk Actions -->
    @can('approve bills')
    <div x-show="selectedBills.length > 0" class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <span class="text-sm text-blue-700" x-text="`${selectedBills.length} tagihan dipilih`"></span>
            </div>
            <div class="flex space-x-2">
                <button @click="bulkUpdateStatus('sp2d')" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                    Tandai SP2D
                </button>
                <button @click="bulkUpdateStatus('cancelled')" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                    Batalkan
                </button>
            </div>
        </div>
    </div>
    @endcan

    <!-- Table -->
    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                @can('approve bills')
                                <th scope="col" class="relative px-6 py-3">
                                    <input type="checkbox" @change="toggleAll($event)"
                                           class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-navy-600 focus:ring-navy-600">
                                </th>
                                @endcan
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nomor Tagihan
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kategori Anggaran
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jumlah
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Periode
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dibuat Oleh
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($bills as $bill)
                            <tr class="hover:bg-gray-50">
                                @can('approve bills')
                                <td class="relative px-6 py-4">
                                    <input type="checkbox" x-model="selectedBills" value="{{ $bill->id }}"
                                           class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-navy-600 focus:ring-navy-600">
                                </td>
                                @endcan
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $bill->bill_number }}</div>
                                    <div class="text-sm text-gray-500">{{ $bill->bill_date->format('d M Y') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $bill->budgetCategory->program_kegiatan_output }}">
                                        {{ $bill->budgetCategory->full_code }}
                                    </div>
                                    <div class="text-sm text-gray-500 max-w-xs truncate">
                                        {{ Str::limit($bill->budgetCategory->program_kegiatan_output, 50) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($bill->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $bill->month_name }} {{ $bill->year }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {!! $bill->status_badge !!}
                                    @if($bill->status === 'sp2d' && $bill->sp2d_number)
                                        <div class="text-xs text-gray-500 mt-1">SP2D: {{ $bill->sp2d_number }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $bill->creator->name }}
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('bills.show', $bill) }}" class="text-navy-600 hover:text-navy-900">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                        @if($bill->status === 'pending' && (auth()->user()->canInputBills() || auth()->id() === $bill->created_by))
                                        <a href="{{ route('bills.edit', $bill) }}" class="text-amber-600 hover:text-amber-900">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z" />
                                            </svg>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center py-8">
                                        <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500">Tidak ada tagihan yang ditemukan</p>
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
    @if($bills->hasPages())
    <div class="mt-6">
        {{ $bills->links() }}
    </div>
    @endif

    <!-- Bulk Update Modal -->
    <div x-show="showBulkModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showBulkModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div x-show="showBulkModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <form @submit.prevent="submitBulkUpdate()">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Update Status Tagihan</h3>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Status Baru</label>
                            <select x-model="bulkStatus" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500">
                                <option value="sp2d">SP2D</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>

                        <div x-show="bulkStatus === 'sp2d'">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Nomor SP2D</label>
                                <input type="text" x-model="sp2dNumber" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Tanggal SP2D</label>
                                <input type="date" x-model="sp2dDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500">
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-navy-600 text-base font-medium text-white hover:bg-navy-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500 sm:col-start-2 sm:text-sm">
                            Update
                        </button>
                        <button type="button" @click="showBulkModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function billsIndex() {
    return {
        selectedBills: [],
        showBulkModal: false,
        bulkStatus: 'sp2d',
        sp2dNumber: '',
        sp2dDate: '',

        toggleAll(event) {
            if (event.target.checked) {
                this.selectedBills = Array.from(document.querySelectorAll('input[type="checkbox"][value]')).map(cb => cb.value);
            } else {
                this.selectedBills = [];
            }
        },

        bulkUpdateStatus(status) {
            this.bulkStatus = status;
            this.showBulkModal = true;
        },

        submitBulkUpdate() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("bills.bulk-update-status") }}';

            // CSRF Token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            // Bill IDs
            this.selectedBills.forEach(billId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'bill_ids[]';
                input.value = billId;
                form.appendChild(input);
            });

            // Status
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = this.bulkStatus;
            form.appendChild(statusInput);

            // SP2D fields if needed
            if (this.bulkStatus === 'sp2d') {
                const sp2dNumberInput = document.createElement('input');
                sp2dNumberInput.type = 'hidden';
                sp2dNumberInput.name = 'sp2d_number';
                sp2dNumberInput.value = this.sp2dNumber;
                form.appendChild(sp2dNumberInput);

                const sp2dDateInput = document.createElement('input');
                sp2dDateInput.type = 'hidden';
                sp2dDateInput.name = 'sp2d_date';
                sp2dDateInput.value = this.sp2dDate;
                form.appendChild(sp2dDateInput);
            }

            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>
@endsection
