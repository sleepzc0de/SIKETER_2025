@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Laporan Keuangan</h1>
            <p class="mt-2 text-sm text-gray-700">Akses berbagai laporan keuangan dan analisis data anggaran.</p>
        </div>
    </div>

    <!-- Report Cards -->
    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Budget Realization Report -->
        <div class="relative group bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div>
                <span class="rounded-lg inline-flex p-3 bg-blue-50 text-blue-600 ring-4 ring-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </span>
            </div>
            <div class="mt-8">
                <h3 class="text-lg font-medium">
                    <a href="{{ route('reports.budget-realization') }}" class="focus:outline-none">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Laporan Realisasi Anggaran
                    </a>
                </h3>
                <p class="mt-2 text-sm text-gray-500">
                    Laporan komprehensif realisasi anggaran per kategori dan periode dengan analisis penyerapan.
                </p>
                <div class="mt-4 flex items-center text-sm text-gray-500">
                    <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export PDF/Excel
                </div>
            </div>
        </div>

        <!-- Bills Report -->
        <div class="relative group bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div>
                <span class="rounded-lg inline-flex p-3 bg-green-50 text-green-600 ring-4 ring-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
            </div>
            <div class="mt-8">
                <h3 class="text-lg font-medium">
                    <a href="{{ route('reports.bills') }}" class="focus:outline-none">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Laporan Tagihan
                    </a>
                </h3>
                <p class="mt-2 text-sm text-gray-500">
                    Laporan status tagihan, SP2D, dan outstanding bills dengan analisis waktu pemrosesan.
                </p>
                <div class="mt-4 flex items-center text-sm text-gray-500">
                    <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Status & Timeline
                </div>
            </div>
        </div>

        <!-- Monthly Comparison -->
        <div class="relative group bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div>
                <span class="rounded-lg inline-flex p-3 bg-purple-50 text-purple-600 ring-4 ring-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </span>
            </div>
            <div class="mt-8">
                <h3 class="text-lg font-medium">
                    <a href="{{ route('reports.monthly-comparison') }}" class="focus:outline-none">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Perbandingan Bulanan
                    </a>
                </h3>
                <p class="mt-2 text-sm text-gray-500">
                    Analisis trend realisasi anggaran per bulan dengan perbandingan target dan capaian.
                </p>
                <div class="mt-4 flex items-center text-sm text-gray-500">
                    <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Grafik & Trend
                </div>
            </div>
        </div>

        <!-- Trend Analysis -->
        <div class="relative group bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div>
                <span class="rounded-lg inline-flex p-3 bg-orange-50 text-orange-600 ring-4 ring-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4" />
                    </svg>
                </span>
            </div>
            <div class="mt-8">
                <h3 class="text-lg font-medium">
                    <a href="{{ route('reports.trend-analysis') }}" class="focus:outline-none">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Analisis Trend
                    </a>
                </h3>
                <p class="mt-2 text-sm text-gray-500">
                    Analisis trend pertumbuhan realisasi anggaran dengan proyeksi dan prediksi.
                </p>
                <div class="mt-4 flex items-center text-sm text-gray-500">
                    <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Prediksi & Proyeksi
                </div>
            </div>
        </div>

        <!-- Summary Dashboard -->
        <div class="relative group bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div>
                <span class="rounded-lg inline-flex p-3 bg-indigo-50 text-indigo-600 ring-4 ring-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </span>
            </div>
            <div class="mt-8">
                <h3 class="text-lg font-medium">
                    <a href="{{ route('dashboard') }}" class="focus:outline-none">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Dashboard Summary
                    </a>
                </h3>
                <p class="mt-2 text-sm text-gray-500">
                    Ringkasan eksekutif dengan key metrics dan indikator kinerja keuangan.
                </p>
                <div class="mt-4 flex items-center text-sm text-gray-500">
                    <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Quick Overview
                </div>
            </div>
        </div>

        <!-- Custom Reports -->
        <div class="relative group bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div>
                <span class="rounded-lg inline-flex p-3 bg-gray-50 text-gray-600 ring-4 ring-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
                    </svg>
                </span>
            </div>
            <div class="mt-8">
                <h3 class="text-lg font-medium">
                    <span class="text-gray-500">Custom Reports</span>
                </h3>
                <p class="mt-2 text-sm text-gray-500">
                    Fitur laporan kustom dengan filter dan parameter sesuai kebutuhan akan segera tersedia.
                </p>
                <div class="mt-4 flex items-center text-sm text-gray-400">
                    <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Coming Soon
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="mt-12 bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Quick Statistics</h3>
        </div>
        <div class="p-6">
            @php
                $totalBudget = \App\Models\BudgetCategory::sum('budget_allocation');
                $totalRealization = \App\Models\BudgetCategory::sum('total_penyerapan');
                $totalOutstanding = \App\Models\BudgetCategory::sum('tagihan_outstanding');
                $totalBills = \App\Models\Bill::count();
                $pendingBills = \App\Models\Bill::where('status', 'pending')->count();
                $sp2dBills = \App\Models\Bill::where('status', 'sp2d')->count();
            @endphp

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <dt class="text-sm font-medium text-blue-600">Total Anggaran</dt>
                    <dd class="text-lg font-semibold text-blue-900">Rp {{ number_format($totalBudget, 0, ',', '.') }}</dd>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <dt class="text-sm font-medium text-green-600">Total Realisasi</dt>
                    <dd class="text-lg font-semibold text-green-900">Rp {{ number_format($totalRealization, 0, ',', '.') }}</dd>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <dt class="text-sm font-medium text-yellow-600">Outstanding</dt>
                    <dd class="text-lg font-semibold text-yellow-900">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</dd>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <dt class="text-sm font-medium text-purple-600">Total Tagihan</dt>
                    <dd class="text-lg font-semibold text-purple-900">{{ number_format($totalBills) }}</dd>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg">
                    <dt class="text-sm font-medium text-orange-600">Pending Bills</dt>
                    <dd class="text-lg font-semibold text-orange-900">{{ number_format($pendingBills) }}</dd>
                </div>
                <div class="bg-indigo-50 p-4 rounded-lg">
                    <dt class="text-sm font-medium text-indigo-600">SP2D Bills</dt>
                    <dd class="text-lg font-semibold text-indigo-900">{{ number_format($sp2dBills) }}</dd>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
