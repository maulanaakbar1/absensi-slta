<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $fillable = [
        'user_id',
        'ekstrakurikuler_id',
        'tahun_masuk',
        'tingkat_awal',
        'jurusan',
        'nis',
        'nisn',
        'kelas',
        'jenis_kelamin',
        'alamat',
        'tempat_lahir',
        'tanggal_lahir',
        'nama_ayah',
        'nama_ibu',
        'no_telp_ayah',
        'no_telp_ibu',
        'no_telp_siswa',
        'tingkatan'
    ];

    protected $casts = [
        'tahun_masuk' => 'integer',
        'tanggal_lahir' => 'date',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }
}