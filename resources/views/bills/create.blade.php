@extends('layouts.app')

@section('content')
<div x-data="billForm()">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <nav class="flex" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-4">
                    <li>
                        <div>
                            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-500">
                                <svg class="flex-shrink-0 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                                </svg>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            <a href="{{ route('bills.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Tagihan</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-gray-500">Tambah Tagihan</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="mt-4">
                <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                    Tambah Tagihan Baru
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Input tagihan untuk realisasi anggaran. Status awal akan menjadi "Pending" hingga disetujui menjadi SP2D.
                </p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white shadow rounded-lg">
            <form method="POST" action="{{ route('bills.store') }}" class="space-y-8 divide-y divide-gray-200">
                @csrf

                <div class="space-y-8 divide-y divide-gray-200 sm:space-y-5">
                    <div class="px-6 py-5">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Informasi Tagihan</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">Detail informasi tagihan yang akan diinput.</p>
                        </div>

                        <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
                            <!-- Budget Category -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="budget_category_id" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    Kategori Anggaran <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <select name="budget_category_id" id="budget_category_id" required
                                            class="max-w-lg block w-full shadow-sm focus:ring-navy-500 focus:border-navy-500 sm:text-sm border-gray-300 rounded-md @error('budget_category_id') border-red-500 @enderror"
                                            x-model="selectedBudget" @change="updateBudgetInfo()">
                                        <option value="">Pilih Kategori Anggaran</option>
                                        @foreach($budgetCategories as $budget)
                                            <option value="{{ $budget->id }}" data-code="{{ $budget->full_code }}" data-description="{{ $budget->program_kegiatan_output }}" {{ old('budget_category_id') == $budget->id ? 'selected' : '' }}>
                                                {{ $budget->full_code }} - {{ Str::limit($budget->program_kegiatan_output, 60) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('budget_category_id')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <div x-show="selectedBudget" class="mt-2 p-3 bg-gray-50 rounded-md">
                                        <p class="text-sm text-gray-600" x-text="budgetDescription"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Bill Number -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="bill_number" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    Nomor Tagihan <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="text" name="bill_number" id="bill_number" value="{{ old('bill_number') }}" required
                                           class="max-w-lg block w-full shadow-sm focus:ring-navy-500 focus:border-navy-500 sm:text-sm border-gray-300 rounded-md @error('bill_number') border-red-500 @enderror"
                                           placeholder="Contoh: TGH-2025-001">
                                    @error('bill_number')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Amount -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="amount" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    Jumlah Tagihan <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" name="amount" id="amount"
                                               value="{{ old('amount') }}" required min="0" step="0.01"
                                               class="pl-8 max-w-lg block w-full shadow-sm focus:ring-navy-500 focus:border-navy-500 sm:text-sm border-gray-300 rounded-md @error('amount') border-red-500 @enderror"
                                               placeholder="0.00">
                                    </div>
                                    @error('amount')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Period -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    Periode <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="month" class="block text-sm font-medium text-gray-700">Bulan</label>
                                            <select name="month" id="month" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm">
                                                <option value="">Pilih Bulan</option>
                                                @for($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}" {{ old('month') == $i ? 'selected' : '' }}>
                                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                                    </option>
                                                @endfor
                                            </select>
                                            @error('month')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label for="year" class="block text-sm font-medium text-gray-700">Tahun</label>
                                            <select name="year" id="year" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm">
                                                @for($y = date('Y'); $y >= 2020; $y--)
                                                    <option value="{{ $y }}" {{ old('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                                @endfor
                                            </select>
                                            @error('year')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bill Date -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="bill_date" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    Tanggal Tagihan <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="date" name="bill_date" id="bill_date" value="{{ old('bill_date', date('Y-m-d')) }}" required
                                           class="max-w-lg block w-full shadow-sm focus:ring-navy-500 focus:border-navy-500 sm:text-sm border-gray-300 rounded-md @error('bill_date') border-red-500 @enderror">
                                    @error('bill_date')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="description" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    Deskripsi Tagihan <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <textarea name="description" id="description" rows="4" required
                                              class="max-w-lg block w-full shadow-sm focus:ring-navy-500 focus:border-navy-500 sm:text-sm border-gray-300 rounded-md @error('description') border-red-500 @enderror"
                                              placeholder="Masukkan deskripsi detail tagihan...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-3 bg-gray-50 text-right space-x-3">
                    <a href="{{ route('bills.index') }}"
                       class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500">
                        Batal
                    </a>
                    <button type="submit" x-bind:disabled="loading"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gradient-to-r from-navy-600 to-navy-700 hover:from-navy-700 hover:to-navy-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Simpan Tagihan</span>
                        <span x-show="loading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function billForm() {
    return {
        loading: false,
        selectedBudget: '{{ old("budget_category_id") }}',
        budgetDescription: '',

        updateBudgetInfo() {
            const select = document.getElementById('budget_category_id');
            const selectedOption = select.options[select.selectedIndex];

            if (selectedOption && selectedOption.dataset.description) {
                this.budgetDescription = selectedOption.dataset.description;
            } else {
                this.budgetDescription = '';
            }
        },

        init() {
            this.updateBudgetInfo();
        }
    }
}
</script>
@endsection
