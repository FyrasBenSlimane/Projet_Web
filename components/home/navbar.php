<?php
/**
 * Navbar Component with Inline CSS
 * Contains the HTML and CSS for the website navigation bar
 * Optimized for faster loading and rendering
 */
?>
<style>
/* Navbar Styles - Optimized for performance */
.navbar {
    position: fixed;
    width: 100%;
    top: 0;
    left: 0;
    z-index: 1000;
    padding: 1rem 0;
    background-color: transparent;
    transition: all 0.3s ease;
    /* Remove initial hidden state */
    opacity: 1;
    transform: translateY(0);
    will-change: transform, opacity, background-color, padding;
}

/* Only hide navbar when scrolling down from hero section */
.navbar.hidden {
    opacity: 0;
    transform: translateY(-10px);
    pointer-events: none;
}

.navbar.scrolled {
    padding: 0.5rem 0;
    background-color: rgba(255, 255, 255, 0.95);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    backdrop-filter: blur(10px);
}

.navbar.scrolled .navbar-container {
    max-width: 1000px; /* More compact container when scrolled */
}

[data-bs-theme="dark"] .navbar.scrolled {
    background-color: rgba(15, 18, 25, 0.9);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(70, 90, 120, 0.1);
}

.navbar-container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
    transition: max-width 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.navbar-brand {
    font-family: var(--font-heading);
    font-weight: 700;
    font-size: 1.8rem;
    color: #FFFFFF;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    display: inline-block;
    position: relative;
}

.navbar-brand .brand-text {
    position: relative;
    z-index: 2;
}

.navbar-brand:hover {
    color: #8FB3DE;
}

.navbar.scrolled .navbar-brand {
    color: var(--accent-dark);
    text-shadow: none;
}

[data-bs-theme="dark"] .navbar-brand {
    color: #FFFFFF;
    text-shadow: 0 0 15px rgba(180, 210, 240, 0.2);
}

[data-bs-theme="dark"] .navbar.scrolled .navbar-brand {
    color: #FFFFFF;
}

[data-bs-theme="dark"] .navbar-brand:hover {
    color: #A8C8E8;
}

.nav-link {
    font-weight: 600;
    margin: 0 0.7rem;
    color: rgba(255, 255, 255, 0.9);
    transition: all 0.3s ease;
    position: relative;
    font-size: 0.95rem;
}

.nav-link:hover {
    color: #8FB3DE;
}

.nav-link::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -4px;
    left: 0;
    background-color: #8FB3DE;
    transition: width 0.3s ease;
}

.nav-link:hover::after {
    width: 100%;
}

[data-bs-theme="dark"] .nav-link {
    color: rgba(210, 225, 245, 0.9);
}

[data-bs-theme="dark"] .nav-link:hover {
    color: #A8C8E8;
}

[data-bs-theme="dark"] .nav-link::after {
    background-color: #A8C8E8;
}

.navbar.scrolled .nav-link {
    color: #555;
}

.navbar.scrolled .nav-link:hover {
    color: #5D8BB3;
}

[data-bs-theme="dark"] .navbar.scrolled .nav-link {
    color: rgba(210, 225, 245, 0.8);
}

[data-bs-theme="dark"] .navbar.scrolled .nav-link:hover {
    color: #A8C8E8;
}

.login-btn {
    background: linear-gradient(90deg, #5D8BB3, #8FB3DE);
    color: white;
    padding: 0.5rem 1.3rem;
    font-weight: 600;
    border-radius: 0.375rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
    border: none;
    box-shadow: 0 4px 10px rgba(93, 139, 179, 0.3);
    font-size: 0.95rem;
    will-change: transform, box-shadow;
}

.login-btn:hover {
    background: linear-gradient(90deg, #8FB3DE, #5D8BB3);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(93, 139, 179, 0.4);
    color: white;
}

[data-bs-theme="dark"] .login-btn {
    background: linear-gradient(90deg, #6D9BC4, #9CBDE2);
    box-shadow: 0 4px 10px rgba(93, 139, 179, 0.25);
}

[data-bs-theme="dark"] .login-btn:hover {
    background: linear-gradient(90deg, #7BA4CD, #A8C8E8);
    box-shadow: 0 6px 15px rgba(93, 139, 179, 0.35), 0 0 8px rgba(93, 139, 179, 0.2);
}

.theme-toggle {
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.1rem;
    cursor: pointer;
    padding: 0.4rem;
    margin-left: 0.7rem;
    transition: all 0.3s ease;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.theme-toggle:hover {
    color: #8FB3DE;
    background-color: rgba(255, 255, 255, 0.1);
    transform: rotate(15deg);
}

/* Add ripple effect for theme toggle button */
.theme-toggle-ripple {
    position: absolute;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.3);
    transform-origin: center;
    transform: scale(0);
    animation: ripple 0.6s ease-out;
    pointer-events: none;
}

@keyframes ripple {
    to {
        transform: scale(4);
        opacity: 0;
    }
}

[data-bs-theme="dark"] .theme-toggle {
    color: rgba(210, 225, 245, 0.9);
}

[data-bs-theme="dark"] .theme-toggle:hover {
    color: #A8C8E8;
    background-color: rgba(210, 225, 245, 0.1);
}

.navbar.scrolled .theme-toggle {
    color: #555;
}

.navbar.scrolled .theme-toggle:hover {
    color: #5D8BB3;
    background-color: rgba(93, 139, 179, 0.1);
}

[data-bs-theme="dark"] .navbar.scrolled .theme-toggle {
    color: rgba(210, 225, 245, 0.8);
}

[data-bs-theme="dark"] .navbar.scrolled .theme-toggle:hover {
    color: #A8C8E8;
    background-color: rgba(210, 225, 245, 0.1);
}

.navbar-toggler {
    border: none;
    padding: 0.25rem;
    background-color: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(5px);
    border-radius: 0.375rem;
}

.navbar-toggler:focus {
    box-shadow: none;
}

.navbar-toggler-icon {
    width: 1.5em;
    height: 1.5em;
    filter: invert(1);
}

[data-bs-theme="dark"] .navbar-toggler {
    background-color: rgba(20, 30, 40, 0.4);
}

/* Responsive Navbar Adjustments */
@media (max-width: 992px) {
    .navbar-container {
        max-width: 100%;
    }
    
    .navbar-collapse {
        background-color: rgba(255, 255, 255, 0.95);
        margin-top: 1rem;
        padding: 1rem;
        border-radius: 0.5rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(10px);
    }
    
    [data-bs-theme="dark"] .navbar-collapse {
        background-color: rgba(15, 18, 25, 0.95);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(70, 90, 120, 0.1);
    }
    
    .nav-link {
        margin: 0.5rem 0;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
    }
    
    .nav-link:hover {
        background-color: rgba(93, 139, 179, 0.1);
    }
    
    [data-bs-theme="dark"] .nav-link:hover {
        background-color: rgba(70, 90, 120, 0.2);
    }
    
    .navbar.scrolled .nav-link:hover {
        background-color: rgba(93, 139, 179, 0.1);
    }
    
    .nav-link::after {
        display: none;
    }
    
    .login-btn {
        margin-top: 0.5rem;
        display: block;
        text-align: center;
        width: 100%;
    }
    
    .theme-toggle {
        margin: 0.5rem 0 0;
        padding: 0.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
    }
}

@media (max-width: 576px) {
    .navbar-container {
        padding: 0 1rem;
    }
    
    .navbar-brand {
        font-size: 1.5rem;
    }
}
</style>

<nav class="navbar navbar-expand-lg">
    <div class="navbar-container">
        <a class="navbar-brand" href="/web/index.php">
            <span class="brand-text">LenSi</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="#home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#categories">Categories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#featured-services">Services</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/web/components/home/offers/offers.php">Job Offers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#how-it-works">How It Works</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#features">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/web/components/home/blogs/">Community</a>
                </li>
                
                <!-- Dynamic nav items based on auth state -->
                <div class="logged-out-nav">
                    <li class="nav-item">
                        <a href="/web/components/login/login.php" class="btn login-btn ms-lg-3" id="loginBtn">Login / Register</a>
                    </li>
                </div>
                
                <div class="user-profile-nav d-none">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="" alt="" class="user-avatar rounded-circle" width="32" height="32">
                            <span class="user-name"></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/web/components/Dashboard/index.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="/web/components/Dashboard/index.php?page=profile"><i class="bi bi-person-circle me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="/web/components/Dashboard/index.php?page=settings"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/web/components/login/login.php?logout=true"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </div>
                
                <li class="nav-item">
                    <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode" onclick="toggleTheme()">
                        <i class="bi bi-sun-fill"></i>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
// Navbar visibility control script
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar');
    const heroSection = document.querySelector('.hero-section');
    
    if (!navbar || !heroSection) return;
    
    // Make sure navbar is visible initially
    navbar.classList.remove('hidden');
    navbar.style.opacity = '1';
    
    // Get hero section height for threshold calculation
    let heroHeight = heroSection.offsetHeight;
    let lastScrollY = window.scrollY;
    let ticking = false;
    
    // Only hide navbar when scrolling down past hero section
    function updateNavbarVisibility() {
        // Always show navbar when at the top of the page
        if (window.scrollY < 10) {
            navbar.classList.remove('hidden');
            navbar.classList.remove('scrolled');
            return;
        }
        
        // Apply scrolled styling when scrolled down
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        
        // Don't hide navbar when in hero section
        if (window.scrollY <= heroHeight) {
            navbar.classList.remove('hidden');
            return;
        }
        
        // Handle hiding/showing based on scroll direction
        const scrollingDown = lastScrollY < window.scrollY;
        
        if (scrollingDown) {
            navbar.classList.add('hidden');
        } else {
            navbar.classList.remove('hidden');
        }
        
        lastScrollY = window.scrollY;
    }
    
    // Handle scroll with performance optimization
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                updateNavbarVisibility();
                ticking = false;
            });
            ticking = true;
        }
    });
    
    // Update on resize (for responsive layouts)
    window.addEventListener('resize', function() {
        // Update hero height on resize
        heroHeight = heroSection.offsetHeight;
        updateNavbarVisibility();
    });
    
    // Initial call to set correct state
    updateNavbarVisibility();
});

// Function to open auth overlay
function openAuthOverlay(isRegistration = false) {
    const authOverlay = document.getElementById('authOverlay');
    const authContainer = document.getElementById('authContainer');
    
    if (!authOverlay || !authContainer) return;
    
    authOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    if (isRegistration) {
        authContainer.classList.add('active');
    } else {
        authContainer.classList.remove('active');
    }
}

// Check if user is logged in and update UI
function checkUserLoginStatus() {
    try {
        const user = JSON.parse(localStorage.getItem('currentUser'));
        
        if (!user) return;
        
        // Update UI based on user status
        const loggedOutNav = document.querySelector('.logged-out-nav');
        const userProfileNav = document.querySelector('.user-profile-nav');
        
        if (loggedOutNav) loggedOutNav.classList.add('d-none');
        
        if (userProfileNav) {
            userProfileNav.classList.remove('d-none');
            
            // Update user name
            const userName = userProfileNav.querySelector('.user-name');
            if (userName) {
                userName.textContent = user.name || user.email.split('@')[0];
            }
        }
    } catch (e) {
        console.error('Error checking user login status:', e);
    }
}

// Function to toggle theme
function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    // Update HTML attribute
    document.documentElement.setAttribute('data-bs-theme', newTheme);
    
    // Save theme preference
    localStorage.setItem('theme', newTheme);
    document.cookie = `theme=${newTheme}; path=/; max-age=31536000`; // 1 year
    
    // Update theme icon
    updateThemeIcon();
    
    // Dispatch event for any components listening for theme changes
    document.dispatchEvent(new CustomEvent('themeChanged', { 
        detail: { theme: newTheme }
    }));
}

// Function to update theme icon based on current theme
function updateThemeIcon() {
    const themeToggle = document.getElementById('themeToggle');
    if (!themeToggle) return;
    
    const isDarkTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const icon = themeToggle.querySelector('i');
    
    if (icon) {
        icon.className = isDarkTheme ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill';
    }
}

// Initialize theme icon on page load
document.addEventListener('DOMContentLoaded', function() {
    updateThemeIcon();
    checkUserLoginStatus();
});

// Navbar visibility control script
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar');
    let lastScrollY = window.scrollY;
    
    // Check login status and update UI
    function checkUserLoginStatus() {
        try {
            const user = JSON.parse(localStorage.getItem('currentUser'));
            const loggedOutNav = document.querySelector('.logged-out-nav');
            const userProfileNav = document.querySelector('.user-profile-nav');
            
            if (user && user.name) {
                if (loggedOutNav) loggedOutNav.classList.add('d-none');
                if (userProfileNav) {
                    userProfileNav.classList.remove('d-none');
                    // Update user info
                    const userName = userProfileNav.querySelector('.user-name');
                    const userAvatar = userProfileNav.querySelector('.user-avatar');
                    if (userName) userName.textContent = user.name;
                    if (userAvatar) {
                        userAvatar.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&size=32&background=random`;
                        userAvatar.alt = user.name;
                    }
                }
            } else {
                if (loggedOutNav) loggedOutNav.classList.remove('d-none');
                if (userProfileNav) userProfileNav.classList.add('d-none');
            }
        } catch (e) {
            console.error('Error checking user login status:', e);
        }
    }
    
    // Check login status on page load
    checkUserLoginStatus();
    
    // Recheck when localStorage changes
    window.addEventListener('storage', function(e) {
        if (e.key === 'currentUser') {
            checkUserLoginStatus();
        }
    });
    
    // Handle navbar visibility on scroll
    window.addEventListener('scroll', () => {
        // ...existing scroll handling code...
    });
});
</script>