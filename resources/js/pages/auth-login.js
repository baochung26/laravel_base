const form = document.getElementById('login-form');
const submitBtn = document.getElementById('submit-btn');

if (form && submitBtn) {
    form.addEventListener('submit', (event) => {
        if (event.defaultPrevented) {
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = submitBtn.dataset.loadingText || 'Loading...';
    });
}

window.onGoogleSignInCallback = function onGoogleSignInCallback(response) {
    if (!response || !response.credential) {
        return;
    }

    const tokenInput = document.getElementById('google-id-token');
    const googleForm = document.getElementById('google-login-form');

    if (!tokenInput || !googleForm) {
        return;
    }

    tokenInput.value = response.credential;
    googleForm.submit();
};
