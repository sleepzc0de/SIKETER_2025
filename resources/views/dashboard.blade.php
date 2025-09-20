<!-- resources/views/dashboard.blade.php -->
@extends('layouts.app')

@section('content')
<div x-data="dashboardData()" x-init="initCharts()">
    <!-- Header -->
    <div class="mb-8">
        <div class="md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Dashboard Keuangan</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Selamat datang, {{ Auth::user()->name }}. Berikut adalah ringkasan aktivitas keuangan terkini.
                </p>
            </div>
            <div class="mt-4 flex md:ml-4 md:mt-0">
                <button type="button" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z" clip-rule="evenodd" />
                    </svg>
                    Export
                </button>
                <button type="button" class="ml-3 inline-flex items-center rounded-md bg-gradient-to-r from-navy-600 to-navy-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:from-navy-700 hover:to-navy-800">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    Tambah Realisasi
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Total Budget -->
        <div class="overflow-hidden rounded-lg bg-gradient-to-br from-blue-600 to-blue-700 shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-blue-100 truncate">Total Anggaran</dt>
                            <dd class="text-lg font-semibold text-white">Rp {{ number_format($totalBudget, 0, ',', '.') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Realization -->
        <div class="overflow-hidden rounded-lg bg-gradient-to-br from-emerald-600 to-emerald-700 shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H15.75c.621 0 1.125.504 1.125 1.125v.375m-13.5 0h12m-12 0v6.75C3 14.621 3.504 15.125 4.125 15.125H8.25c.621 0 1.125-.504 1.125-1.125v-1.875m-4.5 0h3" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-emerald-100 truncate">Total Realisasi</dt>
                            <dd class="text-lg font-semibold text-white">Rp {{ number_format($totalRealization, 0, ',', '.') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outstanding Bills -->
        <div class="overflow-hidden rounded-lg bg-gradient-to-br from-amber-500 to-amber-600 shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-amber-100 truncate">Tagihan Outstanding</dt>
                            <dd class="text-lg font-semibold text-white">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remaining Budget -->
        <div class="overflow-hidden rounded-lg bg-gradient-to-br from-purple-600 to-purple-700 shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-purple-100 truncate">Sisa Anggaran</dt>
                            <dd class="text-lg font-semibold text-white">Rp {{ number_format($remainingBudget, 0, ',', '.') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Budget Realization Chart -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Realisasi Anggaran Bulanan</h3>
                <div class="relative h-80">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Budget Percentage Chart -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Persentase Realisasi</h3>
                <div class="relative h-80 flex items-center justify-center">
                    <canvas id="percentageChart"></canvas>
                </div>
                <div class="mt-4 text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($realizationPercentage, 1) }}%</p>
                    <p class="text-sm text-gray-500">dari total anggaran</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Categories and Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Top Categories -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Top 5 Kategori Anggaran</h3>
                <div class="space-y-4">
                    @foreach($topCategories as $category)
                    <div class="relative">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-gray-900 truncate">{{ Str::limit($category['name'], 30) }}</span>
                            <span class="text-gray-500">{{ number_format($category['percentage'], 1) }}%</span>
                        </div>
                        <div class="mt-1 flex">
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-navy-500 to-navy-600 h-2 rounded-full transition-all duration-500"
                                     style="width: {{ min($category['percentage'], 100) }}%"></div>
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            Rp {{ number_format($category['realization'], 0, ',', '.') }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Transaksi Terbaru</h3>
                    <a href="{{ route('budget.index') }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">
                        Lihat semua
                    </a>
                </div>
                <div class="space-y-3">
                    @forelse($recentTransactions as $transaction)
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-500 flex items-center justify-center">
                                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ Str::limit($transaction->budgetCategory->description, 40) }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $transaction->month_name }} {{ $transaction->year }} • {{ $transaction->creator->name }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 text-sm font-medium text-gray-900">
                            Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Belum ada transaksi</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function dashboardData() {
    return {
        monthlyChart: null,
        percentageChart: null,

        initCharts() {
            this.createMonthlyChart();
            this.createPercentageChart();
        },

        createMonthlyChart() {
            const ctx = document.getElementById('monthlyChart').getContext('2d');
            const monthlyData = @json($monthlyRealization);

            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const data = new Array(12).fill(0);

            monthlyData.forEach(item => {
                data[item.month - 1] = item.total;
            });

            this.monthlyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Realisasi (Rp)',
                        data: data,
                        borderColor: 'rgb(30, 58, 138)',
                        backgroundColor: 'rgba(30, 58, 138, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgb(251, 191, 36)',
                        pointBorderColor: 'rgb(245, 158, 11)',
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
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    elements: {
                        point: {
                            hoverBackgroundColor: 'rgb(251, 191, 36)'
                        }
                    }
                }
            });
        },

        createPercentageChart() {
            const ctx = document.getElementById('percentageChart').getContext('2d');
            const percentage = {{ $realizationPercentage }};

            this.percentageChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [percentage, 100 - percentage],
                        backgroundColor: [
                            'rgb(251, 191, 36)',
                            'rgb(229, 231, 235)'
                        ],
                        borderWidth: 0,
                        cutout: '75%'
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
                                    return context.parsed + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
    }
}
</script>
@endsection
