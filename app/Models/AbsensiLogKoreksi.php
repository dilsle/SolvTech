<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiLogKoreksi extends Model
{
    use HasFactory;

    protected $table = 'absensi_log_koreksis';

    protected $fillable = [
        'absensi_id',
        'admin_id',
        'data_sebelumnya',
        'data_sesudah',
        'alasan',
    ];

    protected $casts = [
        'data_sebelumnya' => 'array',
        'data_sesudah' => 'array',
    ];

    public function absensi()
    {
        return $this->belongsTo(Absensi::class, 'absensi_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
