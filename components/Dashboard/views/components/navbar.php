<?php
/**
 * Navbar component for dashboard
 */

// Get user data from session
$user = $_SESSION['user'];
$userName = $user['first_name'] . ' ' . $user['last_name'];
$userEmail = $user['email'];
$userType = $user['user_type'] ?? 'freelancer';

// Get avatar URL
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&size=128&background=random";

// Check if user has a profile image
require_once __DIR__ . '/../../models/UserModel.php';
$userModel = new UserModel();
$profileData = $userModel->getUserProfile($userEmail);
if ($profileData && !empty($profileData['profile_image'])) {
    $avatarUrl = $profileData['profile_image'];
}
?>

<!-- Top Navigation -->
<nav class="topbar">
    <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <?php 
            $pageTitle = 'Dashboard';
            if (isset($_GET['page'])) {
                switch ($_GET['page']) {
                    case 'profile':
                        $pageTitle = 'Profile';
                        break;
                    case 'settings':
                        $pageTitle = 'Settings';
                        break;
                    case 'projects':
                    case 'my-projects':
                        $pageTitle = 'Projects';
                        break;
                    case 'services':
                        $pageTitle = 'My Services';
                        break;
                    case 'messages':
                        $pageTitle = 'Messages';
                        break;
                    case 'payments':
                        $pageTitle = 'Payments';
                        break;
                    case 'find-talent':
                        $pageTitle = 'Find Talent';
                        break;
                    case 'tickets':
                        $pageTitle = 'Support Tickets';
                        break;
                    case 'analytics':
                        $pageTitle = 'Analytics';
                        break;
                    case 'earnings':
                        $pageTitle = 'Earnings';
                        break;
                    case 'contracts':
                        $pageTitle = 'Contracts';
                        break;
                    case 'proposals':
                        $pageTitle = 'Proposals';
                        break;
                    case 'create-project':
                        $pageTitle = 'Create Project';
                        break;
                    default:
                        // Check if it's a project view page
                        if (strpos($_GET['page'], 'project-') === 0) {
                            $pageTitle = 'Project Details';
                        }
                        break;
                }
            }
            
            // Add breadcrumb data for JavaScript functionality
            echo '<span class="page-breadcrumb-data" data-current="' . htmlspecialchars($pageTitle) . '"></span>';
            echo $pageTitle;
            ?>
        </h1>
    </div>
    
    <div class="topbar-right">
        <div class="topbar-search">
            <input type="text" class="topbar-search-input" placeholder="Search...">
            <i class="bi bi-search topbar-search-icon"></i>
        </div>
        
        <div class="topbar-actions">
            <button class="topbar-action-btn">
                <i class="bi bi-bell-fill"></i>
                <span class="notification-badge">3</span>
            </button>
            
            <button class="topbar-action-btn">
                <i class="bi bi-envelope-fill"></i>
                <span class="notification-badge">5</span>
            </button>
            
            <div class="user-profile dropdown">
                <div class="d-flex align-items-center" data-bs-toggle="dropdown">
                    <img src="<?php echo $avatarUrl; ?>" alt="<?php echo $userName; ?>" class="user-avatar">
                    <span class="user-name"><?php echo $userName; ?></span>
                </div>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="?page=profile"><i class="bi bi-person-circle me-2"></i>My Profile</a></li>
                    <li><a class="dropdown-item" href="?page=settings"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../Login/login.php?logout=true"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>