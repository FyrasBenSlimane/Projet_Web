<?php
/**
 * Dashboard Controller - Handles dashboard operations
 */
class DashboardController {
    /**
     * Display dashboard home page
     */
    public function index() {
        // Get user data from session
        $user = $_SESSION['user'];
        $userName = $user['first_name'] . ' ' . $user['last_name'];
        $userEmail = $user['email'];
        $userType = $user['user_type'] ?? 'freelancer';
        
        // Include just the dashboard content - no surrounding layout
        include_once __DIR__ . '/../views/dashboard/content.php';
    }
}