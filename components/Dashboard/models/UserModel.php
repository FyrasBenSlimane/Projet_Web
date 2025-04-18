<?php
/**
 * User Model - Handles all database operations related to users
 */
class UserModel {
    private $pdo;
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct($pdo = null) {
        if ($pdo) {
            $this->pdo = $pdo;
        } else {
            require_once __DIR__ . '/../../../config/database.php';
            $this->pdo = $GLOBALS['pdo'];
        }
    }
    
    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Get user settings
     */
    public function getUserSettings($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM user_settings WHERE user_email = ?");
        $stmt->execute([$email]);
        $settings = $stmt->fetch();
        
        // If settings don't exist, return default values
        if (!$settings) {
            return [
                'email_notifications' => 1,
                'project_notifications' => 1,
                'message_notifications' => 1,
                'marketing_emails' => 0,
                'profile_visibility' => 'public',
                'show_earnings' => 0,
                'show_projects' => 1
            ];
        }
        
        return $settings;
    }
    
    /**
     * Check if user settings exist
     */
    public function userSettingsExist($email) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM user_settings WHERE user_email = ?");
        $stmt->execute([$email]);
        return (int)$stmt->fetchColumn() > 0;
    }
    
    /**
     * Delete user account and all associated data
     */
    public function deleteUser($email) {
        try {
            // Start transaction to ensure all operations succeed or fail together
            $this->pdo->beginTransaction();
            
            // Delete user settings
            $stmt = $this->pdo->prepare("DELETE FROM user_settings WHERE user_email = ?");
            $stmt->execute([$email]);
            
            // Delete user profile
            $stmt = $this->pdo->prepare("DELETE FROM user_profiles WHERE user_email = ?");
            $stmt->execute([$email]);
            
            // Delete user
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            // Commit transaction
            $this->pdo->commit();
            
            return true;
        } catch (PDOException $e) {
            // Rollback transaction on error
            $this->pdo->rollBack();
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user settings
     */
    public function updateUserSettings($email, $settings) {
        // Check if settings exist
        $stmt = $this->pdo->prepare("SELECT * FROM user_settings WHERE user_email = ?");
        $stmt->execute([$email]);
        $existingSettings = $stmt->fetch();
        
        if ($existingSettings) {
            // Update existing settings
            $sql = "UPDATE user_settings SET ";
            $params = [];
            
            foreach ($settings as $key => $value) {
                $sql .= "$key = ?, ";
                $params[] = $value;
            }
            
            $sql = rtrim($sql, ", ") . " WHERE user_email = ?";
            $params[] = $email;
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } else {
            // Create new settings
            $keys = array_keys($settings);
            $placeholders = array_fill(0, count($keys), '?');
            
            $sql = "INSERT INTO user_settings (user_email, " . implode(", ", $keys) . ") ";
            $sql .= "VALUES (?, " . implode(", ", $placeholders) . ")";
            
            $params = [$email];
            foreach ($settings as $value) {
                $params[] = $value;
            }
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        }
    }
    
    /**
     * Update user password
     */
    public function updatePassword($email, $hashedPassword) {
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        return $stmt->execute([$hashedPassword, $email]);
    }
    
    /**
     * Update user profile - correctly handles both user and profile tables
     */
    public function updateProfile($email, $profileData) {
        // Start a transaction to ensure consistency
        $this->pdo->beginTransaction();
        
        try {
            // Separate data for users table and user_profiles table
            $userData = [];
            $profileData_clean = [];
            
            // Check which fields belong to which table
            if (isset($profileData['first_name'])) $userData['first_name'] = $profileData['first_name'];
            if (isset($profileData['last_name'])) $userData['last_name'] = $profileData['last_name'];
            
            // The rest of the fields go to the user_profiles table
            if (isset($profileData['bio'])) $profileData_clean['bio'] = $profileData['bio'];
            if (isset($profileData['skills'])) $profileData_clean['skills'] = $profileData['skills'];
            if (isset($profileData['hourly_rate'])) $profileData_clean['hourly_rate'] = $profileData['hourly_rate'];
            if (isset($profileData['location'])) $profileData_clean['location'] = $profileData['location'];
            if (isset($profileData['website'])) $profileData_clean['website'] = $profileData['website'];
            if (isset($profileData['profile_image'])) $profileData_clean['profile_image'] = $profileData['profile_image'];
            
            // Update the users table if we have user data
            if (!empty($userData)) {
                $sql = "UPDATE users SET ";
                $params = [];
                
                foreach ($userData as $key => $value) {
                    $sql .= "$key = ?, ";
                    $params[] = $value;
                }
                
                $sql = rtrim($sql, ", ") . " WHERE email = ?";
                $params[] = $email;
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }
            
            // Update or insert into user_profiles
            $stmt = $this->pdo->prepare("SELECT * FROM user_profiles WHERE user_email = ?");
            $stmt->execute([$email]);
            $existingProfile = $stmt->fetch();
            
            if ($existingProfile && !empty($profileData_clean)) {
                // Update existing profile
                $sql = "UPDATE user_profiles SET ";
                $params = [];
                
                foreach ($profileData_clean as $key => $value) {
                    $sql .= "$key = ?, ";
                    $params[] = $value;
                }
                
                $sql = rtrim($sql, ", ") . " WHERE user_email = ?";
                $params[] = $email;
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            } elseif (!empty($profileData_clean)) {
                // Create new profile
                $keys = array_keys($profileData_clean);
                $placeholders = array_fill(0, count($keys), '?');
                
                $sql = "INSERT INTO user_profiles (user_email, " . implode(", ", $keys) . ") ";
                $sql .= "VALUES (?, " . implode(", ", $placeholders) . ")";
                
                $params = [$email];
                foreach ($profileData_clean as $value) {
                    $params[] = $value;
                }
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }
            
            // Commit transaction
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->pdo->rollBack();
            // You might want to log the error here
            return false;
        }
    }
    
    /**
     * Get user profile
     */
    public function getUserProfile($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM user_profiles WHERE user_email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
}
