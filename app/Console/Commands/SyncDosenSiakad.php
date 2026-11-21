<?php

namespace App\Console\Commands;

use App\Services\SiakadSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncDosenSiakad extends Command
{
    protected $signature = 'siakad:sync-dosen';

    protected $description = 'Sinkronisasi data dosen dari API SIAKAD ke tabel tb_dosen';

    public function handle(SiakadSyncService $syncService): int
    {
        $endpoint = rtrim((string) config('services.siakad.url'), '/') . '/dosen/all';
        $this->info("Menghubungi API SIAKAD: {$endpoint} ...");

        try {
            $result = $syncService->syncDosen();

            $this->info('Sinkronisasi dosen berhasil!');
            $this->table(
                ['Metrik', 'Jumlah'],
                [
                    ['Total Halaman API', $result['total_pages']],
                    ['Total dari API', $result['total_api']],
                    ['Berhasil Diproses', $result['processed']],
                    ['Ditambahkan (Baru)', $result['inserted']],
                    ['Diperbarui (Nama berubah)', $result['updated']],
                    ['Sudah Mutakhir', $result['unchanged']],
                    ['Dilewati (Tidak valid)', $result['skipped']],
                ]
            );

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Sinkronisasi dosen gagal: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
