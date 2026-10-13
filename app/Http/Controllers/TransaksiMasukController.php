<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransaksiMasukController extends Controller
{
    public $pageTitle = 'Transaksi Masuk';

    public function create()
    {
        $pageTitle = $this->pageTitle;
        return view('transaksi-masuk.create', compact('pageTitle'));
    }
}
