import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import Swal from 'sweetalert2';

const THEME_STORAGE_KEY = 'ui-theme';
const RAW_UI_CONFIG = window.AppUIConfig || {};
const I18N = RAW_UI_CONFIG.i18n || {};
const DEFAULT_UI_CONFIG = {
    toast: {
        position: 'bottom-end',
        timer: 2600,
    },
    loading: {
        text: I18N.loading || 'Processing...',
    },
};
const UI_CONFIG = {
    ...DEFAULT_UI_CONFIG,
    ...RAW_UI_CONFIG,
    toast: {
        ...DEFAULT_UI_CONFIG.toast,
        ...(RAW_UI_CONFIG.toast || {}),
    },
    loading: {
        ...DEFAULT_UI_CONFIG.loading,
        ...(RAW_UI_CONFIG.loading || {}),
    },
};

const VALIDATION_I18N = I18N.validation || {};
const CONFIRM_I18N = I18N.confirm || {};

const interpolate = (template, vars = {}) => {
    return Object.keys(vars).reduce(
        (result, key) => result.replaceAll(`:${key}`, String(vars[key])),
        template
    );
};

const validationMessage = (key, fallback, vars = {}) => {
    const template = VALIDATION_I18N[key] || fallback;
    return interpolate(template, vars);
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
        confirmButtonText: I18N.alert_close || 'Close',
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
        confirmButtonText: CONFIRM_I18N.confirm || 'Confirm',
        cancelButtonText: CONFIRM_I18N.cancel || 'Cancel',
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

const applyServerValidationErrors = () => {
    const errors = window.AppFormErrors || {};
    if (!errors || typeof errors !== 'object') {
        return;
    }

    const entries = Object.entries(errors);
    if (!entries.length) {
        return;
    }

    let firstInvalidField = null;

    entries.forEach(([fieldName, messages]) => {
        const message = Array.isArray(messages) ? messages[0] : messages;
        if (!message || typeof message !== 'string') {
            return;
        }

        const escapedFieldName = window.CSS?.escape
            ? window.CSS.escape(fieldName)
            : String(fieldName).replace(/"/g, '\\"');
        const selector = `[name="${escapedFieldName}"]`;
        const field = document.querySelector(selector);
        if (!field) {
            return;
        }

        field.dataset.serverError = 'true';
        field.dataset.serverErrorMessage = message;
        field.dataset.serverErrorValue = (field.value || '').trim();
        setFieldError(field, message);
        if (!firstInvalidField) {
            firstInvalidField = field;
        }
    });

    if (firstInvalidField instanceof HTMLElement) {
        firstInvalidField.focus();
    }
};

const validateField = (field) => {
    if (field.disabled || field.type === 'hidden') {
        return true;
    }

    if (field.dataset.serverError === 'true') {
        const currentValue = (field.value || '').trim();
        const originalValue = field.dataset.serverErrorValue || '';

        if (currentValue === originalValue) {
            return setFieldError(
                field,
                field.dataset.serverErrorMessage || validationMessage('invalid', 'This data is invalid.')
            );
        }

        delete field.dataset.serverError;
        delete field.dataset.serverErrorMessage;
        delete field.dataset.serverErrorValue;
    }

    const required = field.dataset.required === 'true' || field.hasAttribute('required');
    const label = field.dataset.label || field.getAttribute('aria-label') || field.name || 'Trường này';
    const value = (field.value || '').trim();

    if (required && value === '') {
        return setFieldError(field, validationMessage('required', ':label is required.', { label }));
    }

    if (value !== '' && field.type === 'email') {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(value)) {
            return setFieldError(field, validationMessage('email', 'Email is not valid.'));
        }
    }

    if (value !== '' && field.dataset.minLength) {
        const minLength = Number(field.dataset.minLength);
        if (Number.isFinite(minLength) && value.length < minLength) {
            return setFieldError(
                field,
                validationMessage('min_length', ':label must be at least :min characters.', {
                    label,
                    min: minLength,
                })
            );
        }
    }

    if (value !== '' && field.dataset.match) {
        const target =
            document.querySelector(field.dataset.match) ||
            field.form?.querySelector(`[name="${field.dataset.match}"]`);
        if (target && value !== target.value.trim()) {
            return setFieldError(field, validationMessage('match', ':label does not match.', { label }));
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
            let firstInvalidField = null;

            fields.forEach((field) => {
                const fieldValid = validateField(field);
                if (!fieldValid) {
                    isValid = false;
                    if (!firstInvalidField) {
                        firstInvalidField = field;
                    }
                }
            });

            if (!isValid) {
                event.preventDefault();
                event.stopPropagation();
                firstInvalidField?.focus();
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
            const title = form.dataset.confirmTitle || CONFIRM_I18N.title || 'Confirm action';
            const text = form.dataset.confirmMessage || CONFIRM_I18N.message || 'Are you sure you want to continue?';
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
    applyServerValidationErrors();
    initConfirmActions();
    initLoadingEvents();
    initAutoSubmitForms();
};

initUi();
