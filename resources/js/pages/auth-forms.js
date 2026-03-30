/**
 * Initialize auth form submit handler: disable button and show loading text.
 *
 * @param {string} formId - Form element id
 * @param {string} btnId - Submit button element id
 * @param {string} [loadingText] - Fallback loading text (optional, uses data-loading-text on button)
 */
export function initAuthFormSubmit(formId, btnId, loadingText = 'Loading...') {
    const form = document.getElementById(formId);
    const submitBtn = document.getElementById(btnId);

    if (!form || !submitBtn) {
        return;
    }

    form.addEventListener('submit', (event) => {
        if (event.defaultPrevented) {
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = submitBtn.dataset.loadingText || loadingText;
    });
}
