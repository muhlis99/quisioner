<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Services\SiakadSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

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

    /**
     * Menjalankan sinkronisasi data dosen dari API SIAKAD ke tb_dosen.
     * Mendukung respon JSON (AJAX/Fetch + SweetAlert2) maupun redirect standar.
     */
    public function sync(Request $request, SiakadSyncService $syncService): JsonResponse|RedirectResponse
    {
        try {
            $result = $syncService->syncDosen();
            $totalDb = Dosen::count();

            $pesan = sprintf(
                'Sinkronisasi Dosen SIAKAD selesai: %d data dari API (%d berhasil diproses: %d baru, %d diperbarui, %d mutakhir%s).',
                $result['total_api'],
                $result['processed'],
                $result['inserted'],
                $result['updated'],
                $result['unchanged'],
                $result['skipped'] > 0 ? ", {$result['skipped']} dilewati" : ''
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => $pesan,
                    'data' => array_merge($result, ['total_db' => $totalDb]),
                ]);
            }

            return redirect()
                ->route('admin.dosen.index')
                ->with('success', $pesan);
        } catch (Throwable $e) {
            $errorMsg = 'Sinkronisasi Dosen SIAKAD gagal: ' . $e->getMessage();

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $errorMsg,
                ], 502);
            }

            return redirect()
                ->route('admin.dosen.index')
                ->with('error', $errorMsg);
        }
    }
}
