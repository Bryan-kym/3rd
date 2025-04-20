document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const phone = document.getElementById('phone').value;
    
    const response = await fetch('/api/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password, phone })
    });
    
    const data = await response.json();
    
    if (data.success) {
        document.getElementById('registerForm').style.display = 'none';
        document.getElementById('otpSection').style.display = 'block';
        // Store temp user ID for OTP verification
        localStorage.setItem('tempUserId', data.userId);
    }
});

document.getElementById('verifyOtp').addEventListener('click', async () => {
    const otp = document.getElementById('otp').value;
    const userId = localStorage.getItem('tempUserId');
    
    const response = await fetch('/api/verify-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ userId, otp })
    });
    
    const data = await response.json();
    
    if (data.success) {
        // Store token and redirect
        localStorage.setItem('authToken', data.token);
        window.location.href = '/dashboard.html';
    }
});