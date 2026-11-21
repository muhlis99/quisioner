<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Services\SiakadSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

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

    /**
     * Menjalankan sinkronisasi data mahasiswa dari API SIAKAD ke tb_mahasiswa.
     * Mendukung respon JSON (AJAX/Fetch + SweetAlert2) maupun redirect standar.
     */
    public function sync(Request $request, SiakadSyncService $syncService): JsonResponse|RedirectResponse
    {
        try {
            $result = $syncService->syncMahasiswa();
            $totalDb = Mahasiswa::count();

            $pesan = sprintf(
                'Sinkronisasi SIAKAD selesai: %d data dari API (%d berhasil diproses: %d baru, %d diperbarui, %d mutakhir%s).',
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
                ->route('admin.mahasiswa.index')
                ->with('success', $pesan);
        } catch (Throwable $e) {
            $errorMsg = 'Sinkronisasi SIAKAD gagal: ' . $e->getMessage();

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $errorMsg,
                ], 502);
            }

            return redirect()
                ->route('admin.mahasiswa.index')
                ->with('error', $errorMsg);
        }
    }
}
