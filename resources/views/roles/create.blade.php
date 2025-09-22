@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto" x-data="roleForm()">
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
                        <a href="{{ route('admin.roles.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Manajemen Role</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="ml-4 text-sm font-medium text-gray-500">Tambah Role</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="mt-4">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Tambah Role Baru
            </h1>
        </div>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-8">
        @csrf

        <!-- Basic Info -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Role</h3>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Role (System)</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           x-model="form.name" @input="generateDisplayName"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 @error('name') border-red-500 @enderror"
                           placeholder="e.g., supervisor">
                    <p class="mt-1 text-xs text-gray-500">Nama role dalam sistem (lowercase, no spaces)</p>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="display_name" class="block text-sm font-medium text-gray-700">Nama Tampilan</label>
                    <input type="text" name="display_name" id="display_name" value="{{ old('display_name') }}" required
                           x-model="form.display_name"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 @error('display_name') border-red-500 @enderror"
                           placeholder="e.g., Supervisor">
                    <p class="mt-1 text-xs text-gray-500">Nama yang ditampilkan kepada user</p>
                    @error('display_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="description" id="description" rows="3" x-model="form.description"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 @error('description') border-red-500 @enderror"
                              placeholder="Deskripsi singkat tentang role ini...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Permissions -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Permissions</h3>
                <div class="flex space-x-2">
                    <button type="button" @click="selectAll" class="text-sm text-navy-600 hover:text-navy-500">Select All</button>
                    <span class="text-gray-300">|</span>
                    <button type="button" @click="deselectAll" class="text-sm text-navy-600 hover:text-navy-500">Deselect All</button>
                </div>
            </div>

            <div class="space-y-6">
                @foreach($permissions as $group => $groupPermissions)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-base font-medium text-gray-900 capitalize">{{ $group }}</h4>
                        <div class="flex space-x-2">
                            <button type="button" @click="selectGroup('{{ $group }}')" class="text-xs text-navy-600 hover:text-navy-500">Select All</button>
                            <span class="text-gray-300">|</span>
                            <button type="button" @click="deselectGroup('{{ $group }}')" class="text-xs text-navy-600 hover:text-navy-500">Deselect All</button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($groupPermissions as $permission)
                        <label class="relative flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                       data-group="{{ $group }}"
                                       x-model="selectedPermissions"
                                       class="focus:ring-navy-500 h-4 w-4 text-navy-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="font-medium text-gray-700">
                                    {{ Cache::get("permission_meta_{$permission->id}.display_name", ucfirst($permission->name)) }}
                                </span>
                                <p class="text-gray-500 text-xs">{{ $permission->name }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end space-x-3 pt-6 border-t">
            <a href="{{ route('admin.roles.index') }}"
               class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                Batal
            </a>
            <button type="submit"
                    class="bg-navy-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-navy-700">
                Simpan Role
            </button>
        </div>
    </form>
</div>

<script>
function roleForm() {
    return {
        form: {
            name: '{{ old('name') }}',
            display_name: '{{ old('display_name') }}',
            description: '{{ old('description') }}'
        },
        selectedPermissions: @json(old('permissions', [])),

        generateDisplayName() {
            if (!this.form.display_name || this.form.display_name === this.capitalizeFirst(this.form.name.replace(/[^a-zA-Z]/g, ''))) {
                this.form.display_name = this.capitalizeFirst(this.form.name.replace(/[^a-zA-Z]/g, ''));
            }
        },

        capitalizeFirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        },

        selectAll() {
            const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
            this.selectedPermissions = Array.from(checkboxes).map(cb => cb.value);
        },

        deselectAll() {
            this.selectedPermissions = [];
        },

        selectGroup(group) {
            const groupCheckboxes = document.querySelectorAll(`input[data-group="${group}"]`);
            const groupValues = Array.from(groupCheckboxes).map(cb => cb.value);
            this.selectedPermissions = [...new Set([...this.selectedPermissions, ...groupValues])];
        },

        deselectGroup(group) {
            const groupCheckboxes = document.querySelectorAll(`input[data-group="${group}"]`);
            const groupValues = Array.from(groupCheckboxes).map(cb => cb.value);
            this.selectedPermissions = this.selectedPermissions.filter(id => !groupValues.includes(id));
        }
    }
}
</script>
@endsection
