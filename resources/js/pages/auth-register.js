const form = document.getElementById('register-form');
const submitBtn = document.getElementById('register-submit-btn');

if (form && submitBtn) {
    form.addEventListener('submit', (event) => {
        if (event.defaultPrevented) {
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = submitBtn.dataset.loadingText || 'Loading...';
    });
}
