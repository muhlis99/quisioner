import './bootstrap';
import Chart from 'chart.js/auto';
import Swal from 'sweetalert2';

window.Chart = Chart;
window.Swal = Swal;

const formatNumber = (val) => new Intl.NumberFormat('id-ID').format(Number(val) || 0);

function initAdminDashboardAnalytics() {
    const root = document.getElementById('admin-dashboard-analytics');
    if (!root) return;

    const endpoint = root.dataset.endpoint;
    const selectTahun = document.getElementById('filter-tahun-ajaran');
    const selectSemester = document.getElementById('filter-semester');
    const selectType = document.getElementById('filter-type');
    const resetBtn = document.getElementById('filter-reset-btn');
    const loadingEl = document.getElementById('dashboard-loading');
    const errorEl = document.getElementById('dashboard-filter-error');

    const kpiMahasiswa = document.getElementById('kpi-total-mahasiswa');
    const kpiDosen = document.getElementById('kpi-total-dosen');
    const kpiQuisioner = document.getElementById('kpi-total-quisioner');
    const kpiPersentase = document.getElementById('kpi-persentase-ketuntasan');
    const kpiDetail = document.getElementById('kpi-detail-ketuntasan');

    const badgeTotalSkor = document.getElementById('badge-total-skor');
    const badgeTotalKetuntasan = document.getElementById('badge-total-ketuntasan');
    const badgeTotalTren = document.getElementById('badge-total-tren');

    const wrapDist = document.getElementById('wrap-chart-distribusi');
    const emptyDist = document.getElementById('empty-chart-distribusi');
    const canvasDist = document.getElementById('dashboardChartDistribusi');

    const wrapKetuntasan = document.getElementById('wrap-chart-ketuntasan');
    const emptyKetuntasan = document.getElementById('empty-chart-ketuntasan');
    const canvasKetuntasan = document.getElementById('dashboardChartKetuntasan');

    const wrapTren = document.getElementById('wrap-chart-tren');
    const emptyTren = document.getElementById('empty-chart-tren');
    const canvasTren = document.getElementById('dashboardChartTren');

    let chartDist = null;
    let chartKetuntasan = null;
    let chartTren = null;
    let abortController = null;

    const toggleEmptyState = (wrapEl, emptyEl, hasData) => {
        if (!wrapEl || !emptyEl) return;
        wrapEl.classList.toggle('hidden', !hasData);
        emptyEl.classList.toggle('hidden', hasData);
        emptyEl.classList.toggle('flex', !hasData);
    };

    const renderDashboard = (payload) => {
        if (!payload || !payload.kpi || !payload.charts) return;

        // Perbarui 4 KPI Cards
        if (kpiMahasiswa) kpiMahasiswa.textContent = formatNumber(payload.kpi.total_mahasiswa);
        if (kpiDosen) kpiDosen.textContent = formatNumber(payload.kpi.total_dosen);
        if (kpiQuisioner) kpiQuisioner.textContent = formatNumber(payload.kpi.total_quisioner);
        if (kpiPersentase) kpiPersentase.textContent = `${Number(payload.kpi.persentase_ketuntasan || 0).toFixed(1)}%`;
        if (kpiDetail) {
            kpiDetail.textContent = `${formatNumber(payload.kpi.total_tuntas)} dari ${formatNumber(payload.kpi.total_ketuntasan)} data tuntas`;
        }

        // Grafik 1: Distribusi Skor Jawaban (Bar Chart Interaktif)
        const dist = payload.charts.distribusi_skor;
        if (badgeTotalSkor) badgeTotalSkor.textContent = `${formatNumber(dist.total)} jawaban`;
        toggleEmptyState(wrapDist, emptyDist, Boolean(dist.has_data));

        if (chartDist) {
            chartDist.destroy();
            chartDist = null;
        }
        if (dist.has_data && canvasDist) {
            chartDist = new Chart(canvasDist, {
                type: 'bar',
                data: {
                    labels: dist.labels,
                    datasets: [{
                        label: 'Jumlah Jawaban',
                        data: dist.data,
                        backgroundColor: [
                            '#062419',
                            '#094728',
                            '#0B6B3A',
                            '#20A464',
                            '#34D399',
                            '#6EE7B7',
                        ],
                        borderRadius: 8,
                        maxBarThickness: 56,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#062419',
                            padding: 10,
                            callbacks: {
                                title: (items) => {
                                    const idx = items[0]?.dataIndex ?? 0;
                                    const ket = dist.keterangan?.[idx] ? ` — ${dist.keterangan[idx]}` : '';
                                    return `${dist.labels[idx] || ''}${ket}`;
                                },
                                label: (ctx) => {
                                    const idx = ctx.dataIndex;
                                    const count = formatNumber(ctx.parsed.y);
                                    const pct = Number(dist.percentages?.[idx] ?? 0).toFixed(1);
                                    return `Jumlah: ${count} jawaban (${pct}%)`;
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: 'rgba(6, 36, 25, 0.08)' },
                        },
                        x: {
                            grid: { display: false },
                        },
                    },
                },
            });
        }

        // Grafik 2: Status Ketuntasan Mahasiswa (Donut Chart)
        const kt = payload.charts.ketuntasan;
        if (badgeTotalKetuntasan) badgeTotalKetuntasan.textContent = `${formatNumber(kt.total)} data`;
        toggleEmptyState(wrapKetuntasan, emptyKetuntasan, Boolean(kt.has_data));

        if (chartKetuntasan) {
            chartKetuntasan.destroy();
            chartKetuntasan = null;
        }
        if (kt.has_data && canvasKetuntasan) {
            chartKetuntasan = new Chart(canvasKetuntasan, {
                type: 'doughnut',
                data: {
                    labels: kt.labels,
                    datasets: [{
                        data: kt.data,
                        backgroundColor: ['#20A464', '#062419'],
                        hoverBackgroundColor: ['#0B6B3A', '#134E36'],
                        borderColor: '#ffffff',
                        borderWidth: 3,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '64%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 18,
                                color: '#062419',
                                font: { size: 12, weight: '600' },
                            },
                        },
                        tooltip: {
                            backgroundColor: '#062419',
                            padding: 10,
                            callbacks: {
                                label: (ctx) => {
                                    const val = Number(ctx.parsed) || 0;
                                    const pct = kt.total > 0 ? ((val / kt.total) * 100).toFixed(1) : '0.0';
                                    return `${ctx.label}: ${formatNumber(val)} (${pct}%)`;
                                },
                            },
                        },
                    },
                },
            });
        }

        // Grafik 3: Tren Partisipasi Harian (Area/Line Chart)
        const tren = payload.charts.tren_harian;
        if (badgeTotalTren) badgeTotalTren.textContent = `${formatNumber(tren.total)} entri`;
        toggleEmptyState(wrapTren, emptyTren, Boolean(tren.has_data));

        if (chartTren) {
            chartTren.destroy();
            chartTren = null;
        }
        if (tren.has_data && canvasTren) {
            const ctx2d = canvasTren.getContext('2d');
            const gradient = ctx2d.createLinearGradient(0, 0, 0, 280);
            gradient.addColorStop(0, 'rgba(32, 164, 100, 0.35)');
            gradient.addColorStop(1, 'rgba(32, 164, 100, 0.02)');

            chartTren = new Chart(canvasTren, {
                type: 'line',
                data: {
                    labels: tren.labels,
                    datasets: [
                        {
                            label: 'Jumlah Jawaban',
                            data: tren.jawaban,
                            borderColor: '#0B6B3A',
                            backgroundColor: gradient,
                            pointBackgroundColor: '#20A464',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            borderWidth: 2.5,
                            tension: 0.35,
                            fill: true,
                        },
                        {
                            label: 'Responden Unik (NIM)',
                            data: tren.responden,
                            borderColor: '#20A464',
                            backgroundColor: 'transparent',
                            borderDash: [5, 5],
                            pointBackgroundColor: '#062419',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 1.5,
                            pointRadius: 3.5,
                            borderWidth: 2,
                            tension: 0.35,
                            fill: false,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                usePointStyle: true,
                                color: '#062419',
                                font: { size: 12, weight: '600' },
                            },
                        },
                        tooltip: {
                            backgroundColor: '#062419',
                            padding: 10,
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: 'rgba(6, 36, 25, 0.08)' },
                        },
                        x: {
                            grid: { display: false },
                        },
                    },
                },
            });
        }
    };

    const fetchUpdatedData = async () => {
        if (!endpoint) return;

        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        const params = new URLSearchParams();
        if (selectTahun?.value) params.set('tahun_ajaran', selectTahun.value);
        if (selectSemester?.value) params.set('semester', selectSemester.value);
        if (selectType?.value) params.set('type', selectType.value);

        loadingEl?.classList.remove('hidden');
        loadingEl?.classList.add('inline-flex');
        errorEl?.classList.add('hidden');

        try {
            const url = params.toString() ? `${endpoint}?${params.toString()}` : endpoint;
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: abortController.signal,
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            renderDashboard(data);
        } catch (err) {
            if (err.name === 'AbortError') return;
            if (errorEl) {
                errorEl.textContent = 'Gagal memuat data dashboard. Silakan coba lagi.';
                errorEl.classList.remove('hidden');
            }
        } finally {
            loadingEl?.classList.add('hidden');
            loadingEl?.classList.remove('inline-flex');
        }
    };

    // Render data awal tanpa request tambahan
    if (root.dataset.initial) {
        try {
            renderDashboard(JSON.parse(root.dataset.initial));
        } catch {
            fetchUpdatedData();
        }
    }

    [selectTahun, selectSemester, selectType].forEach((el) => {
        el?.addEventListener('change', fetchUpdatedData);
    });

    resetBtn?.addEventListener('click', () => {
        if (selectTahun) selectTahun.value = '';
        if (selectSemester) selectSemester.value = '';
        if (selectType) selectType.value = '';
        fetchUpdatedData();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // 1. Sidebar Mobile Drawer & Dropdown Profil
    const sidebar = document.getElementById('app-sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const mobileOpenBtn = document.getElementById('mobile-sidebar-open');
    const mobileCloseBtn = document.getElementById('mobile-sidebar-close');
    const profileToggle = document.getElementById('profile-menu-toggle');
    const profileMenu = document.getElementById('profile-menu');

    const openSidebar = () => {
        if (!sidebar || !backdrop) return;
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
        document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
    };

    const closeSidebar = () => {
        if (!sidebar || !backdrop) return;
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
        document.body.classList.remove('overflow-hidden', 'lg:overflow-auto');
    };

    mobileOpenBtn?.addEventListener('click', openSidebar);
    mobileCloseBtn?.addEventListener('click', closeSidebar);
    backdrop?.addEventListener('click', closeSidebar);

    profileToggle?.addEventListener('click', (event) => {
        event.stopPropagation();
        if (!profileMenu) return;
        const isHidden = profileMenu.classList.toggle('hidden');
        profileToggle.setAttribute('aria-expanded', String(!isHidden));
    });

    document.addEventListener('click', (event) => {
        if (profileMenu && !profileMenu.classList.contains('hidden')) {
            if (!profileMenu.contains(event.target) && !profileToggle?.contains(event.target)) {
                profileMenu.classList.add('hidden');
                profileToggle?.setAttribute('aria-expanded', 'false');
            }
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSidebar();
            if (profileMenu && !profileMenu.classList.contains('hidden')) {
                profileMenu.classList.add('hidden');
                profileToggle?.setAttribute('aria-expanded', 'false');
            }
        }
    });

    // 2. Tutup Flash Alert
    document.querySelectorAll('[data-close-alert]').forEach((btn) => {
        btn.addEventListener('click', () => {
            btn.closest('[data-alert]')?.remove();
        });
    });

    // 3. Toggle Tampilkan/Sembunyikan Password (Halaman Login)
    const togglePasswordBtn = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');
    const eyeOpen = document.getElementById('eye-open');
    const eyeClosed = document.getElementById('eye-closed');

    togglePasswordBtn?.addEventListener('click', () => {
        if (!passwordInput) return;
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        eyeOpen?.classList.toggle('hidden', isPassword);
        eyeClosed?.classList.toggle('hidden', !isPassword);
    });

    // 4. Konfirmasi Hapus Global (SweetAlert2 Lokal via NPM)
    document.querySelectorAll('form[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();

            const title = form.dataset.confirmTitle || 'Konfirmasi Hapus';
            const text = form.dataset.confirmText || 'Apakah Anda yakin ingin menghapus data ini?';
            const confirmText = form.dataset.confirmButton || 'Ya, Hapus';

            Swal.fire({
                title,
                text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#0B6B3A',
                confirmButtonText: confirmText,
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // 5. Sinkronisasi Data SIAKAD (Mahasiswa & Dosen) via AJAX/Fetch + SweetAlert2
    const bindSiakadSyncButton = (buttonId, labelEntity, tableName) => {
        const btn = document.getElementById(buttonId);
        btn?.addEventListener('click', async () => {
            const url = btn.dataset.url;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            if (!url) return;

            const confirmResult = await Swal.fire({
                title: `Sinkronisasi Data ${labelEntity}?`,
                text: `Sistem akan menarik data ${labelEntity.toLowerCase()} terbaru dari API SIAKAD ke tabel ${tableName} tanpa menghapus data lama.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#20A464',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Sinkronkan',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            });

            if (!confirmResult.isConfirmed) return;

            btn.disabled = true;

            Swal.fire({
                title: 'Menyinkronkan Data...',
                text: `Mohon tunggu, sedang mengambil dan memproses data ${labelEntity.toLowerCase()} dari API SIAKAD.`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({}),
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok || payload.status !== 'success') {
                    throw new Error(payload.message || `Sinkronisasi gagal (HTTP ${response.status}).`);
                }

                const d = payload.data || {};
                await Swal.fire({
                    title: 'Sinkronisasi Berhasil',
                    icon: 'success',
                    confirmButtonColor: '#20A464',
                    confirmButtonText: 'Muat Ulang Tabel',
                    html: `
                        <div class="mt-2 space-y-1.5 text-left text-sm">
                            <div class="flex justify-between border-b border-slate-100 py-1"><span>Total Data API</span><strong>${formatNumber(d.total_api)}</strong></div>
                            <div class="flex justify-between border-b border-slate-100 py-1"><span>Berhasil Diproses</span><strong>${formatNumber(d.processed)}</strong></div>
                            <div class="flex justify-between border-b border-slate-100 py-1"><span>${labelEntity} Baru</span><strong class="text-emerald-700">${formatNumber(d.inserted)}</strong></div>
                            <div class="flex justify-between border-b border-slate-100 py-1"><span>Nama Diperbarui</span><strong class="text-amber-600">${formatNumber(d.updated)}</strong></div>
                            <div class="flex justify-between py-1"><span>Sudah Mutakhir</span><span>${formatNumber(d.unchanged)}</span></div>
                        </div>
                    `,
                });

                window.location.reload();
            } catch (err) {
                await Swal.fire({
                    title: 'Sinkronisasi Gagal',
                    text: err?.message || `Terjadi kesalahan saat menyinkronkan data ${labelEntity.toLowerCase()} SIAKAD.`,
                    icon: 'error',
                    confirmButtonColor: '#0B6B3A',
                });
            } finally {
                btn.disabled = false;
            }
        });
    };

    bindSiakadSyncButton('btn-sync-siakad-mahasiswa', 'Mahasiswa', 'tb_mahasiswa');
    bindSiakadSyncButton('btn-sync-siakad-dosen', 'Dosen', 'tb_dosen');

    // 5b. Generate Akun Mahasiswa Otomatis & Reset Password (Kelola Pengguna) via AJAX + SweetAlert2
    const csrfTokenGlobal = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const generateMhsBtn = document.getElementById('btn-generate-akun-mahasiswa');
    generateMhsBtn?.addEventListener('click', async () => {
        const url = generateMhsBtn.dataset.url;
        const pending = Number(generateMhsBtn.dataset.pending || 0);
        if (!url) return;

        const confirmResult = await Swal.fire({
            title: 'Generate Akun Mahasiswa Otomatis?',
            text: pending > 0
                ? `Akan dibuatkan akun login otomatis untuk ${formatNumber(pending)} mahasiswa yang belum memiliki akun (Username = NIM, Password default = NIM).`
                : 'Sistem akan memeriksa ulang seluruh data tb_mahasiswa dan membuat akun bagi yang belum terdaftar.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#20A464',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Generate Sekarang',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        });

        if (!confirmResult.isConfirmed) return;

        generateMhsBtn.disabled = true;
        Swal.fire({
            title: 'Membuat Akun Mahasiswa...',
            text: 'Mohon tunggu, sistem sedang memproses pembuatan akun secara bertahap.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfTokenGlobal,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({}),
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok || payload.status !== 'success') {
                throw new Error(payload.message || `Gagal generate akun (HTTP ${response.status}).`);
            }

            const d = payload.data || {};
            await Swal.fire({
                title: 'Generate Akun Selesai',
                icon: 'success',
                confirmButtonColor: '#20A464',
                confirmButtonText: 'Muat Ulang Tabel',
                html: `
                    <div class="mt-2 space-y-1.5 text-left text-sm">
                        <div class="flex justify-between border-b border-slate-100 py-1"><span>Total Mahasiswa SIAKAD</span><strong>${formatNumber(d.total_mahasiswa)}</strong></div>
                        <div class="flex justify-between border-b border-slate-100 py-1"><span>Akun Baru Dibuat</span><strong class="text-emerald-700">${formatNumber(d.created)}</strong></div>
                        <div class="flex justify-between py-1"><span>Sudah Punya Akun (Dilewati)</span><span>${formatNumber(d.skipped)}</span></div>
                    </div>
                `,
            });

            window.location.reload();
        } catch (err) {
            await Swal.fire({
                title: 'Generate Akun Gagal',
                text: err?.message || 'Terjadi kesalahan saat membuat akun mahasiswa.',
                icon: 'error',
                confirmButtonColor: '#0B6B3A',
            });
        } finally {
            generateMhsBtn.disabled = false;
        }
    });

    const resetMassalBtn = document.getElementById('btn-reset-massal-mahasiswa');
    resetMassalBtn?.addEventListener('click', async () => {
        const url = resetMassalBtn.dataset.url;
        const totalAkun = Number(resetMassalBtn.dataset.count || 0);
        if (!url) return;

        const confirmResult = await Swal.fire({
            title: 'Reset Password Massal Mahasiswa?',
            text: `Password seluruh ${formatNumber(totalAkun)} akun mahasiswa akan dikembalikan ke NIM masing-masing.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Reset Semua',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        });

        if (!confirmResult.isConfirmed) return;

        resetMassalBtn.disabled = true;
        Swal.fire({
            title: 'Mereset Password Massal...',
            text: 'Mohon tunggu hingga proses selesai.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfTokenGlobal,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({}),
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok || payload.status !== 'success') {
                throw new Error(payload.message || `Gagal reset massal (HTTP ${response.status}).`);
            }

            await Swal.fire({
                title: 'Reset Massal Berhasil',
                text: payload.message,
                icon: 'success',
                confirmButtonColor: '#20A464',
            });
        } catch (err) {
            await Swal.fire({
                title: 'Reset Massal Gagal',
                text: err?.message || 'Terjadi kesalahan saat mereset password.',
                icon: 'error',
                confirmButtonColor: '#0B6B3A',
            });
        } finally {
            resetMassalBtn.disabled = false;
        }
    });

    document.querySelectorAll('[data-reset-password-btn]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const url = btn.dataset.url;
            const username = btn.dataset.username || '';
            const isMahasiswa = btn.dataset.isMahasiswa === '1';
            if (!url) return;

            let bodyPayload = {};

            if (isMahasiswa) {
                const confirmResult = await Swal.fire({
                    title: 'Reset Password ke NIM?',
                    text: `Password akun mahasiswa ${username} akan direset kembali menjadi "${username}".`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#20A464',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Reset ke NIM',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                });
                if (!confirmResult.isConfirmed) return;
            } else {
                const inputResult = await Swal.fire({
                    title: `Reset Password (${username})`,
                    input: 'password',
                    inputLabel: 'Masukkan Password Baru (minimal 4 karakter)',
                    inputPlaceholder: 'Password baru...',
                    showCancelButton: true,
                    confirmButtonColor: '#20A464',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Simpan Password',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    inputValidator: (value) => {
                        if (!value || value.length < 4) {
                            return 'Password baru minimal 4 karakter.';
                        }
                        return null;
                    },
                });
                if (!inputResult.isConfirmed) return;
                bodyPayload = { password: inputResult.value };
            }

            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading(),
            });

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfTokenGlobal,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(bodyPayload),
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok || payload.status !== 'success') {
                    throw new Error(payload.message || `Gagal mereset password (HTTP ${response.status}).`);
                }

                await Swal.fire({
                    title: 'Berhasil',
                    text: payload.message,
                    icon: 'success',
                    confirmButtonColor: '#20A464',
                });
            } catch (err) {
                await Swal.fire({
                    title: 'Gagal',
                    text: err?.message || 'Terjadi kesalahan saat mereset password.',
                    icon: 'error',
                    confirmButtonColor: '#0B6B3A',
                });
            }
        });
    });

    // 6. Dashboard Analitik Interaktif (Filter AJAX + 3 Grafik Chart.js)
    initAdminDashboardAnalytics();

    // 6. Visualisasi Grafik Laporan Hasil Quisioner (Chart.js Lokal via NPM)
    const ctxDist = document.getElementById('chartDistribusiSkor');
    if (ctxDist && ctxDist.dataset.chart) {
        try {
            const distribusiData = JSON.parse(ctxDist.dataset.chart);
            if (distribusiData.labels && distribusiData.labels.length > 0) {
                new Chart(ctxDist, {
                    type: 'bar',
                    data: {
                        labels: distribusiData.labels,
                        datasets: [{
                            label: 'Jumlah Jawaban',
                            data: distribusiData.data,
                            backgroundColor: [
                                '#062419',
                                '#094728',
                                '#0B6B3A',
                                '#20A464',
                                '#34D399',
                                '#6EE7B7',
                            ],
                            borderRadius: 8,
                            maxBarThickness: 56,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 },
                                grid: { color: 'rgba(6, 36, 25, 0.08)' },
                            },
                            x: {
                                grid: { display: false },
                            },
                        },
                    },
                });
            }
        } catch (err) {
            console.error('Gagal merender grafik distribusi skor:', err);
        }
    }

    const ctxPertanyaan = document.getElementById('chartRataPertanyaan');
    if (ctxPertanyaan && ctxPertanyaan.dataset.chart) {
        try {
            const pertanyaanData = JSON.parse(ctxPertanyaan.dataset.chart);
            if (pertanyaanData.labels && pertanyaanData.labels.length > 0) {
                new Chart(ctxPertanyaan, {
                    type: 'bar',
                    data: {
                        labels: pertanyaanData.labels,
                        datasets: [{
                            label: 'Rata-rata Skor',
                            data: pertanyaanData.data,
                            backgroundColor: '#20A464',
                            hoverBackgroundColor: '#0B6B3A',
                            borderRadius: 8,
                            maxBarThickness: 44,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#062419',
                                callbacks: {
                                    title: (items) => {
                                        const idx = items[0]?.dataIndex ?? 0;
                                        return `${pertanyaanData.labels[idx]}: ${pertanyaanData.fullLabels[idx] || ''}`;
                                    },
                                },
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(6, 36, 25, 0.08)' },
                            },
                            x: {
                                grid: { display: false },
                            },
                        },
                    },
                });
            }
        } catch (err) {
            console.error('Gagal merender grafik rata-rata pertanyaan:', err);
        }
    }
});
