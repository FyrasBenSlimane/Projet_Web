<?php
/**
 * Dashboard Main Controller
 * This file serves as the entry point for the dashboard and implements MVC architecture
 */

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user'])) {
    header('Location: ../Login/login.php');
    exit;
}

// Get user data from session
$userName = $_SESSION['user']['name'] ?? '';
$userType = $_SESSION['user']['user_type'] ?? 'freelancer'; // Default to freelancer if not set
$user = $_SESSION['user'] ?? [];

// Get theme preference from cookies or system preference
$savedTheme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : null;
$systemTheme = isset($_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME']) ? $_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME'] : null;
$initialTheme = $savedTheme ?: ($systemTheme ?: 'light');

// Include controllers
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/UserController.php';

// Initialize controllers
$dashboardController = new DashboardController();
$userController = new UserController();

// Handle page routing
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Process form submissions first
$formResult = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($page) {
        case 'profile':
            $formResult = $userController->updateProfile();
            break;
        case 'settings':
            if (isset($_POST['change_password'])) {
                $formResult = $userController->updatePassword();
            } elseif (isset($_POST['update_notifications'])) {
                $formResult = $userController->updateNotifications();
            } elseif (isset($_POST['update_privacy'])) {
                $formResult = $userController->updatePrivacy();
            }
            break;
    }
}

// Start output buffering to capture the page content
ob_start();

// Route to the appropriate controller/action based on the page parameter
switch ($page) {
    case 'dashboard':
        $dashboardController->index();
        break;
    case 'job-offers':
        include_once __DIR__ . '/../home/offers/offers.php';
        break;
    case 'profile':
        $userController->profile();
        break;
    case 'settings':
        $userController->settings();
        break;
    default:
        // Default to dashboard if page not found
        $dashboardController->index();
        break;
}

// Get the content generated by the controller action
$pageContent = ob_get_clean();

// Now include the layout template which will use $pageContent
include_once __DIR__ . '/views/layout.php';
?>