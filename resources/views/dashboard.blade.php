@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto" x-data="dashboardManager()" x-init="init()">
    <!-- Header with Controls -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">
                Dashboard Anggaran
                @if(auth()->user()->isPPK())
                    <span class="text-base font-normal text-gray-500">- {{ auth()->user()->name }}</span>
                @endif
            </h1>
            <p class="mt-2 text-sm text-gray-700">
                @if(auth()->user()->isPPK())
                    Monitor realisasi anggaran Anda secara real-time.
                @else
                    Monitor realisasi anggaran dan kinerja keuangan secara real-time.
                @endif
            </p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <div class="flex items-center space-x-4">
                <!-- Year Filter -->
                <select @change="changeYear($event.target.value)" class="rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 text-sm">
                    @foreach($availableYears as $availableYear)
                        <option value="{{ $availableYear }}" {{ $year == $availableYear ? 'selected' : '' }}>{{ $availableYear }}</option>
                    @endforeach
                </select>

                <!-- Auto Refresh Toggle -->
                <button @click="toggleAutoRefresh()"
                        :class="autoRefresh ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-600 hover:bg-gray-700'"
                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm">
                    <svg class="h-4 w-4 mr-1.5" :class="autoRefresh ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span x-text="autoRefresh ? 'Auto' : 'Manual'"></span>
                </button>

                <!-- Manual Refresh -->
                <button @click="refreshData()"
                        :disabled="loading"
                        class="inline-flex items-center rounded-md bg-navy-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-navy-500 disabled:opacity-50">
                    <svg class="h-4 w-4 mr-1.5" :class="loading ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>

                <!-- Export Button (if user has permission) -->
                @if(isset($userPermissions['canExportData']) && $userPermissions['canExportData'])
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export
                        <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export Budget Data</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export Bills Data</a>
                        <a href="#" @click="exportDashboard()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export Dashboard Report</a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- User Role Badge -->
    <div class="mb-6">
        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
            @if(auth()->user()->isAdmin()) bg-red-100 text-red-800
            @elseif(auth()->user()->isPimpinan()) bg-purple-100 text-purple-800
            @elseif(auth()->user()->isPPK()) bg-blue-100 text-blue-800
            @else bg-gray-100 text-gray-800
            @endif">
            @if(auth()->user()->isAdmin()) Administrator
            @elseif(auth()->user()->isPimpinan()) Pimpinan
            @elseif(auth()->user()->isPPK()) PPK
            @else {{ ucfirst(auth()->user()->role) }}
            @endif
        </span>
    </div>

    <!-- Last Updated Info -->
    <div class="mb-6 flex items-center justify-between">
        <div class="text-sm text-gray-500">
            <span>Terakhir diperbarui: </span>
            <span x-text="lastUpdated" class="font-medium"></span>
        </div>
        <div class="flex items-center space-x-2">
            <div class="h-2 w-2 rounded-full" :class="isOnline ? 'bg-green-500' : 'bg-red-500'"></div>
            <span class="text-sm" :class="isOnline ? 'text-green-600' : 'text-red-600'" x-text="isOnline ? 'Online' : 'Offline'"></span>
        </div>
    </div>

    <!-- Alerts Section -->
    <div x-show="alerts.length > 0" class="mb-8">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        @if(auth()->user()->isPPK()) Notifikasi untuk Anda
                        @else Notifikasi Penting
                        @endif
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <template x-for="alert in alerts" :key="alert.title">
                                <li>
                                    <a :href="alert.action_url" x-text="alert.message" class="hover:underline"></a>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Statistics Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Total Budget -->
        <div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-blue-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                @if(auth()->user()->isPPK()) Total Anggaran Anda
                                @else Total Anggaran
                                @endif
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900" x-text="formatCurrency(dashboardData.totalBudget)"></div>
                                <div class="ml-2 flex items-baseline text-sm">
                                    <span x-text="dashboardData.totalCategories + ' kategori'" class="text-gray-500"></span>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Realization -->
        <div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-green-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Realisasi</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900" x-text="formatCurrency(dashboardData.totalRealization)"></div>
                                <div class="ml-2 flex items-baseline text-sm">
                                    <span x-text="dashboardData.realizationPercentage.toFixed(1) + '%'"
                                          :class="dashboardData.realizationPercentage >= 75 ? 'text-green-600' : dashboardData.realizationPercentage >= 50 ? 'text-yellow-600' : 'text-red-600'"
                                          class="font-medium"></span>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full transition-all duration-500"
                             :style="`width: ${Math.min(dashboardData.realizationPercentage, 100)}%`"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outstanding Bills -->
        <div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-yellow-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Outstanding</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900" x-text="formatCurrency(dashboardData.totalOutstanding)"></div>
                                <div class="ml-2 flex items-baseline text-sm">
                                    <span x-text="dashboardData.outstandingPercentage.toFixed(1) + '%'" class="text-yellow-600 font-medium"></span>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remaining Budget -->
        <div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-purple-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Sisa Anggaran</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900" x-text="formatCurrency(dashboardData.remainingBudget)"></div>
                                <div class="ml-2 flex items-baseline text-sm">
                                    <span x-text="((dashboardData.remainingBudget / dashboardData.totalBudget) * 100).toFixed(1) + '%'" class="text-purple-600 font-medium"></span>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions (Based on User Permissions) -->
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @if(isset($userPermissions['canManageBudget']) && $userPermissions['canManageBudget'])
        <a href="{{ route('budget.index') }}" class="group relative rounded-lg p-6 bg-white shadow hover:shadow-lg transition-shadow">
            <div>
                <span class="rounded-lg inline-flex p-3 bg-blue-50 text-blue-600 ring-4 ring-white group-hover:bg-blue-100 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </span>
            </div>
            <div class="mt-8">
                <h3 class="text-lg font-medium">Kelola Anggaran</h3>
                <p class="mt-2 text-sm text-gray-500">Tambah, edit, dan monitor kategori anggaran</p>
            </div>
        </a>
        @endif

        @if(isset($userPermissions['canInputBills']) && $userPermissions['canInputBills'])
        <a href="#" class="group relative rounded-lg p-6 bg-white shadow hover:shadow-lg transition-shadow">
            <div>
                <span class="rounded-lg inline-flex p-3 bg-green-50 text-green-600 ring-4 ring-white group-hover:bg-green-100 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </span>
            </div>
            <div class="mt-8">
                <h3 class="text-lg font-medium">Buat Tagihan Baru</h3>
                <p class="mt-2 text-sm text-gray-500">Input tagihan untuk realisasi anggaran</p>
            </div>
        </a>
        @endif

        @if(isset($userPermissions['canApprove']) && $userPermissions['canApprove'])
        <a href="#" class="group relative rounded-lg p-6 bg-white shadow hover:shadow-lg transition-shadow">
            <div>
                <span class="rounded-lg inline-flex p-3 bg-yellow-50 text-yellow-600 ring-4 ring-white group-hover:bg-yellow-100 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-8">
                <h3 class="text-lg font-medium">Persetujuan Tagihan</h3>
                <p class="mt-2 text-sm text-gray-500">Review dan setujui tagihan pending</p>
            </div>
        </a>
        @endif

        <a href="{{ route('reports.index') }}" class="group relative rounded-lg p-6 bg-white shadow hover:shadow-lg transition-shadow">
            <div>
                <span class="rounded-lg inline-flex p-3 bg-purple-50 text-purple-600 ring-4 ring-white group-hover:bg-purple-100 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a4 4 0 01-4-4V5a4 4 0 014-4h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a4 4 0 01-4 4z" />
                    </svg>
                </span>
            </div>
            <div class="mt-8">
                <h3 class="text-lg font-medium">Laporan</h3>
                <p class="mt-2 text-sm text-gray-500">Lihat berbagai laporan dan analisis</p>
            </div>
        </a>
    </div>

    <!-- KPI Section -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5 mb-8">
        <div class="bg-white p-4 rounded-lg shadow border">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600" x-text="formatPercentage(kpi.budget_efficiency)"></div>
                <div class="text-sm text-gray-500">Efisiensi Anggaran</div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border">
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600" x-text="formatPercentage(kpi.approval_rate)"></div>
                <div class="text-sm text-gray-500">Tingkat Persetujuan</div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border">
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600" x-text="formatTime(kpi.avg_processing_time)"></div>
                <div class="text-sm text-gray-500">Rata-rata Proses</div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border">
            <div class="text-center">
                <div class="text-2xl font-bold text-indigo-600" x-text="formatPercentage(kpi.categories_on_track)"></div>
                <div class="text-sm text-gray-500">On Track</div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border">
            <div class="text-center">
                <div class="text-2xl font-bold" :class="kpi.monthly_growth >= 0 ? 'text-green-600' : 'text-red-600'"
                     x-text="formatGrowth(kpi.monthly_growth)"></div>
                <div class="text-sm text-gray-500">Growth Bulanan</div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Monthly Realization Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Realisasi Bulanan {{ $year }}</h3>
                <div class="flex items-center space-x-2">
                    <div class="h-3 w-3 bg-blue-500 rounded-full"></div>
                    <span class="text-sm text-gray-500">Realisasi</span>
                </div>
            </div>
            <div class="relative h-80">
                <canvas id="monthlyRealizationChart"></canvas>
            </div>
        </div>

        <!-- Bills Status Distribution -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Status Tagihan</h3>
                <div class="text-sm text-gray-500" x-text="getBillsTotal()"></div>
            </div>
            <div class="relative h-80">
                <canvas id="billsStatusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Performance Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Top Performing Categories -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    @if(auth()->user()->isPPK()) Kategori Anggaran Anda Terbaik
                    @else Top Performing Categories
                    @endif
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <template x-for="(category, index) in topCategories" :key="category.id">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900" x-text="category.full_code"></div>
                                <div class="text-xs text-gray-500 truncate" x-text="category.name"></div>
                                <div class="text-xs text-gray-400" x-text="'PIC: ' + category.pic"></div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-green-600" x-text="formatPercentage(category.percentage)"></div>
                                <div class="text-xs text-gray-500" x-text="formatCurrency(category.realization, true)"></div>
                            </div>
                        </div>
                    </template>
                    <div x-show="topCategories.length === 0" class="text-center py-4 text-gray-500">
                        Tidak ada data kategori tersedia
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Performing Categories -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    @if(auth()->user()->isPPK()) Kategori Perlu Perhatian
                    @else Categories Need Attention
                    @endif
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <template x-for="(category, index) in bottomCategories" :key="category.id">
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900" x-text="category.full_code"></div>
                                <div class="text-xs text-gray-500 truncate" x-text="category.name"></div>
                                <div class="text-xs text-gray-400" x-text="'PIC: ' + category.pic"></div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-red-600" x-text="formatPercentage(category.percentage)"></div>
                                <div class="text-xs text-gray-500" x-text="formatCurrency(category.realization, true)"></div>
                            </div>
                        </div>
                    </template>
                    <div x-show="bottomCategories.length === 0" class="text-center py-4 text-gray-500">
                        Tidak ada data kategori tersedia
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PIC Performance (Only for Admin/Pimpinan) -->
    @if(auth()->user()->isAdmin() || auth()->user()->isPimpinan())
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
           <h3 class="text-lg font-medium text-gray-900">Performance by PIC</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIC</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categories</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Budget</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Realization</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Efficiency Score</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="pic in picPerformance" :key="pic.pic">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="pic.pic"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="pic.categories_count"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatCurrency(pic.total_budget, true)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatCurrency(pic.total_realization, true)"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="h-2 rounded-full transition-all duration-300"
                                             :class="getEfficiencyColor(pic.efficiency_score)"
                                             :style="`width: ${Math.min(pic.efficiency_score || 0, 100)}%`"></div>
                                    </div>
                                    <span class="text-sm" x-text="formatPercentage(pic.efficiency_score)"></span>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="picPerformance.length === 0">
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada data PIC tersedia</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Recent Activities -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                @if(auth()->user()->isPPK()) Aktivitas Anggaran Anda
                @else Recent Activities
                @endif
            </h3>
        </div>
        <div class="p-6">
            <div class="flow-root">
                <ul class="-mb-8">
                    <template x-for="(activity, index) in recentActivities" :key="activity.title + index">
                        <li>
                            <div class="relative pb-8" x-show="index < recentActivities.length - 1">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                            </div>
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                          :class="activity.type === 'bill_created' ? 'bg-blue-500' : 'bg-green-500'">
                                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  :d="activity.type === 'bill_created' ? 'M12 6v6m0 0v6m0-6h6m-6 0H6' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'" />
                                        </svg>
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                    <div>
                                        <p class="text-sm text-gray-900" x-text="activity.title"></p>
                                        <p class="text-sm text-gray-500" x-text="activity.description"></p>
                                        <p class="text-xs text-gray-400" x-text="'Rp ' + formatNumber(activity.amount)"></p>
                                    </div>
                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                        <time x-text="formatRelativeTime(activity.time)"></time>
                                        <p class="text-xs" x-text="activity.user"></p>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </template>
                    <li x-show="recentActivities.length === 0">
                        <div class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-2">Belum ada aktivitas terbaru</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function dashboardManager() {
    return {
        // Data properties with safe defaults
        dashboardData: {
            totalBudget: 0,
            totalRealization: 0,
            totalOutstanding: 0,
            remainingBudget: 0,
            realizationPercentage: 0,
            outstandingPercentage: 0,
            realizationGrowth: 0,
            totalCategories: 0,
            avgRealizationPerCategory: 0,
            ...(window.dashboardDataFromServer || {})
        },

        monthlyRealization: window.monthlyRealizationFromServer || [],
        topCategories: window.topCategoriesFromServer || [],
        bottomCategories: window.bottomCategoriesFromServer || [],
        recentActivities: window.recentActivitiesFromServer || [],

        billsStatus: {
            pending: { count: 0, amount: 0, color: '#f59e0b' },
            sp2d: { count: 0, amount: 0, color: '#10b981' },
            cancelled: { count: 0, amount: 0, color: '#ef4444' },
            ...(window.billsStatusFromServer || {})
        },

        picPerformance: window.picPerformanceFromServer || [],

        kpi: {
            budget_efficiency: 0,
            approval_rate: 0,
            avg_processing_time: 0,
            categories_on_track: 0,
            monthly_growth: 0,
            ...(window.kpiFromServer || {})
        },

        alerts: window.alertsFromServer || [],

        userPermissions: {
            canManageBudget: false,
            canInputBills: false,
            canApprove: false,
            canExportData: false,
            canManageUsers: false,
            ...(window.userPermissionsFromServer || {})
        },

        // Control properties
        autoRefresh: false,
        loading: false,
        isOnline: navigator.onLine,
        lastUpdated: new Date().toLocaleString('id-ID'),
        refreshInterval: null,
        currentYear: {{ $year ?? date('Y') }},

        // Charts
        monthlyChart: null,
        billsChart: null,

        init() {
            console.log('Dashboard initializing...');
            this.loadServerData();
            this.initializeCharts();
            this.setupOnlineDetection();

            // Auto-start refresh for PPK users
            @if(auth()->user()->isPPK())
                this.autoRefresh = true;
                this.startAutoRefresh();
            @endif
        },

        loadServerData() {
            try {
                // Load data from PHP backend
                this.dashboardData = @json($dashboardData ?? []);
                this.monthlyRealization = @json($monthlyRealization ?? []);
                this.topCategories = @json($topCategories ?? []);
                this.bottomCategories = @json($bottomCategories ?? []);
                this.recentActivities = @json($recentActivities ?? []);
                this.billsStatus = @json($billsStatus ?? []);
                this.picPerformance = @json($picPerformance ?? []);
                this.kpi = @json($kpi ?? []);
                this.alerts = @json($alerts ?? []);
                this.userPermissions = @json($userPermissions ?? []);

                console.log('Server data loaded successfully');
            } catch (error) {
                console.error('Error loading server data:', error);
            }
        },

        initializeCharts() {
            this.$nextTick(() => {
                try {
                    this.createMonthlyRealizationChart();
                    this.createBillsStatusChart();
                    console.log('Charts initialized successfully');
                } catch (error) {
                    console.error('Error initializing charts:', error);
                }
            });
        },

        createMonthlyRealizationChart() {
            const canvas = document.getElementById('monthlyRealizationChart');
            if (!canvas) {
                console.warn('Monthly realization chart canvas not found');
                return;
            }

            try {
                const ctx = canvas.getContext('2d');

                this.monthlyChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: this.monthlyRealization.map(item => item.month_name || 'N/A'),
                        datasets: [{
                            label: 'Realisasi (Rp)',
                            data: this.monthlyRealization.map(item => item.realization || 0),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            pointBorderColor: 'white',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Realisasi: Rp ' + (context.parsed.y || 0).toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + (value || 0).toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            } catch (error) {
                console.error('Error creating monthly realization chart:', error);
            }
        },

        createBillsStatusChart() {
            const canvas = document.getElementById('billsStatusChart');
            if (!canvas) {
                console.warn('Bills status chart canvas not found');
                return;
            }

            try {
                const ctx = canvas.getContext('2d');

                this.billsChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pending', 'SP2D', 'Cancelled'],
                        datasets: [{
                            data: [
                                this.billsStatus.pending?.count || 0,
                                this.billsStatus.sp2d?.count || 0,
                                this.billsStatus.cancelled?.count || 0
                            ],
                            backgroundColor: [
                                this.billsStatus.pending?.color || '#f59e0b',
                                this.billsStatus.sp2d?.color || '#10b981',
                                this.billsStatus.cancelled?.color || '#ef4444'
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((a, b) => (a || 0) + (b || 0), 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error creating bills status chart:', error);
            }
        },

        updateCharts() {
            try {
                if (this.monthlyChart) {
                    this.monthlyChart.data.datasets[0].data = this.monthlyRealization.map(item => item.realization || 0);
                    this.monthlyChart.update('none');
                }

                if (this.billsChart) {
                    this.billsChart.data.datasets[0].data = [
                        this.billsStatus.pending?.count || 0,
                        this.billsStatus.sp2d?.count || 0,
                        this.billsStatus.cancelled?.count || 0
                    ];
                    this.billsChart.update('none');
                }
            } catch (error) {
                console.error('Error updating charts:', error);
            }
        },

        async refreshData() {
            if (this.loading) return;

            this.loading = true;

            try {
                const response = await fetch(`/dashboard/realtime-data?year=${this.currentYear}`);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.error) {
                    throw new Error(data.message || 'Unknown error');
                }

                // Update data with safe fallbacks
                this.dashboardData = data.summary || this.dashboardData;
                this.monthlyRealization = data.monthly_realization || this.monthlyRealization;
                this.billsStatus = data.bills_status || this.billsStatus;
                this.alerts = data.alerts || this.alerts;
                this.kpi = data.kpi || this.kpi;

                if (data.last_updated) {
                    this.lastUpdated = new Date(data.last_updated).toLocaleString('id-ID');
                }

                // Update charts
                this.updateCharts();

                this.isOnline = true;

                // Show success notification for manual refresh
                if (!this.autoRefresh) {
                    this.showNotification('Data berhasil diperbarui', 'success');
                }
            } catch (error) {
                console.error('Failed to refresh data:', error);
                this.isOnline = false;
                this.showNotification('Gagal memperbarui data: ' + error.message, 'error');
            } finally {
                this.loading = false;
            }
        },

        toggleAutoRefresh() {
            this.autoRefresh = !this.autoRefresh;

            if (this.autoRefresh) {
                this.startAutoRefresh();
                this.showNotification('Auto-refresh diaktifkan', 'info');
            } else {
                this.stopAutoRefresh();
                this.showNotification('Auto-refresh dinonaktifkan', 'info');
            }
        },

        startAutoRefresh() {
            if (this.autoRefresh && !this.refreshInterval) {
                this.refreshInterval = setInterval(() => {
                    this.refreshData();
                }, 30000);
            }
        },

        stopAutoRefresh() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
                this.refreshInterval = null;
            }
        },

        changeYear(year) {
            this.currentYear = parseInt(year) || new Date().getFullYear();
            this.showNotification('Mengubah tahun ke ' + this.currentYear + '...', 'info');
            window.location.href = `?year=${this.currentYear}`;
        },

        setupOnlineDetection() {
            window.addEventListener('online', () => {
                this.isOnline = true;
                this.showNotification('Koneksi internet tersambung kembali', 'success');
                if (this.autoRefresh) {
                    this.refreshData();
                }
            });

            window.addEventListener('offline', () => {
                this.isOnline = false;
                this.stopAutoRefresh();
                this.showNotification('Koneksi internet terputus', 'warning');
            });
        },

        exportDashboard() {
            try {
                const exportUrl = `/dashboard/export?year=${this.currentYear}&format=pdf`;
                window.open(exportUrl, '_blank');
                this.showNotification('Memulai export dashboard...', 'info');
            } catch (error) {
                console.error('Export error:', error);
                this.showNotification('Gagal export dashboard', 'error');
            }
        },

        showNotification(message, type = 'info') {
            try {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transition-all duration-300 ${
                    type === 'success' ? 'bg-green-500 text-white' :
                    type === 'error' ? 'bg-red-500 text-white' :
                    type === 'warning' ? 'bg-yellow-500 text-white' :
                    'bg-blue-500 text-white'
                }`;

                notification.innerHTML = `
                    <div class="flex items-center">
                        <span class="flex-1">${message}</span>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                `;

                document.body.appendChild(notification);

                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.style.opacity = '0';
                        notification.style.transform = 'translateX(100%)';
                        setTimeout(() => {
                            if (notification.parentNode) {
                                notification.parentNode.removeChild(notification);
                            }
                        }, 300);
                    }
                }, 4000);
            } catch (error) {
                console.error('Notification error:', error);
            }
        },

        // Helper formatting functions
        formatCurrency(amount, short = false) {
            try {
                if (!amount || isNaN(amount)) return 'Rp 0';

                const numAmount = Number(amount);

                if (short && numAmount >= 1000000000) {
                    return 'Rp ' + (numAmount / 1000000000).toFixed(1) + 'M';
                } else if (short && numAmount >= 1000000) {
                    return 'Rp ' + (numAmount / 1000000).toFixed(1) + 'Jt';
                } else if (short && numAmount >= 1000) {
                    return 'Rp ' + (numAmount / 1000).toFixed(1) + 'K';
                }
                return 'Rp ' + numAmount.toLocaleString('id-ID');
            } catch (error) {
                console.error('Currency formatting error:', error);
                return 'Rp 0';
            }
        },

        formatNumber(amount) {
            try {
                if (!amount || isNaN(amount)) return '0';
                return Number(amount).toLocaleString('id-ID');
            } catch (error) {
                console.error('Number formatting error:', error);
                return '0';
            }
        },

        formatPercentage(value) {
            try {
                if (!value || isNaN(value)) return '0%';
                return Number(value).toFixed(1) + '%';
            } catch (error) {
                return '0%';
            }
        },

        formatTime(hours) {
            try {
                if (!hours || isNaN(hours)) return '0h';
                return Number(hours).toFixed(1) + 'h';
            } catch (error) {
                return '0h';
            }
        },

        formatGrowth(value) {
            try {
                if (!value || isNaN(value)) return '0%';
                const num = Number(value);
                return (num >= 0 ? '+' : '') + num.toFixed(1) + '%';
            } catch (error) {
                return '0%';
            }
        },

        formatRelativeTime(dateString) {
            if (!dateString) return 'Unknown';

            try {
                const date = new Date(dateString);
                const now = new Date();
                const diff = now - date;

                const minutes = Math.floor(diff / 60000);
                const hours = Math.floor(diff / 3600000);
                const days = Math.floor(diff / 86400000);

                if (minutes < 1) return 'Baru saja';
                if (minutes < 60) return `${minutes} menit lalu`;
                if (hours < 24) return `${hours} jam lalu`;
                return `${days} hari lalu`;
            } catch (error) {
                console.error('Time formatting error:', error);
                return 'Unknown';
            }
        },

        getEfficiencyColor(score) {
            try {
                const num = Number(score) || 0;
                if (num >= 75) return 'bg-green-500';
                if (num >= 50) return 'bg-yellow-500';
                return 'bg-red-500';
            } catch (error) {
                return 'bg-gray-500';
            }
        },

        getBillsTotal() {
            try {
                const total = (this.billsStatus.pending?.count || 0) +
                             (this.billsStatus.sp2d?.count || 0) +
                             (this.billsStatus.cancelled?.count || 0);
                return 'Total: ' + total;
            } catch (error) {
                return 'Total: 0';
            }
        },

        // Cleanup function
        destroy() {
            try {
                this.stopAutoRefresh();
                if (this.monthlyChart) {
                    this.monthlyChart.destroy();
                }
                if (this.billsChart) {
                    this.billsChart.destroy();
                }
            } catch (error) {
                console.error('Cleanup error:', error);
            }
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard DOM loaded');
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (window.dashboardInstance && typeof window.dashboardInstance.destroy === 'function') {
        window.dashboardInstance.destroy();
    }
});
</script>
@endsection
