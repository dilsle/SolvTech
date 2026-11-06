<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawais';

    protected $fillable = [
        'user_id',
        'nip',
        'nama',
        'jenis_kelamin',
        'no_hp',
        'alamat',
        'jabatan',
        'tanggal_masuk',
        'status',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
    ];

    // Relasi: Pegawai dimiliki oleh satu User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi: Pegawai memiliki banyak Absensi
    public function absensis()
    {
        return $this->hasMany(Absensi::class, 'pegawai_id');
    }

    // Relasi: Pegawai memiliki banyak Pengajuan Izin
    public function pengajuanIzins()
    {
        return $this->hasMany(PengajuanIzin::class, 'pegawai_id');
    }
}
