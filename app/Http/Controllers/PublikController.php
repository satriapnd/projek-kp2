<?php

namespace App\Http\Controllers;

use App\Models\Tamu;
use App\Models\Kunjungan;
use Illuminate\Http\Request;

class PublikController extends Controller
{
    public function kunjungan()
    {
        $kunjungans = Kunjungan::with('tamu')
            ->whereDate('jam_masuk', today())
            ->orderBy('jam_masuk', 'desc')
            ->get();

        $totalHariIni = $kunjungans->count();
        $sedangBerkunjung = $kunjungans->where('status', 'sedang berkunjung')->count();
        $totalTamu = Tamu::count();

        return view('publik.kunjungan', compact('kunjungans', 'totalHariIni', 'sedangBerkunjung', 'totalTamu'));
    }
}
