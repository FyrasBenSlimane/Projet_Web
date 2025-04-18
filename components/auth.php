<?php
/**
 * Authentication Component with Inline CSS
 * Contains the login and registration forms as a modal overlay
 */
?>
<style>
/* Auth Overlay Styles */
.auth-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    backdrop-filter: blur(5px);
}

.auth-overlay.active {
    opacity: 1;
    visibility: visible;
}

.auth-container {
    position: relative;
    width: 420px;
    max-width: 90%;
    background: white;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    transform: translateY(20px);
    transition: all 0.3s ease;
}

.auth-overlay.active .auth-container {
    transform: translateY(0);
}

[data-bs-theme="dark"] .auth-container {
    background: #1f2028;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
}

.auth-header {
    position: relative;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

[data-bs-theme="dark"] .auth-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.auth-toggle-container {
    display: flex;
    gap: 0.5rem;
}

.auth-toggle {
    padding: 0.5rem 1rem;
    font-weight: 600;
    cursor: pointer;
    color: var(--accent);
    opacity: 0.7;
    transition: all 0.3s ease;
    border: none;
    background: transparent;
}

.auth-toggle.active,
.auth-toggle:hover {
    opacity: 1;
}

[data-bs-theme="dark"] .auth-toggle {
    color: #A4C2E5;
}

.close-auth {
    position: absolute;
    top: 1.2rem;
    right: 1.2rem;
    font-size: 1.5rem;
    background: transparent;
    border: none;
    cursor: pointer;
    color: var(--accent);
    transition: all 0.3s ease;
}

.close-auth:hover {
    color: var(--primary);
    transform: rotate(90deg);
}

[data-bs-theme="dark"] .close-auth {
    color: #A4C2E5;
}

[data-bs-theme="dark"] .close-auth:hover {
    color: #FFFFFF;
}

.auth-forms-container {
    position: relative;
    width: 200%;
    display: flex;
    transition: transform 0.3s ease;
    min-height: 320px;
}

.auth-container.active .auth-forms-container {
    transform: translateX(-50%);
}

.login-form,
.register-form {
    width: 50%;
    padding: 2rem;
}

.auth-form-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    text-align: center;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 0.8rem 1rem;
    border-radius: 8px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    background-color: #FFFFFF;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(62, 92, 118, 0.1);
    outline: none;
}

[data-bs-theme="dark"] .form-control {
    background-color: #2a2b36;
    border-color: rgba(255, 255, 255, 0.1);
    color: #FFFFFF;
}

[data-bs-theme="dark"] .form-control:focus {
    border-color: #8FB3DE;
    box-shadow: 0 0 0 3px rgba(143, 179, 222, 0.2);
}

.form-text {
    font-size: 0.8rem;
    color: #6c757d;
}

[data-bs-theme="dark"] .form-text {
    color: #A4C2E5;
}

.auth-submit-btn {
    width: 100%;
    padding: 0.8rem;
    background-color: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.auth-submit-btn:hover {
    background-color: #2d4358;
    box-shadow: 0 5px 15px rgba(62, 92, 118, 0.3);
}

[data-bs-theme="dark"] .auth-submit-btn {
    background-color: #8FB3DE;
    color: #1f2028;
}

[data-bs-theme="dark"] .auth-submit-btn:hover {
    background-color: #a8c6e7;
}

.auth-divider {
    display: flex;
    align-items: center;
    margin: 1.5rem 0;
}

.divider-line {
    flex: 1;
    height: 1px;
    background-color: rgba(0, 0, 0, 0.1);
}

[data-bs-theme="dark"] .divider-line {
    background-color: rgba(255, 255, 255, 0.1);
}

.divider-text {
    padding: 0 1rem;
    color: #6c757d;
    font-size: 0.8rem;
}

[data-bs-theme="dark"] .divider-text {
    color: #A4C2E5;
}

.social-login {
    display: flex;
    gap: 0.5rem;
}

.social-btn {
    flex: 1;
    padding: 0.6rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    background-color: white;
    color: var(--accent);
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.social-btn:hover {
    background-color: #f8f9fa;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
}

[data-bs-theme="dark"] .social-btn {
    background-color: #2a2b36;
    border-color: rgba(255, 255, 255, 0.1);
    color: #A4C2E5;
}

[data-bs-theme="dark"] .social-btn:hover {
    background-color: #35363f;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
}

.auth-footer {
    text-align: center;
    margin-top: 1.5rem;
    font-size: 0.9rem;
}

.auth-footer a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
}

.auth-footer a:hover {
    text-decoration: underline;
}

[data-bs-theme="dark"] .auth-footer a {
    color: #8FB3DE;
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .login-form,
    .register-form {
        padding: 1.5rem;
    }
    
    .auth-form-title {
        font-size: 1.3rem;
    }
}
</style>

<div class="auth-overlay" id="authOverlay">
    <div class="auth-container" id="authContainer">
        <div class="auth-header">
            <div class="auth-toggle-container">
                <button id="loginToggle" class="auth-toggle active">Login</button>
                <button id="registerToggle" class="auth-toggle">Register</button>
            </div>
            <button id="closeAuth" class="close-auth">×</button>
        </div>
        
        <div class="auth-forms-container">
            <!-- Login Form -->
            <div class="login-form">
                <h2 class="auth-form-title">Welcome Back</h2>
                <form id="loginForm">
                    <div class="form-group">
                        <label for="loginEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="loginEmail" placeholder="Enter your email">
                    </div>
                    <div class="form-group">
                        <label for="loginPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="loginPassword" placeholder="Enter your password">
                        <small class="form-text">Forgot password? <a href="#">Reset it here</a></small>
                    </div>
                    <button type="submit" class="auth-submit-btn">Login</button>
                </form>
                
                <div class="auth-divider">
                    <div class="divider-line"></div>
                    <span class="divider-text">or continue with</span>
                    <div class="divider-line"></div>
                </div>
                
                <div class="social-login">
                    <button class="social-btn"><i class="fab fa-google"></i></button>
                    <button class="social-btn"><i class="fab fa-facebook-f"></i></button>
                    <button class="social-btn"><i class="fab fa-apple"></i></button>
                </div>
                
                <div class="auth-footer">
                    <p>Don't have an account? <a href="#" id="goToRegister">Register</a></p>
                </div>
            </div>
            
            <!-- Registration Form -->
            <div class="register-form">
                <h2 class="auth-form-title">Create an Account</h2>
                <form id="registerForm">
                    <div class="form-group">
                        <label for="registerName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="registerName" placeholder="Enter your full name">
                    </div>
                    <div class="form-group">
                        <label for="registerEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="registerEmail" placeholder="Enter your email">
                    </div>
                    <div class="form-group">
                        <label for="registerPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="registerPassword" placeholder="Create a password">
                        <small class="form-text">Password must be at least 8 characters</small>
                    </div>
                    <button type="submit" class="auth-submit-btn">Create Account</button>
                </form>
                
                <div class="auth-divider">
                    <div class="divider-line"></div>
                    <span class="divider-text">or register with</span>
                    <div class="divider-line"></div>
                </div>
                
                <div class="social-login">
                    <button class="social-btn"><i class="fab fa-google"></i></button>
                    <button class="social-btn"><i class="fab fa-facebook-f"></i></button>
                    <button class="social-btn"><i class="fab fa-apple"></i></button>
                </div>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="#" id="goToLogin">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const goToRegister = document.getElementById('goToRegister');
    const goToLogin = document.getElementById('goToLogin');
    
    goToRegister.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('authContainer').classList.add('active');
        document.getElementById('loginToggle').classList.remove('active');
        document.getElementById('registerToggle').classList.add('active');
    });
    
    goToLogin.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('authContainer').classList.remove('active');
        document.getElementById('loginToggle').classList.add('active');
        document.getElementById('registerToggle').classList.remove('active');
    });
});
</script>