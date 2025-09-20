<!-- resources/views/partials/sidebar.blade.php -->
<div class="flex grow flex-col gap-y-5 overflow-y-auto bg-navy-900 px-6 pb-4">
    <div class="flex h-16 shrink-0 items-center">
        <div class="flex items-center">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-yellow-400 to-yellow-600">
                <svg class="h-5 w-5 text-navy-900" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"/>
                </svg>
            </div>
            <span class="ml-3 text-lg font-semibold text-white">KETATAUSAHAAN</span>
        </div>
    </div>
    <nav class="flex flex-1 flex-col">
        <ul role="list" class="flex flex-1 flex-col gap-y-7">
            <li>
                <ul role="list" class="-mx-2 space-y-1">
                    <li>
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-navy-800 text-white' : 'text-navy-200 hover:text-white hover:bg-navy-800' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-colors duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <div x-data="{ open: {{ request()->routeIs('budget.*') ? 'true' : 'false' }} }">
                            <button @click="open = !open" class="text-navy-200 hover:text-white hover:bg-navy-800 group flex w-full items-center gap-x-3 rounded-md p-2 text-left text-sm leading-6 font-semibold transition-colors duration-200">
                                <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H15.75c.621 0 1.125.504 1.125 1.125v.375m-13.5 0h12m-12 0v6.75C3 14.621 3.504 15.125 4.125 15.125H8.25c.621 0 1.125-.504 1.125-1.125v-1.875m-4.5 0h3m0 0v.375c0 .621.504 1.125 1.125 1.125h2.625c.621 0 1.125-.504 1.125-1.125V12" />
                                </svg>
                                Modul Keuangan
                                <svg :class="{ 'rotate-90': open }" class="ml-auto h-5 w-5 shrink-0 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <ul x-show="open" x-transition class="mt-1 space-y-1 pl-11">
                                <li>
                                    <a href="{{ route('budget.index') }}" class="{{ request()->routeIs('budget.index') ? 'bg-navy-800 text-white' : 'text-navy-300 hover:text-white hover:bg-navy-800' }} block rounded-md py-2 px-3 text-sm leading-6 font-medium transition-colors duration-200">
                                        Data Anggaran
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('budget.create') }}" class="{{ request()->routeIs('budget.create') ? 'bg-navy-800 text-white' : 'text-navy-300 hover:text-white hover:bg-navy-800' }} block rounded-md py-2 px-3 text-sm leading-6 font-medium transition-colors duration-200">
                                        Tambah Anggaran
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="text-navy-300 hover:text-white hover:bg-navy-800 block rounded-md py-2 px-3 text-sm leading-6 font-medium transition-colors duration-200">
                                        Realisasi Anggaran
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="text-navy-300 hover:text-white hover:bg-navy-800 block rounded-md py-2 px-3 text-sm leading-6 font-medium transition-colors duration-200">
                                        Laporan Keuangan
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <a href="#" class="text-navy-200 hover:text-white hover:bg-navy-800 group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-colors duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            Modul Kepegawaian
                            <span class="ml-auto inline-flex items-center rounded-full bg-yellow-500 px-2 py-0.5 text-xs font-medium text-navy-900">Soon</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-navy-200 hover:text-white hover:bg-navy-800 group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-colors duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.01v.01H12v-.01z" />
                            </svg>
                            Modul Inventaris
                            <span class="ml-auto inline-flex items-center rounded-full bg-yellow-500 px-2 py-0.5 text-xs font-medium text-navy-900">Soon</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-navy-200 hover:text-white hover:bg-navy-800 group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-colors duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Modul Kehadiran
                            <span class="ml-auto inline-flex items-center rounded-full bg-yellow-500 px-2 py-0.5 text-xs font-medium text-navy-900">Soon</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</div>
