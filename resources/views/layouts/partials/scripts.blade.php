<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
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

        document.querySelectorAll('[data-close-alert]').forEach((btn) => {
            btn.addEventListener('click', () => {
                btn.closest('[data-alert]')?.remove();
            });
        });

        // Konfirmasi hapus global menggunakan SweetAlert2
        document.querySelectorAll('form[data-confirm-delete]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();

                const title = form.dataset.confirmTitle || 'Konfirmasi Hapus';
                const text = form.dataset.confirmText || 'Apakah Anda yakin ingin menghapus data ini?';
                const confirmText = form.dataset.confirmButton || 'Ya, Hapus';

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: title,
                        text: text,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: confirmText,
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                } else if (window.confirm(text)) {
                    form.submit();
                }
            });
        });
    });
</script>

@stack('scripts')
