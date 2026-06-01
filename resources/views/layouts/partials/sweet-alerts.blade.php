@php
    $sweetAlertPayload = [
        'success' => session('success'),
        'error' => session('error'),
        'warning' => session('warning'),
        'status' => session('status'),
        'errors' => isset($errors) && $errors->any() ? $errors->all() : [],
    ];
@endphp

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .apar-swal-popup {
        border-radius: 1.75rem !important;
        padding: 2rem !important;
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.28) !important;
    }
    .apar-swal-title {
        color: #0f172a !important;
        font-size: 1.5rem !important;
        font-weight: 900 !important;
        letter-spacing: -0.03em !important;
    }
    .apar-swal-html {
        color: #475569 !important;
        font-size: 0.95rem !important;
        font-weight: 600 !important;
        line-height: 1.65 !important;
    }
    .apar-swal-confirm,
    .apar-swal-cancel {
        border-radius: 0.9rem !important;
        padding: 0.75rem 1.35rem !important;
        font-size: 0.75rem !important;
        font-weight: 900 !important;
        letter-spacing: 0.08em !important;
        text-transform: uppercase !important;
    }
</style>
<script>
    (() => {
        const ALERT_CONFIRM_COLOR = '#dc2626';
        const ALERT_CANCEL_COLOR = '#6b7280';
        const flashPayload = @json($sweetAlertPayload);

        function hasSweetAlert() {
            return typeof window.Swal !== 'undefined' && typeof window.Swal.fire === 'function';
        }

        function normalizeAlertOptions(options = {}) {
            const icon = options.icon || 'info';
            const defaultTitle = {
                success: 'Sukses',
                error: 'Gagal',
                warning: 'Peringatan',
                question: 'Konfirmasi',
                info: 'Informasi',
            }[icon] || 'Informasi';

            return {
                icon,
                title: options.title || defaultTitle,
                text: options.text,
                html: options.html,
                confirmButtonText: options.confirmButtonText || 'OK',
                confirmButtonColor: options.confirmButtonColor || ALERT_CONFIRM_COLOR,
                cancelButtonColor: options.cancelButtonColor || ALERT_CANCEL_COLOR,
                showCancelButton: Boolean(options.showCancelButton),
                cancelButtonText: options.cancelButtonText || 'Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'apar-swal-popup',
                    title: 'apar-swal-title',
                    htmlContainer: 'apar-swal-html',
                    confirmButton: 'apar-swal-confirm',
                    cancelButton: 'apar-swal-cancel',
                },
            };
        }

        window.aparAlert = (options = {}) => {
            if (!hasSweetAlert()) {
                console.warn(options.text || options.html || options.title || 'Notifikasi');
                return Promise.resolve({ isConfirmed: true });
            }

            return window.Swal.fire(normalizeAlertOptions(options));
        };

        window.aparConfirm = (message, options = {}) => window.aparAlert({
            icon: options.icon || 'warning',
            title: options.title || 'Konfirmasi',
            text: message || options.text || 'Apakah Anda yakin ingin melanjutkan?',
            showCancelButton: true,
            confirmButtonText: options.confirmButtonText || 'Ya, Lanjutkan',
            cancelButtonText: options.cancelButtonText || 'Batal',
        });

        window.showAppAlert = (message, icon = 'warning', title = null) => window.aparAlert({
            icon,
            title: title || (icon === 'error' ? 'Gagal' : (icon === 'success' ? 'Sukses' : 'Peringatan')),
            text: message || 'Terjadi kesalahan. Silakan coba lagi.',
        });

        document.addEventListener('submit', function (event) {
            const form = event.target.closest('form[data-confirm]');
            if (!form || form.dataset.confirmed === 'true') {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            window.aparConfirm(form.dataset.confirm, {
                title: form.dataset.confirmTitle || 'Konfirmasi',
                confirmButtonText: form.dataset.confirmButton || 'Ya, Lanjutkan',
                cancelButtonText: form.dataset.cancelButton || 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        }, true);

        document.addEventListener('DOMContentLoaded', () => {
            if (flashPayload.errors && flashPayload.errors.length) {
                window.aparAlert({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: flashPayload.errors.map((message) => String(message)).join('<br>'),
                });
                return;
            }

            if (flashPayload.error) {
                window.aparAlert({ icon: 'error', title: 'Gagal', text: flashPayload.error });
                return;
            }

            if (flashPayload.warning) {
                window.aparAlert({ icon: 'warning', title: 'Peringatan', text: flashPayload.warning });
                return;
            }

            if (flashPayload.success) {
                window.aparAlert({ icon: 'success', title: 'Sukses', text: flashPayload.success });
                return;
            }

            if (flashPayload.status) {
                const statusMessages = {
                    'profile-updated': 'Profil berhasil diperbarui.',
                    'password-updated': 'Password berhasil diperbarui.',
                    'verification-link-sent': 'Link verifikasi baru berhasil dikirim.',
                };

                window.aparAlert({
                    icon: 'success',
                    title: 'Sukses',
                    text: statusMessages[flashPayload.status] || flashPayload.status,
                });
            }
        });
    })();
</script>
