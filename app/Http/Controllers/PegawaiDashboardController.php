<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PegawaiDashboardController extends Controller
{
    public function index(Request $request)
    {
        $pegawai = $request->user()->pegawai;
        return view('pegawai-area.dashboard', compact('pegawai'));
    }
}
