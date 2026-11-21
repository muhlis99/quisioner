<!DOCTYPE html>
<html lang="id">
<head>
    @section('title', 'Portal - Sistem Kuisioner')
    @include('layouts.partials.head')
</head>
<body class="flex min-h-screen items-center justify-center bg-[#F4F8F5] p-4 text-slate-800 antialiased">
    <div class="w-full max-w-3xl rounded-3xl border border-emerald-900/10 bg-white p-6 shadow-sm sm:p-10">
        <div class="text-center">
            <span class="inline-flex items-center rounded-full bg-[#E9F7F0] px-3 py-1 text-xs font-semibold text-[#0B6B3A]">
                Sistem Kuisioner &amp; EDOM
            </span>
            <h1 class="mt-3 text-2xl font-bold tracking-tight text-[#062419] sm:text-3xl">Pilih Tampilan Portal</h1>
            <p class="mt-2 text-sm text-slate-600">
                Layout Admin dan User/Mahasiswa telah dipisahkan dengan tema hijau emerald gelap yang konsisten.
            </p>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <a href="{{ route('admin.dashboard') }}" class="group rounded-2xl border border-[#0B6B3A] bg-[#062419] p-6 text-white transition hover:-translate-y-0.5 hover:shadow-lg">
                <span class="inline-flex rounded-lg bg-[#0B6B3A] px-2.5 py-1 text-xs font-semibold text-white">Admin</span>
                <h2 class="mt-4 text-lg font-bold">Panel Administrator</h2>
                <p class="mt-1.5 text-xs leading-relaxed text-emerald-100/80">
                    Kelola data SIAKAD (Mahasiswa, Dosen, Jatah Makul), Master Skor, Kuisioner, Pertanyaan, Hasil, dan Monitoring Ketuntasan.
                </p>
                <p class="mt-4 text-xs font-semibold text-[#20A464] group-hover:text-emerald-300">Buka Dashboard Admin &rarr;</p>
            </a>

            <a href="{{ route('mahasiswa.dashboard') }}" class="group rounded-2xl border border-[#20A464]/40 bg-[#E9F7F0] p-6 text-[#062419] transition hover:-translate-y-0.5 hover:border-[#20A464] hover:shadow-lg">
                <span class="inline-flex rounded-lg bg-[#0B6B3A] px-2.5 py-1 text-xs font-semibold text-white">Mahasiswa</span>
                <h2 class="mt-4 text-lg font-bold">Portal Mahasiswa</h2>
                <p class="mt-1.5 text-xs leading-relaxed text-slate-600">
                    Isi kuisioner EDOM per mata kuliah/dosen, isi kuisioner umum, serta cek status ketuntasan semester aktif.
                </p>
                <p class="mt-4 text-xs font-semibold text-[#0B6B3A] group-hover:text-[#20A464]">Buka Beranda Mahasiswa &rarr;</p>
            </a>
        </div>
    </div>
</body>
</html>
