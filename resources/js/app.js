import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import Swal from 'sweetalert2';

const THEME_STORAGE_KEY = 'ui-theme';
const DEFAULT_UI_CONFIG = {
    toast: {
        position: 'bottom-end',
        timer: 2600,
    },
    loading: {
        text: 'Đang xử lý...',
    },
};
const UI_CONFIG = {
    ...DEFAULT_UI_CONFIG,
    ...(window.AppUIConfig || {}),
    toast: {
        ...DEFAULT_UI_CONFIG.toast,
        ...(window.AppUIConfig?.toast || {}),
    },
    loading: {
        ...DEFAULT_UI_CONFIG.loading,
        ...(window.AppUIConfig?.loading || {}),
    },
};

const getPreferredTheme = () => {
    const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);

    if (savedTheme === 'dark' || savedTheme === 'light') {
        return savedTheme;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};

const applyTheme = (theme) => {
    document.documentElement.setAttribute('data-theme', theme);
};

const initThemeToggle = () => {
    applyTheme(getPreferredTheme());

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';

            applyTheme(next);
            localStorage.setItem(THEME_STORAGE_KEY, next);
        });
    });
};

const showLoading = (text = UI_CONFIG.loading.text) => {
    let overlay = document.getElementById('app-loading-overlay');

    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'app-loading-overlay';
        overlay.className = 'app-loading-overlay';
        overlay.innerHTML = `
            <div class="app-loading-box" role="status" aria-live="polite">
                <span class="app-loading-spinner" aria-hidden="true"></span>
                <span class="app-loading-text"></span>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    const textNode = overlay.querySelector('.app-loading-text');
    if (textNode) {
        textNode.textContent = text;
    }

    overlay.classList.add('is-active');
};

const hideLoading = () => {
    const overlay = document.getElementById('app-loading-overlay');
    if (overlay) {
        overlay.classList.remove('is-active');
    }
};

const showToast = (message, icon = 'success') => {
    return Swal.fire({
        toast: true,
        icon,
        title: message,
        position: UI_CONFIG.toast.position,
        timer: UI_CONFIG.toast.timer,
        timerProgressBar: true,
        showConfirmButton: false,
        customClass: {
            popup: 'app-toast-popup',
        },
    });
};

const showAlertFromSession = () => {
    const type = document.body.dataset.alertType;
    const message = document.body.dataset.alertMessage;

    if (!type || !message) {
        return;
    }

    const icon = ['success', 'error', 'warning', 'info', 'question'].includes(type)
        ? type
        : 'info';

    if (icon === 'success') {
        showToast(message, 'success');
        return;
    }

    Swal.fire({
        icon,
        text: message,
        confirmButtonText: 'Đóng',
        customClass: {
            popup: 'app-swal-popup',
            confirmButton: 'app-swal-confirm',
        },
        buttonsStyling: false,
    });
};

const showConfirm = async (title, text) => {
    const result = await Swal.fire({
        icon: 'question',
        title,
        text,
        showCancelButton: true,
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        reverseButtons: true,
        customClass: {
            popup: 'app-swal-popup',
            confirmButton: 'app-swal-confirm',
            cancelButton: 'app-swal-cancel',
        },
        buttonsStyling: false,
    });

    return result.isConfirmed;
};

const setFieldError = (field, message = '') => {
    const existingError = field.parentElement?.querySelector('.field-error');

    if (!message) {
        field.classList.remove('is-invalid');
        if (existingError) {
            existingError.remove();
        }
        return true;
    }

    field.classList.add('is-invalid');
    if (existingError) {
        existingError.textContent = message;
        return false;
    }

    const errorNode = document.createElement('p');
    errorNode.className = 'field-error';
    errorNode.textContent = message;
    field.parentElement?.appendChild(errorNode);
    return false;
};

const validateField = (field) => {
    if (field.disabled || field.type === 'hidden') {
        return true;
    }

    const required = field.dataset.required === 'true' || field.hasAttribute('required');
    const label = field.dataset.label || field.getAttribute('aria-label') || field.name || 'Trường này';
    const value = (field.value || '').trim();

    if (required && value === '') {
        return setFieldError(field, `${label} là bắt buộc.`);
    }

    if (value !== '' && field.type === 'email') {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(value)) {
            return setFieldError(field, 'Email không đúng định dạng.');
        }
    }

    if (value !== '' && field.dataset.minLength) {
        const minLength = Number(field.dataset.minLength);
        if (Number.isFinite(minLength) && value.length < minLength) {
            return setFieldError(field, `${label} phải có ít nhất ${minLength} ký tự.`);
        }
    }

    if (value !== '' && field.dataset.match) {
        const target =
            document.querySelector(field.dataset.match) ||
            field.form?.querySelector(`[name="${field.dataset.match}"]`);
        if (target && value !== target.value.trim()) {
            return setFieldError(field, `${label} không khớp.`);
        }
    }

    return setFieldError(field);
};

const initFormValidation = () => {
    document.querySelectorAll('form[data-validate]').forEach((form) => {
        form.setAttribute('novalidate', 'novalidate');
        const fields = form.querySelectorAll('input, select, textarea');

        fields.forEach((field) => {
            field.addEventListener('input', () => validateField(field));
            field.addEventListener('blur', () => validateField(field));
        });

        form.addEventListener('submit', (event) => {
            let isValid = true;

            fields.forEach((field) => {
                const fieldValid = validateField(field);
                if (!fieldValid) {
                    isValid = false;
                }
            });

            if (!isValid) {
                event.preventDefault();
                event.stopPropagation();
                Swal.fire({
                    icon: 'warning',
                    text: 'Vui lòng kiểm tra lại thông tin đã nhập.',
                    confirmButtonText: 'Đã hiểu',
                    customClass: {
                        popup: 'app-swal-popup',
                        confirmButton: 'app-swal-confirm',
                    },
                    buttonsStyling: false,
                });
            }
        });
    });
};

const initConfirmActions = () => {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            if (form.dataset.confirmed === 'true') {
                form.dataset.confirmed = 'false';
                return;
            }

            event.preventDefault();
            const title = form.dataset.confirmTitle || 'Xác nhận thao tác';
            const text = form.dataset.confirmMessage || 'Bạn có chắc muốn tiếp tục?';
            const confirmed = await showConfirm(title, text);

            if (confirmed) {
                form.dataset.confirmed = 'true';
                form.requestSubmit();
            }
        });
    });
};

const initLoadingEvents = () => {
    document.addEventListener('submit', (event) => {
        if (event.defaultPrevented) {
            return;
        }

        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const method = (form.getAttribute('method') || 'get').toLowerCase();
        const shouldForceLoading = form.hasAttribute('data-loading');
        if (method === 'get' && !shouldForceLoading) {
            return;
        }

        const loadingText =
            form.dataset.loadingText ||
            form.querySelector('[type="submit"]')?.dataset.loadingText ||
            UI_CONFIG.loading.text;

        showLoading(loadingText);
    });

    document.querySelectorAll('a[data-loading]').forEach((link) => {
        link.addEventListener('click', () => {
            showLoading(link.dataset.loadingText || UI_CONFIG.loading.text);
        });
    });

    window.addEventListener('pageshow', hideLoading);
};

const initAutoSubmitForms = () => {
    document.querySelectorAll('form[data-auto-submit]').forEach((form) => {
        let timer = null;

        const submitNow = () => {
            if (timer) {
                clearTimeout(timer);
            }
            form.requestSubmit();
        };

        form.querySelectorAll('select').forEach((field) => {
            field.addEventListener('change', submitNow);
        });

        form.querySelectorAll('input[type="text"], input[type="search"], input[type="email"]').forEach((field) => {
            field.addEventListener('input', () => {
                if (timer) {
                    clearTimeout(timer);
                }

                timer = setTimeout(() => {
                    form.requestSubmit();
                }, 450);
            });

            field.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    submitNow();
                }
            });
        });
    });
};

window.AppUI = {
    config: UI_CONFIG,
    toast: showToast,
    alert: (message, icon = 'info') => Swal.fire({ text: message, icon }),
    confirm: showConfirm,
    showLoading,
    hideLoading,
};

const initUi = () => {
    initThemeToggle();
    showAlertFromSession();
    initFormValidation();
    initConfirmActions();
    initLoadingEvents();
    initAutoSubmitForms();
};

initUi();
