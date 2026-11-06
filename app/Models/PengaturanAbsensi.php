<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanAbsensi extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_absensis';

    protected $fillable = [
        'jam_masuk',
        'jam_pulang',
        'batas_toleransi',
        'latitude',
        'longitude',
        'radius_meter',
        'buka_minggu',
    ];
}
