<?php
/**
 * Navbar Component with Inline CSS
 * Contains the HTML and CSS for the website navigation bar
 */
?>
<style>
/* Navbar Styles */
.navbar {
    position: fixed;
    width: 100%;
    top: 0;
    left: 0;
    z-index: 1000;
    padding: 1rem 0;
    background-color: transparent;
    transition: all 0.3s ease;
    opacity: 0;
    transform: translateY(-10px);
}

.navbar.visible {
    opacity: 1;
    transform: translateY(0);
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
}

.navbar-brand:hover {
    color: #8FB3DE;
}

[data-bs-theme="dark"] .navbar-brand {
    color: #FFFFFF;
    text-shadow: 0 0 15px rgba(180, 210, 240, 0.2);
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
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 10px rgba(93, 139, 179, 0.3);
    font-size: 0.95rem;
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
}

.theme-toggle:hover {
    color: #8FB3DE;
    background-color: rgba(255, 255, 255, 0.1);
    transform: rotate(15deg);
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
        <a class="navbar-brand" href="/web/web/index.php">LenSi</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="/web/web/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#categories">Categories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#featured-services">Services</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#how-it-works">How It Works</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#features">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#job-offers">Job Offers</a>
                </li>
                <li class="nav-item">
                    <button class="btn login-btn ms-lg-3" id="loginBtn">Login / Register</button>
                </li>
                <li class="nav-item">
                    <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode" onclick="window.forceReapplyTheme()">
                        <i class="bi bi-sun-fill"></i>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
// Script to handle navbar visibility and scrolling
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar');
    const themeToggle = document.getElementById('themeToggle');
    
    // Show navbar with a slight delay
    setTimeout(() => {
        navbar.classList.add('visible');
    }, 200);
    
    // Handle scroll effects
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Update theme toggle icon based on current theme
    function updateThemeIcon() {
        const isDarkTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        themeToggle.innerHTML = isDarkTheme ? 
            '<i class="bi bi-moon-stars-fill"></i>' : 
            '<i class="bi bi-sun-fill"></i>';
    }
    
    // Initial update
    updateThemeIcon();
    
    // Listen for theme changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'data-bs-theme') {
                updateThemeIcon();
            }
        });
    });
    
    observer.observe(document.documentElement, {
        attributes: true
    });
});
</script>