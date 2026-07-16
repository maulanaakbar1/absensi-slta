<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class KirimWaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $nomor;
    public $pesan;

    public function __construct($nomor, $pesan)
    {
        $this->nomor = $nomor;
        $this->pesan = $pesan;
    }

    public function handle(): void
    {
        Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . config('services.waapi.token'),
            ])
            ->post(config('services.waapi.url'), [
                'phone' => $this->nomor,
                'message' => $this->pesan,
            ]);
    }
}