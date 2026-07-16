<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | EskulMate</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>
<body 
    class="bg-slate-50 min-h-screen flex"
    x-data="{ sidebarOpen: false }"
    x-init="sidebarOpen = false">

    <div x-show="sidebarOpen" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false" 
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 md:hidden">
    </div>

    @include('partials.' . Auth::user()->role . '.sidebar')

    <main class="flex-1 flex flex-col h-screen overflow-y-auto">
        @include('partials.' . Auth::user()->role . '.navbar')
        
        <div class="p-8">
            @php
                $siswa = Auth::user()->siswa ?? null;

                $isProfileComplete = $siswa &&
                    !empty($siswa->no_telp_siswa) &&
                    !empty($siswa->tempat_lahir) &&
                    !empty($siswa->tanggal_lahir) &&
                    !empty($siswa->alamat) &&
                    !empty($siswa->nama_ayah) &&
                    !empty($siswa->no_telp_ayah) &&
                    !empty($siswa->nama_ibu) &&
                    !empty($siswa->no_telp_ibu);
            @endphp

            {{-- Alert Warning Data Belum Lengkap --}}
            @if(Auth::user()->role == 'siswa' && !$isProfileComplete)

            <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 bg-amber-50 border border-amber-200 rounded-2xl shadow-sm">

                <div class="flex items-start gap-3 w-full">
                    <div class="h-10 w-10 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-amber-600"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-amber-900">
                            Perhatian!
                        </p>
                        <p class="text-xs text-amber-700 leading-relaxed mt-0.5">
                            Biodata Anda belum lengkap! Silahkan lengkapi profil agar bisa melakukan absensi.
                        </p>
                    </div>
                </div>

                <div class="w-full md:w-auto flex-shrink-0">
                    <a href="{{ route('siswa.profile') }}"
                        class="block w-full md:w-auto text-center px-4 py-2.5 bg-amber-600 text-white text-xs font-bold rounded-xl hover:bg-amber-700 transition">
                        Lengkapi Sekarang
                    </a>
                </div>

            </div>

            @endif

            @yield('content')
        </div>

        @include('partials.' . Auth::user()->role . '.footer')
    </main>

    @stack('scripts')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if(session('loginSuccess'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Berhasil Masuk!',
                text: "{{ session('loginSuccess') }}",
                icon: 'success',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-[2rem]',
                }
            });
        });
    </script>
    @endif
</body>
</html>