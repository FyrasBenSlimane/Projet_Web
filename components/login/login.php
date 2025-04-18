<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Store the redirect URL if coming from another page
if (!isset($_SESSION['redirect_url']) && isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    // Only store internal redirects
    if (strpos($referer, $_SERVER['HTTP_HOST']) !== false) {
        $_SESSION['redirect_url'] = $referer;
    }
}

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['register']) && !isset($_GET['reset'])) {
    // Validate credentials against the database
    if (isset($_POST['email']) && isset($_POST['password'])) {
        require_once '../../config/database.php';
        
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        // Check if email exists and password matches
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Check if user has a profile record, if not create one
            $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_email = ?");
            $stmt->execute([$email]);
            $profile = $stmt->fetch();
            
            if (!$profile) {
                // Create a default user profile if it doesn't exist
                $stmt = $pdo->prepare("INSERT INTO user_profiles (user_email, bio, skills) VALUES (?, '', '')");
                $stmt->execute([$email]);
            }
            
            // Update last_login timestamp
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE email = ?");
            $stmt->execute([$email]);
            
            // Set session data
            $_SESSION['user'] = [
                'email' => $user['email'],
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'user_type' => $user['user_type'] ?? 'freelancer'
            ];
            
            // Handle redirect URL
            $redirectUrl = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : '../Dashboard/index.php';
            unset($_SESSION['redirect_url']); // Clear the stored URL
            
            // Set user data in localStorage via JavaScript before redirecting
            echo "<script>
                localStorage.setItem('currentUser', JSON.stringify({
                    email: '{$user['email']}',
                    name: '{$_SESSION['user']['name']}'
                }));
                window.location.href = '$redirectUrl';
            </script>";
            exit;
        } else {
            // Invalid credentials
            $loginError = "Invalid email or password";
        }
    }
}

// Process register form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['register'])) {
    if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirm_password'])) {
        require_once '../../config/database.php';
        
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        
        // Check if passwords match
        if ($password !== $confirm_password) {
            $registerError = "Passwords do not match";
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $existingUser = $stmt->fetch();
            
            if ($existingUser) {
                $registerError = "Email already in use. Please use a different email or login to your account.";
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Begin transaction to ensure both inserts succeed or fail together
                $pdo->beginTransaction();
                
                try {
                    // Insert new user into database
                    $stmt = $pdo->prepare("INSERT INTO users (email, password, first_name, last_name, user_type, created_at) VALUES (?, ?, ?, ?, 'freelancer', NOW())");
                    $result = $stmt->execute([$email, $hashedPassword, $first_name, $last_name]);
                    
                    if ($result) {
                        // Also create a user_profile entry with default values
                        $stmt = $pdo->prepare("INSERT INTO user_profiles (user_email, bio, skills) VALUES (?, '', '')");
                        $profileResult = $stmt->execute([$email]);
                        
                        if ($profileResult) {
                            // Commit transaction
                            $pdo->commit();
                            
                            // Set session data
                            $_SESSION['user'] = [
                                'email' => $email,
                                'name' => $first_name . ' ' . $last_name,
                                'first_name' => $first_name,
                                'last_name' => $last_name,
                                'user_type' => 'freelancer'
                            ];
                            
                            // Set user data in localStorage via JavaScript before redirecting
                            echo "<script>
                                localStorage.setItem('currentUser', JSON.stringify({
                                    email: '{$email}',
                                    name: '{$first_name} {$last_name}'
                                }));
                                window.location.href = '../Dashboard/index.php';
                            </script>";
                            exit;
                        } else {
                            // Profile creation failed, rollback
                            $pdo->rollBack();
                            $registerError = "Registration failed. Please try again.";
                        }
                    } else {
                        // User creation failed, rollback
                        $pdo->rollBack();
                        $registerError = "Registration failed. Please try again.";
                    }
                } catch (Exception $e) {
                    // Any exception, rollback
                    $pdo->rollBack();
                    $registerError = "Registration failed: " . $e->getMessage();
                }
            }
        }
    }
}

// Process reset password form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['reset'])) {
    if (isset($_POST['email'])) {
        // In a real application, you would send a reset password email
        // For demo purposes, we'll just show a success message
        echo "<script>
            alert('Password reset link has been sent to your email.');
            window.location.href = 'login.php';
        </script>";
        exit;
    }
}

// Check if user is already logged in
// Only redirect if there's an active session with valid user data AND no logout parameter
if (isset($_SESSION['user']) && !empty($_SESSION['user']) && !isset($_GET['logout'])) {
    $redirectUrl = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : '../Dashboard/index.php';
    unset($_SESSION['redirect_url']); // Clear stored URL
    header('Location: ' . $redirectUrl);
    exit;
}

// Store the referrer URL if coming from another page
if (!isset($_SESSION['redirect_url']) && isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    // Only store internal redirects
    if (strpos($referer, $_SERVER['HTTP_HOST']) !== false && 
        !strpos($referer, 'login.php') && 
        !strpos($referer, 'logout=true')) {
        $_SESSION['redirect_url'] = $referer;
    }
}

// Handle logout if requested
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    // Clear the session data
    $_SESSION = array();
    
    // If a session cookie is used, clear it too
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Clear localStorage via JavaScript
    echo "<script>
        localStorage.removeItem('currentUser');
    </script>";
    
    // Finally, destroy the session
    session_destroy();
    
    // Redirect to main index page instead of login page
    header('Location: /web/index.php');
    exit;
}

// Get current theme from cookies or system preference
$savedTheme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : null;
$systemTheme = isset($_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME']) ? $_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME'] : null;
$initialTheme = $savedTheme ?: ($systemTheme ?: 'light');

// Determine which form to show
$showResetForm = isset($_GET['reset']) && $_GET['reset'] === 'true';
$showRegisterForm = isset($_GET['register']) && $_GET['register'] === 'true';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo $initialTheme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <meta name="description" content="LenSi - Connect with talented freelancers for your business needs">
    <meta name="theme-color" content="#3E5C76">
    <title><?php 
        if ($showResetForm) {
            echo 'Reset Password';
        } elseif ($showRegisterForm) {
            echo 'Register';
        } else {
            echo 'Login';
        }
    ?> - LenSi Freelance Marketplace</title>
    <!-- Preload critical assets -->
    <link rel="preload" href="/web/assets/images/logo_white.svg" as="image" type="image/svg+xml">
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&display=swap" as="style">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/web/assets/images/logo_white.svg" sizes="any">
    <!-- Fonts and CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Inter:wght@300;400;500&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        /* Root CSS Variables */
        :root {
            --primary: #3E5C76;
            --primary-rgb: 62, 92, 118;
            --secondary: #748CAB;
            --accent: #1D2D44;
            --accent-dark: #0D1B2A;
            --light: #F9F7F0;
            --light-gray: #f5f7fa;
            --dark: #0D1B2A;
            --font-primary: 'Montserrat', sans-serif;
            --font-secondary: 'Inter', sans-serif;
            --font-heading: 'Poppins', sans-serif;
            --transition-default: all 0.3s ease;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.1);
            --shadow-md: 0 5px 15px rgba(0,0,0,0.07);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 20px;
        }

        [data-bs-theme="dark"] {
            --light: #121212;
            --dark: #F9F7F0;
            --accent: #A4C2E5;
            --accent-dark: #171821;
            --primary: #5D8BB3;
            --primary-rgb: 93, 139, 179;
            --secondary: #8FB3DE;
            --light-gray: #1a1c24;
        }

        body {
            font-family: var(--font-secondary);
            background-color: var(--light);
            color: var(--accent);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .split-screen {
            display: flex;
            flex-direction: row;
            min-height: 100vh;
        }

        .split-screen__left {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background-color: var(--primary);
            color: white;
            position: relative;
            overflow: hidden;
            display: none;
        }

        .split-screen__left-content {
            position: relative;
            z-index: 2;
            padding: 2rem;
            max-width: 500px;
        }

        .split-screen__left::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(13, 27, 42, 0.7) 0%, rgba(62, 92, 118, 0.4) 100%);
            z-index: 1;
        }

        .split-screen__left-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }

        .split-screen__logo {
            width: 80px;
            margin-bottom: 2rem;
        }

        .split-screen__title {
            font-family: var(--font-heading);
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .split-screen__subtitle {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .features-list {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
            text-align: left;
            margin-bottom: 2rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .split-screen__right {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            background-color: var(--light);
            position: relative;
            min-height: 100vh;
            overflow-y: auto;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 10;
            color: var(--accent);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
            background-color: rgba(0,0,0,0.05);
            transition: var(--transition-default);
        }

        .back-button:hover {
            background-color: rgba(0,0,0,0.1);
            transform: translateX(-3px);
        }

        [data-bs-theme="dark"] .back-button {
            color: var(--light);
            background-color: rgba(255,255,255,0.1);
        }

        [data-bs-theme="dark"] .back-button:hover {
            background-color: rgba(255,255,255,0.15);
        }

        [data-bs-theme="dark"] .split-screen__right {
            background-color: var(--light);
        }

        .auth-container {
            width: 100%;
            max-width: 450px;
            margin-top: 3rem;
        }

        .auth-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .auth-title {
            font-family: var(--font-heading);
            font-weight: 600;
            font-size: 1.8rem;
            color: var(--accent-dark);
            margin-bottom: 0.5rem;
        }

        [data-bs-theme="dark"] .auth-title {
            color: var(--light);
        }

        .auth-subtitle {
            color: var(--accent);
            opacity: 0.8;
        }

        [data-bs-theme="dark"] .auth-subtitle {
            color: var(--accent);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--accent);
        }

        [data-bs-theme="dark"] .form-label {
            color: var(--accent);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: var(--radius-sm);
            border: 1px solid rgba(0, 0, 0, 0.1);
            background-color: #FFFFFF;
            color: var(--accent-dark);
            transition: var(--transition-default);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
            outline: none;
        }

        [data-bs-theme="dark"] .form-control {
            background-color: #2a2b36;
            border-color: rgba(255, 255, 255, 0.1);
            color: #FFFFFF;
        }

        [data-bs-theme="dark"] .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(143, 179, 222, 0.2);
        }

        .auth-submit-btn {
            width: 100%;
            padding: 0.9rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-default);
            margin-top: 1rem;
            font-size: 1rem;
        }

        .auth-submit-btn:hover {
            background-color: #2d4358;
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        [data-bs-theme="dark"] .auth-submit-btn {
            background-color: var(--secondary);
            color: #1f2028;
        }

        [data-bs-theme="dark"] .auth-submit-btn:hover {
            background-color: #a8c6e7;
        }

        .auth-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition-default);
        }
        
        .auth-link:hover {
            color: var(--secondary);
            text-decoration: underline;
        }
        
        [data-bs-theme="dark"] .auth-link {
            color: var(--secondary);
        }
        
        [data-bs-theme="dark"] .auth-link:hover {
            color: var(--accent);
        }
        
        .auth-footer {
            font-size: 0.9rem;
        }
        
        /* Animation for form transition */
        .form-fade {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-check {
            margin-bottom: 1rem;
        }
        
        .form-check-input {
            width: 1.1em;
            height: 1.1em;
            margin-top: 0.2em;
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, 0.25);
            transition: var(--transition-default);
        }
        
        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        [data-bs-theme="dark"] .form-check-input {
            background-color: #2a2b36;
            border-color: rgba(255, 255, 255, 0.25);
        }
        
        [data-bs-theme="dark"] .form-check-input:checked {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }
        
        .form-check-label {
            font-size: 0.9rem;
            cursor: pointer;
        }
        
        .input-group {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            background: none;
            border: none;
            color: var(--accent);
            cursor: pointer;
        }
        
        [data-bs-theme="dark"] .password-toggle {
            color: var(--accent);
        }
        
        .register-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        [data-bs-theme="dark"] .register-section {
            border-top-color: rgba(255,255,255,0.1);
        }
        
        /* Social Login Buttons */
        .social-login {
            margin: 1.5rem 0;
            text-align: center;
        }
        
        .social-divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }
        
        .social-divider::before,
        .social-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: rgba(0,0,0,0.1);
        }
        
        [data-bs-theme="dark"] .social-divider::before,
        [data-bs-theme="dark"] .social-divider::after {
            background-color: rgba(255,255,255,0.1);
        }
        
        .social-divider-text {
            padding: 0 1rem;
            font-size: 0.9rem;
            color: var(--accent);
            opacity: 0.7;
        }
        
        .social-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: 1px solid rgba(0,0,0,0.1);
            background-color: white;
            color: var(--accent-dark);
            margin: 0 0.5rem;
            transition: var(--transition-default);
            font-size: 1.2rem;
        }
        
        .social-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        
        [data-bs-theme="dark"] .social-btn {
            background-color: #2a2b36;
            border-color: rgba(255,255,255,0.1);
            color: var(--light);
        }
        
        .social-btn.google {
            color: #DB4437;
        }
        
        .social-btn.facebook {
            color: #4267B2;
        }
        
        .social-btn.linkedin {
            color: #0077B5;
        }
        
        /* Alert Animation */
        .alert-danger {
            transition: opacity 0.5s ease-out;
        }
        
        .alert-fade-out {
            opacity: 0;
        }
        
        @media (min-width: 992px) {
            .split-screen__left {
                display: flex;
            }
        }
        
        @media (max-width: 991.98px) {
            .split-screen {
                flex-direction: column;
            }
            
            .split-screen__left {
                min-height: 0;
                padding: 3rem 1rem;
            }
            
            .split-screen__right {
                min-height: 0;
            }
        }
        
        @media (max-width: 768px) {
            .auth-container {
                padding: 0 1rem;
            }
            
            .auth-title {
                font-size: 1.5rem;
            }
            
            .back-button {
                top: 10px;
                left: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="split-screen">
        <div class="split-screen__left">
            <img src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1887&q=80" alt="Freelancers collaborating" class="split-screen__left-image">
            <div class="split-screen__left-content">
                <img src="/web/assets/images/logo_white.svg" alt="LenSi Logo" class="split-screen__logo">
                <h1 class="split-screen__title">Join the Future of Work</h1>
                <p class="split-screen__subtitle">Connect with top talents and build your dream team on our freelance marketplace.</p>
                
                <div class="features-list">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-person-check"></i>
                        </div>
                        <div>
                            <h3>Verified Professionals</h3>
                            <p>Access a pool of skilled and vetted freelancers.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <h3>Secure Payments</h3>
                            <p>Protected transactions and milestone-based payments.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <div>
                            <h3>Fast Matches</h3>
                            <p>Find the right talent for your project in minutes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="split-screen__right">
            <a href="/web/index.php" class="back-button">
                <i class="bi bi-arrow-left"></i> Back to Home
            </a>
            
            <div class="auth-container form-fade">
                <?php if ($showResetForm): ?>
                <!-- Reset Password Form -->
                <div class="auth-header">
                    <h1 class="auth-title">Reset Your Password</h1>
                    <p class="auth-subtitle">Enter your email to reset your password</p>
                </div>
                <form method="post">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
                    </div>
                    <button type="submit" class="auth-submit-btn">Send Reset Link</button>
                </form>
                <div class="auth-footer mt-4 text-center">
                    <p>Remember your password? <a href="login.php" class="auth-link">Back to Login</a></p>
                </div>
                
                <?php elseif ($showRegisterForm): ?>
                <!-- Register Form -->
                <div class="auth-header">
                    <h1 class="auth-title">Create an Account</h1>
                    <p class="auth-subtitle">Join our freelance marketplace</p>
                </div>
                <form method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required placeholder="Enter your first name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required placeholder="Enter your last name">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="register_password" name="password" required placeholder="Choose a secure password">
                            <button type="button" class="password-toggle" onclick="togglePassword('register_password')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Confirm your password">
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" class="auth-link">Terms of Service</a> and <a href="#" class="auth-link">Privacy Policy</a>
                        </label>
                    </div>
                    
                    <button type="submit" class="auth-submit-btn">Create Account</button>
                </form>
                
                <div class="social-divider">
                    <span class="social-divider-text">Or sign up with</span>
                </div>
                
                <div class="social-login">
                    <a href="#" class="social-btn google"><i class="bi bi-google"></i></a>
                    <a href="#" class="social-btn facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-btn linkedin"><i class="bi bi-linkedin"></i></a>
                </div>
                
                <div class="auth-footer mt-4 text-center">
                    <p>Already have an account? <a href="login.php" class="auth-link">Login</a></p>
                </div>
                
                <?php else: ?>
                <!-- Login Form -->
                <div class="auth-header">
                    <h1 class="auth-title">Welcome Back</h1>
                    <p class="auth-subtitle">Sign in to your account</p>
                </div>
                <?php if (isset($loginError)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $loginError; ?>
                </div>
                <?php endif; ?>
                <form method="post">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="mt-2 text-end">
                            <a href="login.php?reset=true" class="auth-link">Forgot Password?</a>
                        </div>
                    </div>
                    <button type="submit" class="auth-submit-btn">Login</button>
                </form>
                
                <div class="social-divider">
                    <span class="social-divider-text">Or continue with</span>
                </div>
                
                <div class="social-login">
                    <a href="#" class="social-btn google"><i class="bi bi-google"></i></a>
                    <a href="#" class="social-btn facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-btn linkedin"><i class="bi bi-linkedin"></i></a>
                </div>
                
                <div class="auth-footer mt-4 text-center">
                    <p>Don't have an account? <a href="login.php?register=true" class="auth-link">Sign Up</a></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = event.currentTarget.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
        
        // Display error message for failed login/registration
        document.addEventListener('DOMContentLoaded', function() {
            // Auto fade out PHP-generated alerts
            const phpAlerts = document.querySelectorAll('.alert.alert-danger');
            if (phpAlerts.length > 0) {
                phpAlerts.forEach(alert => {
                    // Auto hide the alert after 3 seconds
                    setTimeout(() => {
                        alert.classList.add('alert-fade-out');
                        setTimeout(() => {
                            alert.remove();
                        }, 500);
                    }, 3000);
                });
            }
            
            <?php if ((isset($loginError) || isset($registerError)) && !isset($_POST)): ?>
            // Only show JavaScript alert if the error wasn't already displayed by PHP
            // This prevents duplicate error messages
            const errorMessage = <?php echo isset($loginError) ? json_encode($loginError) : json_encode($registerError); ?>;
            
            // Check if an error alert already exists
            const existingAlert = document.querySelector('.alert.alert-danger');
            
            if (!existingAlert) {
                const alertElement = document.createElement('div');
                alertElement.className = 'alert alert-danger mt-3 mb-3';
                alertElement.role = 'alert';
                alertElement.textContent = errorMessage;
                
                const form = document.querySelector('form');
                form.parentNode.insertBefore(alertElement, form);
                
                // Auto hide the alert after 3 seconds
                setTimeout(() => {
                    alertElement.classList.add('alert-fade-out');
                    setTimeout(() => {
                        alertElement.remove();
                    }, 500);
                }, 3000);
            }
            <?php endif; ?>
        });
    </script>
</body>
</html>
