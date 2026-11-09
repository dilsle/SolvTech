@extends('layouts.kai')
@section('title', 'Dashboard Pegawai')
@section('content')
    <div class="container">
        <h1>Selamat Datang, {{ $pegawai->nama }}</h1>
        <p>Jabatan : {{ $pegawai->jabatan }}</p>
    </div>
@endsection

