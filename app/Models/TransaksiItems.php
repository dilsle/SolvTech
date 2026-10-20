<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiItems extends Model
{
    protected $guarded = ['id'];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }
}
