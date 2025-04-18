/**
 * LenSi Marketplace - Main JavaScript File
 * Optimized for performance with consolidated functionality
 */

// Immediately Invoked Function Expression to avoid global scope pollution
(function() {
    'use strict';
    
    // ===== INITIALIZATION =====
    // Main initialization function that runs on DOMContentLoaded
    function init() {
        // Initialize UI components in a specific order
        initializeTheme();
        setupNavigation();
        initializeAnimations();
        setupUserInterface();
        
        // Check login status late in the process to avoid blocking rendering
        setTimeout(checkUserLoginStatus, 100);
        
        // Initialize all lazy-loaded sections after initial content is visible
        setTimeout(initializeLazyComponents, 200);
        
        // Remove any loading classes or indicators
        document.body.classList.remove('loading');
    }
    
    // ===== THEME MANAGEMENT =====
    // Handle all theme-related functionality
    function initializeTheme() {
        // Set up theme toggle listeners
        const themeToggle = document.getElementById('themeToggle');
        
        if (themeToggle) {
            // Update toggle icon based on current theme
            updateThemeIcon();
            
            // Set up toggle click handler
            themeToggle.addEventListener('click', function() {
                toggleTheme();
            });
        }
        
        // Listen for system theme changes
        const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
        prefersDarkScheme.addEventListener('change', function(e) {
            // Only change theme if user hasn't explicitly set a preference
            if (!localStorage.getItem('theme')) {
                const newTheme = e.matches ? 'dark' : 'light';
                applyTheme(newTheme, false);
            }
        });
        
        // Initial check for elements in viewport
        refreshThemeSensitiveElements();
    }
    
    // Toggle between light and dark theme
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        applyTheme(newTheme, true);
    }
    
    // Apply the selected theme throughout the application
    function applyTheme(theme, savePreference) {
        // Update HTML attribute
        document.documentElement.setAttribute('data-bs-theme', theme);
        
        // Save preference if requested
        if (savePreference) {
            localStorage.setItem('theme', theme);
            // Also set cookie for server-side awareness
            document.cookie = `theme=${theme}; path=/; max-age=31536000`; // 1 year
        }
        
        // Update UI elements
        updateThemeIcon();
        updateLogos(theme);
        updateCustomElements(theme);
        
        // Force refresh for theme-sensitive elements
        refreshThemeSensitiveElements();
        
        // Dispatch event for any components listening for theme changes
        document.dispatchEvent(new CustomEvent('themeChanged', { 
            detail: { theme: theme }
        }));
    }
    
    // Update theme icon to match current theme
    function updateThemeIcon() {
        const themeToggle = document.getElementById('themeToggle');
        if (!themeToggle) return;
        
        const isDarkTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        const icon = themeToggle.querySelector('i') || themeToggle;
        
        // Update icon class
        icon.className = isDarkTheme ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill';
        
        // Add ripple effect on click
        themeToggle.addEventListener('click', function(e) {
            // Create ripple element
            const ripple = document.createElement('span');
            ripple.classList.add('theme-toggle-ripple');
            this.appendChild(ripple);
            
            // Position ripple at click point
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height) * 2;
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${e.clientX - rect.left - (size/2)}px`;
            ripple.style.top = `${e.clientY - rect.top - (size/2)}px`;
            
            // Remove ripple after animation completes
            setTimeout(() => ripple.remove(), 600);
        });
    }
    
    // Update logo display based on theme
    function updateLogos(theme) {
        const lightLogo = document.querySelector('.logo-light');
        const darkLogo = document.querySelector('.logo-dark');
        
        if (!lightLogo || !darkLogo) return;
        
        // Prepare both logos
        lightLogo.style.display = 'block';
        darkLogo.style.display = 'block';
        
        // Apply correct visibility
        if (theme === 'light') {
            lightLogo.style.opacity = '0';
            darkLogo.style.opacity = '1';
            setTimeout(() => { lightLogo.style.display = 'none'; }, 300);
        } else {
            darkLogo.style.opacity = '0';
            lightLogo.style.opacity = '1';
            setTimeout(() => { darkLogo.style.display = 'none'; }, 300);
        }
    }
    
    // Update any custom elements that have theme-specific behavior
    function updateCustomElements(theme) {
        // Toggle theme-specific classes
        document.querySelectorAll('[data-theme-class]').forEach(el => {
            const classes = el.getAttribute('data-theme-class').split(',');
            if (classes.length === 2) {
                const [lightClass, darkClass] = classes;
                el.classList.remove(lightClass, darkClass);
                el.classList.add(theme === 'dark' ? darkClass : lightClass);
            }
        });
        
        // Update theme-specific text content
        document.querySelectorAll('[data-theme-content]').forEach(el => {
            const contents = el.getAttribute('data-theme-content').split(',');
            if (contents.length === 2) {
                const [lightContent, darkContent] = contents;
                el.textContent = theme === 'dark' ? darkContent : lightContent;
            }
        });
    }
    
    // Force refresh elements that need recalculation on theme change
    function refreshThemeSensitiveElements() {
        // Force reflow of theme-sensitive elements
        const themeElements = document.querySelectorAll(
            '.navbar, .card, .hero-section, .premium-section, .footer, ' +
            '[class*="bg-"], .gradient-bg, .bubble, .hero-background'
        );
        
        themeElements.forEach(el => {
            if (!el) return;
            
            // Force a style recalculation
            const display = window.getComputedStyle(el).display;
            el.style.display = 'none';
            void el.offsetHeight; // Trigger reflow
            el.style.display = display;
        });
    }
    
    // ===== NAVIGATION & UI =====
    // Set up main navigation and UI components
    function setupNavigation() {
        // Navbar scroll behavior
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            // Handle scroll effects
            handleNavbarScroll(navbar);
            
            // Smooth scroll for navigation links
            setupSmoothScrolling(navbar);
        }
    }
    
    // Handle navbar appearance on scroll
    function handleNavbarScroll(navbar) {
        // Show navbar with a slight delay for better perceived performance
        setTimeout(() => navbar.classList.add('visible'), 100);
        
        // Throttled scroll handler
        let lastScrollTop = 0;
        let ticking = false;
        
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    // Apply scrolled class at appropriate threshold
                    if (scrollTop > 50) {
                        navbar.classList.add('scrolled');
                    } else {
                        navbar.classList.remove('scrolled');
                    }
                    
                    lastScrollTop = scrollTop;
                    ticking = false;
                });
                
                ticking = true;
            }
        });
    }
    
    // Set up smooth scrolling for all anchor links
    function setupSmoothScrolling(navbar) {
        document.querySelectorAll('a[href^="#"]:not([href="#"])').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    // Calculate offset with navbar height if applicable
                    const navbarHeight = navbar && navbar.classList.contains('visible') ? navbar.offsetHeight : 0;
                    const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - navbarHeight;
                    
                    // Apply smooth scroll
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                    
                    // Update URL without causing a scroll jump
                    if (history.pushState) {
                        history.pushState(null, null, `#${targetId}`);
                    }
                }
            });
        });
    }
    
    // ===== ANIMATIONS =====
    // Set up scroll-based animations and effects
    function initializeAnimations() {
        // Set up lazy animations for all sections
        const animatedElements = document.querySelectorAll('.section-animate, .timeline-item, .stagger-item');
        
        if (animatedElements.length === 0) return;
        
        // Use Intersection Observer for efficient animations
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // Add visible class to trigger animation
                        entry.target.classList.add('visible');
                        
                        // For stagger items, handle child animations
                        if (entry.target.classList.contains('section-animate')) {
                            handleSectionStaggerItems(entry.target);
                        }
                        
                        // Stop observing this element
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15, rootMargin: '0px 0px -100px 0px' });
            
            // Start observing elements
            animatedElements.forEach(el => observer.observe(el));
        } else {
            // Fallback for browsers without IntersectionObserver
            animatedElements.forEach(el => el.classList.add('visible'));
        }
        
        // Initialize bubbles in the hero background
        initializeHeroBubbles();
        
        // Set up parallax effects
        initializeParallaxEffects();
    }
    
    // Initialize bubbles in the hero background
    function initializeHeroBubbles() {
        const heroBackground = document.querySelector('.hero-background');
        if (!heroBackground) return;
        
        // Clear existing bubbles
        const existingBubbles = heroBackground.querySelectorAll('.bubble');
        existingBubbles.forEach(bubble => bubble.remove());
        
        // Create new bubbles with responsive counts
        const isMobile = window.innerWidth < 768;
        const bubbleCount = isMobile ? 4 : 6;
        
        for (let i = 0; i < bubbleCount; i++) {
            const bubble = document.createElement('div');
            bubble.classList.add('bubble');
            
            // Size based on device
            const size = Math.floor(Math.random() * (isMobile ? 150 : 250)) + (isMobile ? 80 : 120);
            
            // Position with better spread
            const top = Math.floor(Math.random() * 80) + 10;
            const left = Math.floor(Math.random() * 80) + 10;
            
            // Transparency
            const opacity = (Math.random() * 0.3) + 0.1;
            
            // Set styles efficiently
            Object.assign(bubble.style, {
                width: `${size}px`,
                height: `${size}px`,
                top: `${top}%`,
                left: `${left}%`,
                opacity: opacity.toString(),
                animationDelay: `${Math.random() * 4}s`,
                animationDuration: `${Math.random() * 5 + 10}s`
            });
            
            // Add to background
            heroBackground.appendChild(bubble);
        }
    }
    
    // Initialize parallax effects
    function initializeParallaxEffects() {
        // Optimize performance by throttling scroll events
        let lastScrollY = window.scrollY;
        let ticking = false;
        
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    // Apply parallax to bubbles
                    applyBubbleParallax(lastScrollY);
                    
                    // Update last scroll position
                    lastScrollY = window.scrollY;
                    ticking = false;
                });
                
                ticking = true;
            }
        });
        
        // Mouse movement effect for hero content
        const heroContent = document.querySelector('.hero-content');
        if (heroContent) {
            // Skip parallax on mobile devices
            if (window.innerWidth > 768) {
                document.addEventListener('mousemove', throttle(function(e) {
                    const xPos = (e.clientX / window.innerWidth - 0.5) * 15;
                    const yPos = (e.clientY / window.innerHeight - 0.5) * 15;
                    
                    heroContent.style.transform = `translate3d(${xPos}px, ${yPos}px, 0)`;
                }, 50));
            }
        }
    }
    
    // Apply parallax effect to bubbles on scroll
    function applyBubbleParallax(scrollY) {
        const bubbles = document.querySelectorAll('.bubble');
        
        bubbles.forEach((bubble, index) => {
            // Skip for mobile devices to improve performance
            if (window.innerWidth <= 768) return;
            
            const speed = 0.05 + (index * 0.01);
            const yPos = scrollY * speed;
            bubble.style.transform = `translate3d(0, ${yPos}px, 0) rotate(${yPos * 0.01}deg)`;
        });
    }
    
    // Handle stagger animations for section children
    function handleSectionStaggerItems(section) {
        const staggerItems = section.querySelectorAll('.stagger-item');
        
        staggerItems.forEach((item, index) => {
            // Set animation delay based on index
            const delay = index * 70; // milliseconds
            
            setTimeout(() => {
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, delay);
        });
    }
    
    // ===== USER INTERFACE COMPONENTS =====
    // Set up UI interactions
    function setupUserInterface() {
        // Initialize service carousel/slider if present
        initializeServicesCarousel();
    }
    
    // Initialize services carousel/slider
    function initializeServicesCarousel() {
        const servicesScrollContainer = document.querySelector('.services-scroll-container');
        const servicesScrollWrapper = document.querySelector('.services-scroll-wrapper');
        
        if (!servicesScrollWrapper || !servicesScrollContainer) return;
        
        // Variables for mouse/touch dragging
        let isDown = false;
        let startX;
        let scrollLeft;
        let animationPaused = false;
        
        // Mouse events for desktop
        servicesScrollContainer.addEventListener('mousedown', (e) => {
            isDown = true;
            servicesScrollContainer.style.cursor = 'grabbing';
            startX = e.pageX - servicesScrollContainer.offsetLeft;
            scrollLeft = servicesScrollContainer.scrollLeft;
            
            // Pause animation while dragging
            if (!animationPaused) {
                servicesScrollWrapper.style.animationPlayState = 'paused';
                animationPaused = true;
            }
        });
        
        // Events for releasing or leaving
        const endDrag = () => {
            isDown = false;
            servicesScrollContainer.style.cursor = 'grab';
            
            // Resume animation after delay
            if (animationPaused) {
                setTimeout(() => {
                    servicesScrollWrapper.style.animationPlayState = 'running';
                    animationPaused = false;
                }, 800);
            }
        };
        
        servicesScrollContainer.addEventListener('mouseleave', endDrag);
        servicesScrollContainer.addEventListener('mouseup', endDrag);
        
        // Handle mouse movement
        servicesScrollContainer.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - servicesScrollContainer.offsetLeft;
            const walk = (x - startX) * 1.5; // Adjust scroll sensitivity
            servicesScrollContainer.scrollLeft = scrollLeft - walk;
        });
        
        // Touch events for mobile
        servicesScrollContainer.addEventListener('touchstart', (e) => {
            isDown = true;
            startX = e.touches[0].pageX - servicesScrollContainer.offsetLeft;
            scrollLeft = servicesScrollContainer.scrollLeft;
            
            if (!animationPaused) {
                servicesScrollWrapper.style.animationPlayState = 'paused';
                animationPaused = true;
            }
        });
        
        servicesScrollContainer.addEventListener('touchend', endDrag);
        
        servicesScrollContainer.addEventListener('touchmove', (e) => {
            if (!isDown) return;
            const x = e.touches[0].pageX - servicesScrollContainer.offsetLeft;
            const walk = (x - startX) * 1.5;
            servicesScrollContainer.scrollLeft = scrollLeft - walk;
        });
    }
    
    // ===== LAZY COMPONENT INITIALIZATION =====
    // Initialize components that can be delayed
    function initializeLazyComponents() {
        // Set up section transition indicators
        setupSectionTransitionIndicators();
    }
    
    // Set up section transition indicators
    function setupSectionTransitionIndicators() {
        const transitionIndicators = document.querySelectorAll('.section-transition-indicator');
        
        transitionIndicators.forEach(indicator => {
            indicator.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Get target section href from parent
                const href = this.parentElement.querySelector('a').getAttribute('href');
                const targetSection = document.querySelector(href);
                
                if (targetSection) {
                    // Apply visual feedback
                    this.style.transform = 'translateX(-50%) scale(0.9)';
                    setTimeout(() => {
                        this.style.transform = 'translateX(-50%)';
                    }, 200);
                    
                    // Calculate scroll position
                    const navbar = document.querySelector('.navbar');
                    const navbarHeight = navbar && navbar.classList.contains('visible') ? navbar.offsetHeight : 0;
                    const targetPosition = targetSection.getBoundingClientRect().top + window.pageYOffset - navbarHeight;
                    
                    // Scroll smoothly
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }
    
    // ===== USER AUTHENTICATION =====
    // Check login status and update UI accordingly
    function checkUserLoginStatus() {
        try {
            const user = JSON.parse(localStorage.getItem('currentUser'));
            
            // Get UI elements
            const loggedOutNav = document.querySelector('.logged-out-nav');
            const userProfileNav = document.querySelector('.user-profile-nav');
            
            // If no user is logged in, ensure login button is visible
            if (!user) {
                if (loggedOutNav) loggedOutNav.classList.remove('d-none');
                if (userProfileNav) userProfileNav.classList.add('d-none');
                return;
            }
            
            // Update UI based on user status
            if (loggedOutNav) loggedOutNav.classList.add('d-none');
            
            if (userProfileNav) {
                userProfileNav.classList.remove('d-none');
                
                // Update user name
                const userName = userProfileNav.querySelector('.user-name');
                if (userName) {
                    userName.textContent = user.name || user.email.split('@')[0];
                }
            }
            
            // Check for admin status
            const adminEmail = "support@xteam.tn";
            const isAdmin = user.email === adminEmail;
            
            if (isAdmin) {
                const userNavItem = document.querySelector('.user-nav-item');
                if (userNavItem) {
                    userNavItem.classList.remove('d-none');
                    const userNavLink = userNavItem.querySelector('a');
                    if (userNavLink) {
                        userNavLink.innerHTML = '<i class="bi bi-speedometer2"></i> Admin Dashboard';
                    }
                }
            }
        } catch (e) {
            console.error('Error checking user login status:', e);
        }
    }
    
    // Event listener for logout button
    document.addEventListener('DOMContentLoaded', function() {
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                logoutUser();
            });
        }
    });
    
    // Function to handle user logout
    function logoutUser() {
        // Clear the user data from localStorage
        localStorage.removeItem('currentUser');
        
        // Redirect to logout.php which will handle session cleanup and redirect to main index
        window.location.href = '/web/components/Login/login.php?logout=true';
    }
    
    // ===== UTILITY FUNCTIONS =====
    // Throttle function to limit frequency of function calls
    function throttle(callback, delay) {
        let last = 0;
        return function(...args) {
            const now = new Date().getTime();
            if (now - last < delay) return;
            last = now;
            return callback(...args);
        };
    }
    
    // Expose public methods for external script access
    window.LenSi = {
        toggleTheme: toggleTheme,
        refreshTheme: refreshThemeSensitiveElements,
        logoutUser: logoutUser
    };
    
    // Initialize everything on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', init);
})();