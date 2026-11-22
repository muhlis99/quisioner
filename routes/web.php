<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DosenController;
use App\Http\Controllers\Admin\JatahMakulController;
use App\Http\Controllers\Admin\KetuntasanController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\MahasiswaController;
use App\Http\Controllers\Admin\PertanyaanController;
use App\Http\Controllers\Admin\QuisionerController;
use App\Http\Controllers\Admin\SkorController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboardController;
use App\Http\Controllers\Mahasiswa\QuisionerController as MahasiswaQuisionerController;
use Illuminate\Support\Facades\Route;

// Halaman Login sebagai halaman pertama saat web diakses
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/login', fn () => redirect()->route('login'));
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Area khusus Admin
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/chart-data', [AdminDashboardController::class, 'getChartData'])->name('dashboard.chart-data');

        // 1. Menu Mahasiswa (Integrasi SIAKAD)
        Route::get('/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
        Route::post('/mahasiswa/sync', [MahasiswaController::class, 'sync'])->name('mahasiswa.sync');

        // 2. Menu Dosen (Integrasi SIAKAD)
        Route::get('/dosen', [DosenController::class, 'index'])->name('dosen.index');
        Route::post('/dosen/sync', [DosenController::class, 'sync'])->name('dosen.sync');

        // 3. Menu Skor Jawaban
        Route::resource('skor', SkorController::class)->except(['show']);

        // 4. Menu Quisioner & Pertanyaan
        Route::resource('quisioner', QuisionerController::class)->except(['show']);
        Route::prefix('quisioner/{quisioner}')
            ->name('quisioner.pertanyaan.')
            ->group(function () {
                Route::get('/pertanyaan', [PertanyaanController::class, 'index'])->name('index');
                Route::get('/pertanyaan/create', [PertanyaanController::class, 'create'])->name('create');
                Route::post('/pertanyaan', [PertanyaanController::class, 'store'])->name('store');
                Route::get('/pertanyaan/{pertanyaan}/edit', [PertanyaanController::class, 'edit'])->name('edit');
                Route::put('/pertanyaan/{pertanyaan}', [PertanyaanController::class, 'update'])->name('update');
                Route::delete('/pertanyaan/{pertanyaan}', [PertanyaanController::class, 'destroy'])->name('destroy');
            });

        // 5. Menu Hasil Laporan
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/{quisioner}', [LaporanController::class, 'show'])->name('laporan.show');

        // 6. Menu Jatah Mata Kuliah (tb_jatah_makul + Generate Otomatis API SIAMDOS)
        Route::post('/jatah-makul/fetch-api', [JatahMakulController::class, 'fetchFromApi'])->name('jatah-makul.fetch-api');
        Route::post('/jatah-makul/store-api', [JatahMakulController::class, 'storeFromApi'])->name('jatah-makul.store-api');
        Route::post('/jatah-makul/generate-massal', [JatahMakulController::class, 'generateMassal'])->name('jatah-makul.generate-massal');
        Route::resource('jatah-makul', JatahMakulController::class)
            ->parameters(['jatah-makul' => 'jatahMakul'])
            ->except(['show']);

        // 7. Menu Monitoring Ketuntasan (Rekap Per-Mahasiswa, Total Skor & Rata-rata Nilai)
        Route::get('/ketuntasan', [KetuntasanController::class, 'index'])->name('ketuntasan.index');
        Route::get('/ketuntasan/{nim}', [KetuntasanController::class, 'show'])->name('ketuntasan.show');
        Route::delete('/ketuntasan/{ketuntasan}', [KetuntasanController::class, 'destroy'])->name('ketuntasan.destroy');

        // 8. Menu Kelola Pengguna (tb_users: Tab Non-Mahasiswa & Tab Mahasiswa + Generate Massal & Reset Password)
        Route::post('/users/generate-mahasiswa', [UserController::class, 'generateMahasiswaAccounts'])->name('users.generate-mahasiswa');
        Route::post('/users/reset-password-mahasiswa-massal', [UserController::class, 'resetPasswordMahasiswaMassal'])->name('users.reset-mahasiswa-massal');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::resource('users', UserController::class)->except(['show']);
    });

// Area khusus User / Mahasiswa
Route::prefix('mahasiswa')
    ->name('mahasiswa.')
    ->middleware(['auth', 'role:mahasiswa'])
    ->group(function () {
        Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])->name('dashboard');

        // Menu Quisioner & Form Pengisian Quisioner
        Route::get('/quisioner', [MahasiswaQuisionerController::class, 'index'])->name('quisioner.index');
        Route::get('/quisioner/{quisioner}/isi', [MahasiswaQuisionerController::class, 'show'])->name('quisioner.show');
        Route::post('/quisioner/{quisioner}/isi', [MahasiswaQuisionerController::class, 'store'])->name('quisioner.store');

        // Redirect route lama Mata Kuliah Diambil & Status Ketuntasan ke Dashboard Mahasiswa agar tidak 404
        Route::get('/mata-kuliah', fn () => redirect()->route('mahasiswa.dashboard'))->name('makul.index');
        Route::get('/ketuntasan', fn () => redirect()->route('mahasiswa.dashboard'))->name('ketuntasan.index');
    });
