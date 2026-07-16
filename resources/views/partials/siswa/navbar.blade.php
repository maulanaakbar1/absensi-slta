            {{-- @dd(auth()->user()) --}}

<nav class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between sticky top-0 z-30" x-data="{ userMenu: false }">
    <div class="flex items-center gap-4">
        {{-- Burger Button untuk Mobile --}}
        <button @click="sidebarOpen = true" class="md:hidden text-slate-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        
        <div class="hidden md:block">
            <h2 class="text-slate-800 font-extrabold text-lg leading-tight">
                @if (Request::routeIs('siswa.dashboard'))
                    Dashboard Siswa

                @elseif (Request::routeIs('siswa.profile'))
                    Profil Saya

                @elseif (Request::routeIs('siswa.riwayat'))
                    Riwayat Absensi

                @elseif (Request::routeIs('siswa.absen'))
                    Absensi Kehadiran

                @else
                    Panel Siswa
                @endif
            </h2>
        </div>
    </div>

    {{-- Bagian Profil dengan Dropdown --}}
    <div class="flex items-center gap-4 relative">
        <div class="text-right hidden sm:block">
            <p class="text-xs text-slate-400 font-medium leading-tight">Selamat Datang,</p>
            <p class="text-sm font-bold text-slate-700 leading-tight">{{ Auth::user()->name }}</p>
        </div>

        {{-- Tombol Avatar --}}
        <button @click="userMenu = !userMenu" @click.away="userMenu = false" class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold shadow-lg shadow-blue-200 uppercase transition hover:scale-105 active:scale-95">
            {{ substr(Auth::user()->name, 0, 1) }}
        </button>

        {{-- Dropdown Menu (Seperti gambar image_24b58e.png) --}}
        <div
            x-cloak
            x-show="userMenu"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute right-0 top-full mt-3 w-52 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50">

            {{-- Profil Siswa --}}
            <a href="{{ route('siswa.profile') }}" 
            class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Profil Saya
            </a>

            @if(isset($eskul) && count($eskul) > 0)
                <div class="border-t border-slate-100 my-1"></div>
                <div class="px-4 py-3" @click.stop>
                    <label for="ekskul_select" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                        Pilih Ekskul
                    </label>
                    <form action="{{ route('siswa.pilih-ekskul') }}" method="POST">
                        @csrf
                        <select id="ekskul_select" name="ekskul_id"
                            onchange="this.form.submit()"
                            class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm font-medium rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2 transition outline-none cursor-pointer">
                            <option value="" disabled {{ !session('ekskul_aktif') ? 'selected' : '' }}>-- Pilih Ekskul --</option>
                            @foreach($eskul as $e)
                                <option value="{{ $e->id }}" 
                                    {{ session('ekskul_aktif') == $e->id ? 'selected' : '' }}>
                                    {{ $e->nama ?? $e->nama ?? 'Ekstrakurikuler' }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            @endif

            <div class="border-t border-slate-100 my-1"></div>

            {{-- Logout --}}
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" 
                    class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold text-red-500 hover:bg-red-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </div>
</nav>