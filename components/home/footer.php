<?php
/**
 * Footer Component with Inline CSS
 * Contains the footer with links, contact info, and copyright
 */
?>
<style>
/* Footer Styles */
.footer {
    background-color: var(--accent-dark);
    color: rgba(255, 255, 255, 0.9);
    padding: 4rem 0 2rem;
    position: relative;
    transition: background-color 0.3s ease, color 0.3s ease;
}

[data-bs-theme="dark"] .footer {
    background-color: #171821;
    color: rgba(255, 255, 255, 0.85);
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.footer-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

.footer-brand {
    grid-column: span 1;
}

.footer-logo {
    margin-bottom: 1.5rem;
    display: block;
    max-width: 150px;
    transition: filter 0.3s ease;
}

[data-bs-theme="dark"] .footer-logo {
    filter: brightness(1.1) contrast(0.95);
}

.footer-description {
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
    opacity: 0.85;
    line-height: 1.6;
}

[data-bs-theme="dark"] .footer-description {
    opacity: 0.75;
}

.footer-social {
    display: flex;
    gap: 0.75rem;
}

.social-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.social-icon:hover {
    background-color: var(--primary);
    transform: translateY(-3px);
    color: #fff;
}

[data-bs-theme="dark"] .social-icon:hover {
    background-color: var(--secondary);
}

.footer-links {
    grid-column: span 1;
}

.footer-title {
    color: #fff;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    position: relative;
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, transform 0.3s ease, opacity 0.3s ease;
}

.footer-title:after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -10px;
    width: 40px;
    height: 2px;
    background-color: var(--primary);
    transition: background-color 0.3s ease, width 0.3s ease 0.1s;
}

[data-bs-theme="dark"] .footer-title:after {
    background-color: var(--secondary);
    width: 50px;
}

.footer-nav {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-nav li {
    margin-bottom: 0.75rem;
}

.footer-nav a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    display: block;
    font-size: 0.95rem;
    position: relative;
    padding-left: 0;
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, transform 0.3s ease, opacity 0.3s ease;
}

.footer-nav a:hover {
    color: #fff;
    transform: translateX(5px);
}

[data-bs-theme="dark"] .footer-nav a {
    color: rgba(255, 255, 255, 0.7);
}

[data-bs-theme="dark"] .footer-nav a:hover {
    color: var(--secondary);
}

.footer-contact {
    grid-column: span 1;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1rem;
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, transform 0.3s ease, opacity 0.3s ease;
}

.contact-icon {
    color: var(--primary);
    margin-right: 1rem;
    font-size: 1.2rem;
    transition: color 0.3s ease, transform 0.3s ease;
}

.contact-item:hover .contact-icon {
    transform: scale(1.1);
}

[data-bs-theme="dark"] .contact-icon {
    color: var(--secondary);
}

.contact-text {
    font-size: 0.95rem;
    line-height: 1.5;
    transition: color 0.3s ease;
}

.contact-item:hover .contact-text {
    color: #fff;
}

.footer-bottom {
    margin-top: 3.5rem;
    padding-top: 2rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.copyright {
    font-size: 0.9rem;
    opacity: 0.75;
    transition: opacity 0.3s ease;
}

.copyright:hover {
    opacity: 1;
}

.footer-bottom-links {
    display: flex;
    gap: 1.5rem;
}

.footer-bottom-link {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s ease, background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, transform 0.3s ease, opacity 0.3s ease;
    position: relative;
}

.footer-bottom-link:after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 0;
    width: 0;
    height: 1px;
    background-color: #fff;
    transition: width 0.3s ease;
}

.footer-bottom-link:hover {
    color: #fff;
}

.footer-bottom-link:hover:after {
    width: 100%;
}

[data-bs-theme="dark"] .footer-bottom-link:after {
    background-color: var(--secondary);
}

[data-bs-theme="dark"] .footer-bottom-link:hover {
    color: var(--secondary);
}

/* Dynamic theme indicator in footer */
.theme-status {
    font-size: 0.8rem;
    opacity: 0.6;
    margin-left: 10px;
    transition: opacity 0.3s ease;
}

.theme-status:hover {
    opacity: 1;
}

@media (max-width: 991px) {
    .footer-container {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .footer-brand {
        grid-column: span 2;
    }
}

@media (max-width: 767px) {
    .footer-container {
        grid-template-columns: 1fr;
    }
    
    .footer-brand,
    .footer-links,
    .footer-contact {
        grid-column: span 1;
    }
    
    .footer-bottom {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .footer-bottom-links {
        justify-content: center;
    }
}
</style>

<footer class="footer">
    <div class="container">
        <div class="footer-container">
            <div class="footer-brand">
                <img src="assets/images/logo-light.png" alt="LenSi Logo" class="footer-logo">
                <p class="footer-description">
                    Connect with talented professionals through our platform to get your projects done quickly and efficiently.
                </p>
                <div class="footer-social">
                    <a href="#" class="social-icon" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            
            <div class="footer-links">
                <h3 class="footer-title">Quick Links</h3>
                <ul class="footer-nav">
                    <li><a href="#">Home</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            
            <div class="footer-contact">
                <h3 class="footer-title">Contact Info</h3>
                <div class="contact-item">
                    <span class="contact-icon"><i class="fas fa-map-marker-alt"></i></span>
                    <span class="contact-text">123 Business Avenue, Tech Park, Silicon Valley, CA 94025</span>
                </div>
                <div class="contact-item">
                    <span class="contact-icon"><i class="fas fa-phone-alt"></i></span>
                    <span class="contact-text">+1 (555) 123-4567</span>
                </div>
                <div class="contact-item">
                    <span class="contact-icon"><i class="fas fa-envelope"></i></span>
                    <span class="contact-text">info@lensi.com</span>
                </div>
                <div class="contact-item">
                    <span class="contact-icon"><i class="fas fa-clock"></i></span>
                    <span class="contact-text">Mon - Fri: 9AM - 6PM</span>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p class="copyright">
                © <span id="year">2023</span> LenSi Marketplace. All rights reserved.
                <span class="theme-status" data-theme-content="Light Mode,Dark Mode"></span>
            </p>
            <div class="footer-bottom-links">
                <a href="#" class="footer-bottom-link">Privacy Policy</a>
                <a href="#" class="footer-bottom-link">Terms of Service</a>
                <a href="#" class="footer-bottom-link">Cookie Policy</a>
            </div>
        </div>
    </div>
    
    <script>
    // Listen for theme changes to enhance footer elements
    document.addEventListener('themeChanged', (event) => {
        const theme = event.detail.theme;
        const footer = document.querySelector('.footer');
        
        if (footer) {
            // Force repaint of footer elements
            const elementsToRefresh = [
                '.footer-title',
                '.social-icon',
                '.footer-nav a',
                '.contact-item',
                '.footer-bottom-link'
            ].map(selector => 
                footer.querySelectorAll(selector)
            ).flat();
            
            // Apply sequential micro-delays for a cascading effect
            elementsToRefresh.forEach((el, index) => {
                if (el) {
                    el.style.transitionDelay = `${index * 0.01}s`;
                    // Reset delay after transition
                    setTimeout(() => {
                        el.style.transitionDelay = '';
                    }, 500);
                }
            });
        }
    });
    </script>
</footer>
