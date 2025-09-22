@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto" x-data="userRolesManager()">
    <!-- Header -->
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Assign Role ke User</h1>
            <p class="mt-2 text-sm text-gray-700">Kelola role yang diberikan kepada setiap user dalam sistem.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <button @click="showBulkAssignModal = true" class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Bulk Assign
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="mt-6 bg-white shadow rounded-lg p-6">
        <form method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:space-x-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700">Pencarian</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm"
                       placeholder="Cari nama atau email user...">
            </div>
            <div class="min-w-0 flex-1 sm:max-w-xs">
                <label for="role" class="block text-sm font-medium text-gray-700">Filter Role</label>
                <select name="role" id="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 sm:text-sm">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                            {{ Cache::get("role_meta_{$role->id}.display_name", ucfirst($role->name)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="inline-flex items-center rounded-md bg-navy-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-navy-500">
                    Filter
                </button>
                <a href="{{ route('admin.user-roles.index') }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="relative px-6 py-3">
                                    <input type="checkbox" @change="toggleAll" class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-navy-600 focus:ring-navy-500">
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Current Roles
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($users as $user)
                            <tr>
                                <td class="relative px-6 py-4">
                                    <input type="checkbox" x-model="selectedUsers" value="{{ $user->id }}" class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-navy-600 focus:ring-navy-500">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">{{ substr($user->name, 0, 2) }}</span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        @forelse($user->roles as $role)
                                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                                @if($role->name === 'admin') bg-red-100 text-red-800
                                                @elseif($role->name === 'pimpinan') bg-purple-100 text-purple-800
                                                @elseif($role->name === 'ppk') bg-blue-100 text-blue-800
                                                @elseif($role->name === 'staff') bg-green-100 text-green-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ Cache::get("role_meta_{$role->id}.display_name", ucfirst($role->name)) }}
                                                <button @click="removeRole({{ $user->id }}, {{ $role->id }})" class="ml-1 text-red-600 hover:text-red-800">
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </span>
                                        @empty
                                            <span class="text-sm text-gray-500">No roles assigned</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->is_active)
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="assignRole({{ $user->id }}, '{{ $user->name }}')" class="text-navy-600 hover:text-navy-900">
                                        Assign Roles
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center py-8">
                                        <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500">Tidak ada user ditemukan</p>
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
    @if($users->hasPages())
    <div class="mt-6">
        {{ $users->links() }}
    </div>
    @endif

    <!-- Assign Role Modal -->
    <div x-show="showAssignModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showAssignModal" x-transition class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="showAssignModal" x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form :action="'/admin/users/' + selectedUserId + '/assign-role'" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" x-text="'Assign Roles untuk ' + selectedUserName"></h3>
                                <div class="mt-4">
                                    <div class="space-y-3">
                                        @foreach($roles as $role)
                                        <label class="flex items-center">
                                            <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                                   class="h-4 w-4 text-navy-600 focus:ring-navy-500 border-gray-300 rounded">
                                            <span class="ml-3 text-sm text-gray-700">
                                                {{ Cache::get("role_meta_{$role->id}.display_name", ucfirst($role->name)) }}
                                            </span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-navy-600 text-base font-medium text-white hover:bg-navy-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Assign Roles
                        </button>
                        <button @click="showAssignModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Assign Modal -->
    <div x-show="showBulkAssignModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showBulkAssignModal" x-transition class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="showBulkAssignModal" x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('admin.bulk-assign-role') }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Bulk Assign Role</h3>
                                <div class="mt-4">
                                    <p class="text-sm text-gray-500 mb-4" x-text="'Assign role untuk ' + selectedUsers.length + ' user yang dipilih'"></p>

                                    <!-- Hidden inputs for selected users -->
                                    <template x-for="userId in selectedUsers" :key="userId">
                                        <input type="hidden" name="user_ids[]" :value="userId">
                                    </template>

                                    <div class="space-y-3">
                                        <label class="block text-sm font-medium text-gray-700">Pilih Role:</label>
                                        @foreach($roles as $role)
                                        <label class="flex items-center">
                                            <input type="radio" name="role_id" value="{{ $role->id }}"
                                                   class="h-4 w-4 text-navy-600 focus:ring-navy-500 border-gray-300">
                                            <span class="ml-3 text-sm text-gray-700">
                                                {{ Cache::get("role_meta_{$role->id}.display_name", ucfirst($role->name)) }}
                                            </span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Assign Role
                        </button>
                        <button @click="showBulkAssignModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function userRolesManager() {
    return {
        showAssignModal: false,
        showBulkAssignModal: false,
        selectedUserId: null,
        selectedUserName: '',
        selectedUsers: [],

        assignRole(userId, userName) {
            this.selectedUserId = userId;
            this.selectedUserName = userName;
            this.showAssignModal = true;
        },

        removeRole(userId, roleId) {
            if (confirm('Apakah Anda yakin ingin menghapus role ini dari user?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/users/${userId}/roles/${roleId}`;

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

        toggleAll(event) {
            if (event.target.checked) {
                this.selectedUsers = Array.from(document.querySelectorAll('input[type="checkbox"][value]')).map(cb => cb.value);
            } else {
                this.selectedUsers = [];
            }
        }
    }
}
</script>
@endsection
