@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto" x-data="rolesManager()">
    <!-- Header -->
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Manajemen Role</h1>
            <p class="mt-2 text-sm text-gray-700">Kelola role dan permission dalam sistem aplikasi.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none space-x-3">
            <button @click="syncPermissions" class="inline-flex items-center rounded-md bg-yellow-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-500">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Sync Default
            </button>
            <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center rounded-md bg-navy-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-navy-500">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Tambah Role
            </a>
        </div>
    </div>

    <!-- Search -->
    <div class="mt-6 bg-white shadow rounded-lg p-6">
        <form method="GET" class="flex items-end space-x-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700">Pencarian</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm"
                       placeholder="Cari role...">
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="inline-flex items-center rounded-md bg-navy-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-navy-500">
                    Cari
                </button>
                <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Roles Grid -->
    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($roles as $role)
        <div class="relative group bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div>
                <span class="rounded-lg inline-flex p-3 bg-navy-50 text-navy-600 ring-4 ring-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-8">
                <h3 class="text-lg font-medium">
                    <a href="{{ route('admin.roles.show', $role->id) }}" class="focus:outline-none">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        {{ Cache::get("role_meta_{$role->id}.display_name", ucfirst($role->name)) }}
                    </a>
                </h3>
                <p class="mt-2 text-sm text-gray-500">
                    {{ Cache::get("role_meta_{$role->id}.description", "Role {$role->name}") }}
                </p>
                <div class="mt-4 flex items-center justify-between">
                    <div class="flex space-x-4 text-sm text-gray-500">
                        <span>{{ $role->users_count }} users</span>
                        <span>{{ $role->permissions_count }} permissions</span>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="text-navy-600 hover:text-navy-900">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </a>
                        @if($role->users_count == 0)
                        <button @click="deleteRole({{ $role->id }}, '{{ $role->name }}')" class="text-red-600 hover:text-red-900">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada role</h3>
            <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat role baru.</p>
            <div class="mt-6">
                <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center rounded-md bg-navy-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-navy-500">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tambah Role
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($roles->hasPages())
    <div class="mt-8">
        {{ $roles->links() }}
    </div>
    @endif

    <!-- Delete Modal -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Hapus Role
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" x-text="'Apakah Anda yakin ingin menghapus role ' + deleteRoleName + '? Tindakan ini tidak dapat dibatalkan.'"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="confirmDelete" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Hapus
                    </button>
                    <button @click="showDeleteModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function rolesManager() {
    return {
        showDeleteModal: false,
        deleteRoleId: null,
        deleteRoleName: '',

        deleteRole(roleId, roleName) {
            this.deleteRoleId = roleId;
            this.deleteRoleName = roleName;
            this.showDeleteModal = true;
        },

        confirmDelete() {
            if (this.deleteRoleId) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/roles/${this.deleteRoleId}`;

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
        },

        syncPermissions() {
            if (confirm('Apakah Anda yakin ingin melakukan sinkronisasi default permissions? Ini akan menambahkan permission yang belum ada.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.sync-permissions") }}';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                form.appendChild(csrfToken);
                document.body.appendChild(form);
                form.submit();
            }
        }
    }
}
</script>
@endsection
