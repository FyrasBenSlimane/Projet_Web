<?php
/**
 * User Controller - Handles all user-related operations
 */
class UserController {
    private $userModel;
    
    /**
     * Constructor - Initialize models
     */
    public function __construct() {
        require_once __DIR__ . '/../models/UserModel.php';
        $this->userModel = new UserModel();
    }
    
    /**
     * Display user profile
     */
    public function profile() {
        // Get user data from session
        $user = $_SESSION['user'];
        $userEmail = $user['email'];
        
        // Get user profile data
        $profileData = $this->userModel->getUserProfile($userEmail);
        
        // Include the view - Fixed path to match actual directory structure
        include_once __DIR__ . '/../user/profile.php';
    }
    
    /**
     * Update user profile
     */
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['update_profile'])) {
            return false;
        }
        
        $userEmail = $_SESSION['user']['email'];
        
        $profileData = [
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'bio' => trim($_POST['bio'] ?? ''),
            'skills' => trim($_POST['skills'] ?? ''),
            'hourly_rate' => isset($_POST['hourly_rate']) ? (float)$_POST['hourly_rate'] : 0,
            'location' => trim($_POST['location'] ?? ''),
            'website' => trim($_POST['website'] ?? '')
        ];
        
        // Validate inputs
        $errors = [];
        
        if (empty($profileData['first_name'])) {
            $errors[] = "First name is required";
        }
        
        if (empty($profileData['last_name'])) {
            $errors[] = "Last name is required";
        }
        
        // If no errors, update the database
        if (empty($errors)) {
            // Handle profile image upload if present
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                // Process image upload logic here
                // For now, we'll just use a placeholder
                $profileData['profile_image'] = "https://ui-avatars.com/api/?name=" . urlencode($profileData['first_name'] . ' ' . $profileData['last_name']) . "&size=128&background=random";
            }
            
            // Update user profile
            $result = $this->userModel->updateProfile($userEmail, $profileData);
            
            if ($result) {
                // Update session data
                $_SESSION['user']['first_name'] = $profileData['first_name'];
                $_SESSION['user']['last_name'] = $profileData['last_name'];
                $_SESSION['user']['name'] = $profileData['first_name'] . ' ' . $profileData['last_name'];
                
                return ['success' => "Profile updated successfully!"];
            } else {
                return ['error' => "Failed to update profile. Please try again."];
            }
        } else {
            return ['error' => implode("<br>", $errors)];
        }
    }
    
    /**
     * Display user settings
     */
    public function settings() {
        // Get user data from session
        $user = $_SESSION['user'];
        $userEmail = $user['email'];
        $userName = $user['first_name'] . ' ' . $user['last_name'];
        $userType = $user['user_type'] ?? 'freelancer'; // Ensure user_type is defined
        
        // Get user settings
        $settings = $this->userModel->getUserSettings($userEmail);
        
        // Create user settings if they don't exist
        if (!$this->userModel->userSettingsExist($userEmail)) {
            $defaultSettings = [
                'email_notifications' => 1,
                'project_notifications' => 1,
                'message_notifications' => 1,
                'marketing_emails' => 0,
                'profile_visibility' => 'public',
                'show_earnings' => 0,
                'show_projects' => 1
            ];
            $this->userModel->updateUserSettings($userEmail, $defaultSettings);
        }
        
        // Get avatar URL
        $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&size=128&background=random";
        
        $profileData = $this->userModel->getUserProfile($userEmail);
        if ($profileData && !empty($profileData['profile_image'])) {
            $avatarUrl = $profileData['profile_image'];
        }
        
        // Include the view - Fixed path to match actual directory structure
        include_once __DIR__ . '/../user/settings.php';
    }
    
    /**
     * Update user password
     */
    public function updatePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['change_password'])) {
            return false;
        }
        
        $userEmail = $_SESSION['user']['email'];
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate inputs
        $errors = [];
        
        // Check if current password is correct
        $userData = $this->userModel->getUserByEmail($userEmail);
        
        if (!$userData || !password_verify($current_password, $userData['password'])) {
            $errors[] = "Current password is incorrect";
        }
        
        if (strlen($new_password) < 8) {
            $errors[] = "New password must be at least 8 characters long";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        }
        
        // If no errors, update the password
        if (empty($errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $result = $this->userModel->updatePassword($userEmail, $hashed_password);
            
            if ($result) {
                return ['password_success' => "Password updated successfully!"];
            } else {
                return ['password_error' => "Failed to update password. Please try again."];
            }
        } else {
            return ['password_error' => implode("<br>", $errors)];
        }
    }
    
    /**
     * Update notification settings
     */
    public function updateNotifications() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['update_notifications'])) {
            return false;
        }
        
        $userEmail = $_SESSION['user']['email'];
        
        $settings = [
            'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
            'project_notifications' => isset($_POST['project_notifications']) ? 1 : 0,
            'message_notifications' => isset($_POST['message_notifications']) ? 1 : 0,
            'marketing_emails' => isset($_POST['marketing_emails']) ? 1 : 0
        ];
        
        $result = $this->userModel->updateUserSettings($userEmail, $settings);
        
        if ($result) {
            return ['notification_success' => "Notification settings updated successfully!"];
        } else {
            return ['notification_error' => "Failed to update notification settings. Please try again."];
        }
    }
    
    /**
     * Update privacy settings
     */
    public function updatePrivacy() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['update_privacy'])) {
            return false;
        }
        
        $userEmail = $_SESSION['user']['email'];
        
        $settings = [
            'profile_visibility' => $_POST['profile_visibility'],
            'show_earnings' => isset($_POST['show_earnings']) ? 1 : 0,
            'show_projects' => isset($_POST['show_projects']) ? 1 : 0
        ];
        
        $result = $this->userModel->updateUserSettings($userEmail, $settings);
        
        if ($result) {
            return ['privacy_success' => "Privacy settings updated successfully!"];
        } else {
            return ['privacy_error' => "Failed to update privacy settings. Please try again."];
        }
    }
    
    /**
     * Delete user account
     */
    public function deleteAccount() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['delete_account'])) {
            return false;
        }
        
        $userEmail = $_SESSION['user']['email'];
        $confirmation = $_POST['delete_confirmation'] ?? '';
        
        if ($confirmation !== 'DELETE') {
            return ['delete_error' => "Incorrect confirmation text. Please type 'DELETE' to confirm account deletion."];
        }
        
        $result = $this->userModel->deleteUser($userEmail);
        
        if ($result) {
            // Clear session and cookies
            $_SESSION = array();
            
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            session_destroy();
            
            return ['success' => true, 'message' => "Your account has been successfully deleted."];
        } else {
            return ['delete_error' => "Failed to delete your account. Please try again or contact support."];
        }
    }
    
    /**
     * Generate and download user data
     */
    public function downloadUserData() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['download_data'])) {
            return false;
        }
        
        $userEmail = $_SESSION['user']['email'];
        
        // Get all user data
        $userData = $this->userModel->getUserByEmail($userEmail);
        $profileData = $this->userModel->getUserProfile($userEmail);
        $settingsData = $this->userModel->getUserSettings($userEmail);
        
        // Remove sensitive information
        unset($userData['password']);
        
        // Combine all data
        $allData = [
            'user' => $userData,
            'profile' => $profileData,
            'settings' => $settingsData
        ];
        
        // Encode as JSON
        $jsonData = json_encode($allData, JSON_PRETTY_PRINT);
        
        // Set headers for download
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="user_data_' . date('Y-m-d') . '.json"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($jsonData));
        
        // Output the JSON data
        echo $jsonData;
        exit;
        
        // Return data for download
        return [
            'success' => true,
            'data' => $jsonData,
            'filename' => 'user_data_' . date('Y-m-d') . '.json'
        ];
    }
}