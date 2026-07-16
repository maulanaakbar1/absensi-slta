@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    {{-- Header Admin --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Panel Admin </h3>
            <p class="text-slate-500 text-sm mt-1">Ringkasan aktivitas sistem manajemen hari ini.</p>
        </div>

        <div class="bg-white px-5 py-3 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <i class="fas fa-calendar-alt text-lg"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-xs font-bold text-slate-400 uppercase">Hari Ini</span>
                <span class="text-sm font-bold text-slate-700">
                    {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Statistik Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Card Total Siswa --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="h-12 w-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
            <p class="text-slate-500 text-sm font-medium">Total Siswa</p>
            <h3 class="text-3xl font-bold text-slate-800 mt-1">{{ $totalSiswa }}</h3>
        </div>

        {{-- Card Total Ekskul --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="h-12 w-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-running text-xl"></i>
                </div>
            </div>
            <p class="text-slate-500 text-sm font-medium">Ekstrakurikuler</p>
            <h3 class="text-3xl font-bold text-slate-800 mt-1">{{ $totalEkskul }}</h3>
        </div>

        {{-- Card Total Pembina --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="h-12 w-12 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-user-tie text-xl"></i>
                </div>
            </div>
            <p class="text-slate-500 text-sm font-medium">Total Pembina</p>
            <h3 class="text-3xl font-bold text-slate-800 mt-1">{{ $totalPembina }}</h3>
        </div>

        {{-- Card Kehadiran --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="h-12 w-12 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
            </div>
            <p class="text-slate-500 text-sm font-medium">Kehadiran Hari Ini</p>
            <h3 class="text-3xl font-bold text-slate-800 mt-1">{{ $persentaseHadir }}%</h3>
        </div>
    </div>

    {{-- Diagram Grafik Full Width Tanpa Tombol --}}
    <div class="grid grid-cols-1 gap-8 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <div class="mb-6">
                <h4 class="font-bold text-slate-800 text-lg">Grafik Kehadiran (7 Hari Terakhir)</h4>
                <p class="text-slate-400 text-xs mt-0.5">Visualisasi data tingkat kehadiran siswa secara real-time</p>
            </div>
            <div class="h-[350px] w-full">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    
    const gradient = ctx.createLinearGradient(0, 0, 0, 350);
    gradient.addColorStop(0, 'rgba(94, 114, 228, 0.24)'); 
    gradient.addColorStop(1, 'rgba(94, 114, 228, 0)');  

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($labels) !!},
            datasets: [{
                label: 'Siswa Hadir',
                data: {!! json_encode($values) !!},
                borderColor: '#5e72e4', 
                borderWidth: 3,
                fill: true,
                backgroundColor: gradient,
                tension: 0.4,           
                pointRadius: 4,         
                pointBackgroundColor: '#5e72e4',
                pointHoverRadius: 6,    
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false } 
            },
            scales: {
                y: {
                    grid: {
                        drawBorder: false,   
                        display: true,
                        color: '#e9ecef',     
                        borderDash: [5, 5]  
                    },
                    ticks: {
                        stepSize: 1,
                        color: '#adb5bd',
                        font: { size: 11 }
                    }
                },
                x: {
                    grid: {
                        display: false,       
                        drawBorder: false
                    },
                    ticks: {
                        display: true,
                        color: '#adb5bd',
                        padding: 10,
                        font: { size: 11 }
                    }
                }
            }
        }
    });
</script>
@endpush