@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Laporan Tagihan</h1>
            <p class="mt-2 text-sm text-gray-700">Laporan status tagihan, SP2D, dan outstanding bills.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('reports.bills', array_merge(request()->all(), ['format' => 'pdf'])) }}"
               class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export PDF
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="mt-6 bg-white shadow rounded-lg p-6">
        <form method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:space-x-4">
            <div class="min-w-0 flex-1 sm:max-w-xs">
                <label for="year" class="block text-sm font-medium text-gray-700">Tahun</label>
                <select name="year" id="year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm">
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="min-w-0 flex-1 sm:max-w-xs">
                <label for="month" class="block text-sm font-medium text-gray-700">Bulan</label>
                <select name="month" id="month" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm">
                    <option value="">Semua Bulan</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="min-w-0 flex-1 sm:max-w-xs">
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="sp2d" {{ $status == 'sp2d' ? 'selected' : '' }}>SP2D</option>
                    <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="inline-flex items-center rounded-md bg-navy-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-navy-500">
                    Filter
                </button>
                <a href="{{ route('reports.bills') }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary -->
    <div class="mt-6 bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Ringkasan</h3>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <dt class="text-sm font-medium text-blue-600">Total Tagihan</dt>
                <dd class="text-xl font-semibold text-blue-900">{{ $summary['total_bills'] }}</dd>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <dt class="text-sm font-medium text-green-600">SP2D</dt>
                <dd class="text-xl font-semibold text-green-900">{{ $summary['sp2d_count'] }}</dd>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <dt class="text-sm font-medium text-yellow-600">Pending</dt>
                <dd class="text-xl font-semibold text-yellow-900">{{ $summary['pending_count'] }}</dd>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <dt class="text-sm font-medium text-red-600">Dibatalkan</dt>
                <dd class="text-xl font-semibold text-red-900">{{ $summary['cancelled_count'] }}</dd>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <dt class="text-sm font-medium text-purple-600">Nilai SP2D</dt>
                <dd class="text-lg font-semibold text-purple-900">Rp {{ number_format($summary['sp2d_amount'], 0, ',', '.') }}</dd>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg">
                <dt class="text-sm font-medium text-orange-600">Nilai Pending</dt>
                <dd class="text-lg font-semibold text-orange-900">Rp {{ number_format($summary['pending_amount'], 0, ',', '.') }}</dd>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nomor Tagihan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kategori Anggaran
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Periode
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jumlah
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dibuat Oleh
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($bills as $bill)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $bill->bill_number }}</div>
                                    <div class="text-sm text-gray-500">{{ $bill->bill_date->format('d M Y') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $bill->budgetCategory->full_code }}</div>
                                    <div class="text-sm text-gray-500 max-w-xs truncate">{{ $bill->budgetCategory->program_kegiatan_output }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $bill->month_name }} {{ $bill->year }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($bill->amount, 0, ',', '.') }}
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
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada data tagihan untuk periode yang dipilih
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
