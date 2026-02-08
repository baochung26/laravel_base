import { initAuthFormSubmit } from './auth-forms';

/* Login form */
if (document.getElementById('login-form')) {
    initAuthFormSubmit('login-form', 'submit-btn', 'Đang đăng nhập...');
}

/* Register form */
if (document.getElementById('register-form')) {
    initAuthFormSubmit('register-form', 'register-submit-btn', 'Đang đăng ký...');
}

/* Google Sign-In – only on login page */
if (document.getElementById('g_id_onload')) {
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

    const script = document.createElement('script');
    script.src = 'https://accounts.google.com/gsi/client';
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
}
