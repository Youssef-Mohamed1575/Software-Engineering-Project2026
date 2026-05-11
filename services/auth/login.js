function runMigrations() {
    fetch('api/migrations.php')
        .then(r => r.text())
        .then(d => console.log('DB Status:', d));
}

function checkSessionAndRedirect() {
    fetch('api/check_session.php')
        .then(res => res.json())
        .then(data => {
            if (data.loggedIn) {
                window.location.href = 'dashboard.html';
            }
        });
}

async function handleLoginSubmit(e, loginForm, submitBtn, errorMessage) {
    e.preventDefault();
    
    submitBtn.disabled = true;
    const originalContent = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>';
    errorMessage.classList.add('hidden');

    const formData = new FormData(loginForm);
    const data = Object.fromEntries(formData.entries());

    try {
        const response = await fetch('api/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            window.location.href = 'dashboard.html';
        } else {
            errorMessage.textContent = result.message;
            errorMessage.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalContent;
        }
    } catch (error) {
        console.error('Login error:', error);
        errorMessage.textContent = 'System error. Please try again.';
        errorMessage.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalContent;
    }
}
