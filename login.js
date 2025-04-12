document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    const response = await fetch('/api/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
    });
    
    const data = await response.json();
    
    if (data.success) {
        document.getElementById('loginForm').style.display = 'none';
        document.getElementById('otpSection').style.display = 'block';
        localStorage.setItem('tempUserId', data.userId);
    }
});

document.getElementById('verifyLoginOtp').addEventListener('click', async () => {
    const otp = document.getElementById('otp').value;
    const userId = localStorage.getItem('tempUserId');
    
    const response = await fetch('/api/verify-login-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ userId, otp })
    });
    
    const data = await response.json();
    
    if (data.success) {
        localStorage.setItem('authToken', data.token);
        window.location.href = '/dashboard.html';
    }
});