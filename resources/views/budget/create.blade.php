@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Tambah Data Anggaran</h1>
            <p class="mt-2 text-sm text-gray-700">Buat kategori anggaran baru dengan alokasi dana.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('budget.index') }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow rounded-lg">
        <form action="{{ route('budget.store') }}" method="POST" id="createBudgetForm">
            @csrf

            <div class="px-6 py-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Informasi Anggaran</h3>
                <p class="mt-1 text-sm text-gray-500">Masukkan informasi dasar kategori anggaran. Field referensi akan otomatis dibuat dari kombinasi kode-kode.</p>
            </div>

            <div class="px-6 py-6 space-y-6">
                <!-- Error Messages -->
                @if($errors->any())
                <div class="rounded-md bg-red-50 p-4">
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

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Kegiatan -->
                    <div>
                        <label for="kegiatan" class="block text-sm font-medium text-gray-700">Kegiatan</label>
                        <input type="text"
                               name="kegiatan"
                               id="kegiatan"
                               value="{{ old('kegiatan') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('kegiatan') border-red-300 @enderror"
                               required
                               oninput="updateDerivedFields()">
                        @error('kegiatan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- PIC -->
                    <div>
                        <label for="pic" class="block text-sm font-medium text-gray-700">PIC (Person In Charge)</label>
                        <input type="text"
                               name="pic"
                               id="pic"
                               value="{{ old('pic') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('pic') border-red-300 @enderror"
                               required>
                        @error('pic')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Kode-kode -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-4">
                    <!-- KRO Code -->
                    <div>
                        <label for="kro_code" class="block text-sm font-medium text-gray-700">Kode KRO</label>
                        <input type="text"
                               name="kro_code"
                               id="kro_code"
                               value="{{ old('kro_code') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('kro_code') border-red-300 @enderror"
                               required
                               oninput="updateDerivedFields()">
                        @error('kro_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- RO Code -->
                    <div>
                        <label for="ro_code" class="block text-sm font-medium text-gray-700">Kode RO</label>
                        <input type="text"
                               name="ro_code"
                               id="ro_code"
                               value="{{ old('ro_code') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('ro_code') border-red-300 @enderror"
                               required
                               oninput="updateDerivedFields()">
                        @error('ro_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Initial Code -->
                    <div>
                        <label for="initial_code" class="block text-sm font-medium text-gray-700">Kode Initial</label>
                        <input type="text"
                               name="initial_code"
                               id="initial_code"
                               value="{{ old('initial_code') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('initial_code') border-red-300 @enderror"
                               required
                               oninput="updateDerivedFields()">
                        @error('initial_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Account Code -->
                    <div>
                        <label for="account_code" class="block text-sm font-medium text-gray-700">Kode Akun</label>
                        <input type="text"
                               name="account_code"
                               id="account_code"
                               value="{{ old('account_code') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('account_code') border-red-300 @enderror"
                               required
                               oninput="updateDerivedFields()">
                        @error('account_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Program Kegiatan Output -->
                <div>
                    <label for="program_kegiatan_output" class="block text-sm font-medium text-gray-700">Program Kegiatan Output</label>
                    <textarea name="program_kegiatan_output"
                              id="program_kegiatan_output"
                              rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('program_kegiatan_output') border-red-300 @enderror"
                              required>{{ old('program_kegiatan_output') }}</textarea>
                    @error('program_kegiatan_output')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Auto-Generated References Section -->
                <div class="bg-gray-50 p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-900 mb-4">Referensi Otomatis (Auto-generated)</h4>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Reference (Auto-generated) -->
                        <div>
                            <label for="reference" class="block text-sm font-medium text-gray-700">
                                Referensi
                                <span class="text-xs text-gray-500">(Kegiatan + KRO + RO + Initial + Akun)</span>
                            </label>
                            <input type="text"
                                   name="reference"
                                   id="reference"
                                   value="{{ old('reference') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm"
                                   readonly>
                            <p class="mt-1 text-xs text-gray-500">Auto-generated dari kombinasi semua kode</p>
                        </div>

                        <!-- Reference 2 (Auto-generated) -->
                        <div>
                            <label for="reference2" class="block text-sm font-medium text-gray-700">
                                Referensi 2
                                <span class="text-xs text-gray-500">(Kegiatan + KRO + RO + Initial)</span>
                            </label>
                            <input type="text"
                                   name="reference2"
                                   id="reference2"
                                   value="{{ old('reference2') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm"
                                   readonly>
                            <p class="mt-1 text-xs text-gray-500">Auto-generated tanpa kode akun</p>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Reference Output (Auto-generated) -->
                        <div>
                            <label for="reference_output" class="block text-sm font-medium text-gray-700">
                                Referensi Output
                                <span class="text-xs text-gray-500">(Kegiatan + KRO + RO)</span>
                            </label>
                            <input type="text"
                                   name="reference_output"
                                   id="reference_output"
                                   value="{{ old('reference_output') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm"
                                   readonly>
                            <p class="mt-1 text-xs text-gray-500">Auto-generated dari Kegiatan + KRO + RO</p>
                        </div>

                        <!-- Length (Character count of reference) -->
                        <div>
                            <label for="length" class="block text-sm font-medium text-gray-700">
                                Panjang Karakter Referensi
                                <span class="text-xs text-gray-500">(Auto-calculated)</span>
                            </label>
                            <input type="number"
                                   name="length"
                                   id="length"
                                   value="{{ old('length', 0) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm"
                                   readonly>
                            <p class="mt-1 text-xs text-gray-500">Jumlah karakter dari referensi utama</p>
                        </div>
                    </div>
                </div>

                <!-- Budget Allocation -->
                <div>
                    <label for="budget_allocation" class="block text-sm font-medium text-gray-700">Alokasi Anggaran (Rp)</label>
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">Rp</span>
                        </div>
                        <input type="number"
                               name="budget_allocation"
                               id="budget_allocation"
                               value="{{ old('budget_allocation') }}"
                               class="block w-full pl-12 rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm @error('budget_allocation') border-red-300 @enderror"
                               min="0"
                               step="1"
                               required>
                    </div>
                    @error('budget_allocation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Info about auto-generated fields -->
                <div class="rounded-md bg-blue-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Informasi Auto-Generated Fields</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li><strong>Referensi:</strong> Gabungan Kegiatan + KRO + RO + Initial + Akun</li>
                                    <li><strong>Referensi 2:</strong> Gabungan Kegiatan + KRO + RO + Initial</li>
                                    <li><strong>Referensi Output:</strong> Gabungan Kegiatan + KRO + RO</li>
                                    <li><strong>Panjang Karakter:</strong> Jumlah karakter dari referensi utama</li>
                                </ul>
                                <p class="mt-2">Field-field ini akan otomatis diperbarui saat Anda mengisi kode-kode di atas.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end">
                <div class="flex space-x-3">
                    <a href="{{ route('budget.index') }}"
                       class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit"
                            class="inline-flex items-center rounded-md bg-navy-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-navy-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-navy-600">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createBudgetForm');

    // Initialize auto-generated fields on page load
    updateDerivedFields();

    // Form submission
    form.addEventListener('submit', function(e) {
        // Show loading state
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Menyimpan...
        `;

        // Re-enable button after timeout as fallback
        setTimeout(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }, 10000);
    });
});

function updateDerivedFields() {
    // Get input values
    const kegiatan = document.getElementById('kegiatan').value.trim();
    const kroCode = document.getElementById('kro_code').value.trim();
    const roCode = document.getElementById('ro_code').value.trim();
    const initialCode = document.getElementById('initial_code').value.trim();
    const accountCode = document.getElementById('account_code').value.trim();

    // Generate references
    const reference = kegiatan + kroCode + roCode + initialCode + accountCode;
    const reference2 = kegiatan + kroCode + roCode + initialCode;
    const referenceOutput = kegiatan + kroCode + roCode;
    const referenceLength = reference.length;

    // Update fields
    document.getElementById('reference').value = reference;
    document.getElementById('reference2').value = reference2;
    document.getElementById('reference_output').value = referenceOutput;
    document.getElementById('length').value = referenceLength;
}
</script>
@endsection
