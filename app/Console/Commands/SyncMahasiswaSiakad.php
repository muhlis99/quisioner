<?php

namespace App\Console\Commands;

use App\Services\SiakadSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncMahasiswaSiakad extends Command
{
    protected $signature = 'siakad:sync-mahasiswa';

    protected $description = 'Sinkronisasi data mahasiswa dari API SIAKAD ke tabel tb_mahasiswa';

    public function handle(SiakadSyncService $syncService): int
    {
        $endpoint = rtrim((string) config('services.siakad.url'), '/') . '/mahasiswa/all';
        $this->info("Menghubungi API SIAKAD: {$endpoint} ...");

        try {
            $result = $syncService->syncMahasiswa();

            $this->info('Sinkronisasi berhasil!');
            $this->table(
                ['Metrik', 'Jumlah'],
                [
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
            $this->error('Sinkronisasi gagal: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
