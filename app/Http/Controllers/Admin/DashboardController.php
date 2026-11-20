<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Ketuntasan;
use App\Models\Mahasiswa;
use App\Models\Quisioner;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalMahasiswa' => Mahasiswa::count(),
            'totalDosen' => Dosen::count(),
            'totalQuisioner' => Quisioner::aktif()->count(),
            'totalTuntas' => Ketuntasan::where('ketuntasan', '1')->count(),
        ]);
    }
}
