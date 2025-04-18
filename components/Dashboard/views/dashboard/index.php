<?php
/**
 * Dashboard main view
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
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo $initialTheme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="LenSi Dashboard - Manage your freelance work and projects">
    <meta name="theme-color" content="#3E5C76">
    <title>Dashboard - LenSi Freelance Marketplace</title>
    
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
        --sidebar-width: 280px;
        --topbar-height: 70px;
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
        background-color: var(--light-gray);
        color: var(--accent);
        min-height: 100vh;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }
    
    /* Layout */
    .dashboard-container {
        display: flex;
        width: 100%;
        min-height: 100vh;
        position: relative;
    }
    
    /* Sidebar */
    .sidebar {
        width: var(--sidebar-width);
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background-color: white;
        box-shadow: var(--shadow-md);
        z-index: 1030;
        transition: transform 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    
    [data-bs-theme="dark"] .sidebar {
        background-color: var(--accent-dark);
    }
    
    .sidebar-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    [data-bs-theme="dark"] .sidebar-header {
        border-bottom-color: rgba(255,255,255,0.05);
    }
    
    .sidebar-brand {
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 1.5rem;
        color: var(--primary);
        text-decoration: none;
        display: flex;
        align-items: center;
    }
    
    .sidebar-brand:hover {
        color: var(--accent);
    }
    
    [data-bs-theme="dark"] .sidebar-brand {
        color: var(--secondary);
    }
    
    [data-bs-theme="dark"] .sidebar-brand:hover {
        color: var(--accent);
    }
    
    .sidebar-logo {
        height: 30px;
        margin-right: 10px;
    }
    
    .sidebar-body {
        flex: 1;
        overflow-y: auto;
        padding: 1rem 0;
    }
    
    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .sidebar-menu-item {
        margin-bottom: 0.25rem;
    }
    
    .sidebar-menu-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1.5rem;
        color: var(--accent);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }
    
    .sidebar-menu-link:hover, .sidebar-menu-link.active {
        background-color: rgba(var(--primary-rgb), 0.05);
        color: var(--primary);
        border-left-color: var(--primary);
    }
    
    [data-bs-theme="dark"] .sidebar-menu-link:hover, 
    [data-bs-theme="dark"] .sidebar-menu-link.active {
        background-color: rgba(143, 179, 222, 0.1);
        color: var(--secondary);
        border-left-color: var(--secondary);
    }
    
    .sidebar-menu-icon {
        font-size: 1.2rem;
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }
    
    .sidebar-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    [data-bs-theme="dark"] .sidebar-footer {
        border-top-color: rgba(255,255,255,0.05);
    }
    
    .sidebar-toggle-btn {
        display: none;
        position: fixed;
        bottom: 20px;
        left: 20px;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background-color: var(--primary);
        color: white;
        border: none;
        box-shadow: var(--shadow-md);
        z-index: 1040;
        font-size: 1.2rem;
    }
    
    /* Content Area */
    .content {
        flex: 1;
        margin-left: var(--sidebar-width);
        width: calc(100% - var(--sidebar-width));
        min-height: 100vh;
        transition: margin-left 0.3s ease, width 0.3s ease;
    }
    
    /* Top Navigation */
    .topbar {
        height: var(--topbar-height);
        background-color: white;
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 1.5rem;
        position: sticky;
        top: 0;
        z-index: 1020;
    }
    
    [data-bs-theme="dark"] .topbar {
        background-color: var(--accent-dark);
    }
    
    .topbar-left {
        display: flex;
        align-items: center;
    }
    
    .menu-toggle {
        background: transparent;
        border: none;
        color: var(--accent);
        font-size: 1.5rem;
        cursor: pointer;
        margin-right: 1rem;
        display: none;
    }
    
    .page-title {
        font-family: var(--font-heading);
        font-weight: 600;
        font-size: 1.4rem;
        color: var(--accent-dark);
        margin: 0;
    }
    
    [data-bs-theme="dark"] .page-title {
        color: var(--light);
    }
    
    .topbar-right {
        display: flex;
        align-items: center;
    }
    
    .topbar-search {
        position: relative;
        margin-right: 1rem;
    }
    
    .topbar-search-input {
        padding: 0.5rem 1rem 0.5rem 2.5rem;
        border-radius: 20px;
        border: 1px solid rgba(0,0,0,0.1);
        font-size: 0.9rem;
        background-color: var(--light-gray);
        width: 220px;
        transition: all 0.3s ease;
    }
    
    [data-bs-theme="dark"] .topbar-search-input {
        background-color: rgba(255,255,255,0.1);
        border-color: rgba(255,255,255,0.05);
        color: var(--light);
    }
    
    .topbar-search-input:focus {
        outline: none;
        width: 280px;
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
    }
    
    [data-bs-theme="dark"] .topbar-search-input:focus {
        box-shadow: 0 0 0 3px rgba(143, 179, 222, 0.2);
    }
    
    .topbar-search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--accent);
        font-size: 0.9rem;
    }
    
    .topbar-actions {
        display: flex;
        align-items: center;
    }
    
    .topbar-action-btn {
        background: transparent;
        border: none;
        color: var(--accent);
        font-size: 1.2rem;
        cursor: pointer;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 0.5rem;
        position: relative;
        transition: all 0.2s ease;
    }
    
    .topbar-action-btn:hover {
        background-color: var(--light-gray);
    }
    
    [data-bs-theme="dark"] .topbar-action-btn:hover {
        background-color: rgba(255,255,255,0.1);
    }
    
    .notification-badge {
        position: absolute;
        top: 3px;
        right: 3px;
        width: 18px;
        height: 18px;
        background-color: #dc3545;
        color: white;
        font-size: 0.7rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .user-profile {
        display: flex;
        align-items: center;
        margin-left: 1rem;
        cursor: pointer;
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 0.5rem;
    }
    
    .user-name {
        font-weight: 500;
        font-size: 0.9rem;
        color: var(--accent-dark);
    }
    
    [data-bs-theme="dark"] .user-name {
        color: var(--light);
    }
    
    /* Main Content Area */
    .main-content {
        padding: 1.5rem;
        min-height: calc(100vh - var(--topbar-height));
    }
    
    .welcome-section {
        background-color: white;
        border-radius: var(--radius-md);
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-sm);
    }
    
    [data-bs-theme="dark"] .welcome-section {
        background-color: var(--accent-dark);
    }
    
    .welcome-title {
        font-family: var(--font-heading);
        font-weight: 600;
        font-size: 1.5rem;
        color: var(--accent-dark);
        margin-bottom: 0.5rem;
    }
    
    [data-bs-theme="dark"] .welcome-title {
        color: var(--light);
    }
    
    .welcome-subtitle {
        color: var(--accent);
        margin-bottom: 1rem;
    }
    
    /* Dashboard Cards */
    .dashboard-stats {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .stat-card {
        background-color: white;
        border-radius: var(--radius-md);
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
    }
    
    [data-bs-theme="dark"] .stat-card {
        background-color: var(--accent-dark);
    }
    
    .stat-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-3px);
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .stat-icon.blue {
        background-color: rgba(var(--primary-rgb), 0.1);
        color: var(--primary);
    }
    
    .stat-icon.green {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
    }
    
    .stat-icon.orange {
        background-color: rgba(255, 153, 0, 0.1);
        color: #ff9900;
    }
    
    .stat-icon.purple {
        background-color: rgba(137, 80, 252, 0.1);
        color: #8950fc;
    }
    
    .stat-title {
        font-family: var(--font-primary);
        font-weight: 500;
        font-size: 0.9rem;
        color: var(--accent);
        margin-bottom: 0.25rem;
    }
    
    .stat-value {
        font-family: var(--font-heading);
        font-weight: 600;
        font-size: 1.8rem;
        color: var(--accent-dark);
    }
    
    [data-bs-theme="dark"] .stat-value {
        color: var(--light);
    }
    
    .stat-change {
        color: #198754;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
    }
    
    .stat-change.negative {
        color: #dc3545;
    }
    
    /* Dashboard Tables */
    .dashboard-table-section {
        background-color: white;
        border-radius: var(--radius-md);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-sm);
    }
    
    [data-bs-theme="dark"] .dashboard-table-section {
        background-color: var(--accent-dark);
    }
    
    .dashboard-table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .dashboard-table-title {
        font-family: var(--font-heading);
        font-weight: 600;
        font-size: 1.2rem;
        color: var(--accent-dark);
    }
    
    [data-bs-theme="dark"] .dashboard-table-title {
        color: var(--light);
    }
    
    .dashboard-table-action {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
    }
    
    [data-bs-theme="dark"] .dashboard-table-action {
        color: var(--secondary);
    }
    
    .table {
        margin-bottom: 0;
    }
    
    [data-bs-theme="dark"] .table {
        color: var(--light);
    }
    
    .table thead th {
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--accent);
        border-bottom-width: 1px;
    }
    
    .table tbody td {
        vertical-align: middle;
        padding: 0.75rem;
    }
    
    .status-badge {
        padding: 0.35rem 0.65rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .status-badge.completed {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
    }
    
    .status-badge.in-progress {
        background-color: rgba(255, 153, 0, 0.1);
        color: #ff9900;
    }
    
    .status-badge.on-hold {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    
    /* Responsive */
    @media (max-width: 992px) {
        .sidebar {
            transform: translateX(-100%);
        }
        
        .sidebar.expanded {
            transform: translateX(0);
        }
        
        .content {
            margin-left: 0;
            width: 100%;
        }
        
        .menu-toggle {
            display: block;
        }
        
        .sidebar-toggle-btn {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .topbar-search-input {
            width: 180px;
        }
        
        .topbar-search-input:focus {
            width: 220px;
        }
    }
    
    @media (max-width: 768px) {
        .user-name {
            display: none;
        }
        
        .dashboard-stats {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }
        
        .topbar-search {
            display: none;
        }
    }
    
    @media (max-width: 576px) {
        .topbar {
            padding: 0 1rem;
        }
        
        .main-content {
            padding: 1rem;
        }
        
        .welcome-section,
        .dashboard-table-section {
            padding: 1.25rem;
        }
        
        .dashboard-stats {
            grid-template-columns: 1fr;
        }
        
        .welcome-title {
            font-size: 1.3rem;
        }
        
        .dashboard-table-section {
            overflow-x: auto;
        }
    }
    
    /* Theme Toggle Switch */
    .theme-switch {
        position: relative;
        width: 60px;
        height: 30px;
    }
    
    .theme-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .theme-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: var(--light-gray);
        transition: .4s;
        border-radius: 30px;
    }
    
    .theme-slider:before {
        position: absolute;
        content: "🌙";
        display: flex;
        align-items: center;
        justify-content: center;
        height: 22px;
        width: 22px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        border-radius: 50%;
        transition: .4s;
        font-size: 12px;
    }
    
    input:checked + .theme-slider {
        background-color: var(--primary);
    }
    
    input:checked + .theme-slider:before {
        transform: translateX(30px);
        content: "☀️";
    }
    
    [data-bs-theme="dark"] input:checked + .theme-slider {
        background-color: var(--secondary);
    }
    
    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    .fade-in {
        animation: fadeIn 0.5s ease;
    }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include_once __DIR__ . '/../components/sidebar.php'; ?>
        
        <!-- Content Area -->
        <div class="content" id="content">
            <!-- Include Navbar -->
            <?php include_once __DIR__ . '/../components/navbar.php'; ?>
            
            <!-- Main Content -->
            <main class="main-content fade-in">
                <!-- Welcome Section -->
                <section class="welcome-section">
                    <h2 class="welcome-title">Welcome back, <?php echo $user['first_name']; ?>!</h2>
                    <p class="welcome-subtitle">Here's what's happening with your account today.</p>
                    
                    <div class="d-flex flex-wrap gap-3">
                        <a href="#" class="btn btn-primary">
                            <?php echo $userType === 'freelancer' ? 'Find Projects' : 'Post a Project'; ?>
                        </a>
                        <a href="?page=profile" class="btn btn-outline-secondary">Complete Your Profile</a>
                    </div>
                </section>
                
                <!-- Stats Section -->
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <i class="bi bi-briefcase-fill"></i>
                        </div>
                        <div class="stat-title">Active Projects</div>
                        <div class="stat-value">12</div>
                        <div class="stat-change">
                            <i class="bi bi-arrow-up-short"></i> 8% from last month
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div class="stat-title">Total Earnings</div>
                        <div class="stat-value">$2,850</div>
                        <div class="stat-change">
                            <i class="bi bi-arrow-up-short"></i> 12% from last month
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <div class="stat-title">Avg. Rating</div>
                        <div class="stat-value">4.8</div>
                        <div class="stat-change">
                            <i class="bi bi-arrow-up-short"></i> 0.2 from last month
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon purple">
                            <i class="bi bi-clock-fill"></i>
                        </div>
                        <div class="stat-title">Hours Worked</div>
                        <div class="stat-value">187</div>
                        <div class="stat-change negative">
                            <i class="bi bi-arrow-down-short"></i> 5% from last month
                        </div>
                    </div>
                </div>
                
                <!-- Recent Projects Section -->
                <section class="dashboard-table-section">
                    <div class="dashboard-table-header">
                        <h3 class="dashboard-table-title">Recent Projects</h3>
                        <a href="#" class="dashboard-table-action">View All</a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th>Client</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Budget</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Website Redesign</td>
                                    <td>TechCorp Inc.</td>
                                    <td>May 15, 2025</td>
                                    <td><span class="status-badge in-progress">In Progress</span></td>
                                    <td>$1,500</td>
                                </tr>
                                <tr>
                                    <td>Mobile App Development</td>
                                    <td>StartUp Labs</td>
                                    <td>June 28, 2025</td>
                                    <td><span class="status-badge in-progress">In Progress</span></td>
                                    <td>$3,200</td>
                                </tr>
                                <tr>
                                    <td>Logo Design Package</td>
                                    <td>Creative Agency</td>
                                    <td>April 30, 2025</td>
                                    <td><span class="status-badge completed">Completed</span></td>
                                    <td>$800</td>
                                </tr>
                                <tr>
                                    <td>SEO Optimization</td>
                                    <td>E-commerce Shop</td>
                                    <td>May 10, 2025</td>
                                    <td><span class="status-badge on-hold">On Hold</span></td>
                                    <td>$1,200</td>
                                </tr>
                                <tr>
                                    <td>Content Writing</td>
                                    <td>Blog Network</td>
                                    <td>April 25, 2025</td>
                                    <td><span class="status-badge completed">Completed</span></td>
                                    <td>$500</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
                
                <!-- Recent Messages Section -->
                <section class="dashboard-table-section">
                    <div class="dashboard-table-header">