<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanIzin extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_izins';

    protected $fillable = [
        'pegawai_id',
        'jenis',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'bukti',
        'status',
        'catatan_admin',
        'disetujui_oleh',
        'diproses_pada',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'diproses_pada' => 'datetime',
    ];

    // Relasi: Pengajuan izin dimiliki oleh satu Pegawai
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    // Relasi: Pengajuan izin diproses oleh satu User admin
    public function admin()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
