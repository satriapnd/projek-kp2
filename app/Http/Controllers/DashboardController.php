<?php

namespace App\Http\Controllers;

use App\Models\Tamu;
use App\Models\Kunjungan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Kunjungan::with('tamu')->orderBy('jam_masuk', 'desc');

        // Pencarian berdasarkan nama tamu
        if ($request->has('search') && $request->search != '') {
            $query->whereHas('tamu', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        // Filter status (sedang berkunjung / selesai)
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $kunjungans = $query->paginate(10);

        // Statistik
        $totalTamuTerdaftar = Tamu::count();
        $totalKunjunganHariIni = Kunjungan::whereDate('jam_masuk', today())->count();
        $sedangBerkunjung = Kunjungan::whereIn('status', ['sedang berkunjung', 'sedang_berkunjung'])->count();

        return view('dashboard', compact(
            'kunjungans', 
            'totalTamuTerdaftar', 
            'totalKunjunganHariIni', 
            'sedangBerkunjung'
        ));
    }

    public function deleteTamu($id)
    {
        $tamu = Tamu::findOrFail($id);
        
        // Hapus file foto dari storage
        if ($tamu->foto && \Storage::disk('public')->exists($tamu->foto)) {
            \Storage::disk('public')->delete($tamu->foto);
        }
        
        $tamu->delete(); // Karena on cascade, data kunjungan juga terhapus

        return redirect()->back()->with('success', 'Data tamu berhasil dihapus!');
    }
}
