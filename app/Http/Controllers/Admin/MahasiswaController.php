<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100], true) ? $perPage : 15;

        $mahasiswa = Mahasiswa::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('nim', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%");
                });
            })
            ->orderBy('nim')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.mahasiswa.index', [
            'mahasiswa' => $mahasiswa,
            'search' => $search,
            'perPage' => $perPage,
            'totalSemua' => Mahasiswa::count(),
        ]);
    }
}
