<?php
/**
 * Sidebar component for dashboard
 */

// Get user data from session
$user = $_SESSION['user'];
$userName = $user['first_name'] . ' ' . $user['last_name'];
$userEmail = $user['email'];
$userType = $user['user_type'] ?? 'freelancer';

// Get current theme
$savedTheme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : null;
$systemTheme = isset($_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME']) ? $_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME'] : null;
$initialTheme = $savedTheme ?: ($systemTheme ?: 'light');
?>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="/web/index.php" class="sidebar-brand">
            <img src="/web/assets/images/logo_dark.svg" alt="LenSi Logo" class="sidebar-logo">
            LenSi
        </a>
    </div>
    
    <div class="sidebar-body">
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="index.php" class="sidebar-menu-link <?php echo (!isset($_GET['page']) || $_GET['page'] === 'dashboard') ? 'active' : ''; ?>">
                    <i class="bi bi-grid-1x2-fill sidebar-menu-icon"></i>
                    Dashboard
                </a>
            </li>
            <?php if ($userType === 'freelancer'): ?>
            <li class="sidebar-menu-item">
                <a href="?page=projects" class="sidebar-menu-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'projects') ? 'active' : ''; ?>">
                    <i class="bi bi-briefcase-fill sidebar-menu-icon"></i>
                    Projects
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="?page=services" class="sidebar-menu-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'services') ? 'active' : ''; ?>">
                    <i class="bi bi-person-workspace sidebar-menu-icon"></i>
                    My Services
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="?page=messages" class="sidebar-menu-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'messages') ? 'active' : ''; ?>">
                    <i class="bi bi-chat-dots-fill sidebar-menu-icon"></i>
                    Messages
                </a>
            </li>
            <?php elseif ($userType === 'employer'): ?>
            <li class="sidebar-menu-item">
                <a href="?page=my-projects" class="sidebar-menu-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'my-projects') ? 'active' : ''; ?>">
                    <i class="bi bi-briefcase-fill sidebar-menu-icon"></i>
                    My Projects
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="?page=find-talent" class="sidebar-menu-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'find-talent') ? 'active' : ''; ?>">
                    <i class="bi bi-people-fill sidebar-menu-icon"></i>
                    Find Talent
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="?page=messages" class="sidebar-menu-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'messages') ? 'active' : ''; ?>">
                    <i class="bi bi-chat-dots-fill sidebar-menu-icon"></i>
                    Messages
                </a>
            </li>
            <?php endif; ?>
            <li class="sidebar-menu-item">
                <a href="?page=payments" class="sidebar-menu-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'payments') ? 'active' : ''; ?>">
                    <i class="bi bi-wallet2 sidebar-menu-icon"></i>
                    Payments
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="?page=profile" class="sidebar-menu-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'profile') ? 'active' : ''; ?>">
                    <i class="bi bi-person-circle sidebar-menu-icon"></i>
                    Profile
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="?page=settings" class="sidebar-menu-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'settings') ? 'active' : ''; ?>">
                    <i class="bi bi-gear-fill sidebar-menu-icon"></i>
                    Settings
                </a>
            </li>
        </ul>
    </div>
    
    <div class="sidebar-footer">
        <div class="theme-switch">
            <label for="theme-toggle" class="d-flex align-items-center">
                <small class="me-2">Theme</small>
                <input type="checkbox" id="theme-toggle" <?php echo $initialTheme === 'dark' ? 'checked' : ''; ?>>
                <span class="theme-slider"></span>
            </label>
        </div>
        <a href="../Login/login.php?logout=true" class="sidebar-menu-link text-danger">
            <i class="bi bi-box-arrow-right"></i>
            Logout
        </a>
    </div>
</aside>

<!-- Mobile sidebar toggle button -->
<button class="sidebar-toggle-btn" id="sidebarToggleBtn">
    <i class="bi bi-list"></i>
</button>