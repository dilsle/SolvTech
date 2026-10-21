<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransaksiReturController extends Controller
{
    public $pageTitle = 'Transaksi Retur';
    public function create() {
        $pageTitle =$this->pageTitle;
        return view('transaksi-retur.create', compact('pageTitle'));
    }
}
