document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    checkUserLoginStatus();

    // Set up the auth overlay
    setupAuthOverlay();

    // Theme toggle functionality
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = themeToggle.querySelector('i');
    const html = document.documentElement;

    themeToggle.addEventListener('click', () => {
        const isDark = html.getAttribute('data-bs-theme') === 'dark';
        html.setAttribute('data-bs-theme', isDark ? 'light' : 'dark');
        themeIcon.classList.toggle('bi-sun-fill');
        themeIcon.classList.toggle('bi-moon-fill');
        
        // Handle logo transition
        const lightLogo = document.querySelector('.logo-light');
        const darkLogo = document.querySelector('.logo-dark');
        
        if (isDark) {
            darkLogo.style.opacity = '0';
            setTimeout(() => {
                darkLogo.style.display = 'none';
                lightLogo.style.display = 'block';
                setTimeout(() => lightLogo.style.opacity = '1', 50);
            }, 300);
        } else {
            lightLogo.style.opacity = '0';
            setTimeout(() => {
                lightLogo.style.display = 'none';
                darkLogo.style.display = 'block';
                setTimeout(() => darkLogo.style.opacity = '1', 50);
            }, 300);
        }
        
        // Save preference
        localStorage.setItem('theme', isDark ? 'light' : 'dark');
    });

    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-bs-theme', savedTheme);
    themeIcon.classList.toggle('bi-sun-fill', savedTheme === 'light');
    themeIcon.classList.toggle('bi-moon-fill', savedTheme === 'dark');

    // Navbar scroll behavior
    const navbar = document.querySelector('.navbar');
    const header = document.querySelector('header');
    let headerHeight;
    
    // Function to update header height
    function updateHeaderHeight() {
        headerHeight = header.offsetHeight;
    }
    
    // Initialize header height
    updateHeaderHeight();
    
    // Update on resize
    window.addEventListener('resize', updateHeaderHeight);
    
    // Handle navbar visibility on scroll
    window.addEventListener('scroll', () => {
        if (window.scrollY > headerHeight * 0.7) {
            navbar.classList.add('visible');
        } else {
            navbar.classList.remove('visible');
        }
    });

    // Add fade-in animation to header and container
    const headerTitle = document.querySelector('header h1');
    const container = document.querySelector('.container');

    // Trigger animations with slight delay
    setTimeout(() => headerTitle.classList.add('fade-in'), 300);
    setTimeout(() => container.classList.add('fade-in'), 600);
    
    // Add fade-in for service cards
    const serviceCards = document.querySelectorAll('.service-card');
    serviceCards.forEach((card, index) => {
        setTimeout(() => card.classList.add('fade-in'), 800 + (index * 200));
    });

    // Add animations for features
    const featureItems = document.querySelectorAll('.feature-item');
    featureItems.forEach((item, index) => {
        setTimeout(() => item.classList.add('fade-in'), 1000 + (index * 200));
    });

    // Intersection Observer for scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.container, .service-card, .feature-item, .footer-content').forEach(el => {
        observer.observe(el);
    });

    // Enhanced Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.15,
        rootMargin: '0px 0px -100px 0px'
    };

    const sectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                
                // Handle stagger animations for child elements
                const staggerItems = entry.target.querySelectorAll('.stagger-item');
                staggerItems.forEach((item, index) => {
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, index * 100);
                });
            }
        });
    }, observerOptions);

    // Observe all sections and items
    document.querySelectorAll('.section-animate, .timeline-item').forEach(el => {
        sectionObserver.observe(el);
    });

    // Add stagger animation classes to items
    document.querySelectorAll('.feature-item, .premium-benefit-card, .category-card, .service-card-listing').forEach((item, index) => {
        item.style.setProperty('--item-index', index);
        item.classList.add('stagger-item');
        item.style.opacity = '0';
        item.style.transform = 'translateY(30px)';
        item.style.transition = 'opacity 0.6s ease, transform 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        item.style.transitionDelay = `${index * 0.1}s`;
    });

    // Smooth scroll for anchor links with improved behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                // Calculate position with navbar offset if visible
                const navbarHeight = navbar.classList.contains('visible') ? navbar.offsetHeight : 0;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - navbarHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Update URL without scrolling
                history.pushState(null, null, `#${targetId}`);
            }
        });
    });

    // Update active state in navigation based on scroll position with improved accuracy
    const navObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.getAttribute('id');
                if (id) {
                    document.querySelectorAll('.nav-links a').forEach(navLink => {
                        navLink.classList.remove('active');
                        if (navLink.getAttribute('href') === `#${id}`) {
                            navLink.classList.add('active');
                        }
                    });
                }
            }
        });
    }, {
        threshold: 0.4,
        rootMargin: '-100px 0px -300px 0px'
    });

    // Observe all sections for navigation highlighting
    document.querySelectorAll('header, #categories, #featured-services, #how-it-works, #features, #become-seller').forEach(section => {
        if (section.getAttribute('id')) {
            navObserver.observe(section);
        }
    });

    // Enhanced parallax effect for header bubbles
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const bubbles = document.querySelectorAll('.bubble');
        
        bubbles.forEach((bubble, index) => {
            const speed = 0.05 + (index * 0.02);
            const yPos = scrolled * speed;
            bubble.style.transform = `translate3d(0, ${yPos}px, 0) rotate(${yPos * 0.02}deg)`;
        });
    });
    
    // Enhanced mouse movement effect for hero section
    const heroContent = document.querySelector('.hero-content');
    
    if (heroContent) {
        document.addEventListener('mousemove', (e) => {
            const xPos = (e.clientX / window.innerWidth - 0.5) * 20;
            const yPos = (e.clientY / window.innerHeight - 0.5) * 20;
            
            heroContent.style.transform = `translate3d(${xPos}px, ${yPos}px, 0)`;
        });
    }

    // Animate bubbles with random properties
    const bubbles = document.querySelectorAll('.bubble');
    bubbles.forEach(bubble => {
        const delay = Math.random() * 5;
        const duration = 15 + Math.random() * 10;
        
        bubble.style.animationDelay = `${delay}s`;
        bubble.style.animationDuration = `${duration}s`;
    });

    // Set current year in footer
    const yearElement = document.getElementById('year');
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }

    // Auth Modal Functionality
    const authModal = document.getElementById('authModal');
    const authTabs = document.querySelectorAll('.auth-tab');
    const authForms = document.querySelectorAll('.auth-form');
    const modalTitle = document.getElementById('authModalTitle');

    authModal.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        const authType = button.getAttribute('data-auth-type');
        if (authType) {
            switchAuthForm(authType);
        }
    });

    authTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const authType = tab.getAttribute('data-auth');
            switchAuthForm(authType);
        });
    });

    function switchAuthForm(type) {
        authTabs.forEach(tab => {
            tab.classList.toggle('active', tab.getAttribute('data-auth') === type);
        });

        authForms.forEach(form => {
            form.classList.toggle('active', form.id === `${type}Form`);
        });

        modalTitle.textContent = type === 'login' ? 'Login' : 'Sign Up';
    }

    // Handle form submissions
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');

    loginForm.addEventListener('submit', (e) => {
        e.preventDefault();
        // Add your login logic here
        console.log('Login submitted');
    });

    signupForm.addEventListener('submit', (e) => {
        e.preventDefault();
        // Add your signup logic here
        console.log('Signup submitted');
    });

    // Remove setupFullPageScrolling call

    // Handle "Become a Seller" button
    const becomeSellerBtn = document.getElementById('becomeSellerBtn');
    if (becomeSellerBtn) {
        becomeSellerBtn.addEventListener('click', () => {
            const user = JSON.parse(localStorage.getItem('currentUser'));
            if (user) {
                // If user is logged in, redirect to dashboard with seller registration
                window.location.href = 'dashboard.html?becomeSeller=true';
            } else {
                // If not logged in, show auth overlay with signup tab
                showAuthOverlay('signup');
            }
        });
    }

    // Add animation for premium benefits section
    const premiumSection = document.querySelector('.premium-section');
    const premiumCards = document.querySelectorAll('.premium-benefit-card');
    
    if (premiumSection && premiumCards.length) {
        // Create an observer for the premium section
        const premiumObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Animate each card with a delay
                    premiumCards.forEach((card, index) => {
                        setTimeout(() => {
                            card.classList.add('fade-in');
                        }, index * 200);
                    });
                    
                    // Stop observing once animated
                    premiumObserver.unobserve(premiumSection);
                }
            });
        }, {
            threshold: 0.2
        });
        
        // Start observing the premium section
        premiumObserver.observe(premiumSection);
        
        // Add initial styles to cards
        premiumCards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        });
    }

    // Monitor theme changes to adjust premium section accordingly
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            // Let CSS handle most transitions but we can add specific adjustments here if needed
            
            // Force repaint the premium section to refresh gradients
            if (premiumSection) {
                premiumSection.style.display = 'none';
                premiumSection.offsetHeight; // Trigger reflow
                premiumSection.style.display = '';
            }
        });
    }

    // Remove the call to initTextCarousel() since we're using CSS animations now
    // Completely remove the initTextCarousel function if it exists

    // Enhanced smooth scroll for the scroll-down button
    const scrollDownBtn = document.querySelector('.scroll-down-btn');
    if (scrollDownBtn) {
        scrollDownBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                // Add a visual indicator that the button was clicked
                this.classList.add('clicked');
                
                // Remove the class after animation completes
                setTimeout(() => {
                    this.classList.remove('clicked');
                }, 700);
                
                // Get the navbar height if it's visible
                const navbarHeight = navbar.classList.contains('visible') ? navbar.offsetHeight : 0;
                
                // Calculate the scroll position
                const targetPosition = targetSection.getBoundingClientRect().top + window.pageYOffset - navbarHeight;
                
                // Scroll to the target section smoothly
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    }
});

// Add this to your existing CSS (in script tag or via stylesheet)
document.head.insertAdjacentHTML('beforeend', `
    <style>
        .scroll-down-btn.clicked {
            animation: clickPulse 0.7s ease-out;
            background: rgba(var(--primary-rgb), 0.4) !important;
        }
        
        @keyframes clickPulse {
            0% { transform: scale(1); }
            50% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }
        
        /* Add extra attention to the scroll button with this overlay */
        .scroll-down-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
            transform: translateX(-100%);
            transition: all 0.5s ease;
        }
        
        .scroll-down-btn:hover::before {
            transform: translateX(100%);
        }
    </style>
`);

// Check if user is logged in and update UI accordingly
function checkUserLoginStatus() {
    const user = JSON.parse(localStorage.getItem('currentUser'));
    const loggedOutNav = document.querySelector('.logged-out-nav');
    const userProfileNav = document.querySelector('.user-profile-nav');
    
    if (user) {
        // User is logged in
        if (loggedOutNav) loggedOutNav.classList.add('d-none');
        if (userProfileNav) {
            userProfileNav.classList.remove('d-none');
            
            // Update user name
            const userName = userProfileNav.querySelector('.user-name');
            if (userName) {
                userName.textContent = user.name || user.email.split('@')[0];
            }
        }
        
        // Check if user is admin
        const adminEmail = "support@xteam.tn";
        const isAdmin = user.email && typeof user.email === 'string' && 
                       user.email.toLowerCase().trim() === adminEmail.toLowerCase();
        
        if (isAdmin) {
            // Add admin-specific UI elements
            const userNavItem = document.querySelector('.user-nav-item');
            if (userNavItem) {
                userNavItem.classList.remove('d-none');
                const userNavLink = userNavItem.querySelector('a');
                if (userNavLink) {
                    userNavLink.innerHTML = '<i class="bi bi-speedometer2"></i> Admin Dashboard';
                }
            }
        } else {
            // Regular user UI elements
            const userNavItem = document.querySelector('.user-nav-item');
            if (userNavItem) {
                userNavItem.classList.remove('d-none');
            }
        }
    }
}

// Set up the authentication overlay
function setupAuthOverlay() {
    const authOverlay = document.getElementById('authOverlay');
    const authContainer = document.getElementById('authContainer');
    const loginBtn = document.getElementById('loginBtn');
    const closeAuth = document.getElementById('closeAuth');
    const registerToggle = document.getElementById('registerToggle');
    const loginToggle = document.getElementById('loginToggle');
    const logoutBtn = document.getElementById('logoutBtn');
    
    // Open auth overlay with login form
    if (loginBtn) {
        loginBtn.addEventListener('click', () => {
            // Default to login view when the button is first clicked
            showAuthOverlay('login');
        });
    }
    
    // Close auth overlay
    if (closeAuth) {
        closeAuth.addEventListener('click', () => {
            authOverlay.classList.remove('active');
            setTimeout(() => {
                authContainer.classList.remove('active');
            }, 200);
        });
    }
    
    // Toggle between login and signup forms
    if (registerToggle) {
        registerToggle.addEventListener('click', () => {
            authContainer.classList.add('active');
        });
    }
    
    if (loginToggle) {
        loginToggle.addEventListener('click', () => {
            authContainer.classList.remove('active');
        });
    }
    
    // Form submissions
    const signInForm = document.getElementById('signInFormOverlay');
    const signUpForm = document.getElementById('signUpFormOverlay');
    
    if (signInForm) {
        signInForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = signInForm.querySelector('input[type="email"]').value;
            const password = signInForm.querySelector('input[type="password"]').value;
            
            // Simulate successful login
            if (email && password) {
                // Check if admin login
                const adminEmail = "support@xteam.tn";
                const isAdmin = email.toLowerCase().trim() === adminEmail.toLowerCase();
                
                const user = { 
                    email, 
                    name: email.split('@')[0], 
                    isAdmin
                };
                
                // Store user in localStorage
                localStorage.setItem('currentUser', JSON.stringify(user));
                
                // Close auth overlay and redirect to dashboard
                authOverlay.classList.remove('active');
                
                setTimeout(() => {
                    // Update UI to reflect logged in state
                    checkUserLoginStatus();
                    
                    // Redirect to dashboard if specified
                    if (e.submitter && e.submitter.dataset.redirect === 'dashboard') {
                        window.location.href = 'dashboard.html';
                    } else {
                        // Just refresh the current page to show logged in state
                        window.location.reload();
                    }
                }, 300);
            }
        });
    }
    
    if (signUpForm) {
        signUpForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const name = signUpForm.querySelector('input[type="text"]').value;
            const email = signUpForm.querySelector('input[type="email"]').value;
            const password = signUpForm.querySelector('input[type="password"]').value;
            
            if (name && email && password) {
                // Simulate user registration
                const user = { email, name };
                
                // Store user in localStorage
                localStorage.setItem('currentUser', JSON.stringify(user));
                
                // Close auth overlay
                authOverlay.classList.remove('active');
                
                setTimeout(() => {
                    // Update UI to reflect logged in state
                    checkUserLoginStatus();
                    
                    // Reload the page or redirect
                    window.location.reload();
                }, 300);
            }
        });
    }
    
    // Logout functionality
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Remove user from localStorage
            localStorage.removeItem('currentUser');
            
            // Reload the page to show logged out state
            window.location.reload();
        });
    }
}

// Show auth overlay with specified form active
function showAuthOverlay(formType) {
    const authOverlay = document.getElementById('authOverlay');
    const authContainer = document.getElementById('authContainer');
    
    if (authOverlay && authContainer) {
        // Set the active form
        if (formType === 'signup') {
            authContainer.classList.add('active');
        } else {
            authContainer.classList.remove('active');
        }
        
        // Show the overlay
        authOverlay.classList.add('active');
    }
}

// Enhanced card interactions
document.addEventListener('DOMContentLoaded', function() {
    // 3D card effect
    const serviceCards = document.querySelectorAll('.service-card');
    
    serviceCards.forEach(card => {
        card.addEventListener('mousemove', function(e) {
            const cardRect = card.getBoundingClientRect();
            const cardCenterX = cardRect.left + cardRect.width / 2;
            const cardCenterY = cardRect.top + cardRect.height / 2;
            
            // Calculate rotation values
            const rotateX = (e.clientY - cardCenterY) * 0.05;
            const rotateY = (cardCenterX - e.clientX) * 0.05;
            
            // Apply rotation
            card.style.transform = `translateY(-15px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        });
        
        // Reset transform when mouse leaves
        card.addEventListener('mouseleave', function() {
            card.style.transform = '';
            setTimeout(() => {
                card.style.transition = 'all 0.5s var(--transition-bounce)';
            }, 100);
        });
        
        // Set faster initial transition
        card.addEventListener('mouseenter', function() {
            card.style.transition = 'all 0.2s var(--transition-bounce)';
        });
    });
    
    // Parallax effect for eco cards
    const ecoCards = document.querySelectorAll('.eco-card');
    
    ecoCards.forEach(card => {
        card.addEventListener('mousemove', function(e) {
            const cardRect = this.getBoundingClientRect();
            const mouseX = e.clientX - cardRect.left;
            const mouseY = e.clientY - cardRect.top;
            
            const cardCenterX = cardRect.width / 2;
            const cardCenterY = cardRect.height / 2;
            
            const moveX = (mouseX - cardCenterX) * 0.05;
            const moveY = (mouseY - cardCenterY) * 0.05;
            
            const icon = this.querySelector('i');
            if (icon) {
                icon.style.transform = `translate(${moveX}px, ${moveY}px) scale(1.1)`;
            }
        });
        
        card.addEventListener('mouseleave', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.style.transform = '';
            }
        });
    });
    
    // Initialize staggered animations for timeline cards
    const timelineCards = document.querySelectorAll('.timeline-card');
    
    const observerOptions = {
        threshold: 0.2,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const timelineObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                timelineObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    timelineCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transitionDelay = `${index * 0.1}s`;
        timelineObserver.observe(card);
    });
});

// Add a new smooth parallax scroll function
function parallaxScroll() {
    const scrollTop = window.pageYOffset;
    
    // Apply parallax to different elements
    document.querySelectorAll('.parallax-element').forEach(el => {
        const speed = el.getAttribute('data-parallax-speed') || 0.2;
        const yPos = -(scrollTop * speed);
        el.style.transform = `translate3d(0, ${yPos}px, 0)`;
    });
}

// Call the parallax scroll function on scroll
window.addEventListener('scroll', parallaxScroll);