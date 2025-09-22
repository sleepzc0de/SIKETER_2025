@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto" x-data="permissionsManager()">
    <!-- Header -->
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Manajemen Permission</h1>
            <p class="mt-2 text-sm text-gray-700">Kelola permission yang tersedia dalam sistem aplikasi.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('admin.permissions.create') }}" class="inline-flex items-center rounded-md bg-navy-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-navy-500">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Tambah Permission
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="mt-6 bg-white shadow rounded-lg p-6">
        <form method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:space-x-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700">Pencarian</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm"
                       placeholder="Cari permission...">
            </div>
            <div class="min-w-0 flex-1 sm:max-w-xs">
                <label for="group" class="block text-sm font-medium text-gray-700">Group</label>
                <select name="group" id="group" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm">
                    <option value="">Semua Group</option>
                    @foreach($groups as $group)
                        <option value="{{ $group }}" {{ request('group') == $group ? 'selected' : '' }}>{{ ucfirst($group) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="inline-flex items-center rounded-md bg-navy-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-navy-500">
                    Filter
                </button>
                <a href="{{ route('admin.permissions.index') }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Permissions Table -->
    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Permission
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Group
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Roles
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($permissions as $permission)
                            @php
                                $meta = Cache::get("permission_meta_{$permission->id}", [
                                    'display_name' => ucfirst($permission->name),
                                    'group' => 'general'
                                ]);
                            @endphp
                            <tr>
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $meta['display_name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $permission->name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst($meta['group']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $permission->roles_count }} roles
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.permissions.show', $permission->id) }}" class="text-navy-600 hover:text-navy-900">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="text-navy-600 hover:text-navy-900">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        @if($permission->roles_count == 0)
                                        <button @click="deletePermission({{ $permission->id }}, '{{ $permission->name }}')" class="text-red-600 hover:text-red-900">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center py-8">
                                        <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500">Tidak ada permission ditemukan</p>
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
    @if($permissions->hasPages())
    <div class="mt-6">
        {{ $permissions->links() }}
    </div>
    @endif

    <!-- Delete Modal -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Hapus Permission</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" x-text="'Apakah Anda yakin ingin menghapus permission ' + deletePermissionName + '?'"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="confirmDelete" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Hapus
                    </button>
                    <button @click="showDeleteModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function permissionsManager() {
    return {
        showDeleteModal: false,
        deletePermissionId: null,
        deletePermissionName: '',

        deletePermission(permissionId, permissionName) {
            this.deletePermissionId = permissionId;
            this.deletePermissionName = permissionName;
            this.showDeleteModal = true;
        },

        confirmDelete() {
            if (this.deletePermissionId) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/permissions/${this.deletePermissionId}`;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        }
    }
}
</script>
@endsection
