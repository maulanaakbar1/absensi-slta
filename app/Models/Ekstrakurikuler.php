<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ekstrakurikuler extends Model {
    protected $fillable = [
        'nama',
        'nama_satuan',
        'deskripsi',
        'foto'
    ];

    public function pembinas() {
        return $this->hasMany(Pembina::class);
    }
}