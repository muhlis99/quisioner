@if (session('success'))
    <div data-alert class="animate-alert-in mb-5 flex items-start justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-xs transition-all duration-200">
        <div class="flex items-start gap-2.5">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" data-close-alert class="rounded-md px-1 text-base leading-none text-emerald-600 transition hover:bg-emerald-100 hover:text-emerald-900" aria-label="Tutup notifikasi">&times;</button>
    </div>
@endif

@if (session('error'))
    <div data-alert class="animate-alert-in mb-5 flex items-start justify-between gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-xs transition-all duration-200">
        <div class="flex items-start gap-2.5">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" data-close-alert class="rounded-md px-1 text-base leading-none text-rose-600 transition hover:bg-rose-100 hover:text-rose-900" aria-label="Tutup notifikasi">&times;</button>
    </div>
@endif

@if (session('warning'))
    <div data-alert class="animate-alert-in mb-5 flex items-start justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 shadow-xs transition-all duration-200">
        <div class="flex items-start gap-2.5">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <span>{{ session('warning') }}</span>
        </div>
        <button type="button" data-close-alert class="rounded-md px-1 text-base leading-none text-amber-600 transition hover:bg-amber-100 hover:text-amber-900" aria-label="Tutup notifikasi">&times;</button>
    </div>
@endif

@if ($errors->any())
    <div data-alert class="animate-alert-in mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-xs transition-all duration-200">
        <div class="flex items-center justify-between gap-2 font-semibold">
            <span>Terdapat kesalahan pada input Anda:</span>
            <button type="button" data-close-alert class="rounded-md px-1 text-base leading-none text-rose-600 transition hover:bg-rose-100 hover:text-rose-900" aria-label="Tutup notifikasi">&times;</button>
        </div>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
