@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto" x-data="permissionEditForm()">
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
                        <a href="{{ route('admin.permissions.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Manajemen Permission</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="ml-4 text-sm font-medium text-gray-500">Edit Permission</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="mt-4">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Edit Permission: {{ $permissionMeta['display_name'] }}
            </h1>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('admin.permissions.update', $permission->id) }}" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Permission (System)</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $permission->name) }}" required
                           x-model="form.name"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 @error('name') border-red-500 @enderror"
                           placeholder="e.g., view users">
                    <p class="mt-1 text-xs text-gray-500">Nama permission dalam sistem (lowercase dengan spasi)</p>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="display_name" class="block text-sm font-medium text-gray-700">Nama Tampilan</label>
                    <input type="text" name="display_name" id="display_name" value="{{ old('display_name', $permissionMeta['display_name']) }}" required
                           x-model="form.display_name"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 @error('display_name') border-red-500 @enderror"
                           placeholder="e.g., Lihat User">
                    <p class="mt-1 text-xs text-gray-500">Nama yang ditampilkan kepada user</p>
                    @error('display_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="group" class="block text-sm font-medium text-gray-700">Group</label>
                    <select name="group" id="group" required x-model="form.group"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 @error('group') border-red-500 @enderror">
                        <option value="">Pilih Group</option>
                        @foreach($groups as $group)
                            <option value="{{ $group }}" {{ old('group', $permissionMeta['group']) == $group ? 'selected' : '' }}>{{ ucfirst($group) }}</option>
                        @endforeach
                        <option value="new">+ Buat Group Baru</option>
                    </select>
                    @error('group')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="form.group === 'new'">
                    <label for="new_group" class="block text-sm font-medium text-gray-700">Nama Group Baru</label>
                    <input type="text" x-model="form.newGroup" @input="updateGroupField"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500"
                           placeholder="e.g., inventory">
                    <input type="hidden" name="group" x-bind:value="form.group === 'new' ? form.newGroup : form.group">
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="description" id="description" rows="3" x-model="form.description"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 @error('description') border-red-500 @enderror"
                              placeholder="Deskripsi singkat tentang permission ini...">{{ old('description', $permissionMeta['description']) }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t">
                <a href="{{ route('admin.permissions.show', $permission->id) }}"
                   class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                        class="bg-navy-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-navy-700">
                    Update Permission
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function permissionEditForm() {
    return {
        form: {
            name: '{{ old('name', $permission->name) }}',
            display_name: '{{ old('display_name', $permissionMeta['display_name']) }}',
            description: '{{ old('description', $permissionMeta['description']) }}',
            group: '{{ old('group', $permissionMeta['group']) }}',
            newGroup: ''
        },

        updateGroupField() {
            // This ensures the hidden input gets the new group value
        }
    }
}
</script>
@endsection
