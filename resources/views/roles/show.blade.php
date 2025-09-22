@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto" x-data="roleDetail()">
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
                        <span class="ml-4 text-sm font-medium text-gray-500">Detail Role</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="mt-4 md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                    {{ $roleMeta['display_name'] }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">{{ $roleMeta['description'] }}</p>
            </div>
            <div class="mt-4 flex md:ml-4 md:mt-0">
                <a href="{{ route('admin.roles.edit', $role->id) }}"
                   class="ml-3 inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Role
                </a>
            </div>
        </div>
    </div>

    <!-- Role Info -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Informasi Role</h3>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Nama System</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $role->name }}</dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Nama Tampilan</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $roleMeta['display_name'] }}</dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Deskripsi</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $roleMeta['description'] ?: '-' }}</dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Jumlah User</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $role->users->count() }} users</dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Jumlah Permission</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $role->permissions->count() }} permissions</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Permissions -->
    <div class="bg-white shadow rounded-lg mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Permissions yang Diberikan</h3>
        </div>
        <div class="p-6">
            @if($role->permissions->count() > 0)
                @php
                    $groupedPermissions = $role->permissions->groupBy(function($permission) {
                        $meta = Cache::get("permission_meta_{$permission->id}", ['group' => 'general']);
                        return $meta['group'];
                    });
                @endphp

                <div class="space-y-6">
                    @foreach($groupedPermissions as $group => $permissions)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-base font-medium text-gray-900 capitalize mb-3">{{ $group }}</h4>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($permissions as $permission)
                            @php
                                $meta = Cache::get("permission_meta_{$permission->id}", [
                                    'display_name' => ucfirst($permission->name)
                                ]);
                            @endphp
                            <div class="flex items-center">
                                <svg class="h-4 w-4 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-sm text-gray-700">{{ $meta['display_name'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada permission</h3>
                    <p class="mt-1 text-sm text-gray-500">Role ini belum memiliki permission yang diberikan.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Users with this role -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">User dengan Role Ini</h3>
        </div>
        <div class="p-6">
            @if($role->users->count() > 0)
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($role->users as $user)
                    <div class="flex items-center p-3 border border-gray-200 rounded-lg">
                        <div class="h-8 w-8 flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-xs font-medium text-gray-700">{{ substr($user->name, 0, 2) }}</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                        </div>
                        @if($user->is_active)
                            <span class="ml-auto inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">
                                Active
                            </span>
                        @else
                            <span class="ml-auto inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                                Inactive
                            </span>
                        @endif
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada user</h3>
                    <p class="mt-1 text-sm text-gray-500">Belum ada user yang memiliki role ini.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function roleDetail() {
    return {
        // Add any interactive functionality here
    }
}
</script>
@endsection
