<?php
/**
 * User Settings Content
 * This file contains ONLY the settings content to be inserted into the main dashboard layout
 */

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include database connection
    require_once __DIR__ . '/../../../config/database.php';
    // Make sure we have the $pdo connection variable available from database.php
    if (!isset($pdo) || $pdo === null) {
        $db_error = "Database connection error. Please try again later.";
    } else {
        // Change password form
        if (isset($_POST['change_password'])) {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Validate inputs
            $errors = [];
            
            // Check if current password is correct
            $stmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
            $stmt->execute([$userEmail]);
            $user_data = $stmt->fetch();
            
            if (!$user_data || !password_verify($current_password, $user_data['password'])) {
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
                try {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                    $result = $stmt->execute([$hashed_password, $userEmail]);
                    
                    if ($result) {
                        $password_success = "Password updated successfully!";
                    } else {
                        $password_error = "Failed to update password. Please try again.";
                    }
                } catch (PDOException $e) {
                    $password_error = "Database error: " . $e->getMessage();
                }
            } else {
                $password_error = implode("<br>", $errors);
            }
        }
        
        // Update notification settings
        if (isset($_POST['update_notifications'])) {
            $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
            $project_notifications = isset($_POST['project_notifications']) ? 1 : 0;
            $message_notifications = isset($_POST['message_notifications']) ? 1 : 0;
            $marketing_emails = isset($_POST['marketing_emails']) ? 1 : 0;
            
            try {
                // Check if settings exist
                $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_email = ?");
                $stmt->execute([$userEmail]);
                $settings = $stmt->fetch();
                
                if ($settings) {
                    // Update existing settings
                    $stmt = $pdo->prepare("UPDATE user_settings SET email_notifications = ?, project_notifications = ?, message_notifications = ?, marketing_emails = ? WHERE user_email = ?");
                    $stmt->execute([$email_notifications, $project_notifications, $message_notifications, $marketing_emails, $userEmail]);
                } else {
                    // Create new settings
                    $stmt = $pdo->prepare("INSERT INTO user_settings (user_email, email_notifications, project_notifications, message_notifications, marketing_emails) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$userEmail, $email_notifications, $project_notifications, $message_notifications, $marketing_emails]);
                }
                
                $notification_success = "Notification settings updated successfully!";
                
            } catch (PDOException $e) {
                $notification_error = "Database error: " . $e->getMessage();
            }
        }
        
        // Update privacy settings
        if (isset($_POST['update_privacy'])) {
            $profile_visibility = $_POST['profile_visibility'];
            $show_earnings = isset($_POST['show_earnings']) ? 1 : 0;
            $show_projects = isset($_POST['show_projects']) ? 1 : 0;
            
            try {
                // Check if settings exist
                $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_email = ?");
                $stmt->execute([$userEmail]);
                $settings = $stmt->fetch();
                
                if ($settings) {
                    // Update existing settings
                    $stmt = $pdo->prepare("UPDATE user_settings SET profile_visibility = ?, show_earnings = ?, show_projects = ? WHERE user_email = ?");
                    $stmt->execute([$profile_visibility, $show_earnings, $show_projects, $userEmail]);
                } else {
                    // Create new settings
                    $stmt = $pdo->prepare("INSERT INTO user_settings (user_email, profile_visibility, show_earnings, show_projects) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$userEmail, $profile_visibility, $show_earnings, $show_projects]);
                }
                
                $privacy_success = "Privacy settings updated successfully!";
                
            } catch (PDOException $e) {
                $privacy_error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Fetch user settings
try {
    require_once __DIR__ . '/../../../config/database.php';
    
    if (!isset($pdo) || $pdo === null) {
        throw new Exception("Database connection error. Please try again later.");
    }
    
    $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_email = ?");
    $stmt->execute([$userEmail]);
    $settings = $stmt->fetch();
    
    // If settings don't exist, initialize with default values
    if (!$settings) {
        $settings = [
            'email_notifications' => 1,
            'project_notifications' => 1,
            'message_notifications' => 1,
            'marketing_emails' => 0,
            'profile_visibility' => 'public',
            'show_earnings' => 0,
            'show_projects' => 1
        ];
    }
    
} catch (PDOException $e) {
    $fetch_error = "Could not fetch settings data: " . $e->getMessage();
    // Create default settings even on error
    $settings = [
        'email_notifications' => 1,
        'project_notifications' => 1,
        'message_notifications' => 1,
        'marketing_emails' => 0,
        'profile_visibility' => 'public',
        'show_earnings' => 0,
        'show_projects' => 1
    ];
} catch (Exception $e) {
    $fetch_error = $e->getMessage();
    // Create default settings even on exception
    $settings = [
        'email_notifications' => 1,
        'project_notifications' => 1,
        'message_notifications' => 1,
        'marketing_emails' => 0,
        'profile_visibility' => 'public',
        'show_earnings' => 0,
        'show_projects' => 1
    ];
}
?>

<!-- Settings Content - only the content, no HTML/body tags or dashboard container -->
<!-- Profile Header Section -->
<section class="profile-header">
    <div class="profile-avatar-container">
        <img src="<?php echo $avatarUrl; ?>" alt="<?php echo $userName; ?>" class="profile-avatar">
    </div>
    
    <div class="profile-info">
        <h2 class="profile-name"><?php echo $userName; ?></h2>
        <p class="profile-title"><?php echo ucfirst($userType ?? 'freelancer'); ?></p>
        <p><i class="bi bi-envelope-fill me-2"></i><?php echo $userEmail; ?></p>
        
        <div class="profile-actions">
            <a href="?page=profile" class="btn btn-outline-light"><i class="bi bi-pencil me-2"></i>Edit Profile</a>
        </div>
    </div>
</section>

<!-- Settings Tabs -->
<div class="settings-container">
    <ul class="nav nav-tabs settings-tabs" id="settingsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="true">
                <i class="bi bi-shield-lock me-2"></i>Security
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab" aria-controls="notifications" aria-selected="false">
                <i class="bi bi-bell me-2"></i>Notifications
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="privacy-tab" data-bs-toggle="tab" data-bs-target="#privacy" type="button" role="tab" aria-controls="privacy" aria-selected="false">
                <i class="bi bi-eye me-2"></i>Privacy
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="data-tab" data-bs-toggle="tab" data-bs-target="#data" type="button" role="tab" aria-controls="data" aria-selected="false">
                <i class="bi bi-database me-2"></i>Data
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content settings-tab-content" id="settingsTabsContent">
        <!-- Security Tab -->
        <div class="tab-pane fade show active" id="security" role="tabpanel" aria-labelledby="security-tab">
            <div class="settings-panel">
                <h3 class="settings-section-title">Change Password</h3>
                
                <?php if (isset($password_success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo $password_success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($password_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $password_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="?page=settings" class="settings-form">
                    <div class="mb-4">
                        <label for="current_password" class="form-label">Current Password</label>
                        <div class="input-group settings-input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control settings-input" id="current_password" name="current_password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="new_password" class="form-label">New Password</label>
                        <div class="input-group settings-input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control settings-input" id="new_password" name="new_password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Password must be at least 8 characters long.</div>
                        <div class="password-strength mt-2" id="password-strength">
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar" role="progressbar" style="width: 0%;" id="password-strength-bar"></div>
                            </div>
                            <small class="text-muted" id="password-strength-text">Password strength</small>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="input-group settings-input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control settings-input" id="confirm_password" name="confirm_password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="password-match-feedback">Passwords do not match</div>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-primary settings-submit-btn">
                        <i class="bi bi-check-circle me-2"></i>Update Password
                    </button>
                </form>
                
                <div class="settings-divider"></div>
                
                <h3 class="settings-section-title">Account Security</h3>
                
                <div class="security-options">
                    <div class="security-option">
                        <div class="security-option-info">
                            <h4 class="security-option-title">Two-Factor Authentication</h4>
                            <p class="security-option-desc">Add an additional layer of security to your account.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="two_factor" disabled>
                            <div class="security-option-badge">Coming Soon</div>
                        </div>
                    </div>
                    
                    <div class="security-option">
                        <div class="security-option-info">
                            <h4 class="security-option-title">Login Alerts</h4>
                            <p class="security-option-desc">Receive email notifications for new login activities.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="login_alerts" checked>
                        </div>
                    </div>
                    
                    <div class="security-option">
                        <div class="security-option-info">
                            <h4 class="security-option-title">Automatically Log Out</h4>
                            <p class="security-option-desc">Automatically log out after a period of inactivity.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto_logout" checked>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Notifications Tab -->
        <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
            <div class="settings-panel">
                <h3 class="settings-section-title">Notification Preferences</h3>
                
                <?php if (isset($notification_success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo $notification_success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($notification_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $notification_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="?page=settings" class="settings-form">
                    <div class="settings-card mb-4">
                        <div class="settings-card-header">
                            <h4 class="settings-card-title">
                                <i class="bi bi-envelope me-2"></i>Email Notifications
                            </h4>
                        </div>
                        <div class="settings-card-body">
                            <div class="notification-option">
                                <div class="notification-option-info">
                                    <h5 class="notification-option-title">Email Notifications</h5>
                                    <p class="notification-option-desc">Receive important notifications via email.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" <?php echo $settings['email_notifications'] ? 'checked' : ''; ?>>
                                </div>
                            </div>
                            
                            <div class="notification-option">
                                <div class="notification-option-info">
                                    <h5 class="notification-option-title">Marketing Emails</h5>
                                    <p class="notification-option-desc">Receive promotional offers and updates.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="marketing_emails" name="marketing_emails" <?php echo $settings['marketing_emails'] ? 'checked' : ''; ?>>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="settings-card mb-4">
                        <div class="settings-card-header">
                            <h4 class="settings-card-title">
                                <i class="bi bi-app-indicator me-2"></i>Platform Notifications
                            </h4>
                        </div>
                        <div class="settings-card-body">
                            <div class="notification-option">
                                <div class="notification-option-info">
                                    <h5 class="notification-option-title">Project Updates</h5>
                                    <p class="notification-option-desc">Get notified about changes to your projects.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="project_notifications" name="project_notifications" <?php echo $settings['project_notifications'] ? 'checked' : ''; ?>>
                                </div>
                            </div>
                            
                            <div class="notification-option">
                                <div class="notification-option-info">
                                    <h5 class="notification-option-title">Message Notifications</h5>
                                    <p class="notification-option-desc">Receive notifications for new messages.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="message_notifications" name="message_notifications" <?php echo $settings['message_notifications'] ? 'checked' : ''; ?>>
                                </div>
                            </div>
                            
                            <div class="notification-option">
                                <div class="notification-option-info">
                                    <h5 class="notification-option-title">Reminders</h5>
                                    <p class="notification-option-desc">Get reminded about deadlines and tasks.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="reminder_notifications" name="reminder_notifications" checked>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="update_notifications" class="btn btn-primary settings-submit-btn">
                        <i class="bi bi-check-circle me-2"></i>Save Notification Preferences
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Privacy Tab -->
        <div class="tab-pane fade" id="privacy" role="tabpanel" aria-labelledby="privacy-tab">
            <div class="settings-panel">
                <h3 class="settings-section-title">Privacy Settings</h3>
                
                <?php if (isset($privacy_success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo $privacy_success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($privacy_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $privacy_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="?page=settings" class="settings-form">
                    <div class="settings-card mb-4">
                        <div class="settings-card-header">
                            <h4 class="settings-card-title">
                                <i class="bi bi-person-badge me-2"></i>Profile Visibility
                            </h4>
                        </div>
                        <div class="settings-card-body">
                            <div class="mb-4">
                                <label for="profile_visibility" class="form-label">Who can see your profile?</label>
                                <select class="form-select settings-select" id="profile_visibility" name="profile_visibility">
                                    <option value="public" <?php echo $settings['profile_visibility'] === 'public' ? 'selected' : ''; ?>>Public - Anyone can view your profile</option>
                                    <option value="private" <?php echo $settings['profile_visibility'] === 'private' ? 'selected' : ''; ?>>Private - Only you can view your profile</option>
                                    <option value="contacts" <?php echo $settings['profile_visibility'] === 'contacts' ? 'selected' : ''; ?>>Contacts Only - Only your contacts can view your profile</option>
                                </select>
                            </div>
                            
                            <div class="privacy-option">
                                <div class="privacy-option-info">
                                    <h5 class="privacy-option-title">Profile Indexing</h5>
                                    <p class="privacy-option-desc">Allow search engines to index your profile</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="profile_indexing" name="profile_indexing" checked>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="settings-card mb-4">
                        <div class="settings-card-header">
                            <h4 class="settings-card-title">
                                <i class="bi bi-eye me-2"></i>Profile Information
                            </h4>
                        </div>
                        <div class="settings-card-body">
                            <div class="privacy-option">
                                <div class="privacy-option-info">
                                    <h5 class="privacy-option-title">Show Earnings</h5>
                                    <p class="privacy-option-desc">Display your earnings on your public profile.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="show_earnings" name="show_earnings" <?php echo $settings['show_earnings'] ? 'checked' : ''; ?>>
                                </div>
                            </div>
                            
                            <div class="privacy-option">
                                <div class="privacy-option-info">
                                    <h5 class="privacy-option-title">Show Projects</h5>
                                    <p class="privacy-option-desc">Display your projects on your public profile.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="show_projects" name="show_projects" <?php echo $settings['show_projects'] ? 'checked' : ''; ?>>
                                </div>
                            </div>
                            
                            <div class="privacy-option">
                                <div class="privacy-option-info">
                                    <h5 class="privacy-option-title">Show Online Status</h5>
                                    <p class="privacy-option-desc">Let others see when you're online.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="show_online_status" name="show_online_status" checked>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="update_privacy" class="btn btn-primary settings-submit-btn">
                        <i class="bi bi-check-circle me-2"></i>Save Privacy Settings
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Data Management Tab -->
        <div class="tab-pane fade" id="data" role="tabpanel" aria-labelledby="data-tab">
            <div class="settings-panel">
                <h3 class="settings-section-title">Data Management</h3>
                
                <?php if (isset($data_success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo $data_success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($data_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $data_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="settings-card mb-4">
                    <div class="settings-card-header">
                        <h4 class="settings-card-title">
                            <i class="bi bi-download me-2"></i>Your Data
                        </h4>
                    </div>
                    <div class="settings-card-body">
                        <p class="mb-4">You can download a copy of your personal data at any time. This includes your profile information, account settings, and activity history.</p>
                        
                        <form method="POST" action="?page=settings" id="download-data-form">
                            <button type="submit" name="download_data" class="btn btn-primary settings-action-btn">
                                <i class="bi bi-download me-2"></i>Download My Data
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="settings-card mb-4">
                    <div class="settings-card-header">
                        <h4 class="settings-card-title">
                            <i class="bi bi-trash me-2"></i>Delete Account
                        </h4>
                    </div>
                    <div class="settings-card-body">
                        <p class="mb-2">Permanently delete your account and all associated data. This action cannot be undone.</p>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Warning:</strong> Deleting your account will permanently remove all your data, including:
                            <ul class="mt-2 mb-0">
                                <li>Profile information</li>
                                <li>Project history</li>
                                <li>Messages and communications</li>
                                <li>Payment information</li>
                            </ul>
                        </div>
                        
                        <button type="button" class="btn btn-danger settings-action-btn mt-3" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            <i class="bi bi-trash me-2"></i>Delete My Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAccountModalLabel">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Confirm Account Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
                <p>Deleting your account will permanently remove all your data from our systems.</p>
                <form id="delete-account-form" method="POST" action="?page=settings">
                    <div class="mb-3">
                        <label for="delete_confirmation" class="form-label">Type "DELETE" to confirm:</label>
                        <input type="text" class="form-control" id="delete_confirmation" name="delete_confirmation" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger" form="delete-account-form" name="delete_account" id="confirmDeleteBtn">Delete My Account</button>
            </div>
        </div>
    </div>
</div>

<!-- Data Download Modal -->
<div class="modal fade" id="downloadDataModal" tabindex="-1" aria-labelledby="downloadDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="downloadDataModalLabel">
                    <i class="bi bi-download me-2"></i>Download Your Data
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-5">
                <div class="download-icon mb-4">
                    <i class="bi bi-file-earmark-arrow-down-fill"></i>
                </div>
                <h4 class="mb-3">Your data is ready!</h4>
                <p class="mb-4">We've prepared a file containing all your personal data. Click the button below to download it.</p>
                <a href="#" class="btn btn-primary" id="dataDownloadLink" download>
                    <i class="bi bi-download me-2"></i>Download JSON File
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Settings Page Styles */
.profile-header {
    background: linear-gradient(to right, var(--primary), var(--secondary));
    border-radius: var(--radius-md);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-md);
    color: white;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 2rem;
}

[data-bs-theme="dark"] .profile-header {
    background: linear-gradient(to right, var(--accent-dark), #273444);
}

.profile-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgcGF0dGVyblRyYW5zZm9ybT0icm90YXRlKDQ1KSI+PHJlY3QgaWQ9InBhdHRlcm4tYmFja2dyb3VuZCIgd2lkdGg9IjQwMCUiIGhlaWdodD0iNDAwJSIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjApIj48L3JlY3Q+PGNpcmNsZSBmaWxsPSJyZ2JhKDI1NSwyNTUsMjU1LDAuMDUpIiBjeD0iMjAiIGN5PSIyMCIgcj0iMSI+PC9jaXJjbGU+PGNpcmNsZSBmaWxsPSJyZ2JhKDI1NSwyNTUsMjU1LDAuMDMpIiBjeD0iMCIgY3k9IjAiIHI9IjEiPjwvY2lyY2xlPjwvcGF0dGVybj48L2RlZnM+PHJlY3QgZmlsbD0idXJsKCNwYXR0ZXJuKSIgaGVpZ2h0PSIxMDAlIiB3aWR0aD0iMTAwJSI+PC9yZWN0Pjwvc3ZnPg==');
    opacity: 0.8;
    z-index: 0;
}

.profile-avatar-container {
    position: relative;
    width: 120px;
    height: 120px;
    z-index: 1;
    flex-shrink: 0;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid rgba(255, 255, 255, 0.7);
    box-shadow: var(--shadow-md);
}

.profile-info {
    z-index: 1;
    flex: 1;
}

.profile-name {
    font-family: var(--font-heading);
    font-weight: 700;
    font-size: 1.8rem;
    color: white;
    margin-bottom: 0.25rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.profile-title {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.profile-info p {
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
}

.profile-actions {
    margin-top: 1rem;
}

.settings-container {
    background-color: white;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

[data-bs-theme="dark"] .settings-container {
    background-color: var(--accent-dark);
}

.settings-tabs {
    padding: 0 1rem;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

[data-bs-theme="dark"] .settings-tabs {
    border-bottom-color: rgba(255,255,255,0.1);
}

.settings-tabs .nav-link {
    padding: 1rem 1.25rem;
    margin-right: 0.5rem;
    color: var(--accent);
    border: none;
    border-bottom: 3px solid transparent;
    border-radius: 0;
    font-weight: 500;
    transition: all 0.2s ease;
}

.settings-tabs .nav-link:hover {
    color: var(--primary);
    background-color: rgba(0,0,0,0.03);
}

.settings-tabs .nav-link.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
    background-color: transparent;
}

[data-bs-theme="dark"] .settings-tabs .nav-link {
    color: var(--light);
}

[data-bs-theme="dark"] .settings-tabs .nav-link:hover {
    color: var(--secondary);
    background-color: rgba(255,255,255,0.05);
}

[data-bs-theme="dark"] .settings-tabs .nav-link.active {
    color: var(--secondary);
    border-bottom-color: var(--secondary);
}

.settings-tab-content {
    padding: 0;
}

.settings-panel {
    padding: 2rem;
}

.settings-section-title {
    font-family: var(--font-heading);
    font-weight: 600;
    font-size: 1.4rem;
    color: var(--accent-dark);
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid rgba(var(--primary-rgb), 0.1);
    position: relative;
}

.settings-section-title::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -2px;
    width: 60px;
    height: 2px;
    background-color: var(--primary);
}

[data-bs-theme="dark"] .settings-section-title {
    color: var(--light);
    border-bottom-color: rgba(255, 255, 255, 0.1);
}

[data-bs-theme="dark"] .settings-section-title::after {
    background-color: var(--secondary);
}

.settings-form {
    max-width: 700px;
}

.settings-divider {
    height: 1px;
    background-color: rgba(0,0,0,0.1);
    margin: 2.5rem 0;
}

[data-bs-theme="dark"] .settings-divider {
    background-color: rgba(255,255,255,0.1);
}

.settings-card {
    border-radius: var(--radius-md);
    border: 1px solid rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s ease;
}

.settings-card:hover {
    box-shadow: var(--shadow-sm);
    transform: translateY(-2px);
}

[data-bs-theme="dark"] .settings-card {
    border-color: rgba(255,255,255,0.1);
    background-color: rgba(255,255,255,0.02);
}

.settings-card-header {
    padding: 1.25rem 1.5rem;
    background-color: rgba(0,0,0,0.03);
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

[data-bs-theme="dark"] .settings-card-header {
    background-color: rgba(255,255,255,0.05);
    border-bottom-color: rgba(255,255,255,0.1);
}

.settings-card-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--accent-dark);
    display: flex;
    align-items: center;
}

[data-bs-theme="dark"] .settings-card-title {
    color: var(--light);
}

.settings-card-body {
    padding: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: var(--accent);
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.settings-input-group {
    position: relative;
    margin-bottom: 0.5rem;
}

.settings-input-group .input-group-text {
    background-color: var(--light-gray);
    border-color: rgba(0, 0, 0, 0.1);
    color: var(--accent);
    border-top-left-radius: var(--radius-sm);
    border-bottom-left-radius: var(--radius-sm);
}

[data-bs-theme="dark"] .settings-input-group .input-group-text {
    background-color: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.1);
    color: var(--light);
}

.settings-input, .settings-textarea, .settings-select {
    border: 1px solid rgba(0, 0, 0, 0.1);
    box-shadow: none;
    border-radius: var(--radius-sm);
    padding: 0.75rem 1rem;
    font-size: 1rem;
    color: var(--accent-dark);
    background-color: white;
    transition: all 0.3s ease;
}

.settings-input:focus, .settings-textarea:focus, .settings-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.25rem rgba(var(--primary-rgb), 0.2);
    outline: none;
}

[data-bs-theme="dark"] .settings-input, 
[data-bs-theme="dark"] .settings-textarea,
[data-bs-theme="dark"] .settings-select {
    background-color: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.1);
    color: var(--light);
}

[data-bs-theme="dark"] .settings-input:focus, 
[data-bs-theme="dark"] .settings-textarea:focus,
[data-bs-theme="dark"] .settings-select:focus {
    border-color: var(--secondary);
    box-shadow: 0 0 0 0.25rem rgba(143, 179, 222, 0.2);
}

.settings-submit-btn {
    background-color: var(--primary);
    border: none;
    padding: 0.8rem 2rem;
    font-weight: 600;
    border-radius: var(--radius-sm);
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.settings-submit-btn:hover {
    background-color: #2d4358;
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

[data-bs-theme="dark"] .settings-submit-btn {
    background-color: var(--secondary);
    color: #1f2028;
}

[data-bs-theme="dark"] .settings-submit-btn:hover {
    background-color: #a8c6e7;
}

.settings-action-btn {
    padding: 0.6rem 1.25rem;
    border-radius: var(--radius-sm);
    font-weight: 500;
    transition: all 0.3s ease;
}

.settings-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

/* Security Options */
.security-options {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.security-option {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-radius: var(--radius-sm);
    background-color: var(--light-gray);
    transition: all 0.2s ease;
}

.security-option:hover {
    background-color: rgba(var(--primary-rgb), 0.05);
}

[data-bs-theme="dark"] .security-option {
    background-color: rgba(255, 255, 255, 0.05);
}

[data-bs-theme="dark"] .security-option:hover {
    background-color: rgba(143, 179, 222, 0.1);
}

.security-option-info {
    flex: 1;
}

.security-option-title {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.25rem;
    color: var(--accent-dark);
}

[data-bs-theme="dark"] .security-option-title {
    color: var(--light);
}

.security-option-desc {
    font-size: 0.85rem;
    color: var(--accent);
    margin-bottom: 0;
}

.security-option-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    background-color: var(--secondary);
    color: white;
    border-radius: 20px;
    font-weight: 500;
}

[data-bs-theme="dark"] .security-option-badge {
    background-color: var(--primary);
}

/* Notification Options */
.notification-option {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.notification-option:last-child {
    border-bottom: none;
}

[data-bs-theme="dark"] .notification-option {
    border-bottom-color: rgba(255,255,255,0.05);
}

.notification-option-info {
    flex: 1;
}

.notification-option-title {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.25rem;
    color: var(--accent-dark);
}

[data-bs-theme="dark"] .notification-option-title {
    color: var(--light);
}

.notification-option-desc {
    font-size: 0.85rem;
    color: var(--accent);
    margin-bottom: 0;
}

/* Privacy Options */
.privacy-option {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.privacy-option:last-child {
    border-bottom: none;
}

[data-bs-theme="dark"] .privacy-option {
    border-bottom-color: rgba(255,255,255,0.05);
}

.privacy-option-info {
    flex: 1;
}

.privacy-option-title {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.25rem;
    color: var(--accent-dark);
}

[data-bs-theme="dark"] .privacy-option-title {
    color: var(--light);
}

.privacy-option-desc {
    font-size: 0.85rem;
    color: var(--accent);
    margin-bottom: 0;
}

/* Form Switch Styling */
.form-check-input {
    width: 3rem;
    height: 1.5rem;
    margin-top: 0.25rem;
    background-color: #e9ecef;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba(255, 255, 255, 1)'/%3e%3c/svg%3e");
    background-position: left center;
    border-radius: 2rem;
    transition: background-position .15s ease-in-out;
    border: none;
    cursor: pointer;
}

.form-check-input:checked {
    background-color: var(--primary);
    border-color: var(--primary);
    background-position: right center;
}

[data-bs-theme="dark"] .form-check-input {
    background-color: rgba(143, 179, 222, 0.2);
}

[data-bs-theme="dark"] .form-check-input:checked {
    background-color: var(--secondary);
    border-color: var(--secondary);
}

/* Alert Styling */
.alert {
    border-radius: var(--radius-sm);
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    border: none;
    position: relative;
    overflow: hidden;
}

.alert-success {
    background-color: rgba(25, 135, 84, 0.1);
    color: #198754;
    border-left: 4px solid #198754;
}

.alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    border-left: 4px solid #dc3545;
}

.alert-warning {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
    border-left: 4px solid #ffc107;
}

[data-bs-theme="dark"] .alert-warning {
    color: #ffe066;
    border-left-color: #ffe066;
}

/* Password Toggle Button */
.toggle-password {
    background-color: var(--light-gray);
    border-color: rgba(0, 0, 0, 0.1);
    color: var(--accent);
    cursor: pointer;
}

[data-bs-theme="dark"] .toggle-password {
    background-color: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.1);
    color: var(--light);
}

/* Data Download Modal */
.download-icon {
    font-size: 4rem;
    color: var(--primary);
    margin-bottom: 1rem;
}

[data-bs-theme="dark"] .download-icon {
    color: var(--secondary);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
        padding: 1.5rem;
    }
    
    .profile-avatar-container {
        margin-bottom: 1rem;
    }
    
    .settings-panel {
        padding: 1.5rem;
    }
    
    .settings-tabs .nav-link {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
    
    .security-option, 
    .notification-option, 
    .privacy-option {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .security-option .form-check, 
    .notification-option .form-check, 
    .privacy-option .form-check {
        margin-top: 0.5rem;
        align-self: flex-start;
    }
    
    .security-option .security-option-badge {
        margin-top: 0.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });
    
    // Password strength checker
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');
    const passwordMatchFeedback = document.getElementById('password-match-feedback');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const deleteConfirmInput = document.getElementById('delete_confirmation');
    
    if (newPassword) {
        newPassword.addEventListener('input', function() {
            const value = this.value;
            let strength = 0;
            
            // Check length
            if (value.length >= 8) {
                strength += 25;
            }
            
            // Check for lowercase letters
            if (value.match(/[a-z]/)) {
                strength += 25;
            }
            
            // Check for uppercase letters
            if (value.match(/[A-Z]/)) {
                strength += 25;
            }
            
            // Check for numbers or special characters
            if (value.match(/[0-9]/) || value.match(/[^a-zA-Z0-9]/)) {
                strength += 25;
            }
            
            // Update strength bar
            strengthBar.style.width = strength + '%';
            
            // Update strength color
            if (strength <= 25) {
                strengthBar.className = 'progress-bar bg-danger';
                strengthText.textContent = 'Weak password';
            } else if (strength <= 50) {
                strengthBar.className = 'progress-bar bg-warning';
                strengthText.textContent = 'Moderate password';
            } else if (strength <= 75) {
                strengthBar.className = 'progress-bar bg-info';
                strengthText.textContent = 'Strong password';
            } else {
                strengthBar.className = 'progress-bar bg-success';
                strengthText.textContent = 'Very strong password';
            }
            
            // Check if passwords match
            if (confirmPassword.value && confirmPassword.value !== value) {
                confirmPassword.classList.add('is-invalid');
                passwordMatchFeedback.style.display = 'block';
            } else if (confirmPassword.value) {
                confirmPassword.classList.remove('is-invalid');
                passwordMatchFeedback.style.display = 'none';
            }
        });
    }
    
    if (confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            if (this.value !== newPassword.value) {
                this.classList.add('is-invalid');
                passwordMatchFeedback.style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                passwordMatchFeedback.style.display = 'none';
            }
        });
    }
    
    // Delete account confirmation
    if (deleteConfirmInput) {
        deleteConfirmInput.addEventListener('input', function() {
            if (this.value === 'DELETE') {
                confirmDeleteBtn.disabled = false;
            } else {
                confirmDeleteBtn.disabled = true;
            }
        });
    }
    
    // Data download handling
    const downloadForm = document.getElementById('download-data-form');
    if (downloadForm) {
        downloadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // In a real application, you would make an AJAX request to get the data
            // For demo purposes, we'll just show the modal
            const modal = new bootstrap.Modal(document.getElementById('downloadDataModal'));
            modal.show();
            
            // Create sample data
            const sampleData = {
                user: {
                    email: '<?php echo $userEmail; ?>',
                    name: '<?php echo $userName; ?>',
                    user_type: '<?php echo $userType; ?>'
                },
                settings: <?php echo json_encode($settings); ?>
            };
            
            // Create a data URL for the JSON file
            const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(sampleData, null, 2));
            const downloadLink = document.getElementById('dataDownloadLink');
            downloadLink.setAttribute("href", dataStr);
            downloadLink.setAttribute("download", "user_data_<?php echo date('Y-m-d'); ?>.json");
        });
    }
});
</script>