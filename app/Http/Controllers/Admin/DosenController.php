<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DosenController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100], true) ? $perPage : 15;

        $dosen = Dosen::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('nipy', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%");
                });
            })
            ->orderBy('nipy')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.dosen.index', [
            'dosen' => $dosen,
            'search' => $search,
            'perPage' => $perPage,
            'totalSemua' => Dosen::count(),
        ]);
    }
}
