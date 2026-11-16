<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->limit(30)->get()->map(function ($notif) {
            return [
                'id' => $notif->id,
                'judul' => $notif->data['judul'] ?? 'Notifikasi SIBENGKEL',
                'pesan' => $notif->data['pesan'] ?? '',
                'dibaca' => $notif->read_at !== null,
                'created_at' => $notif->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar notifikasi berhasil diambil.',
            'data' => $notifications,
        ], 200);
    }

    public function tandaiDibaca(Request $request, $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sudah dibaca.',
            'data' => null,
        ], 200);
    }
}
