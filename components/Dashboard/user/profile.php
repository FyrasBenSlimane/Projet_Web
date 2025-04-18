<?php
/**
 * User Profile Content
 * This file contains ONLY the profile content to be inserted into the main dashboard layout
 */

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user'])) {
    header('Location: ../../login/login.php');
    exit;
}

// Get user data from session
$user = $_SESSION['user'];
$userName = $user['first_name'] . ' ' . $user['last_name'];
$userEmail = $user['email'];
$userType = $user['user_type'] ?? 'freelancer';

// Get current theme from cookies or system preference
$savedTheme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : null;
$systemTheme = isset($_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME']) ? $_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME'] : null;
$initialTheme = $savedTheme ?: ($systemTheme ?: 'light');

// Initialize profile data or handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    require_once __DIR__ . '/../../../config/database.php';
    // Make sure we have the $pdo connection variable available from database.php
    if (!isset($pdo) || $pdo === null) {
        $fetch_error = "Database connection error. Please try again later.";
    } else {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $bio = trim($_POST['bio'] ?? '');
        $skills = trim($_POST['skills'] ?? '');
        $hourly_rate = isset($_POST['hourly_rate']) ? (float)$_POST['hourly_rate'] : 0;
        $location = trim($_POST['location'] ?? '');
        $website = trim($_POST['website'] ?? '');
        
        // Validate inputs
        $errors = [];
        
        if (empty($first_name)) {
            $errors[] = "First name is required";
        }
        
        if (empty($last_name)) {
            $errors[] = "Last name is required";
        }
        
        // If no errors, update the database
        if (empty($errors)) {
            try {
                // Check if user profile exists
                $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_email = ?");
                $stmt->execute([$userEmail]);
                $profile = $stmt->fetch();
                
                if ($profile) {
                    // Update existing profile
                    $stmt = $pdo->prepare("UPDATE user_profiles SET bio = ?, skills = ?, hourly_rate = ?, location = ?, website = ? WHERE user_email = ?");
                    $stmt->execute([$bio, $skills, $hourly_rate, $location, $website, $userEmail]);
                } else {
                    // Create new profile
                    $stmt = $pdo->prepare("INSERT INTO user_profiles (user_email, bio, skills, hourly_rate, location, website) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$userEmail, $bio, $skills, $hourly_rate, $location, $website]);
                }
                
                // Update user table
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE email = ?");
                $stmt->execute([$first_name, $last_name, $userEmail]);
                
                // Update session data
                $_SESSION['user']['first_name'] = $first_name;
                $_SESSION['user']['last_name'] = $last_name;
                $_SESSION['user']['name'] = $first_name . ' ' . $last_name;
                
                $success_message = "Profile updated successfully!";
                
                // Refresh user data
                $user = $_SESSION['user'];
                $userName = $user['first_name'] . ' ' . $user['last_name'];
                
            } catch (PDOException $e) {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Fetch user profile data
try {
    require_once __DIR__ . '/../../../config/database.php';
    // Make sure we have the $pdo connection variable available from database.php
    if (!isset($pdo) || $pdo === null) {
        throw new Exception("Database connection error. Please try again later.");
    }
    
    $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_email = ?");
    $stmt->execute([$userEmail]);
    $profile = $stmt->fetch();
    
    // If profile doesn't exist, initialize with empty values
    if (!$profile) {
        $profile = [
            'bio' => '',
            'skills' => '',
            'hourly_rate' => 0,
            'location' => '',
            'website' => '',
            'profile_image' => ''
        ];
    }
    
} catch (PDOException $e) {
    $fetch_error = "Could not fetch profile data: " . $e->getMessage();
    // Initialize $profile with empty values even if there's an error
    $profile = [
        'bio' => '',
        'skills' => '',
        'hourly_rate' => 0,
        'location' => '',
        'website' => '',
        'profile_image' => ''
    ];
} catch (Exception $e) {
    $fetch_error = $e->getMessage();
    // Initialize $profile with empty values even if there's an exception
    $profile = [
        'bio' => '',
        'skills' => '',
        'hourly_rate' => 0,
        'location' => '',
        'website' => '',
        'profile_image' => ''
    ];
}

// Handle profile image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../../../assets/uploads/profile_images/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_name = $userEmail . '_' . time() . '_' . basename($_FILES['profile_image']['name']);
    $target_file = $upload_dir . $file_name;
    $image_url = '/web/assets/uploads/profile_images/' . $file_name;
    
    // Check if image file is a actual image
    $check = getimagesize($_FILES['profile_image']['tmp_name']);
    if ($check !== false) {
        // Check file size (limit to 5MB)
        if ($_FILES['profile_image']['size'] > 5000000) {
            $image_error = "Sorry, your file is too large. Max size is 5MB.";
        } else {
            // Allow certain file formats
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES['profile_image']['type'], $allowed_types)) {
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                    // Update database with new image URL
                    try {
                        require_once __DIR__ . '/../../../config/database.php';
                        // Make sure we have the $pdo connection variable available from database.php
                        if (!isset($pdo) || $pdo === null) {
                            throw new Exception("Database connection error. Please try again later.");
                        }
                        
                        // Check if user profile exists
                        $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_email = ?");
                        $stmt->execute([$userEmail]);
                        $profile_exists = $stmt->fetch();
                        
                        if ($profile_exists) {
                            // Update existing profile
                            $stmt = $pdo->prepare("UPDATE user_profiles SET profile_image = ? WHERE user_email = ?");
                            $stmt->execute([$image_url, $userEmail]);
                        } else {
                            // Create new profile
                            $stmt = $pdo->prepare("INSERT INTO user_profiles (user_email, profile_image) VALUES (?, ?)");
                            $stmt->execute([$userEmail, $image_url]);
                        }
                        
                        // Update profile data for display
                        $profile['profile_image'] = $image_url;
                        
                        $image_success = "Profile image updated successfully!";
                        
                    } catch (PDOException $e) {
                        $image_error = "Database error: " . $e->getMessage();
                    } catch (Exception $e) {
                        $image_error = $e->getMessage();
                    }
                } else {
                    $image_error = "Sorry, there was an error uploading your file.";
                }
            } else {
                $image_error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            }
        }
    } else {
        $image_error = "File is not an image.";
    }
}

// Get avatar URL
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&size=128&background=random";
if (isset($profile['profile_image']) && !empty($profile['profile_image'])) {
    $avatarUrl = $profile['profile_image'];
}
?>

<!-- Profile Content - only the content, no HTML/body tags or dashboard container -->
<!-- Profile Header -->
<section class="profile-header">
    <div class="profile-avatar-container">
        <img src="<?php echo $avatarUrl; ?>" alt="<?php echo $userName; ?>" class="profile-avatar">
        <label for="profile-image-upload" class="profile-avatar-edit" title="Change profile picture">
            <i class="bi bi-camera-fill"></i>
        </label>
        <form id="avatar-form" method="POST" enctype="multipart/form-data" style="display: none;">
            <input type="file" id="profile-image-upload" name="profile_image" accept="image/*" onchange="document.getElementById('avatar-form').submit();">
        </form>
    </div>
    
    <div class="profile-info">
        <h2 class="profile-name"><?php echo $userName; ?></h2>
        <p class="profile-title"><?php echo ucfirst($userType); ?></p>
        
        <?php if (!empty($profile['location'])): ?>
        <p><i class="bi bi-geo-alt-fill me-2"></i><?php echo $profile['location']; ?></p>
        <?php endif; ?>
        
        <?php if (!empty($profile['website'])): ?>
        <p><i class="bi bi-globe me-2"></i><a href="<?php echo $profile['website']; ?>" target="_blank"><?php echo $profile['website']; ?></a></p>
        <?php endif; ?>
        
        <div class="profile-stats">
            <div class="profile-stat">
                <div class="profile-stat-value">12</div>
                <div class="profile-stat-label">Projects</div>
            </div>
            <div class="profile-stat">
                <div class="profile-stat-value">4.8</div>
                <div class="profile-stat-label">Rating</div>
            </div>
            <div class="profile-stat">
                <div class="profile-stat-value">$<?php echo number_format($profile['hourly_rate'] ?? 0); ?></div>
                <div class="profile-stat-label">Hourly Rate</div>
            </div>
        </div>
    </div>
</section>

<!-- Profile Form -->
<section class="profile-content">
    <?php if (isset($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($image_success)): ?>
    <div class="alert alert-success"><?php echo $image_success; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
            <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <?php if (isset($image_error)): ?>
    <div class="alert alert-danger"><?php echo $image_error; ?></div>
    <?php endif; ?>
    
    <h3 class="profile-section-title">Personal Information</h3>
    
    <form method="POST" action="?page=profile" class="profile-form">
        <div class="row mb-4">
            <div class="col-md-6 mb-3 mb-md-0">
                <label for="first_name" class="form-label">First Name</label>
                <div class="input-group profile-input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control profile-input" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <label for="last_name" class="form-label">Last Name</label>
                <div class="input-group profile-input-group">
                    <span class="input-group-text"><i class="bi bi-person-plus"></i></span>
                    <input type="text" class="form-control profile-input" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" required>
                </div>
            </div>
        </div>
        
        <div class="mb-4">
            <label for="email" class="form-label">Email</label>
            <div class="input-group profile-input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control profile-input disabled-input" id="email" value="<?php echo $userEmail; ?>" disabled>
            </div>
            <div class="form-text">Your email cannot be changed.</div>
        </div>
        
        <div class="mb-4">
            <label for="bio" class="form-label">Bio</label>
            <div class="input-group profile-input-group">
                <span class="input-group-text"><i class="bi bi-file-text"></i></span>
                <textarea class="form-control profile-textarea" id="bio" name="bio" rows="4" placeholder="Tell clients about yourself and your expertise..."><?php echo $profile['bio']; ?></textarea>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6 mb-3 mb-md-0">
                <label for="location" class="form-label">Location</label>
                <div class="input-group profile-input-group">
                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                    <input type="text" class="form-control profile-input" id="location" name="location" value="<?php echo $profile['location']; ?>" placeholder="e.g. New York, USA">
                </div>
            </div>
            <div class="col-md-6">
                <label for="website" class="form-label">Website</label>
                <div class="input-group profile-input-group">
                    <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                    <input type="url" class="form-control profile-input" id="website" name="website" value="<?php echo $profile['website']; ?>" placeholder="https://yourwebsite.com">
                </div>
            </div>
        </div>
        
        <h3 class="profile-section-title mt-5">Professional Information</h3>
        
        <div class="mb-4">
            <label for="skills" class="form-label">Skills</label>
            <div class="input-group profile-input-group">
                <span class="input-group-text"><i class="bi bi-tools"></i></span>
                <textarea class="form-control profile-textarea" id="skills" name="skills" rows="2" placeholder="e.g. Web Development, Graphic Design, Content Writing"><?php echo $profile['skills']; ?></textarea>
            </div>
            <div class="form-text">Separate skills with commas</div>
        </div>
        
        <div class="mb-4">
            <label for="hourly_rate" class="form-label">Hourly Rate ($)</label>
            <div class="input-group profile-input-group">
                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                <input type="number" class="form-control profile-input" id="hourly_rate" name="hourly_rate" value="<?php echo $profile['hourly_rate']; ?>" min="0" step="0.01" placeholder="Your hourly rate">
            </div>
        </div>
        
        <div class="mt-4 text-end">
            <button type="submit" name="update_profile" class="btn btn-primary profile-submit-btn">
                <i class="bi bi-check-circle me-2"></i>Save Changes
            </button>
        </div>
    </form>
</section>

<style>
/* Enhanced Profile Styles */
.profile-header {
    background: linear-gradient(to right, var(--primary), var(--secondary));
    border-radius: var(--radius-md);
    padding: 2.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-md);
    color: white;
    position: relative;
    overflow: hidden;
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
    width: 160px;
    height: 160px;
    z-index: 1;
}

.profile-avatar {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid rgba(255, 255, 255, 0.7);
    box-shadow: var(--shadow-md);
    transition: transform 0.3s ease;
}

.profile-avatar:hover {
    transform: scale(1.02);
}

.profile-avatar-edit {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 44px;
    height: 44px;
    background-color: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: var(--shadow-md);
    transition: all 0.3s ease;
    border: 3px solid white;
    font-size: 1.1rem;
}

.profile-avatar-edit:hover {
    transform: scale(1.1) rotate(15deg);
    background-color: var(--secondary);
}

[data-bs-theme="dark"] .profile-avatar-edit {
    background-color: var(--secondary);
    border-color: var(--accent-dark);
}

[data-bs-theme="dark"] .profile-avatar-edit:hover {
    background-color: var(--accent);
}

.profile-info {
    flex: 1;
    min-width: 250px;
    z-index: 1;
}

.profile-name {
    font-family: var(--font-heading);
    font-weight: 700;
    font-size: 2.2rem;
    color: white;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.profile-title {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.2rem;
    margin-bottom: 1rem;
    font-weight: 500;
}

.profile-info p {
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
}

.profile-info a {
    color: white;
    text-decoration: none;
    border-bottom: 1px dotted rgba(255, 255, 255, 0.5);
    transition: all 0.2s ease;
}

.profile-info a:hover {
    border-bottom: 1px solid white;
}

.profile-stats {
    display: flex;
    gap: 2rem;
    margin-top: 1.5rem;
    background-color: rgba(255, 255, 255, 0.1);
    padding: 1rem;
    border-radius: var(--radius-sm);
    backdrop-filter: blur(5px);
}

.profile-stat {
    text-align: center;
}

.profile-stat-value {
    font-weight: 700;
    font-size: 1.5rem;
    color: white;
}

.profile-stat-label {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.8);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Form Styling */
.profile-content {
    background-color: white;
    border-radius: var(--radius-md);
    padding: 2.5rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow-sm);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.profile-content:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-3px);
}

[data-bs-theme="dark"] .profile-content {
    background-color: var(--accent-dark);
}

.profile-section-title {
    font-family: var(--font-heading);
    font-weight: 600;
    font-size: 1.4rem;
    color: var(--accent-dark);
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid rgba(var(--primary-rgb), 0.1);
    position: relative;
}

.profile-section-title::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -2px;
    width: 60px;
    height: 2px;
    background-color: var(--primary);
}

[data-bs-theme="dark"] .profile-section-title {
    color: var(--light);
    border-bottom-color: rgba(255, 255, 255, 0.1);
}

[data-bs-theme="dark"] .profile-section-title::after {
    background-color: var(--secondary);
}

.form-label {
    font-weight: 600;
    color: var(--accent);
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.profile-input-group {
    position: relative;
    margin-bottom: 0.5rem;
}

.profile-input-group .input-group-text {
    background-color: var(--light-gray);
    border-color: rgba(0, 0, 0, 0.1);
    color: var(--accent);
    border-top-left-radius: var(--radius-sm);
    border-bottom-left-radius: var(--radius-sm);
}

[data-bs-theme="dark"] .profile-input-group .input-group-text {
    background-color: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.1);
    color: var(--light);
}

.profile-input, .profile-textarea {
    border: 1px solid rgba(0, 0, 0, 0.1);
    box-shadow: none;
    border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    color: var(--accent-dark);
    background-color: white;
    transition: all 0.3s ease;
}

.profile-input:focus, .profile-textarea:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.25rem rgba(var(--primary-rgb), 0.2);
    outline: none;
}

[data-bs-theme="dark"] .profile-input, 
[data-bs-theme="dark"] .profile-textarea {
    background-color: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.1);
    color: var(--light);
}

[data-bs-theme="dark"] .profile-input:focus, 
[data-bs-theme="dark"] .profile-textarea:focus {
    border-color: var(--secondary);
    box-shadow: 0 0 0 0.25rem rgba(143, 179, 222, 0.2);
}

.profile-textarea {
    min-height: 100px;
    resize: vertical;
}

.disabled-input {
    background-color: var(--light-gray) !important;
    cursor: not-allowed;
    opacity: 0.7;
}

[data-bs-theme="dark"] .disabled-input {
    background-color: rgba(255, 255, 255, 0.02) !important;
}

.form-text {
    color: var(--accent);
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

[data-bs-theme="dark"] .form-text {
    color: rgba(255, 255, 255, 0.6);
}

.profile-submit-btn {
    background-color: var(--primary);
    border: none;
    padding: 0.8rem 2rem;
    font-weight: 600;
    border-radius: var(--radius-sm);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.profile-submit-btn:hover {
    background-color: #2d4358;
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

[data-bs-theme="dark"] .profile-submit-btn {
    background-color: var(--secondary);
    color: #1f2028;
}

[data-bs-theme="dark"] .profile-submit-btn:hover {
    background-color: #a8c6e7;
}

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

.alert::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background-color: currentColor;
    opacity: 0.8;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 1.5rem;
    }
    
    .profile-avatar-container {
        margin-bottom: 1.5rem;
    }
    
    .profile-stats {
        justify-content: center;
        padding: 0.75rem;
        gap: 1rem;
    }
    
    .profile-stat-value {
        font-size: 1.2rem;
    }
    
    .profile-content {
        padding: 1.5rem;
    }
}

/* Animation effects */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.profile-content, .profile-header {
    animation: fadeInUp 0.5s ease forwards;
}

.profile-content {
    animation-delay: 0.2s;
}
</style>