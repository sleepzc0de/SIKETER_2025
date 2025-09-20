<!-- resources/views/budget/create.blade.php -->
@extends('layouts.app')

@section('content')
<div x-data="budgetForm()">
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
                                <span class="sr-only">Home</span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            <a href="{{ route('budget.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Data Anggaran</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-gray-500">Tambah Anggaran</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="mt-4">
                <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                    Tambah Data Anggaran
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Isi form di bawah untuk menambahkan kategori anggaran baru.
                </p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white shadow rounded-lg">
            <form method="POST" action="{{ route('budget.store') }}" class="space-y-8 divide-y divide-gray-200">
                @csrf

                <div class="space-y-8 divide-y divide-gray-200 sm:space-y-5">
                    <div class="px-6 py-5">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Informasi Dasar</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">Informasi dasar kategori anggaran.</p>
                        </div>

                        <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
                            <!-- KRO Code -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="kro_code" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    Kode KRO <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="text" name="kro_code" id="kro_code" value="{{ old('kro_code') }}" required
                                           class="max-w-lg block w-full shadow-sm focus:ring-navy-500 focus:border-navy-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md @error('kro_code') border-red-500 @enderror">
                                    @error('kro_code')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- RO Code -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="ro_code" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    Kode RO <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="text" name="ro_code" id="ro_code" value="{{ old('ro_code') }}" required
                                           class="max-w-lg block w-full shadow-sm focus:ring-navy-500 focus:border-navy-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md @error('ro_code') border-red-500 @enderror">
                                    @error('ro_code')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Initial Code -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="initial_code" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    Kode Inisial <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="text" name="initial_code" id="initial_code" value="{{ old('initial_code') }}" required
                                           class="max-w-lg block w-full shadow-sm focus:ring-navy-500 focus:border-navy-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md @error('initial_code') border-red-500 @enderror">
                                    @error('initial_code')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Account Code -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="account_code" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    Kode Akun <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="text" name="account_code" id="account_code" value="{{ old('account_code') }}" required
                                           class="max-w-lg block w-full shadow-sm focus:ring-navy-500 focus:border-navy-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md @error('account_code') border-red-500 @enderror">
                                    @error('account_code')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="description" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    Deskripsi <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <textarea name="description" id="description" rows="4" required
                                              class="max-w-lg block w-full shadow-sm focus:ring-navy-500 focus:border-navy-500 sm:text-sm border-gray-300 rounded-md @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                                    @error('description')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- PIC -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="pic" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    PIC (Person In Charge) <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="text" name="pic" id="pic" value="{{ old('pic') }}" required
                                           class="max-w-lg block w-full shadow-sm focus:ring-navy-500 focus:border-navy-500 sm:text-sm border-gray-300 rounded-md @error('pic') border-red-500 @enderror"
                                           placeholder="Nama PIC">
                                    @error('pic')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Budget Allocation -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="budget_allocation" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    Pagu Anggaran <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" name="budget_allocation" id="budget_allocation"
                                               value="{{ old('budget_allocation') }}" required min="0" step="0.01"
                                               class="pl-7 max-w-lg block w-full shadow-sm focus:ring-navy-500 focus:border-navy-500 sm:text-sm border-gray-300 rounded-md @error('budget_allocation') border-red-500 @enderror"
                                               placeholder="0.00">
                                    </div>
                                    @error('budget_allocation')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-5">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Referensi</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">Informasi referensi dan metadata.</p>
                        </div>

                        <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
                            <!-- Reference -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="reference" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    Referensi <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="text" name="reference" id="reference" value="{{ old('reference') }}" required
                                           class="max-w-lg block w-full shadow-sm focus:ring-navy-500 focus:border-navy-500 sm:text-sm border-gray-300 rounded-md @error('reference') border-red-500 @enderror">
                                    @error('reference')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Reference 2 -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="reference2" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    Referensi 2
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="text" name="reference2" id="reference2" value="{{ old('reference2') }}"
                                           class="max-w-lg block w-full shadow-sm focus:ring-navy-500 focus:border-navy-500 sm:text-sm border-gray-300 rounded-md @error('reference2') border-red-500 @enderror">
                                    @error('reference2')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Reference Output -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="reference_output" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    Referensi Output
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="text" name="reference_output" id="reference_output" value="{{ old('reference_output') }}"
                                           class="max-w-lg block w-full shadow-sm focus:ring-navy-500 focus:border-navy-500 sm:text-sm border-gray-300 rounded-md @error('reference_output') border-red-500 @enderror">
                                    @error('reference_output')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Length -->
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="length" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    Panjang <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="number" name="length" id="length" value="{{ old('length') }}" required min="1"
                                           class="max-w-lg block w-full shadow-sm focus:ring-navy-500 focus:border-navy-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md @error('length') border-red-500 @enderror">
                                    @error('length')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-3 bg-gray-50 text-right space-x-3">
                    <a href="{{ route('budget.index') }}"
                       class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500">
                        Batal
                    </a>
                    <button type="submit" x-bind:disabled="loading"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gradient-to-r from-navy-600 to-navy-700 hover:from-navy-700 hover:to-navy-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Simpan</span>
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
function budgetForm() {
    return {
        loading: false
    }
}
</script>
@endsection
