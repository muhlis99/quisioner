import './bootstrap';
import Chart from 'chart.js/auto';
import Swal from 'sweetalert2';

window.Chart = Chart;
window.Swal = Swal;

const formatNumber = (val) => new Intl.NumberFormat('id-ID').format(Number(val) || 0);

const escapeHtml = (str) => String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

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

    // 2. Tutup Flash Alert dengan Animasi Halus
    document.querySelectorAll('[data-close-alert]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const alertBox = btn.closest('[data-alert]');
            if (!alertBox) return;
            alertBox.classList.add('opacity-0', '-translate-y-1', 'pointer-events-none');
            window.setTimeout(() => alertBox.remove(), 200);
        });
    });

    // 3. Toggle Tampilkan/Sembunyikan Password & Loading State Anti Double-Submit (Halaman Login)
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

    const loginForm = document.getElementById('login-form');
    const loginSubmitBtn = document.getElementById('btn-login-submit');
    const loginSpinner = document.getElementById('login-spinner');
    const loginBtnText = document.getElementById('login-btn-text');
    const loginArrow = document.getElementById('login-arrow');

    if (loginForm && loginSubmitBtn) {
        const resetLoginButton = () => {
            loginSubmitBtn.disabled = false;
            loginSpinner?.classList.add('hidden');
            loginArrow?.classList.remove('hidden');
            if (loginBtnText) loginBtnText.textContent = 'Masuk';
        };

        loginForm.addEventListener('submit', (event) => {
            if (loginSubmitBtn.disabled) {
                event.preventDefault();
                return;
            }
            if (!loginForm.checkValidity()) return;

            loginSubmitBtn.disabled = true;
            loginSpinner?.classList.remove('hidden');
            loginArrow?.classList.add('hidden');
            if (loginBtnText) loginBtnText.textContent = 'Memproses...';
        });

        window.addEventListener('pageshow', resetLoginButton);
    }

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

    // 7. Konfirmasi Logout Portal Mahasiswa (SweetAlert2 Lokal via NPM)
    document.querySelectorAll('form[data-confirm-logout]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            Swal.fire({
                title: 'Keluar dari Portal Mahasiswa?',
                text: 'Sesi login Anda akan diakhiri. Anda dapat masuk kembali kapan saja menggunakan NIM dan password Anda.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // 8. Dashboard Portal Mahasiswa (Tema Biru Modern): Animasi Progress + KPI Clickable + Filter Instan
    const mhsDashboardRoot = document.getElementById('mahasiswa-dashboard-root');
    if (mhsDashboardRoot) {
        const circularEl = document.getElementById('mhs-circular-progress');
        const linearEl = document.getElementById('mhs-linear-progress');
        const circumference = 2 * Math.PI * 42;

        window.requestAnimationFrame(() => {
            if (circularEl) {
                const targetPct = Math.min(100, Math.max(0, Number(circularEl.dataset.targetPercent || 0)));
                const offset = circumference - (targetPct / 100) * circumference;
                circularEl.style.strokeDashoffset = String(offset.toFixed(2));
            }
            if (linearEl) {
                const targetWidth = Math.min(100, Math.max(0, Number(linearEl.dataset.targetWidth || 0)));
                linearEl.style.width = `${targetWidth}%`;
            }
        });

        const mhsKtSection = document.getElementById('mahasiswa-ketuntasan-section');
        const kpiCards = Array.from(mhsDashboardRoot.querySelectorAll('.mhs-kpi-card'));
        const items = Array.from(mhsDashboardRoot.querySelectorAll('.mhs-kt-item'));
        const statusTabs = Array.from(mhsDashboardRoot.querySelectorAll('.mhs-status-tab'));
        const selectType = document.getElementById('mhs-filter-type');
        const searchInput = document.getElementById('mhs-search-quisioner');
        const emptyBox = document.getElementById('mhs-kt-empty-filter');
        const emptyTitle = document.getElementById('mhs-empty-title');
        const emptyDesc = document.getElementById('mhs-empty-desc');
        const resetFilterBtn = document.getElementById('mhs-reset-filter-btn');
        const visibleCounterEl = document.getElementById('mhs-visible-counter');
        const countAllEl = document.getElementById('mhs-count-all');
        const countBelumEl = document.getElementById('mhs-count-belum');
        const countTuntasEl = document.getElementById('mhs-count-tuntas');

        let activeStatus = '';

        const updateKpiCardStyles = () => {
            kpiCards.forEach((card) => {
                const val = card.dataset.kpiFilter ?? '';
                const isActive = val === activeStatus;
                card.setAttribute('aria-pressed', String(isActive));

                card.classList.remove(
                    'border-2',
                    'border-[#2563EB]',
                    'border-emerald-500',
                    'border-amber-500',
                    'ring-2',
                    'ring-[#2563EB]/15',
                    'ring-emerald-400/20',
                    'ring-amber-400/25',
                    'border',
                    'border-blue-100',
                    'border-amber-200/80'
                );

                if (isActive) {
                    if (val === '0') {
                        card.classList.add('border-2', 'border-amber-500', 'ring-2', 'ring-amber-400/25');
                    } else if (val === '1') {
                        card.classList.add('border-2', 'border-emerald-500', 'ring-2', 'ring-emerald-400/20');
                    } else {
                        card.classList.add('border-2', 'border-[#2563EB]', 'ring-2', 'ring-[#2563EB]/15');
                    }
                } else if (val === '0') {
                    card.classList.add('border', 'border-amber-200/80');
                } else {
                    card.classList.add('border', 'border-blue-100');
                }
            });
        };

        const updateTabStyles = () => {
            statusTabs.forEach((btn) => {
                const val = btn.dataset.mhsStatus ?? '';
                const isActive = val === activeStatus;
                const badge = btn.querySelector('span[id^="mhs-count-"]');
                btn.setAttribute('aria-pressed', String(isActive));

                if (val === '') {
                    btn.className = isActive
                        ? 'mhs-status-tab inline-flex items-center gap-1.5 rounded-full bg-[#0F294A] px-3.5 py-1.5 text-xs font-semibold text-white shadow-2xs transition'
                        : 'mhs-status-tab inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-[#0F294A] transition hover:bg-blue-50';
                    if (badge) {
                        badge.className = isActive
                            ? 'rounded-full bg-white/20 px-2 py-0.5 text-[11px] font-bold text-white tabular-nums'
                            : 'rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-bold text-[#1E3A8A] tabular-nums';
                    }
                } else if (val === '1') {
                    btn.className = isActive
                        ? 'mhs-status-tab inline-flex items-center gap-1.5 rounded-full bg-emerald-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-2xs transition'
                        : 'mhs-status-tab inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50';
                    if (badge) {
                        badge.className = isActive
                            ? 'rounded-full bg-white/20 px-2 py-0.5 text-[11px] font-bold text-white tabular-nums'
                            : 'rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700 tabular-nums';
                    }
                } else {
                    btn.className = isActive
                        ? 'mhs-status-tab inline-flex items-center gap-1.5 rounded-full bg-amber-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-2xs transition'
                        : 'mhs-status-tab inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-50';
                    if (badge) {
                        badge.className = isActive
                            ? 'rounded-full bg-white/20 px-2 py-0.5 text-[11px] font-bold text-white tabular-nums'
                            : 'rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-700 tabular-nums';
                    }
                }
            });
        };

        const applyFilters = () => {
            const typeVal = (selectType?.value || '').toLowerCase();
            const keyword = (searchInput?.value || '').trim().toLowerCase();

            let baseTotal = 0;
            let baseTuntas = 0;
            let baseBelum = 0;
            let visibleCount = 0;

            items.forEach((card) => {
                const itemStatus = card.dataset.status || '0';
                const itemType = card.dataset.type || '';
                const itemSearch = card.dataset.search || '';

                const matchBase =
                    (typeVal === '' || itemType === typeVal) &&
                    (keyword === '' || itemSearch.includes(keyword));

                if (matchBase) {
                    baseTotal += 1;
                    if (itemStatus === '1') baseTuntas += 1;
                    else baseBelum += 1;
                }

                const matchAll = matchBase && (activeStatus === '' || itemStatus === activeStatus);
                card.classList.toggle('hidden', !matchAll);
                if (matchAll) visibleCount += 1;
            });

            if (countAllEl) countAllEl.textContent = formatNumber(baseTotal);
            if (countTuntasEl) countTuntasEl.textContent = formatNumber(baseTuntas);
            if (countBelumEl) countBelumEl.textContent = formatNumber(baseBelum);
            if (visibleCounterEl) visibleCounterEl.textContent = `Menampilkan ${formatNumber(visibleCount)} item`;

            if (emptyBox && items.length > 0) {
                const showEmpty = visibleCount === 0;
                emptyBox.classList.toggle('hidden', !showEmpty);
                emptyBox.classList.toggle('flex', showEmpty);

                if (showEmpty && emptyTitle && emptyDesc) {
                    if (activeStatus === '0' && keyword === '' && typeVal === '' && baseBelum === 0) {
                        emptyTitle.textContent = 'Tidak ada tanggungan quisioner yang tersisa!';
                        emptyDesc.textContent = 'Semua kuisioner dan EDOM Anda sudah selesai diisi dengan status Tuntas.';
                    } else {
                        emptyTitle.textContent = 'Tidak ada quisioner yang sesuai filter';
                        emptyDesc.textContent = 'Coba ubah kata kunci pencarian, tipe quisioner, atau tab status untuk melihat daftar lainnya.';
                    }
                }
            }
        };

        const setStatusFilter = (nextStatus, scrollToPanel = false) => {
            activeStatus = nextStatus;
            updateKpiCardStyles();
            updateTabStyles();
            applyFilters();

            if (scrollToPanel && mhsKtSection) {
                mhsKtSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        };

        kpiCards.forEach((card) => {
            card.addEventListener('click', () => {
                setStatusFilter(card.dataset.kpiFilter ?? '', true);
            });
        });

        statusTabs.forEach((btn) => {
            btn.addEventListener('click', () => {
                setStatusFilter(btn.dataset.mhsStatus ?? '', false);
            });
        });

        selectType?.addEventListener('change', applyFilters);
        searchInput?.addEventListener('input', applyFilters);

        resetFilterBtn?.addEventListener('click', () => {
            if (selectType) selectType.value = '';
            if (searchInput) searchInput.value = '';
            setStatusFilter('', false);
        });

        updateKpiCardStyles();
        updateTabStyles();
    }

    // 9. Form Pengisian Quisioner Mahasiswa: Sticky Progress Tracker + Validasi Visual + SweetAlert2
    const mhsQuisionerForm = document.getElementById('mahasiswa-quisioner-form');
    if (mhsQuisionerForm) {
        const questionCards = Array.from(mhsQuisionerForm.querySelectorAll('.mhs-question-card'));
        const totalQuestions = questionCards.length;
        const progressTextEl = document.getElementById('mhs-form-progress-text');
        const progressBarEl = document.getElementById('mhs-form-progress-bar');
        const remainingBadgeEl = document.getElementById('mhs-form-remaining-badge');

        const isCardAnswered = (card) => {
            if (card.dataset.questionType === 'pilihan') {
                return Boolean(card.querySelector('input[type="radio"]:checked'));
            }
            const textarea = card.querySelector('textarea');
            return Boolean(textarea && textarea.value.trim().length > 0);
        };

        const setCardErrorState = (card, hasError) => {
            const badge = card.querySelector('.mhs-unanswered-badge');
            card.classList.toggle('bg-rose-50/50', hasError);
            card.classList.toggle('ring-2', hasError);
            card.classList.toggle('ring-inset', hasError);
            card.classList.toggle('ring-rose-400', hasError);
            if (badge) {
                badge.classList.toggle('hidden', !hasError);
                badge.classList.toggle('inline-flex', hasError);
            }
        };

        const setCardAnsweredState = (card, isAnswered) => {
            const answeredBadge = card.querySelector('.mhs-answered-badge');
            const numberBadge = card.querySelector('.mhs-question-number');

            card.classList.toggle('border-l-[#2563EB]', isAnswered);
            card.classList.toggle('border-l-transparent', !isAnswered);
            card.classList.toggle('bg-blue-50/20', isAnswered);

            if (answeredBadge) {
                answeredBadge.classList.toggle('hidden', !isAnswered);
                answeredBadge.classList.toggle('inline-flex', isAnswered);
            }

            if (numberBadge) {
                numberBadge.classList.toggle('bg-[#2563EB]', isAnswered);
                numberBadge.classList.toggle('text-white', isAnswered);
                numberBadge.classList.toggle('ring-[#2563EB]', isAnswered);
                numberBadge.classList.toggle('bg-blue-50', !isAnswered);
                numberBadge.classList.toggle('text-[#2563EB]', !isAnswered);
                numberBadge.classList.toggle('ring-blue-200/80', !isAnswered);
            }
        };

        const updateFormProgress = () => {
            let answered = 0;
            questionCards.forEach((card) => {
                const ok = isCardAnswered(card);
                setCardAnsweredState(card, ok);
                if (ok) {
                    answered += 1;
                    setCardErrorState(card, false);
                }
            });

            const pct = totalQuestions > 0 ? Math.round((answered / totalQuestions) * 100) : 0;
            const remaining = Math.max(0, totalQuestions - answered);

            if (progressTextEl) {
                progressTextEl.textContent = `${answered} dari ${totalQuestions} Pertanyaan Terjawab — ${pct}%`;
            }
            if (progressBarEl) {
                progressBarEl.style.width = `${pct}%`;
            }
            if (remainingBadgeEl) {
                if (remaining === 0 && totalQuestions > 0) {
                    remainingBadgeEl.textContent = 'Semua soal terjawab';
                    remainingBadgeEl.className = 'inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800';
                } else {
                    remainingBadgeEl.textContent = `Tersisa ${remaining} soal`;
                    remainingBadgeEl.className = 'inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800 tabular-nums';
                }
            }
        };

        questionCards.forEach((card) => {
            card.querySelectorAll('input[type="radio"]').forEach((radio) => {
                radio.addEventListener('change', updateFormProgress);
            });

            const textarea = card.querySelector('.mhs-answer-textarea');
            const counter = card.querySelector('.mhs-char-count');
            if (textarea) {
                const syncCharCount = () => {
                    if (counter) {
                        counter.textContent = `${formatNumber(textarea.value.length)} / 2.000 karakter`;
                    }
                    updateFormProgress();
                };
                textarea.addEventListener('input', syncCharCount);
            }
        });

        updateFormProgress();

        mhsQuisionerForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const unansweredCards = questionCards.filter((card) => !isCardAnswered(card));
            if (unansweredCards.length > 0) {
                unansweredCards.forEach((card) => setCardErrorState(card, true));

                const firstMissing = unansweredCards[0];
                firstMissing.scrollIntoView({ behavior: 'smooth', block: 'center' });

                await Swal.fire({
                    title: 'Jawaban Belum Lengkap',
                    text: `Masih ada ${unansweredCards.length} pertanyaan yang belum terjawab. Silakan lengkapi pertanyaan yang disorot merah.`,
                    icon: 'warning',
                    confirmButtonColor: '#2563EB',
                    confirmButtonText: 'Lengkapi Sekarang',
                });
                return;
            }

            const title = mhsQuisionerForm.dataset.quisionerTitle || 'Quisioner ini';
            const confirmResult = await Swal.fire({
                title: 'Kirim Jawaban Sekarang?',
                text: `Seluruh ${totalQuestions} jawaban pada "${title}" akan disimpan dan tidak dapat diubah setelah dikirim.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Kirim Jawaban',
                cancelButtonText: 'Periksa Kembali',
                reverseButtons: true,
            });

            if (confirmResult.isConfirmed) {
                mhsQuisionerForm.submit();
            }
        });
    }

    // 10. Toggle Panel Filter Lanjutan & Auto-Submit Per-Page (Halaman Monitoring Ketuntasan Admin)
    document.querySelectorAll('select[data-auto-submit]').forEach((selectEl) => {
        selectEl.addEventListener('change', () => {
            selectEl.closest('form')?.submit();
        });
    });

    const toggleAdvancedBtn = document.getElementById('btn-toggle-advanced-filter');
    const advancedPanel = document.getElementById('panel-advanced-filter');
    const advancedChevron = document.getElementById('icon-advanced-chevron');
    if (toggleAdvancedBtn && advancedPanel) {
        toggleAdvancedBtn.addEventListener('click', () => {
            const isHidden = advancedPanel.classList.toggle('hidden');
            toggleAdvancedBtn.setAttribute('aria-expanded', String(!isHidden));
            advancedChevron?.classList.toggle('rotate-180', !isHidden);
        });
    }

    // 11. Halaman Detail Rekap Mahasiswa: Tab Per-Quisioner, Filter Cepat, Lihat Jawaban & Cetak Detail
    const detailRoot = document.getElementById('admin-ketuntasan-detail-root');
    if (detailRoot) {
        detailRoot.querySelectorAll('[data-print-detail]').forEach((btn) => {
            btn.addEventListener('click', () => window.print());
        });

        const rincianSection = document.getElementById('rincian-jawaban-section');
        const tabButtons = Array.from(detailRoot.querySelectorAll('.ans-quisioner-tab'));
        const summaryGroups = Array.from(detailRoot.querySelectorAll('.ans-kategori-summary-group'));
        const tipeButtons = Array.from(detailRoot.querySelectorAll('.ans-tipe-btn'));
        const kategoriSelect = document.getElementById('ans-filter-kategori');
        const rows = Array.from(detailRoot.querySelectorAll('.ans-row'));
        const emptyFilterRow = document.getElementById('ans-empty-filter-row');
        const visibleCounter = document.getElementById('ans-visible-counter');

        let activeTab = 'all';
        let activeTipe = '';

        const syncKategoriOptions = (tabBtn) => {
            if (!kategoriSelect || !tabBtn) return;
            let cats = [];
            try {
                cats = JSON.parse(tabBtn.dataset.ansCategories || '[]');
            } catch {
                cats = [];
            }
            const currentVal = kategoriSelect.value;
            kategoriSelect.innerHTML = '<option value="">Semua Kategori</option>';
            cats.forEach((cat) => {
                const opt = document.createElement('option');
                opt.value = cat;
                opt.textContent = cat;
                kategoriSelect.appendChild(opt);
            });
            kategoriSelect.value = cats.includes(currentVal) ? currentVal : '';
        };

        const updateTabUi = () => {
            tabButtons.forEach((btn) => {
                const isActive = (btn.dataset.ansTab || 'all') === activeTab;
                const badge = btn.querySelector('.ans-tab-count');
                btn.setAttribute('aria-pressed', String(isActive));
                btn.className = isActive
                    ? 'ans-quisioner-tab inline-flex items-center gap-1.5 rounded-full bg-[#062419] px-3.5 py-1.5 text-xs font-semibold text-white shadow-2xs transition'
                    : 'ans-quisioner-tab inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-[#062419] transition hover:bg-emerald-50';
                if (badge) {
                    badge.className = isActive
                        ? 'ans-tab-count rounded-full bg-white/20 px-2 py-0.5 text-[11px] font-bold text-white tabular-nums'
                        : 'ans-tab-count rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-[#0B6B3A] tabular-nums';
                }
            });

            summaryGroups.forEach((grp) => {
                grp.classList.toggle('hidden', (grp.dataset.summaryTab || 'all') !== activeTab);
            });
        };

        const updateTipeUi = () => {
            tipeButtons.forEach((btn) => {
                const isActive = (btn.dataset.ansTipe ?? '') === activeTipe;
                btn.className = isActive
                    ? 'ans-tipe-btn rounded-full bg-[#20A464] px-3 py-1 text-xs font-semibold text-white transition'
                    : 'ans-tipe-btn rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 transition hover:bg-emerald-50';
            });
        };

        const applyDetailFilters = () => {
            const katVal = kategoriSelect?.value || '';
            let tabTotal = 0;
            let visible = 0;

            rows.forEach((row) => {
                const matchTab = activeTab === 'all' || (row.dataset.quisionerId || '') === activeTab;
                if (matchTab) tabTotal += 1;

                const matchKat = katVal === '' || (row.dataset.kategori || '') === katVal;
                const matchTipe = activeTipe === '' || (row.dataset.tipe || '') === activeTipe;
                const show = matchTab && matchKat && matchTipe;

                row.classList.toggle('hidden', !show);
                if (show) {
                    visible += 1;
                    const noEl = row.querySelector('.ans-row-no');
                    if (noEl) noEl.textContent = String(visible);
                }
            });

            if (visibleCounter) {
                visibleCounter.textContent = `Menampilkan ${formatNumber(visible)} dari ${formatNumber(tabTotal)} jawaban`;
            }
            if (emptyFilterRow && rows.length > 0) {
                emptyFilterRow.classList.toggle('hidden', visible > 0);
            }
        };

        const activateTab = (tabKey) => {
            const targetBtn = tabButtons.find((b) => (b.dataset.ansTab || 'all') === tabKey);
            if (!targetBtn) return;
            activeTab = tabKey;
            syncKategoriOptions(targetBtn);
            updateTabUi();
            applyDetailFilters();
        };

        tabButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                activateTab(btn.dataset.ansTab || 'all');
            });
        });

        tipeButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                activeTipe = btn.dataset.ansTipe ?? '';
                updateTipeUi();
                applyDetailFilters();
            });
        });

        kategoriSelect?.addEventListener('change', applyDetailFilters);

        // Tombol "Lihat Jawaban" pada tabel Rekap Per-Quisioner -> auto-select tab & smooth scroll
        detailRoot.querySelectorAll('[data-jump-quisioner]').forEach((jumpBtn) => {
            jumpBtn.addEventListener('click', () => {
                const qId = jumpBtn.dataset.jumpQuisioner || 'all';
                if (kategoriSelect) kategoriSelect.value = '';
                activeTipe = '';
                updateTipeUi();
                activateTab(qId);
                rincianSection?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    // 12. Halaman Tambah Jatah Mata Kuliah (2 Mode Interaktif: API SIAMDOS + Manual Searchable Combobox)
    const jmRoot = document.getElementById('admin-jatah-makul-create');
    if (jmRoot) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const fetchUrl = jmRoot.dataset.fetchUrl || '';
        const storeApiUrl = jmRoot.dataset.storeApiUrl || '';
        const massalUrl = jmRoot.dataset.massalUrl || '';
        const indexUrl = jmRoot.dataset.indexUrl || '';
        const totalStudents = Number(jmRoot.dataset.totalStudents || 0);
        const preselectedNim = (jmRoot.dataset.preselectedNim || '').trim();

        const safeJsonParse = (str, fallback = []) => {
            try {
                return JSON.parse(str || '[]');
            } catch {
                return fallback;
            }
        };

        const mahasiswas = safeJsonParse(jmRoot.dataset.mahasiswas);
        const dosens = safeJsonParse(jmRoot.dataset.dosens);
        const makuls = safeJsonParse(jmRoot.dataset.makuls);

        // 12a. Segmented Tab Switcher (API vs Manual)
        const modeTabs = Array.from(jmRoot.querySelectorAll('.jm-mode-tab'));
        const panelApi = document.getElementById('jm-panel-api');
        const panelManual = document.getElementById('jm-panel-manual');

        const switchModeTab = (tabName) => {
            const isApi = tabName === 'api';
            panelApi?.classList.toggle('hidden', !isApi);
            panelManual?.classList.toggle('hidden', isApi);

            modeTabs.forEach((btn) => {
                const active = btn.dataset.jmTab === tabName;
                btn.setAttribute('aria-selected', String(active));
                btn.className = active
                    ? 'jm-mode-tab inline-flex items-center justify-center gap-2 rounded-xl bg-[#20A464] px-4 py-2.5 text-xs font-bold text-white shadow-md transition-all duration-200'
                    : 'jm-mode-tab inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold text-emerald-100/80 transition-all duration-200 hover:text-white';
            });
        };

        modeTabs.forEach((btn) => {
            btn.addEventListener('click', () => switchModeTab(btn.dataset.jmTab || 'api'));
        });

        // 12b. Reusable Searchable Combobox Factory
        const createSearchableCombobox = ({
            containerSelector,
            inputEl,
            items,
            filterFn,
            renderOptionFn,
            onSelect,
            onClear,
            footerActionHtml = '',
            onFooterAction = null,
        }) => {
            const container = jmRoot.querySelector(containerSelector);
            if (!container || !inputEl) return null;

            const listEl = container.querySelector('[data-combo-list]');
            const clearBtn = container.querySelector('[data-combo-clear]');

            const openList = () => listEl?.classList.remove('hidden');
            const closeList = () => listEl?.classList.add('hidden');

            const syncClearBtn = () => {
                if (!clearBtn) return;
                const hasVal = inputEl.value.trim().length > 0;
                clearBtn.classList.toggle('hidden', !hasVal);
                clearBtn.classList.toggle('flex', hasVal);
            };

            const renderList = (keyword = '') => {
                if (!listEl) return;
                const q = keyword.trim().toLowerCase();
                const matched = (q === '' ? items : items.filter((item) => filterFn(item, q))).slice(0, 40);

                if (matched.length === 0) {
                    listEl.innerHTML = `
                        <div class="px-3 py-3 text-center text-xs text-slate-400">Tidak ditemukan data yang cocok.</div>
                        ${footerActionHtml}
                    `;
                } else {
                    listEl.innerHTML = matched.map((item, idx) => renderOptionFn(item, idx)).join('') + footerActionHtml;
                    listEl.querySelectorAll('[data-combo-idx]').forEach((optBtn) => {
                        optBtn.addEventListener('click', () => {
                            const idx = Number(optBtn.dataset.comboIdx);
                            const picked = matched[idx];
                            if (picked) {
                                onSelect(picked);
                                syncClearBtn();
                                closeList();
                            }
                        });
                    });
                }

                if (onFooterAction) {
                    listEl.querySelector('[data-combo-footer-btn]')?.addEventListener('click', () => {
                        onFooterAction(inputEl.value.trim());
                        closeList();
                    });
                }
            };

            inputEl.addEventListener('focus', () => {
                renderList(inputEl.value);
                openList();
            });

            inputEl.addEventListener('input', () => {
                syncClearBtn();
                renderList(inputEl.value);
                openList();
            });

            clearBtn?.addEventListener('click', () => {
                inputEl.value = '';
                syncClearBtn();
                onClear?.();
                inputEl.focus();
            });

            document.addEventListener('click', (event) => {
                if (!container.contains(event.target)) {
                    closeList();
                }
            });

            syncClearBtn();
            return { syncClearBtn, closeList };
        };

        // Helper tampilkan badge mahasiswa
        const renderMahasiswaBadge = (badgeEl, mhs, onReset) => {
            if (!badgeEl) return;
            if (!mhs) {
                badgeEl.classList.add('hidden');
                badgeEl.classList.remove('flex');
                badgeEl.innerHTML = '';
                return;
            }
            badgeEl.innerHTML = `
                <div class="flex min-w-0 items-center gap-2.5">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#20A464] text-xs font-bold text-white">
                        ${escapeHtml((mhs.nama || mhs.nim).charAt(0).toUpperCase())}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate font-bold text-[#062419]">${escapeHtml(mhs.nama)}</p>
                        <p class="font-mono text-[11px] font-semibold text-[#0B6B3A]">NIM: ${escapeHtml(mhs.nim)}</p>
                    </div>
                </div>
                <button type="button" data-badge-clear class="shrink-0 text-[11px] font-semibold text-slate-400 hover:text-rose-600">Ganti</button>
            `;
            badgeEl.classList.remove('hidden');
            badgeEl.classList.add('flex');
            badgeEl.querySelector('[data-badge-clear]')?.addEventListener('click', onReset);
        };

        // Combobox 1: Mahasiswa (Mode API)
        const apiNimHidden = document.getElementById('jm-api-nim');
        const apiNimSearch = document.getElementById('jm-api-nim-search');
        const apiNimBadge = document.getElementById('jm-api-nim-badge');
        let selectedApiMhs = null;

        const setApiMahasiswa = (mhs) => {
            selectedApiMhs = mhs;
            if (apiNimHidden) apiNimHidden.value = mhs ? mhs.nim : '';
            if (apiNimSearch) apiNimSearch.value = mhs ? `${mhs.nim} — ${mhs.nama}` : '';
            renderMahasiswaBadge(apiNimBadge, mhs, () => {
                setApiMahasiswa(null);
                apiNimSearch?.focus();
            });
        };

        createSearchableCombobox({
            containerSelector: '[data-combo="api-mahasiswa"]',
            inputEl: apiNimSearch,
            items: mahasiswas,
            filterFn: (m, q) => m.nim.toLowerCase().includes(q) || m.nama.toLowerCase().includes(q),
            renderOptionFn: (m, idx) => `
                <button type="button" data-combo-idx="${idx}" class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-xs transition hover:bg-emerald-50">
                    <span class="truncate font-semibold text-[#062419]">${escapeHtml(m.nama)}</span>
                    <span class="shrink-0 rounded bg-emerald-50 px-1.5 py-0.5 font-mono text-[11px] font-bold text-[#0B6B3A]">${escapeHtml(m.nim)}</span>
                </button>
            `,
            onSelect: (m) => setApiMahasiswa(m),
            onClear: () => setApiMahasiswa(null),
        });

        // Combobox 2: Mahasiswa (Mode Manual)
        const manualNimHidden = document.getElementById('jm-manual-nim');
        const manualNimSearch = document.getElementById('jm-manual-nim-search');
        const manualNimBadge = document.getElementById('jm-manual-nim-badge');

        const setManualMahasiswa = (mhs) => {
            if (manualNimHidden) manualNimHidden.value = mhs ? mhs.nim : '';
            if (manualNimSearch) manualNimSearch.value = mhs ? `${mhs.nim} — ${mhs.nama}` : '';
            renderMahasiswaBadge(manualNimBadge, mhs, () => {
                setManualMahasiswa(null);
                manualNimSearch?.focus();
            });
        };

        createSearchableCombobox({
            containerSelector: '[data-combo="manual-mahasiswa"]',
            inputEl: manualNimSearch,
            items: mahasiswas,
            filterFn: (m, q) => m.nim.toLowerCase().includes(q) || m.nama.toLowerCase().includes(q),
            renderOptionFn: (m, idx) => `
                <button type="button" data-combo-idx="${idx}" class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-xs transition hover:bg-emerald-50">
                    <span class="truncate font-semibold text-[#062419]">${escapeHtml(m.nama)}</span>
                    <span class="shrink-0 rounded bg-emerald-50 px-1.5 py-0.5 font-mono text-[11px] font-bold text-[#0B6B3A]">${escapeHtml(m.nim)}</span>
                </button>
            `,
            onSelect: (m) => setManualMahasiswa(m),
            onClear: () => setManualMahasiswa(null),
        });

        if (manualNimHidden?.value) {
            const matchedMhs = mahasiswas.find((m) => m.nim === manualNimHidden.value);
            if (matchedMhs) setManualMahasiswa(matchedMhs);
        }

        // Auto-preselect mahasiswa jika dibuka lewat tombol "+ Tambah" (?nim=...)
        if (preselectedNim) {
            const matchedPre = mahasiswas.find((m) => m.nim === preselectedNim);
            if (matchedPre) {
                setApiMahasiswa(matchedPre);
                if (!manualNimHidden?.value) {
                    setManualMahasiswa(matchedPre);
                }
            }
        }

        // Combobox 3: Mata Kuliah (Mode Manual + Tombol "+ Input Makul Baru")
        const manualMakulSearch = document.getElementById('jm-manual-makul-search');
        const manualMakulBadge = document.getElementById('jm-manual-makul-badge');
        const makulNewInputs = document.getElementById('jm-makul-new-inputs');
        const toggleNewMakulBtn = document.getElementById('jm-btn-toggle-new-makul');
        const kodeMakulInput = document.getElementById('kode_makul');
        const namaMakulInput = document.getElementById('nama_makul');

        const renderMakulBadge = (mk) => {
            if (!manualMakulBadge) return;
            if (!mk) {
                manualMakulBadge.classList.add('hidden');
                manualMakulBadge.classList.remove('flex');
                manualMakulBadge.innerHTML = '';
                return;
            }
            manualMakulBadge.innerHTML = `
                <div class="flex min-w-0 items-center gap-2.5">
                    <span class="rounded-lg bg-emerald-50 px-2 py-1 font-mono text-[11px] font-bold text-[#0B6B3A] ring-1 ring-emerald-200">
                        ${escapeHtml(mk.kode_makul)}
                    </span>
                    <span class="truncate font-bold text-[#062419]">${escapeHtml(mk.nama_makul)}</span>
                </div>
                <button type="button" data-makul-clear class="shrink-0 text-[11px] font-semibold text-slate-400 hover:text-rose-600">Ganti</button>
            `;
            manualMakulBadge.classList.remove('hidden');
            manualMakulBadge.classList.add('flex');
            manualMakulBadge.querySelector('[data-makul-clear]')?.addEventListener('click', () => {
                if (kodeMakulInput) kodeMakulInput.value = '';
                if (namaMakulInput) namaMakulInput.value = '';
                if (manualMakulSearch) manualMakulSearch.value = '';
                renderMakulBadge(null);
                manualMakulSearch?.focus();
            });
        };

        const openNewMakulForm = (prefillText = '') => {
            makulNewInputs?.classList.remove('hidden');
            if (prefillText && namaMakulInput && !namaMakulInput.value) {
                namaMakulInput.value = prefillText;
            }
            kodeMakulInput?.focus();
        };

        toggleNewMakulBtn?.addEventListener('click', () => {
            const isHidden = makulNewInputs?.classList.toggle('hidden');
            if (!isHidden) kodeMakulInput?.focus();
        });

        createSearchableCombobox({
            containerSelector: '[data-combo="manual-makul"]',
            inputEl: manualMakulSearch,
            items: makuls,
            filterFn: (mk, q) => mk.kode_makul.toLowerCase().includes(q) || mk.nama_makul.toLowerCase().includes(q),
            renderOptionFn: (mk, idx) => `
                <button type="button" data-combo-idx="${idx}" class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-xs transition hover:bg-emerald-50">
                    <span class="truncate font-semibold text-[#062419]">${escapeHtml(mk.nama_makul)}</span>
                    <span class="shrink-0 rounded bg-emerald-50 px-1.5 py-0.5 font-mono text-[11px] font-bold text-[#0B6B3A]">${escapeHtml(mk.kode_makul)}</span>
                </button>
            `,
            onSelect: (mk) => {
                if (kodeMakulInput) kodeMakulInput.value = mk.kode_makul;
                if (namaMakulInput) namaMakulInput.value = mk.nama_makul;
                if (manualMakulSearch) manualMakulSearch.value = `${mk.kode_makul} — ${mk.nama_makul}`;
                makulNewInputs?.classList.add('hidden');
                renderMakulBadge(mk);
            },
            onClear: () => {
                if (kodeMakulInput) kodeMakulInput.value = '';
                if (namaMakulInput) namaMakulInput.value = '';
                renderMakulBadge(null);
            },
            footerActionHtml: `
                <div class="mt-1 border-t border-emerald-100 pt-1">
                    <button type="button" data-combo-footer-btn class="flex w-full items-center justify-center gap-1 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-bold text-[#0B6B3A] transition hover:bg-[#20A464] hover:text-white">
                        + Input Mata Kuliah Baru
                    </button>
                </div>
            `,
            onFooterAction: (typed) => openNewMakulForm(typed),
        });

        // Combobox 4: Dosen Pengampu (Mode Manual)
        const manualNipyHidden = document.getElementById('nipy');
        const manualNamaDosenHidden = document.getElementById('nama_dosen');
        const manualDosenSearch = document.getElementById('jm-manual-dosen-search');
        const manualDosenBadge = document.getElementById('jm-manual-dosen-badge');

        const setManualDosen = (dosen) => {
            if (manualNipyHidden) manualNipyHidden.value = dosen ? dosen.nipy : '';
            if (manualNamaDosenHidden) manualNamaDosenHidden.value = dosen ? dosen.nama : '';
            if (manualDosenSearch) manualDosenSearch.value = dosen ? `${dosen.nama} (${dosen.nipy})` : '';

            if (!manualDosenBadge) return;
            if (!dosen) {
                manualDosenBadge.classList.add('hidden');
                manualDosenBadge.classList.remove('flex');
                manualDosenBadge.innerHTML = '';
                return;
            }
            manualDosenBadge.innerHTML = `
                <div class="flex min-w-0 items-center gap-2.5">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#0B6B3A] text-xs font-bold text-white">
                        ${escapeHtml((dosen.nama || dosen.nipy).charAt(0).toUpperCase())}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate font-bold text-[#062419]">${escapeHtml(dosen.nama)}</p>
                        <p class="font-mono text-[11px] font-semibold text-[#0B6B3A]">NIPY: ${escapeHtml(dosen.nipy)}</p>
                    </div>
                </div>
                <button type="button" data-dosen-clear class="shrink-0 text-[11px] font-semibold text-slate-400 hover:text-rose-600">Ganti</button>
            `;
            manualDosenBadge.classList.remove('hidden');
            manualDosenBadge.classList.add('flex');
            manualDosenBadge.querySelector('[data-dosen-clear]')?.addEventListener('click', () => {
                setManualDosen(null);
                manualDosenSearch?.focus();
            });
        };

        createSearchableCombobox({
            containerSelector: '[data-combo="manual-dosen"]',
            inputEl: manualDosenSearch,
            items: dosens,
            filterFn: (d, q) => d.nama.toLowerCase().includes(q) || d.nipy.toLowerCase().includes(q),
            renderOptionFn: (d, idx) => `
                <button type="button" data-combo-idx="${idx}" class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-xs transition hover:bg-emerald-50">
                    <span class="truncate font-semibold text-[#062419]">${escapeHtml(d.nama)}</span>
                    <span class="shrink-0 rounded bg-emerald-50 px-1.5 py-0.5 font-mono text-[11px] font-bold text-[#0B6B3A]">${escapeHtml(d.nipy)}</span>
                </button>
            `,
            onSelect: (d) => setManualDosen(d),
            onClear: () => setManualDosen(null),
        });

        if (manualNipyHidden?.value) {
            const matchedDosen = dosens.find((d) => d.nipy === manualNipyHidden.value);
            if (matchedDosen) setManualDosen(matchedDosen);
        }

        // 12c. Mode 1 — Toggle Single vs Generate Massal
        const toggleMassal = document.getElementById('jm-toggle-massal');
        const singleSection = document.getElementById('jm-api-single-section');
        const massalSection = document.getElementById('jm-api-massal-section');

        toggleMassal?.addEventListener('change', () => {
            const isMassal = toggleMassal.checked;
            singleSection?.classList.toggle('hidden', isMassal);
            massalSection?.classList.toggle('hidden', !isMassal);
        });

        // 12d. Mode 1A — Fetch & Preview Jadwal API SIAMDOS + Simpan Terpilih
        const fetchBtn = document.getElementById('jm-btn-fetch-api');
        const fetchSpinner = document.getElementById('jm-fetch-spinner');
        const fetchIcon = document.getElementById('jm-fetch-icon');
        const fetchBtnText = document.getElementById('jm-fetch-btn-text');
        const apiTaInput = document.getElementById('jm-api-ta');

        const previewContainer = document.getElementById('jm-preview-container');
        const identitasBox = document.getElementById('jm-identitas-box');
        const previewTableWrap = document.getElementById('jm-preview-table-wrap');
        const previewTbody = document.getElementById('jm-preview-tbody');
        const previewEmpty = document.getElementById('jm-preview-empty');
        const previewActions = document.getElementById('jm-preview-actions');
        const previewCountEl = document.getElementById('jm-preview-count');
        const previewSelectedInfo = document.getElementById('jm-preview-selected-info');
        const checkAllBox = document.getElementById('jm-check-all');
        const storeApiBtn = document.getElementById('jm-btn-store-api');
        const storeApiText = document.getElementById('jm-store-api-text');
        const switchManualFromEmptyBtn = document.getElementById('jm-switch-manual-from-empty');

        let fetchedItems = [];
        let fetchedContext = { nim: '', tahun_ajaran: '', semester: 'ganjil' };

        const syncSelectedCount = () => {
            const checkboxes = Array.from(previewTbody?.querySelectorAll('.jm-row-check') || []);
            const checkedCount = checkboxes.filter((cb) => cb.checked).length;

            if (previewSelectedInfo) previewSelectedInfo.textContent = `${checkedCount} dipilih`;
            if (storeApiText) storeApiText.textContent = `Simpan Jadwal Terpilih (${checkedCount})`;
            if (storeApiBtn) storeApiBtn.disabled = checkedCount === 0;
            if (checkAllBox) {
                checkAllBox.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
                checkAllBox.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
            }
        };

        checkAllBox?.addEventListener('change', () => {
            previewTbody?.querySelectorAll('.jm-row-check').forEach((cb) => {
                cb.checked = checkAllBox.checked;
            });
            syncSelectedCount();
        });

        switchManualFromEmptyBtn?.addEventListener('click', () => {
            if (selectedApiMhs) setManualMahasiswa(selectedApiMhs);
            switchModeTab('manual');
        });

        fetchBtn?.addEventListener('click', async () => {
            const nim = (apiNimHidden?.value || '').trim();
            const tahunAjaran = (apiTaInput?.value || '').trim();
            const semester = jmRoot.querySelector('input[name="api_semester"]:checked')?.value || 'ganjil';

            if (!nim) {
                await Swal.fire({
                    title: 'Mahasiswa Belum Dipilih',
                    text: 'Silakan cari dan pilih mahasiswa terlebih dahulu sebelum menarik jadwal dari API.',
                    icon: 'warning',
                    confirmButtonColor: '#20A464',
                });
                apiNimSearch?.focus();
                return;
            }

            fetchBtn.disabled = true;
            fetchSpinner?.classList.remove('hidden');
            fetchIcon?.classList.add('hidden');
            if (fetchBtnText) fetchBtnText.textContent = 'Menarik Jadwal...';

            try {
                const response = await fetch(fetchUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ nim, tahun_ajaran: tahunAjaran, semester }),
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok || payload.status !== 'success') {
                    throw new Error(payload.message || `Gagal menarik jadwal (HTTP ${response.status}).`);
                }

                fetchedItems = Array.isArray(payload.items) ? payload.items : [];
                fetchedContext = { nim, tahun_ajaran: tahunAjaran, semester };

                const idt = payload.identitas || {};
                if (identitasBox) {
                    identitasBox.innerHTML = `
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-[#0B6B3A]">Identitas Mahasiswa (SIAMDOS)</p>
                                <p class="mt-1 text-sm font-extrabold text-[#062419]">${escapeHtml(idt.nama || selectedApiMhs?.nama || nim)} <span class="font-mono text-xs font-semibold text-[#0B6B3A]">(${escapeHtml(idt.nim || nim)})</span></p>
                                <p class="mt-0.5 text-xs text-slate-600">
                                    ${escapeHtml(idt.prodi || '-')} ${idt.fakultas ? `&middot; ${escapeHtml(idt.fakultas)}` : ''} ${idt.semester_ke ? `&middot; Semester ${escapeHtml(idt.semester_ke)}` : ''}
                                </p>
                            </div>
                            ${idt.tahun_ajaran ? `<span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-[#062419] ring-1 ring-emerald-200">Periode API: ${escapeHtml(idt.tahun_ajaran)}</span>` : ''}
                        </div>
                    `;
                }

                previewContainer?.classList.remove('hidden');
                if (previewCountEl) previewCountEl.textContent = String(fetchedItems.length);

                if (fetchedItems.length === 0) {
                    previewTableWrap?.classList.add('hidden');
                    previewActions?.classList.add('hidden');
                    previewEmpty?.classList.remove('hidden');
                    previewEmpty?.classList.add('flex');
                } else {
                    previewEmpty?.classList.add('hidden');
                    previewEmpty?.classList.remove('flex');
                    previewTableWrap?.classList.remove('hidden');
                    previewActions?.classList.remove('hidden');

                    if (previewTbody) {
                        previewTbody.innerHTML = fetchedItems.map((item, idx) => `
                            <tr class="transition hover:bg-emerald-50/40">
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" data-item-idx="${idx}" checked class="jm-row-check h-4 w-4 rounded border-slate-300 text-[#20A464] focus:ring-[#20A464]">
                                </td>
                                <td class="px-4 py-3 font-mono text-xs font-bold text-[#0B6B3A]">${escapeHtml(item.kode_makul)}</td>
                                <td class="px-4 py-3 font-semibold text-[#062419]">${escapeHtml(item.nama_makul)}</td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-600">${escapeHtml(item.nipy)}</td>
                                <td class="px-4 py-3 text-slate-700">${escapeHtml(item.nama_dosen)}</td>
                                <td class="px-4 py-3 text-center">
                                    ${item.already_exists
                                        ? '<span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-bold text-amber-700 ring-1 ring-amber-200">Sudah Ada</span>'
                                        : '<span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-200">Baru</span>'
                                    }
                                </td>
                            </tr>
                        `).join('');

                        previewTbody.querySelectorAll('.jm-row-check').forEach((cb) => {
                            cb.addEventListener('change', syncSelectedCount);
                        });
                    }
                    syncSelectedCount();
                }
            } catch (err) {
                await Swal.fire({
                    title: 'Gagal Menarik Jadwal',
                    text: err?.message || 'Terjadi kesalahan saat menghubungi API SIAMDOS.',
                    icon: 'error',
                    confirmButtonColor: '#0B6B3A',
                });
            } finally {
                fetchBtn.disabled = false;
                fetchSpinner?.classList.add('hidden');
                fetchIcon?.classList.remove('hidden');
                if (fetchBtnText) fetchBtnText.textContent = 'Tarik & Preview Jadwal dari API';
            }
        });

        storeApiBtn?.addEventListener('click', async () => {
            const selectedItems = [];
            previewTbody?.querySelectorAll('.jm-row-check:checked').forEach((cb) => {
                const idx = Number(cb.dataset.itemIdx);
                if (fetchedItems[idx]) selectedItems.push(fetchedItems[idx]);
            });

            if (selectedItems.length === 0) return;

            storeApiBtn.disabled = true;
            Swal.fire({
                title: 'Menyimpan Jadwal...',
                text: `Sedang menyimpan ${selectedItems.length} mata kuliah ke tb_jatah_makul.`,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading(),
            });

            try {
                const response = await fetch(storeApiUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        nim: fetchedContext.nim,
                        tahun_ajaran: fetchedContext.tahun_ajaran,
                        semester: fetchedContext.semester,
                        items: selectedItems,
                    }),
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok || payload.status !== 'success') {
                    throw new Error(payload.message || `Gagal menyimpan jadwal (HTTP ${response.status}).`);
                }

                const d = payload.data || {};
                const nextAction = await Swal.fire({
                    title: 'Jadwal Berhasil Disimpan',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonColor: '#20A464',
                    cancelButtonColor: '#0B6B3A',
                    confirmButtonText: 'Lihat Daftar Jatah Makul',
                    cancelButtonText: 'Tarik Mahasiswa Lain',
                    html: `
                        <div class="mt-2 space-y-1.5 text-left text-sm">
                            <div class="flex justify-between border-b border-slate-100 py-1"><span>Jadwal Baru Ditambahkan</span><strong class="text-emerald-700">${formatNumber(d.inserted)}</strong></div>
                            <div class="flex justify-between py-1"><span>Jadwal Diperbarui</span><strong class="text-amber-600">${formatNumber(d.updated)}</strong></div>
                        </div>
                    `,
                });

                if (nextAction.isConfirmed && indexUrl) {
                    window.location.href = indexUrl;
                } else {
                    fetchBtn?.click();
                }
            } catch (err) {
                await Swal.fire({
                    title: 'Gagal Menyimpan',
                    text: err?.message || 'Terjadi kesalahan saat menyimpan jadwal.',
                    icon: 'error',
                    confirmButtonColor: '#0B6B3A',
                });
            } finally {
                syncSelectedCount();
            }
        });

        // 12e. Mode 1B — Generate Massal Bertahap dengan Progress Bar
        const massalBtn = document.getElementById('jm-btn-massal-start');
        const massalTaInput = document.getElementById('jm-massal-ta');
        const massalProgressWrap = document.getElementById('jm-massal-progress-wrap');
        const massalStatus = document.getElementById('jm-massal-status');
        const massalPercent = document.getElementById('jm-massal-percent');
        const massalBar = document.getElementById('jm-massal-bar');
        const massalCounters = document.getElementById('jm-massal-counters');

        massalBtn?.addEventListener('click', async () => {
            const tahunAjaran = (massalTaInput?.value || '').trim();
            const semester = jmRoot.querySelector('input[name="massal_semester"]:checked')?.value || 'ganjil';

            if (!tahunAjaran) return;

            const confirmRes = await Swal.fire({
                title: 'Mulai Generate Massal?',
                text: `Sistem akan menarik jadwal dari API SIAMDOS untuk seluruh ${formatNumber(totalStudents)} mahasiswa pada TA ${tahunAjaran} (${semester}) secara bertahap.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#20A464',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Mulai Sekarang',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            });
            if (!confirmRes.isConfirmed) return;

            massalBtn.disabled = true;
            massalProgressWrap?.classList.remove('hidden');

            let offset = 0;
            const limit = 10;
            let done = false;
            const totals = { processed: 0, withSchedule: 0, empty: 0, failed: 0, inserted: 0, updated: 0 };

            try {
                while (!done) {
                    const response = await fetch(massalUrl, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ tahun_ajaran: tahunAjaran, semester, offset, limit }),
                    });

                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok || payload.status !== 'success') {
                        throw new Error(payload.message || `Batch gagal (HTTP ${response.status}).`);
                    }

                    const b = payload.batch || {};
                    totals.processed += Number(b.processed_students || 0);
                    totals.withSchedule += Number(b.students_with_schedule || 0);
                    totals.empty += Number(b.students_empty || 0);
                    totals.failed += Number(b.students_failed || 0);
                    totals.inserted += Number(b.inserted || 0);
                    totals.updated += Number(b.updated || 0);

                    const totalAll = Math.max(1, Number(payload.total_students || totalStudents || 1));
                    const pct = Math.min(100, Math.round((totals.processed / totalAll) * 100));

                    if (massalStatus) massalStatus.textContent = `Memproses ${formatNumber(totals.processed)} dari ${formatNumber(totalAll)} mahasiswa...`;
                    if (massalPercent) massalPercent.textContent = `${pct}%`;
                    if (massalBar) massalBar.style.width = `${pct}%`;
                    if (massalCounters) {
                        massalCounters.textContent = `Jadwal baru: ${formatNumber(totals.inserted)} · Diperbarui: ${formatNumber(totals.updated)} · Kosong: ${formatNumber(totals.empty)} · Gagal: ${formatNumber(totals.failed)}`;
                    }

                    offset = Number(payload.next_offset || (offset + limit));
                    done = Boolean(payload.done);
                }

                if (massalStatus) massalStatus.textContent = 'Generate massal selesai!';

                await Swal.fire({
                    title: 'Generate Massal Selesai',
                    icon: 'success',
                    confirmButtonColor: '#20A464',
                    html: `
                        <div class="mt-2 space-y-1.5 text-left text-sm">
                            <div class="flex justify-between border-b border-slate-100 py-1"><span>Mahasiswa Diproses</span><strong>${formatNumber(totals.processed)}</strong></div>
                            <div class="flex justify-between border-b border-slate-100 py-1"><span>Jadwal Baru Disimpan</span><strong class="text-emerald-700">${formatNumber(totals.inserted)}</strong></div>
                            <div class="flex justify-between border-b border-slate-100 py-1"><span>Jadwal Diperbarui</span><strong class="text-amber-600">${formatNumber(totals.updated)}</strong></div>
                            <div class="flex justify-between border-b border-slate-100 py-1"><span>Mahasiswa Jadwal Kosong</span><span>${formatNumber(totals.empty)}</span></div>
                            <div class="flex justify-between py-1"><span>Gagal Koneksi API</span><span class="text-rose-600">${formatNumber(totals.failed)}</span></div>
                        </div>
                    `,
                });
            } catch (err) {
                await Swal.fire({
                    title: 'Generate Massal Terhenti',
                    text: err?.message || 'Terjadi kesalahan saat menarik data batch SIAMDOS.',
                    icon: 'error',
                    confirmButtonColor: '#0B6B3A',
                });
            } finally {
                massalBtn.disabled = false;
            }
        });
    }

    // 13. Halaman Daftar Jatah Mata Kuliah Per-Mahasiswa (Expandable Accordion Row)
    const jmIndexRoot = document.getElementById('admin-jatah-makul-index');
    if (jmIndexRoot) {
        const triggers = Array.from(jmIndexRoot.querySelectorAll('[data-jm-accordion-trigger]'));
        const toggleAllBtn = document.getElementById('jm-toggle-all-btn');
        const toggleAllText = document.getElementById('jm-toggle-all-text');

        const setAccordionState = (trigger, open) => {
            const rowKey = trigger.dataset.jmAccordionTrigger;
            if (!rowKey) return;
            const panel = jmIndexRoot.querySelector(`[data-jm-accordion-panel="${rowKey}"]`);
            const parentRow = trigger.closest('.jm-parent-row');
            const chevron = trigger.querySelector('[data-jm-chevron]');

            trigger.setAttribute('aria-expanded', String(open));
            panel?.classList.toggle('hidden', !open);
            parentRow?.classList.toggle('bg-emerald-50/60', open);
            chevron?.classList.toggle('rotate-180', open);
        };

        const syncToggleAllLabel = () => {
            if (!toggleAllBtn || !toggleAllText || triggers.length === 0) return;
            const allOpen = triggers.every((btn) => btn.getAttribute('aria-expanded') === 'true');
            toggleAllBtn.dataset.expanded = String(allOpen);
            toggleAllText.textContent = allOpen ? 'Tutup Semua' : 'Buka Semua';
        };

        triggers.forEach((btn) => {
            btn.addEventListener('click', () => {
                const currentlyOpen = btn.getAttribute('aria-expanded') === 'true';
                setAccordionState(btn, !currentlyOpen);
                syncToggleAllLabel();
            });
        });

        toggleAllBtn?.addEventListener('click', () => {
            const shouldOpen = toggleAllBtn.dataset.expanded !== 'true';
            triggers.forEach((btn) => setAccordionState(btn, shouldOpen));
            syncToggleAllLabel();
        });
    }

    // 14. Halaman Hasil Laporan Quisioner (View Switcher Grid/Tabel + localStorage + Pencarian Instan)
    const laporanIndexRoot = document.getElementById('admin-laporan-index');
    if (laporanIndexRoot) {
        const storageKey = 'admin_laporan_view_mode';
        const viewGrid = document.getElementById('laporan-view-grid');
        const viewTable = document.getElementById('laporan-view-table');
        const viewButtons = Array.from(laporanIndexRoot.querySelectorAll('[data-laporan-view-btn]'));
        const searchInput = document.getElementById('laporan-search-input');
        const clearBtn = document.getElementById('laporan-search-clear');
        const instantEmpty = document.getElementById('laporan-instant-empty');
        const instantResetBtn = document.getElementById('laporan-instant-reset-btn');
        const resetUrl = laporanIndexRoot.dataset.resetUrl || '';
        const hasServerSearch = laporanIndexRoot.dataset.hasServerSearch === '1';

        let activeMode = 'grid';
        try {
            const saved = window.localStorage.getItem(storageKey);
            if (saved === 'table' || saved === 'grid') activeMode = saved;
        } catch {
            activeMode = 'grid';
        }

        const applyViewMode = (mode) => {
            activeMode = mode === 'table' ? 'table' : 'grid';
            const isGrid = activeMode === 'grid';

            const instantEmptyVisible = instantEmpty && !instantEmpty.classList.contains('hidden');
            if (!instantEmptyVisible) {
                viewGrid?.classList.toggle('hidden', !isGrid);
                viewTable?.classList.toggle('hidden', isGrid);
            }

            viewButtons.forEach((btn) => {
                const selected = btn.dataset.laporanViewBtn === activeMode;
                btn.setAttribute('aria-pressed', String(selected));
                btn.className = selected
                    ? 'inline-flex items-center gap-1.5 rounded-lg bg-[#20A464] px-3 py-1.5 text-xs font-bold text-white shadow-xs transition'
                    : 'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:text-[#062419]';
            });

            try {
                window.localStorage.setItem(storageKey, activeMode);
            } catch {
                // Abaikan jika browser memblokir localStorage
            }
        };

        const applyInstantSearch = () => {
            if (!searchInput) return;
            const keyword = searchInput.value.trim().toLowerCase();

            if (clearBtn) {
                clearBtn.classList.toggle('hidden', keyword === '');
                clearBtn.classList.toggle('flex', keyword !== '');
            }

            const gridItems = Array.from(viewGrid?.querySelectorAll('[data-laporan-item]') || []);
            const tableItems = Array.from(viewTable?.querySelectorAll('[data-laporan-item]') || []);
            if (gridItems.length === 0 && tableItems.length === 0) return;

            let visibleCount = 0;
            gridItems.forEach((el) => {
                const hay = (el.dataset.searchText || '').toLowerCase();
                const match = keyword === '' || hay.includes(keyword);
                el.classList.toggle('hidden', !match);
                if (match) visibleCount++;
            });

            tableItems.forEach((el) => {
                const hay = (el.dataset.searchText || '').toLowerCase();
                const match = keyword === '' || hay.includes(keyword);
                el.classList.toggle('hidden', !match);
            });

            const noneMatched = visibleCount === 0 && keyword !== '';
            instantEmpty?.classList.toggle('hidden', !noneMatched);
            if (noneMatched) {
                viewGrid?.classList.add('hidden');
                viewTable?.classList.add('hidden');
            } else {
                viewGrid?.classList.toggle('hidden', activeMode !== 'grid');
                viewTable?.classList.toggle('hidden', activeMode !== 'table');
            }
        };

        viewButtons.forEach((btn) => {
            btn.addEventListener('click', () => applyViewMode(btn.dataset.laporanViewBtn || 'grid'));
        });

        searchInput?.addEventListener('input', applyInstantSearch);

        clearBtn?.addEventListener('click', () => {
            if (!searchInput) return;
            searchInput.value = '';
            if (hasServerSearch && resetUrl) {
                window.location.href = resetUrl;
                return;
            }
            applyInstantSearch();
            searchInput.focus();
        });

        instantResetBtn?.addEventListener('click', () => {
            if (hasServerSearch && resetUrl) {
                window.location.href = resetUrl;
                return;
            }
            if (searchInput) {
                searchInput.value = '';
                applyInstantSearch();
                searchInput.focus();
            }
        });

        applyViewMode(activeMode);
    }

    // 15. Halaman Data Quisioner (View Switcher Kartu/Tabel + localStorage + Pencarian Instan + Auto-Submit Semester)
    const quisionerIndexRoot = document.getElementById('admin-quisioner-index');
    if (quisionerIndexRoot) {
        const storageKey = 'admin_quisioner_view_mode';
        const viewGrid = document.getElementById('quisioner-view-grid');
        const viewTable = document.getElementById('quisioner-view-table');
        const viewButtons = Array.from(quisionerIndexRoot.querySelectorAll('[data-quisioner-view-btn]'));
        const filterForm = document.getElementById('quisioner-filter-form');
        const semesterSelect = document.getElementById('quisioner-semester-select');
        const searchInput = document.getElementById('quisioner-search-input');
        const clearBtn = document.getElementById('quisioner-search-clear');
        const instantEmpty = document.getElementById('quisioner-instant-empty');
        const instantResetBtn = document.getElementById('quisioner-instant-reset-btn');
        const resetUrl = quisionerIndexRoot.dataset.resetUrl || '';
        const hasServerSearch = quisionerIndexRoot.dataset.hasServerSearch === '1';

        let activeMode = 'grid';
        try {
            const saved = window.localStorage.getItem(storageKey);
            if (saved === 'table' || saved === 'grid') activeMode = saved;
        } catch {
            activeMode = 'grid';
        }

        const applyViewMode = (mode) => {
            activeMode = mode === 'table' ? 'table' : 'grid';
            const isGrid = activeMode === 'grid';

            const instantEmptyVisible = instantEmpty && !instantEmpty.classList.contains('hidden');
            if (!instantEmptyVisible) {
                viewGrid?.classList.toggle('hidden', !isGrid);
                viewTable?.classList.toggle('hidden', isGrid);
            }

            viewButtons.forEach((btn) => {
                const selected = btn.dataset.quisionerViewBtn === activeMode;
                btn.setAttribute('aria-pressed', String(selected));
                btn.className = selected
                    ? 'inline-flex items-center gap-1.5 rounded-lg bg-[#20A464] px-2.5 py-1.5 text-xs font-bold text-white shadow-xs transition'
                    : 'inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-600 transition hover:text-[#062419]';
            });

            try {
                window.localStorage.setItem(storageKey, activeMode);
            } catch {
                // Abaikan jika browser memblokir localStorage
            }
        };

        const applyInstantSearch = () => {
            if (!searchInput) return;
            const keyword = searchInput.value.trim().toLowerCase();

            if (clearBtn) {
                clearBtn.classList.toggle('hidden', keyword === '');
                clearBtn.classList.toggle('flex', keyword !== '');
            }

            const gridItems = Array.from(viewGrid?.querySelectorAll('[data-quisioner-item]') || []);
            const tableItems = Array.from(viewTable?.querySelectorAll('[data-quisioner-item]') || []);
            if (gridItems.length === 0 && tableItems.length === 0) return;

            let visibleCount = 0;
            gridItems.forEach((el) => {
                const hay = (el.dataset.searchText || '').toLowerCase();
                const match = keyword === '' || hay.includes(keyword);
                el.classList.toggle('hidden', !match);
                if (match) visibleCount++;
            });

            tableItems.forEach((el) => {
                const hay = (el.dataset.searchText || '').toLowerCase();
                const match = keyword === '' || hay.includes(keyword);
                el.classList.toggle('hidden', !match);
            });

            const noneMatched = visibleCount === 0 && keyword !== '';
            instantEmpty?.classList.toggle('hidden', !noneMatched);
            if (noneMatched) {
                viewGrid?.classList.add('hidden');
                viewTable?.classList.add('hidden');
            } else {
                viewGrid?.classList.toggle('hidden', activeMode !== 'grid');
                viewTable?.classList.toggle('hidden', activeMode !== 'table');
            }
        };

        viewButtons.forEach((btn) => {
            btn.addEventListener('click', () => applyViewMode(btn.dataset.quisionerViewBtn || 'grid'));
        });

        semesterSelect?.addEventListener('change', () => {
            filterForm?.submit();
        });

        searchInput?.addEventListener('input', applyInstantSearch);

        clearBtn?.addEventListener('click', () => {
            if (!searchInput) return;
            searchInput.value = '';
            if (hasServerSearch && resetUrl) {
                window.location.href = resetUrl;
                return;
            }
            applyInstantSearch();
            searchInput.focus();
        });

        instantResetBtn?.addEventListener('click', () => {
            if (hasServerSearch && resetUrl) {
                window.location.href = resetUrl;
                return;
            }
            if (searchInput) {
                searchInput.value = '';
                applyInstantSearch();
                searchInput.focus();
            }
        });

        applyViewMode(activeMode);
    }
});
