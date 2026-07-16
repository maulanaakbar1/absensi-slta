<aside 
    x-cloak
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
    class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 flex flex-col transition-transform duration-300 ease-in-out md:relative md:translate-x-0 md:flex">
    
    <div class="p-6 flex items-center justify-between">

        {{-- LOGO + TITLE --}}
        <div class="flex items-center gap-3">

            <div class="h-12 w-12 rounded-xl overflow-hidden shadow-lg shadow-blue-200 bg-white border border-slate-100">
                <img 
                    src="{{ asset('images/logompc.png ') }}" 
                    alt="Logo Sekolah"
                    class="h-full w-full object-cover">
            </div>

            <div>
            <h1 class="text-2xl font-bold text-blue-800">EskulMate</h1>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">
                {{ Auth::user()->role }} Panel
            </p>
        </div>

        </div>
        <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-red-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    
    <nav class="flex-1 px-4 space-y-2">
        <a href="{{ route('admin.dashboard') }}" 
            class="flex items-center gap-3 {{ Request::is('admin/dashboard') ? 'bg-blue-50 text-blue-600' : 'text-slate-500 hover:bg-slate-50' }} px-4 py-3 rounded-xl font-semibold transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </a>

        <div x-data="{ dropdownOpen: {{ Request::is('admin/pembina*', 'admin/siswa*', 'admin/ekskul*') ? 'true' : 'false' }} }">
            <button @click="dropdownOpen = !dropdownOpen" 
                class="w-full flex items-center justify-between gap-3 {{ Request::is('admin/pembina*', 'admin/siswa*', 'admin/ekskul*') ? 'bg-blue-50 text-blue-600' : 'text-slate-500 hover:bg-slate-50' }} px-4 py-3 rounded-xl font-semibold transition text-left">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <span>Data Master</span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4 transition-transform duration-200" 
                    :class="dropdownOpen ? 'rotate-180' : ''" 
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="dropdownOpen" 
                x-transition:enter="transition-all ease-out duration-300"
                x-transition:enter-start="opacity-0 max-h-0"
                x-transition:enter-end="opacity-100 max-h-48"
                x-transition:leave="transition-all ease-in duration-200"
                x-transition:leave-start="opacity-100 max-h-48"
                x-transition:leave-end="opacity-0 max-h-0"
                class="mt-1 pl-4 space-y-1 overflow-hidden">
                
                <a href="{{ route('admin.pembina.index') }}" 
                    class="flex items-center gap-3 {{ Request::is('admin/pembina*') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' }} px-4 py-2.5 rounded-lg text-sm transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Data Pembina
                </a>

                <a href="{{ route('admin.siswa.index') }}" 
                    class="flex items-center gap-3 {{ Request::is('admin/siswa*') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' }} px-4 py-2.5 rounded-lg text-sm transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Data Siswa
                </a>
                
                <a href="{{ route('admin.ekskul.index') }}" 
                    class="flex items-center gap-3 {{ Request::is('admin/ekskul*') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' }} px-4 py-2.5 rounded-lg text-sm transition">
                    <svg xmlns="http://www.w3.org/2000/xl" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 00-2-2m0 0V5a2 2 0 012-2h6.28a2 2 0 011.414.586l.828.828A2 2 0 0014.12 5H19a2 2 0 012 2v2" />
                    </svg>
                    Data Ekskul
                </a>
            </div>
        </div>

        <a href="{{ route('admin.rekap.index') }}" 
            class="flex items-center gap-3 {{ Request::is('admin/rekap*') ? 'bg-blue-50 text-blue-600' : 'text-slate-500 hover:bg-slate-50' }} px-4 py-3 rounded-xl font-semibold transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 002-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Rekap Absensi
        </a>

        <a href="{{ route('admin.jadwal.index') }}" 
            class="flex items-center gap-3 {{ Request::is('admin/jadwal*') ? 'bg-blue-50 text-blue-600' : 'text-slate-500 hover:bg-slate-50' }} px-4 py-3 rounded-xl font-semibold transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 8h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Jadwal Ekskul
        </a>
    </nav>

</aside>