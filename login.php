<?php
require_once 'config/config.php';
require_once SERVICES_PATH . '/AuthService.php';

// If already logged in as customer, redirect to checkout or home
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'customer') {
    $redirect = $_GET['redirect'] ?? 'index.php';
    header("Location: $redirect");
    exit;
}

$redirectUrl = $_GET['redirect'] ?? 'index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinjaya Karadant - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; min-height: 100vh; overflow-x: hidden; background: #fff9f0; }
        .login-wrapper { min-height: 100vh; display: flex; }
        .left-panel { position: relative; width: 50%; min-height: 100vh; overflow: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .left-bg { position: absolute; inset: 0; z-index: 0; background: url('assets/images/login_bg.png') center/cover no-repeat; }
        .left-bg::before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(139, 26, 26, 0.9) 0%, rgba(192, 57, 43, 0.6) 20%, transparent 50%); z-index: 1; }
        .left-content { position: relative; z-index: 5; text-align: center; padding: 40px 30px; width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: flex-start; padding-top: 8%; }
        .logo-container { margin-bottom: 20px; }
        .logo-box { display: inline-block; background: rgba(107, 21, 21, 0.8); border-radius: 8px; padding: 10px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); backdrop-filter: blur(4px); }
        .tagline { font-family: 'Playfair Display', serif; font-size: 2.2rem; font-weight: 800; color: #5A1E1E; line-height: 1.4; max-width: 500px; margin-left: auto; margin-right: auto; }
        .illustration-container { position: relative; z-index: 5; width: 100%; max-width: 600px; margin: 0 auto; }
        
        .right-panel { width: 50%; min-height: 100vh; background: #ffffff; display: flex; flex-direction: column; justify-content: center; align-items: center; position: relative; overflow: hidden; }
        .corner-arc { position: absolute; top: -30px; right: -30px; width: 150px; height: 150px; border-radius: 50%; background: linear-gradient(135deg, #F5C77E, #E8A849); opacity: 0.6; }
        .form-container { width: 100%; max-width: 460px; padding: 40px 30px; position: relative; z-index: 2; }
        .welcome-title { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 800; color: #6B1515; margin-bottom: 40px; text-align: center; }
        .form-label-custom { font-size: 0.95rem; font-weight: 500; color: #333; margin-bottom: 8px; }
        .form-control-custom { border: 2px solid #E8A849; border-radius: 10px; padding: 14px 18px; font-size: 1rem; color: #333; background: #fff; transition: all 0.3s ease; font-family: 'Poppins', sans-serif; }
        .form-control-custom:focus { border-color: #6B1515; box-shadow: 0 0 0 3px rgba(107, 21, 21, 0.1); outline: none; }
        .password-wrapper { position: relative; }
        .password-wrapper .form-control-custom { padding-right: 50px; }
        .password-toggle { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #888; cursor: pointer; font-size: 1.2rem; padding: 5px; transition: color 0.3s; }
        .password-toggle:hover { color: #6B1515; }
        .forgot-link { text-align: right; margin-top: 10px; margin-bottom: 30px; }
        .forgot-link a { color: #6B1515; text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: color 0.3s; }
        .btn-signin { width: 100%; padding: 15px; border: none; border-radius: 12px; background: linear-gradient(135deg, #C0392B 0%, #6B1515 100%); color: #fff; font-size: 1.15rem; font-weight: 600; font-family: 'Poppins', sans-serif; cursor: pointer; transition: all 0.4s ease; position: relative; overflow: hidden; }
        .btn-signin:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(107, 21, 21, 0.4); }
        .btn-signin .spinner-border { display: none; width: 1.2rem; height: 1.2rem; margin-right: 8px; }
        .btn-signin.loading .spinner-border { display: inline-block; }
        .btn-signin.loading .btn-text { opacity: 0.8; }
        .skyline-container { position: absolute; bottom: 0; left: 0; right: 0; z-index: 1; overflow: hidden; height: 120px; }
        .skyline-svg { width: 100%; height: 100%; }
        .form-group { margin-bottom: 20px; }
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
        .custom-toast { background: #6B1515; color: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.3); display: none; animation: slideInRight 0.4s ease; font-size: 0.95rem; }
        .custom-toast.show { display: flex; align-items: center; gap: 10px; }
        @keyframes slideInRight { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        .mobile-banner { display: none; width: 100%; height: 35vh; background: url('assets/images/moblie-login.png') center/cover no-repeat; }
        @media (max-width: 320px) {
            .mobile-banner { height: 351px; }
        }
        @media (max-width: 850px) {
            .left-panel { display: none; }
            .right-panel { width: 100%; align-items: stretch; justify-content: flex-start; }
            .mobile-banner { display: block; position: relative;  }
            .form-container { 
                margin: -30px auto 0 auto; 
                background: #ffffff; 
                border-radius: 30px 30px 0 0; 
                padding: 40px 20px; 
                box-shadow: 0 -10px 20px rgba(0,0,0,0.05); 
                z-index: 10; 
                flex-grow: 1;
            }
            .skyline-container { display: none; }
            .corner-arc { display: none; }
        }

        /* OTP Input Styling */
        .otp-container { display: flex; gap: 10px; justify-content: center; margin-bottom: 25px; }
        .otp-digit { width: 45px; height: 55px; border: 2px solid #E8A849; border-radius: 8px; text-align: center; font-size: 1.5rem; font-weight: 700; color: #6B1515; background: #fff; transition: all 0.3s; }
        .otp-digit:focus { border-color: #6B1515; outline: none; box-shadow: 0 0 0 3px rgba(107, 21, 21, 0.1); }
        .step-title { font-size: 1.1rem; font-weight: 600; color: #6B1515; margin-bottom: 15px; }
        .resend-link { font-size: 0.85rem; color: #666; text-align: center; margin-top: 15px; }
        .resend-link a { color: #6B1515; font-weight: 600; text-decoration: none; }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="left-panel">
        <div class="left-bg"></div>
        <div class="left-content">
            <div class="logo-container">
                <div class="logo-box">
                    <img src="<?php echo BASE_URL . SITE_LOGO; ?>" alt="<?php echo SITE_NAME; ?>" style="max-height: 60px; width: auto;">
                </div>
            </div>
            <h1 class="tagline">Fresh Sweets. Premium Taste.<br>Delivered with Love.</h1>
        </div>
    </div>
    <div class="right-panel">
        <div class="mobile-banner"></div>
        <div class="corner-arc"></div>
        <div class="form-container">
            <h2 class="welcome-title">Welcome to Vinjaya Karadant</h2>
            <form id="loginForm" novalidate>
                <div class="form-group">
                    <label class="form-label-custom" for="emailInput">Email Address</label>
                    <input type="email" class="form-control-custom w-100" id="emailInput" placeholder="youremail@gmail.com" autocomplete="email">
                    <div class="invalid-feedback" style="display:none;color:#C0392B;font-size:0.85rem;margin-top:5px;" id="emailError">Please enter a valid email address</div>
                </div>
                <div class="form-group">
                    <label class="form-label-custom" for="passwordInput">Password</label>
                    <div class="password-wrapper">
                        <input type="password" class="form-control-custom w-100" id="passwordInput" placeholder="••••••••" autocomplete="current-password">
                        <button type="button" class="password-toggle" id="togglePassword"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="invalid-feedback" style="display:none;color:#C0392B;font-size:0.85rem;margin-top:5px;" id="passwordError">Password is required</div>
                    <div class="invalid-feedback" style="display:none;color:#C0392B;font-size:0.85rem;margin-top:5px;" id="serverError"></div>
                </div>
                <div class="forgot-link"><a href="#" id="showForgotBtn">Forgot Password ?</a></div>
                <button type="submit" class="btn-signin" id="signinBtn">
                    <span class="spinner-border spinner-border-sm"></span>
                    <span class="btn-text">Signing in</span>
                </button>
                <div class="text-center mt-4">
                    <span style="font-size:0.95rem; color:#555;">Don't have an account? </span>
                    <a href="#" id="showRegisterBtn" style="color:#6B1515; font-weight:700; text-decoration:none;">Create one</a>
                </div>
            </form>

            <form id="registerForm" novalidate style="display: none;">
                <div class="form-group">
                    <label class="form-label-custom" for="regNameInput">Full Name</label>
                    <input type="text" class="form-control-custom w-100" id="regNameInput" placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label class="form-label-custom" for="regEmailInput">Email Address</label>
                    <input type="email" class="form-control-custom w-100" id="regEmailInput" placeholder="youremail@gmail.com">
                </div>
                <div class="form-group">
                    <label class="form-label-custom" for="regPhoneInput">Phone Number (Optional)</label>
                    <input type="tel" class="form-control-custom w-100" id="regPhoneInput" placeholder="1234567890">
                </div>
                <div class="form-group">
                    <label class="form-label-custom" for="regPasswordInput">Password</label>
                    <div class="password-wrapper">
                        <input type="password" class="form-control-custom w-100" id="regPasswordInput" placeholder="••••••••">
                        <button type="button" class="password-toggle" id="regTogglePassword"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="invalid-feedback" style="display:none;color:#C0392B;font-size:0.85rem;margin-top:5px;" id="regServerError"></div>
                </div>
                <button type="submit" class="btn-signin" id="registerBtn" style="margin-top: 15px;">
                    <span class="spinner-border spinner-border-sm"></span>
                    <span class="btn-text">Create Account</span>
                </button>
                <div class="text-center mt-4">
                    <span style="font-size:0.95rem; color:#555;">Already have an account? </span>
                    <a href="#" class="showLoginBtn" style="color:#6B1515; font-weight:700; text-decoration:none;">Sign in</a>
                </div>
            </form>

            <div id="forgotSection" style="display: none;">
                <div class="alert alert-info" style="display:none;font-size:0.85rem;margin-bottom:15px;" id="forgotResetLinkBox"></div>
                
                <!-- Step 1: Email -->
                <form id="forgotForm" novalidate>
                    <p style="color: #666; font-size: 0.95rem; margin-bottom: 25px;">Enter your email address and we'll send you an OTP to reset your password.</p>
                    <div class="form-group">
                        <label class="form-label-custom" for="forgotEmailInput">Email Address</label>
                        <input type="email" class="form-control-custom w-100" id="forgotEmailInput" placeholder="youremail@gmail.com">
                        <div class="invalid-feedback" style="display:none;color:#C0392B;font-size:0.85rem;margin-top:5px;" id="forgotEmailError">Please enter a valid email address</div>
                    </div>
                    <div class="invalid-feedback" style="display:none;color:#C0392B;font-size:0.85rem;margin-bottom:15px;" id="forgotServerError"></div>
                    <button type="submit" class="btn-signin" id="forgotBtn">
                        <span class="spinner-border spinner-border-sm"></span>
                        <span class="btn-text">Send Verification Code</span>
                    </button>
                </form>

                <!-- Step 2: OTP -->
                <form id="otpForm" novalidate style="display: none;">
                    <div class="step-title">Verify Your Email</div>
                    <p style="color: #666; font-size: 0.85rem; margin-bottom: 20px;">We've sent a 6-digit code to <span id="displayEmail" class="fw-bold"></span></p>
                    
                    <div class="otp-container">
                        <input type="text" class="otp-digit" maxlength="1" pattern="\d*">
                        <input type="text" class="otp-digit" maxlength="1" pattern="\d*">
                        <input type="text" class="otp-digit" maxlength="1" pattern="\d*">
                        <input type="text" class="otp-digit" maxlength="1" pattern="\d*">
                        <input type="text" class="otp-digit" maxlength="1" pattern="\d*">
                        <input type="text" class="otp-digit" maxlength="1" pattern="\d*">
                    </div>
                    
                    <div class="invalid-feedback" style="display:none;color:#C0392B;font-size:0.85rem;margin-bottom:15px;text-align:center;" id="otpError"></div>
                    
                    <button type="submit" class="btn-signin" id="verifyOtpBtn">
                        <span class="spinner-border spinner-border-sm"></span>
                        <span class="btn-text">Verify & Continue</span>
                    </button>
                    
                    <div class="resend-link">
                        Didn't receive the code? <a href="#" id="resendOtpBtn">Resend</a>
                    </div>
                </form>

                <!-- Step 3: New Password -->
                <form id="newPasswordForm" novalidate style="display: none;">
                    <div class="step-title">Set New Password</div>
                    <p style="color: #666; font-size: 0.85rem; margin-bottom: 20px;">Create a secure password for your account.</p>
                    
                    <div class="form-group">
                        <label class="form-label-custom" for="newPassInput">New Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control-custom w-100" id="newPassInput" placeholder="••••••••">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label-custom" for="confirmPassInput">Confirm New Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control-custom w-100" id="confirmPassInput" placeholder="••••••••">
                        </div>
                    </div>
                    
                    <div class="invalid-feedback" style="display:none;color:#C0392B;font-size:0.85rem;margin-bottom:15px;" id="newPassError"></div>
                    
                    <button type="submit" class="btn-signin" id="resetPassBtn">
                        <span class="spinner-border spinner-border-sm"></span>
                        <span class="btn-text">Reset Password</span>
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="#" class="showLoginBtn" style="color:#6B1515; font-weight:700; text-decoration:none;"><i class="bi bi-arrow-left"></i> Back to Sign in</a>
                </div>
            </div>
        </div>
        
        <div class="skyline-container">
            <img src="assets/images/login.png" class="skyline-svg" alt="Skyline" style="object-fit: contain; object-position: bottom; width: 100%; height: 100%;">
        </div>
    </div>
</div>
<div class="toast-container"><div class="custom-toast" id="customToast"><i class="bi bi-check-circle-fill"></i><span id="toastMessage">Login successful!</span></div></div>
<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');
    togglePassword.addEventListener('click', () => {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        togglePassword.querySelector('i').classList.toggle('bi-eye');
        togglePassword.querySelector('i').classList.toggle('bi-eye-slash');
    });

    const regTogglePassword = document.getElementById('regTogglePassword');
    const regPasswordInput = document.getElementById('regPasswordInput');
    regTogglePassword.addEventListener('click', () => {
        const type = regPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        regPasswordInput.setAttribute('type', type);
        regTogglePassword.querySelector('i').classList.toggle('bi-eye');
        regTogglePassword.querySelector('i').classList.toggle('bi-eye-slash');
    });

    // Toggle Forms
    document.getElementById('showRegisterBtn').addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('loginForm').style.display = 'none';
        document.getElementById('forgotForm').style.display = 'none';
        document.getElementById('registerForm').style.display = 'block';
        document.querySelector('.welcome-title').textContent = 'Create an Account';
    });

    document.getElementById('showForgotBtn').addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('loginForm').style.display = 'none';
        document.getElementById('registerForm').style.display = 'none';
        document.getElementById('forgotSection').style.display = 'block';
        document.getElementById('forgotForm').style.display = 'block';
        document.getElementById('otpForm').style.display = 'none';
        document.getElementById('newPasswordForm').style.display = 'none';
        document.querySelector('.welcome-title').textContent = 'Reset Password';
    });

    document.querySelectorAll('.showLoginBtn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('forgotSection').style.display = 'none';
            document.getElementById('loginForm').style.display = 'block';
            document.querySelector('.welcome-title').textContent = 'Welcome to Vinjaya Karadant';
        });
    });

    function validateEmail(email) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email); }
    function showToast(message, isError = false) {
        const toast = document.getElementById('customToast');
        toast.style.background = isError ? '#C0392B' : '#16A34A';
        document.getElementById('toastMessage').textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const email = document.getElementById('emailInput');
        const emailVal = email.value.trim();
        const password = document.getElementById('passwordInput');
        let valid = true;

        if (!emailVal || !validateEmail(emailVal)) { email.style.borderColor = '#C0392B'; valid = false; }
        if (!password.value) { password.style.borderColor = '#C0392B'; valid = false; }

        if (valid) {
            const btn = document.getElementById('signinBtn');
            btn.classList.add('loading'); btn.disabled = true;
            document.getElementById('serverError').style.display = 'none';

            try {
                const formData = new FormData();
                formData.append('email', emailVal);
                formData.append('password', password.value);
                formData.append('redirect', '<?php echo htmlspecialchars($redirectUrl); ?>');

                const response = await fetch('api/v1/customer_auth.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    showToast('Login successful! Redirecting...');
                    setTimeout(() => window.location.href = result.redirect, 1000);
                } else {
                    document.getElementById('serverError').textContent = result.message;
                    document.getElementById('serverError').style.display = 'block';
                    btn.classList.remove('loading'); btn.disabled = false;
                }
            } catch (err) {
                showToast('An error occurred. Please try again.', true);
                btn.classList.remove('loading'); btn.disabled = false;
            }
        }
    });

    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const name = document.getElementById('regNameInput');
        const email = document.getElementById('regEmailInput');
        const emailVal = email.value.trim();
        const phone = document.getElementById('regPhoneInput');
        const password = document.getElementById('regPasswordInput');
        let valid = true;

        if (!name.value.trim()) { name.style.borderColor = '#C0392B'; valid = false; } else { name.style.borderColor = '#E8A849'; }
        if (!emailVal || !validateEmail(emailVal)) { email.style.borderColor = '#C0392B'; valid = false; } else { email.style.borderColor = '#E8A849'; }
        if (!password.value || password.value.length < 6) { 
            password.style.borderColor = '#C0392B'; 
            document.getElementById('regServerError').textContent = "Password must be at least 6 characters";
            document.getElementById('regServerError').style.display = 'block';
            valid = false; 
        } else { 
            password.style.borderColor = '#E8A849'; 
            document.getElementById('regServerError').style.display = 'none';
        }

        if (valid) {
            const btn = document.getElementById('registerBtn');
            btn.classList.add('loading'); btn.disabled = true;

            try {
                const formData = new FormData();
                formData.append('name', name.value);
                formData.append('email', emailVal);
                formData.append('phone', phone.value);
                formData.append('password', password.value);
                formData.append('redirect', '<?php echo htmlspecialchars($redirectUrl); ?>');

                const response = await fetch('api/v1/customer_register.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    showToast('Account created successfully! Redirecting...');
                    setTimeout(() => window.location.href = result.redirect, 1000);
                } else {
                    document.getElementById('regServerError').textContent = result.message;
                    document.getElementById('regServerError').style.display = 'block';
                    btn.classList.remove('loading'); btn.disabled = false;
                }
            } catch (err) {
                showToast('An error occurred. Please try again.', true);
                btn.classList.remove('loading'); btn.disabled = false;
            }
        }
    });

    // OTP Multi-step Logic
    let resetEmail = '';
    let resetToken = '';

    // Step 1: Forgot Email
    document.getElementById('forgotForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const email = document.getElementById('forgotEmailInput');
        const emailVal = email.value.trim();
        let valid = true;

        if (!emailVal || !validateEmail(emailVal)) { 
            email.style.borderColor = '#C0392B'; 
            document.getElementById('forgotEmailError').style.display = 'block';
            valid = false; 
        } else { 
            email.style.borderColor = '#E8A849'; 
            document.getElementById('forgotEmailError').style.display = 'none';
        }

        if (valid) {
            const btn = document.getElementById('forgotBtn');
            btn.classList.add('loading'); btn.disabled = true;
            document.getElementById('forgotServerError').style.display = 'none';
            document.getElementById('forgotResetLinkBox').style.display = 'none';

            try {
                const formData = new FormData();
                formData.append('email', emailVal);

                const response = await fetch('api/v1/forgot_password.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    resetEmail = emailVal;
                    document.getElementById('displayEmail').textContent = emailVal;
                    
                    if (result.otp) {
                        const linkBox = document.getElementById('forgotResetLinkBox');
                        linkBox.innerHTML = `Local testing OTP: <strong style="font-size:1.2rem; color:#7b1d1d;">${result.otp}</strong>`;
                        linkBox.style.display = 'block';
                    }

                    // Transition to OTP Step
                    setTimeout(() => {
                        document.getElementById('forgotForm').style.display = 'none';
                        document.getElementById('otpForm').style.display = 'block';
                    }, 1000);
                } else {
                    document.getElementById('forgotServerError').textContent = result.message;
                    document.getElementById('forgotServerError').style.display = 'block';
                }
            } catch (err) {
                showToast('An error occurred. Please try again.', true);
            } finally {
                btn.classList.remove('loading'); btn.disabled = false;
            }
        }
    });

    // OTP Input Auto-focus
    const otpDigits = document.querySelectorAll('.otp-digit');
    otpDigits.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            if (e.target.value.length === 1 && index < otpDigits.length - 1) {
                otpDigits[index + 1].focus();
            }
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                otpDigits[index - 1].focus();
            }
        });
    });

    // Step 2: Verify OTP
    document.getElementById('otpForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const otp = Array.from(otpDigits).map(input => input.value).join('');
        
        if (otp.length < 6) {
            document.getElementById('otpError').textContent = 'Please enter all 6 digits';
            document.getElementById('otpError').style.display = 'block';
            return;
        }

        const btn = document.getElementById('verifyOtpBtn');
        btn.classList.add('loading'); btn.disabled = true;
        document.getElementById('otpError').style.display = 'none';

        try {
            const formData = new FormData();
            formData.append('email', resetEmail);
            formData.append('otp', otp);

            const response = await fetch('api/v1/verify_otp.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.success) {
                resetToken = result.token;
                // Transition to New Password Step
                document.getElementById('otpForm').style.display = 'none';
                document.getElementById('newPasswordForm').style.display = 'block';
            } else {
                document.getElementById('otpError').textContent = result.message;
                document.getElementById('otpError').style.display = 'block';
            }
        } catch (err) {
            showToast('Verification failed. Try again.', true);
        } finally {
            btn.classList.remove('loading'); btn.disabled = false;
        }
    });

    // Step 3: Reset Password
    document.getElementById('newPasswordForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const pass = document.getElementById('newPassInput').value;
        const confirm = document.getElementById('confirmPassInput').value;
        const errorEl = document.getElementById('newPassError');

        if (pass.length < 6) {
            errorEl.textContent = 'Password must be at least 6 characters';
            errorEl.style.display = 'block';
            return;
        }
        if (pass !== confirm) {
            errorEl.textContent = 'Passwords do not match';
            errorEl.style.display = 'block';
            return;
        }

        const btn = document.getElementById('resetPassBtn');
        btn.classList.add('loading'); btn.disabled = true;
        errorEl.style.display = 'none';

        try {
            const formData = new FormData();
            formData.append('email', resetEmail);
            formData.append('token', resetToken);
            formData.append('password', pass);

            const response = await fetch('api/v1/reset_password_with_otp.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.success) {
                showToast('Password reset successful!');
                setTimeout(() => {
                    document.querySelector('.showLoginBtn').click();
                }, 2000);
            } else {
                errorEl.textContent = result.message;
                errorEl.style.display = 'block';
            }
        } catch (err) {
            showToast('Reset failed. Try again.', true);
        } finally {
            btn.classList.remove('loading'); btn.disabled = false;
        }
    });

    document.getElementById('resendOtpBtn').addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('otpForm').style.display = 'none';
        document.getElementById('forgotForm').style.display = 'block';
    });
</script>
</body>
</html>
