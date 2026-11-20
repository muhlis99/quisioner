<!DOCTYPE html>
<html lang="id">
<head>
    @section('title', 'Portal - Sistem Kuisioner')
    @include('layouts.partials.head')
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 p-4 text-slate-800 antialiased">
    <div class="w-full max-w-3xl rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
        <div class="text-center">
            <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                Sistem Kuisioner &amp; EDOM
            </span>
            <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Pilih Tampilan Portal</h1>
            <p class="mt-2 text-sm text-slate-600">
                Layout Admin dan User/Mahasiswa telah dipisahkan. Pilih tampilan di bawah ini untuk pratinjau.
            </p>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <a href="{{ route('admin.dashboard') }}" class="group rounded-2xl border border-slate-200 bg-slate-900 p-6 text-white transition hover:-translate-y-0.5 hover:shadow-lg">
                <span class="inline-flex rounded-lg bg-indigo-600 px-2.5 py-1 text-xs font-semibold">Admin</span>
                <h2 class="mt-4 text-lg font-bold">Panel Administrator</h2>
                <p class="mt-1.5 text-xs leading-relaxed text-slate-300">
                    Kelola data SIAKAD (Mahasiswa, Dosen, Jatah Makul), Master Skor, Kuisioner, Pertanyaan, Hasil, dan Monitoring Ketuntasan.
                </p>
                <p class="mt-4 text-xs font-semibold text-indigo-300 group-hover:text-indigo-200">Buka Dashboard Admin &rarr;</p>
            </a>

            <a href="{{ route('mahasiswa.dashboard') }}" class="group rounded-2xl border border-emerald-200 bg-emerald-50/60 p-6 text-slate-900 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg">
                <span class="inline-flex rounded-lg bg-emerald-600 px-2.5 py-1 text-xs font-semibold text-white">Mahasiswa</span>
                <h2 class="mt-4 text-lg font-bold">Portal Mahasiswa</h2>
                <p class="mt-1.5 text-xs leading-relaxed text-slate-600">
                    Isi kuisioner EDOM per mata kuliah/dosen, isi kuisioner umum, serta cek status ketuntasan semester aktif.
                </p>
                <p class="mt-4 text-xs font-semibold text-emerald-700 group-hover:text-emerald-800">Buka Beranda Mahasiswa &rarr;</p>
            </a>
        </div>
    </div>
</body>
</html>
