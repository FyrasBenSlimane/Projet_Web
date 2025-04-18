<?php
// Set up basic error reporting to debug component loading issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the components with absolute paths
$componentsPath = __DIR__ . '/components/Home';
$components = [
    'navbar' => $componentsPath . '/navbar.php',
    'hero' => $componentsPath . '/hero.php',
    'content-sections' => $componentsPath . '/content-sections.php',
    'job-offers' => $componentsPath . '/job-offers.php',
    'footer' => $componentsPath . '/footer.php'
];

// Pre-load component check to avoid render-blocking errors
$missingComponents = [];
foreach ($components as $name => $path) {
    if (!file_exists($path)) {
        $missingComponents[$name] = $path;
    }
}

// Get current theme from cookies or system preference
$savedTheme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : null;
$systemTheme = isset($_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME']) ? $_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME'] : null;
$initialTheme = $savedTheme ?: ($systemTheme ?: 'light');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo $initialTheme; ?>" class="no-js preload">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=5.0, minimum-scale=1.0">
    <meta name="description" content="LenSi - Connect with talented freelancers for your business needs">
    <meta name="theme-color" content="#3E5C76">
    <title>LenSi - Freelance Marketplace</title>
    
    <!-- Preload critical assets -->
    <link rel="preload" href="assets/images/logo_white.svg" as="image" type="image/svg+xml">
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&display=swap" as="style">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="script.js" as="script">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="assets/images/logo_white.svg" sizes="any">
    
    <!-- Fonts and CSS, loaded with minimal blocking -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Inter:wght@300;400;500&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Inline critical CSS for faster initial render -->
    <style>
    /* Root CSS Variables and Global Styles */
    :root {
        --primary: #3E5C76;
        --primary-rgb: 62, 92, 118;
        --secondary: #748CAB;
        --accent: #1D2D44;
        --accent-dark: #0D1B2A;
        --light: #F9F7F0;
        --dark: #0D1B2A;
        --font-primary: 'Montserrat', sans-serif;
        --font-secondary: 'Inter', sans-serif;
        --font-heading: 'Poppins', sans-serif;
        --transition-default: all 0.3s ease;
        --transition-bounce: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        --spacing-xs: 0.5rem;
        --spacing-sm: 1rem;
        --spacing-md: 1.5rem;
        --spacing-lg: 2rem;
        --spacing-xl: 3rem;
        --border-radius-sm: 0.25rem;
        --border-radius-md: 0.5rem;
        --border-radius-lg: 1rem;
        --container-width: 1400px;
        --header-height: 80px;
        --text-xs: 0.75rem;
        --text-sm: 0.875rem;
        --text-md: 1rem;
        --text-lg: 1.25rem;
        --text-xl: 1.5rem;
        --text-2xl: 2rem;
        --text-3xl: 2.5rem;
        --text-4xl: 3rem;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html {
        font-size: 16px;
        scroll-behavior: smooth;
    }

    body {
        font-family: var(--font-secondary);
        line-height: 1.6;
        color: var(--accent);
        background-color: var(--light);
        min-height: 100vh;
        transition: background-color 0.3s ease;
        padding-bottom: 0;
        overflow-x: hidden;
        opacity: 0; /* Start with invisible body to prevent flash of unstyled content */
        animation: fadeIn 0.5s ease-in forwards;
        animation-delay: 0.1s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    [data-bs-theme="dark"] {
        --light: #121212;
        --dark: #F9F7F0;
        --accent: #A4C2E5;
        --accent-dark: #171821;
        --primary: #5D8BB3;
        --primary-rgb: 93, 139, 179;
        --secondary: #8FB3DE;
        color: var(--accent);
        background-color: var(--light);
    }

    h1, h2, h3, h4, h5, h6 {
        font-family: var(--font-primary);
        font-weight: 600;
        color: var(--accent);
        line-height: 1.2;
    }

    h1 {
        font-size: var(--text-4xl);
    }

    h2 {
        font-size: var(--text-3xl);
        margin-bottom: 1.5rem;
    }

    h3 {
        font-size: var(--text-2xl);
    }

    h4 {
        font-size: var(--text-xl);
    }

    p {
        margin-bottom: 1rem;
    }

    img {
        max-width: 100%;
        height: auto;
    }

    .container {
        max-width: var(--container-width);
        width: 100%;
        padding: 0 var(--spacing-sm);
        margin: var(--spacing-xl) auto;
    }

    .btn {
        transition: var(--transition-default);
    }

    /* Page-level loading animation */
    .page-loading {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: var(--light);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: opacity 0.5s ease, visibility 0.5s ease;
    }

    .page-loading.loaded {
        opacity: 0;
        visibility: hidden;
    }

    .loading-spinner {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: 3px solid rgba(var(--primary-rgb), 0.1);
        border-top-color: var(--primary);
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Prevent transitions on page load */
    .preload * {
        transition: none !important;
    }

    /* Enhanced Responsive Typography */
    @media (max-width: 1400px) {
        :root {
            --container-width: 1140px;
        }
    }

    @media (max-width: 1200px) {
        :root {
            --container-width: 960px;
            --text-4xl: 2.75rem;
            --text-3xl: 2.25rem;
            --text-2xl: 1.75rem;
        }
    }

    @media (max-width: 992px) {
        :root {
            --container-width: 720px;
            --text-4xl: 2.5rem;
            --text-3xl: 2rem;
            --text-2xl: 1.5rem;
            --text-xl: 1.3rem;
        }
        
        .container {
            padding: 0 var(--spacing-md);
        }
    }

    @media (max-width: 768px) {
        :root {
            --container-width: 540px;
            --text-4xl: 2.25rem;
            --text-3xl: 1.75rem;
            --text-2xl: 1.4rem;
            --text-xl: 1.25rem;
            --text-lg: 1.125rem;
            --spacing-xl: 2rem;
        }
        
        html {
            font-size: 15px;
        }
        
        h2 {
            margin-bottom: 1.25rem;
        }
        
        .container {
            margin: var(--spacing-lg) auto;
        }
    }

    @media (max-width: 576px) {
        :root {
            --container-width: 100%;
            --text-4xl: 2rem;
            --text-3xl: 1.5rem;
            --text-2xl: 1.3rem;
            --text-xl: 1.15rem;
            --spacing-xl: 1.5rem;
            --spacing-lg: 1.25rem;
        }
        
        html {
            font-size: 14px;
        }
        
        .container {
            padding: 0 var(--spacing-sm);
            margin: var(--spacing-md) auto;
        }
    }

    /* Utility classes for responsiveness */
    .d-sm-none {
        display: block;
    }
    
    .d-sm-block {
        display: none;
    }
    
    @media (max-width: 768px) {
        .d-sm-none {
            display: none !important;
        }
        
        .d-sm-block {
            display: block !important;
        }
    }
    
    .text-center-sm {
        text-align: inherit;
    }
    
    @media (max-width: 768px) {
        .text-center-sm {
            text-align: center !important;
        }
    }

    /* Universal transitions for smooth theme changes */
    .universal-transition {
        transition: 
            background-color 0.3s ease,
            color 0.3s ease, 
            border-color 0.3s ease, 
            box-shadow 0.3s ease,
            opacity 0.3s ease,
            transform 0.3s ease;
    }

    /* Logo states for theme switching */
    .logo-light, .logo-dark {
        transition: opacity 0.3s ease;
        position: absolute;
        top: 0;
        left: 0;
    }
    </style>
</head>
<body>
    <!-- Page loading animation -->
    <div class="page-loading">
        <div class="loading-spinner"></div>
    </div>
    
    <!-- Navbar Component -->
    <?php if (!isset($missingComponents['navbar'])): ?>
        <?php include $components['navbar']; ?>
    <?php else: ?>
        <div class="alert alert-danger m-3">Error loading navbar component: File not found at <?= $missingComponents['navbar'] ?></div>
    <?php endif; ?>
    
    <!-- Hero Component -->
    <?php if (!isset($missingComponents['hero'])): ?>
        <?php include $components['hero']; ?>
    <?php else: ?>
        <div class="alert alert-danger m-3">Error loading hero component: File not found at <?= $missingComponents['hero'] ?></div>
    <?php endif; ?>
    
    <!-- Content Sections Component -->
    <?php if (!isset($missingComponents['content-sections'])): ?>
        <?php include $components['content-sections']; ?>
    <?php else: ?>
        <div class="alert alert-danger m-3">Error loading content sections component: File not found at <?= $missingComponents['content-sections'] ?></div>
    <?php endif; ?>

    <!-- Job Offers Component -->
    <?php if (file_exists($components['job-offers'])): ?>
        <?php include $components['job-offers']; ?>
    <?php else: ?>
        <div class="alert alert-danger m-3">Error loading job offers component: File not found at <?= $components['job-offers'] ?></div>
    <?php endif; ?>
    
    <!-- Footer Component -->
    <?php if (!isset($missingComponents['footer'])): ?>
        <?php include $components['footer']; ?>
    <?php else: ?>
        <div class="alert alert-danger m-3">Error loading footer component: File not found at <?= $missingComponents['footer'] ?></div>
    <?php endif; ?>
    
    <!-- Load remaining CSS in a non-blocking way -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'">
    
    <!-- Initialize JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="script.js" defer></script>
    
    <!-- Inline critical JavaScript for initial loading and theme -->
    <script>
    // Mark document as having JavaScript
    document.documentElement.classList.remove('no-js');
    
    // Handle page loading
    window.addEventListener('load', function() {
        // Remove preload class to enable transitions
        document.documentElement.classList.remove('preload');
        
        // Hide loading screen
        const pageLoading = document.querySelector('.page-loading');
        if (pageLoading) {
            pageLoading.classList.add('loaded');
            setTimeout(() => {
                pageLoading.style.display = 'none';
            }, 500);
        }
    });
    
    // Initialize theme based on preferences on page load (critical)
    document.addEventListener('DOMContentLoaded', function() {
        // Get saved theme from localStorage if available
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            document.cookie = `theme=${savedTheme}; path=/; max-age=31536000`; // 1 year
        }
        
        // Update theme icon
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            const isDarkTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const icon = themeToggle.querySelector('i');
            
            if (icon) {
                icon.className = isDarkTheme ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill';
            }
        }
        
        // Set initial date in footer
        const yearElement = document.getElementById('year');
        if (yearElement) {
            yearElement.textContent = new Date().getFullYear();
        }
    });
    </script>
</body>
</html>
