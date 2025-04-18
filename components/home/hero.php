<?php
/**
 * Hero Component with Modern Design
 * Contains the HTML and CSS for the website hero section with 3D background and animations
 */
?>
<style>
/* Hero Section Styles */
.hero-section {
    position: relative;
    min-height: 100vh;
    padding: 6rem 0;
    display: flex;
    align-items: center;
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
    background-image: linear-gradient(135deg, rgba(13,14,18,0.7) 30%, rgba(62, 92, 118, 0.5) 100%);
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(13, 27, 42, 0.4);
    z-index: -1;
}

[data-bs-theme="dark"] .hero-background {
    background-image: linear-gradient(135deg, rgba(10,12,20,0.85) 30%, rgba(35, 55, 80, 0.7) 100%);
}

[data-bs-theme="dark"] .hero-overlay {
    background-color: rgba(10, 15, 25, 0.6);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
}

.bubble {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(116, 140, 171, 0.4), rgba(62, 92, 118, 0.2));
    animation: float 15s ease-in-out infinite;
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.05);
}

[data-bs-theme="dark"] .bubble {
    background: linear-gradient(135deg, rgba(45, 65, 95, 0.35), rgba(20, 30, 50, 0.2));
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(80, 100, 140, 0.1);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

@keyframes float {
    0% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(5deg);
    }
    100% {
        transform: translateY(0) rotate(0deg);
    }
}

.hero-content {
    z-index: 1;
    padding: 2rem;
    max-width: 800px;
    margin: 0 auto;
    opacity: 0;
    transform: translateY(30px);
    animation: fadeUp 0.7s ease-out forwards;
    animation-delay: 0.3s;
}

@keyframes fadeUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hero-title {
    font-family: var(--font-heading);
    font-weight: 700;
    font-size: 3.5rem;
    line-height: 1.2;
    margin-bottom: 1.5rem;
    color: #ffffff;
    letter-spacing: -0.02em;
}

.hero-title .highlight {
    background: linear-gradient(90deg, #5D8BB3, #8FB3DE);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    display: inline-block;
    position: relative;
    animation: pulse 3s ease-in-out infinite;
}

[data-bs-theme="dark"] .hero-title .highlight {
    background: linear-gradient(90deg, #7BA4CD, #A8C8E8);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    text-shadow: 0 0 15px rgba(122, 164, 205, 0.3);
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.8;
    }
}

.hero-description {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    color: rgba(255, 255, 255, 0.9);
    opacity: 0;
    transform: translateY(20px);
    animation: fadeUp 0.7s ease-out forwards;
    animation-delay: 0.45s;
}

.hero-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 2rem;
    opacity: 0;
    transform: translateY(20px);
    animation: fadeUp 0.7s ease-out forwards;
    animation-delay: 0.6s;
}

.hero-btn {
    padding: 0.875rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.hero-primary-btn {
    background: linear-gradient(90deg, #5D8BB3, #8FB3DE);
    color: white;
    border: none;
}

.hero-primary-btn:hover {
    background: linear-gradient(90deg, #8FB3DE, #5D8BB3);
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(93, 139, 179, 0.4);
    color: white;
}

[data-bs-theme="dark"] .hero-primary-btn {
    background: linear-gradient(90deg, #6D9BC4, #9CBDE2);
    box-shadow: 0 5px 15px -3px rgba(93, 139, 179, 0.25);
}

[data-bs-theme="dark"] .hero-primary-btn:hover {
    background: linear-gradient(90deg, #7BA4CD, #A8C8E8);
    box-shadow: 0 8px 25px -3px rgba(93, 139, 179, 0.4), 0 0 10px rgba(93, 139, 179, 0.2);
}

.hero-secondary-btn {
    background: transparent;
    color: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.4);
}

.hero-secondary-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.6);
    transform: translateY(-2px);
    color: white;
}

[data-bs-theme="dark"] .hero-secondary-btn {
    border-color: rgba(180, 200, 230, 0.4);
    color: rgba(210, 225, 245, 0.9);
}

[data-bs-theme="dark"] .hero-secondary-btn:hover {
    background: rgba(180, 200, 230, 0.1);
    border-color: rgba(210, 225, 245, 0.6);
}

.btn-icon {
    margin-left: 0.5rem;
    transition: transform 0.2s ease;
}

.hero-primary-btn:hover .btn-icon {
    transform: translateX(4px);
}

.hero-bottom-fade {
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    height: 3rem;
    background: linear-gradient(to top, var(--light), transparent);
    z-index: 1;
}

[data-bs-theme="dark"] .hero-bottom-fade {
    background: linear-gradient(to top, var(--dark, #121212), transparent);
    opacity: 0.9;
}

/* Enhanced Responsive Hero Adjustments */
@media (max-width: 1400px) {
    .hero-content {
        max-width: 700px;
    }
    
    .hero-title {
        font-size: 3.2rem;
    }
}

@media (max-width: 1200px) {
    .hero-section {
        min-height: 90vh;
    }
    
    .hero-title {
        font-size: 3rem;
    }
    
    .hero-description {
        font-size: 1.2rem;
    }
}

@media (max-width: 992px) {
    .hero-section {
        padding: 5rem 0;
    }
    
    .hero-content {
        max-width: 600px;
        padding: 1.5rem;
    }
    
    .hero-title {
        font-size: 2.7rem;
    }
    
    .hero-description {
        font-size: 1.125rem;
        margin-bottom: 1.5rem;
    }
    
    .hero-buttons {
        margin-top: 1.5rem;
    }
    
    .hero-btn {
        padding: 0.75rem 1.3rem;
        font-size: 0.95rem;
    }
}

@media (max-width: 768px) {
    .hero-section {
        padding-top: 120px;
        padding-bottom: 4rem;
        min-height: 85vh;
        justify-content: center;
    }
    
    .hero-content {
        text-align: center;
        padding: 1.5rem 1rem;
    }
    
    .hero-title {
        font-size: 2.5rem;
        margin-bottom: 1.2rem;
    }
    
    .hero-description {
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
    }
    
    .hero-buttons {
        justify-content: center;
        gap: 0.8rem;
    }
    
    /* Responsive bubble adjustments */
    .bubble {
        opacity: 0.7 !important;
    }
}

@media (max-width: 576px) {
    .hero-section {
        padding-top: 100px;
        min-height: 80vh;
    }
    
    .hero-content {
        padding: 1rem 0.5rem;
    }
    
    .hero-title {
        font-size: 2.2rem;
        margin-bottom: 1rem;
    }
    
    .hero-description {
        font-size: 1rem;
        margin-bottom: 1.2rem;
    }
    
    .hero-buttons {
        flex-direction: column;
        width: 100%;
        gap: 0.7rem;
    }
    
    .hero-btn {
        width: 100%;
        padding: 0.7rem 1.2rem;
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .hero-section {
        padding-top: 90px;
    }
    
    .hero-title {
        font-size: 1.9rem;
    }
    
    .hero-description {
        font-size: 0.95rem;
    }
    
    /* Reduce animation complexity on small devices */
    .bubble {
        display: none;
    }
    
    .bubble:nth-child(-n+3) {
        display: block;
    }
}

/* Portrait orientation specific adjustments */
@media (max-height: 700px) {
    .hero-section {
        min-height: auto;
        padding-top: 90px;
        padding-bottom: 3rem;
    }
    
    .hero-title {
        font-size: calc(1.8rem + 1vw);
    }
    
    .hero-description {
        margin-bottom: 1rem;
    }
}
</style>

<section class="hero-section" id="home">
    <div class="hero-background">
        <!-- JS will add bubbles here -->
    </div>
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Unleash your business with <span class="highlight">exceptional freelance</span> talent</h1>
            <p class="hero-description">Access a world of skilled professionals at your fingertips. From creative design to technical development, find the right expert to transform your vision into reality.</p>
            <div class="hero-buttons">
                <a href="components/home/services.php" class="hero-btn hero-primary-btn">
                    Explore Services
                    <i class="bi bi-arrow-right btn-icon"></i>
                </a>
                <a href="#become-seller" class="hero-btn hero-secondary-btn">Become a Seller</a>
            </div>
        </div>
    </div>
    <div class="hero-bottom-fade"></div>
    <div class="scroll-indicator"><i class="bi bi-chevron-down"></i></div>
</section>

<script>
// Add this to your existing JavaScript or keep it inline
document.addEventListener('DOMContentLoaded', function() {
    // Create a more complex bubble animation
    const bubbleCount = Math.min(8, window.innerWidth < 768 ? 5 : 8); // Reduce on mobile
    const heroBackground = document.querySelector('.hero-background');
    if (heroBackground) {
        // Remove any existing bubbles
        const existingBubbles = heroBackground.querySelectorAll('.bubble');
        existingBubbles.forEach(bubble => bubble.remove());
        
        // Create new bubbles with varied characteristics
        for (let i = 0; i < bubbleCount; i++) {
            const bubble = document.createElement('div');
            bubble.classList.add('bubble');
            
            // Varied sizes (smaller on mobile)
            const maxSize = window.innerWidth < 768 ? 200 : 300;
            const size = Math.floor(Math.random() * maxSize) + (window.innerWidth < 768 ? 60 : 100);
            
            // Spread them throughout the hero area
            const top = Math.floor(Math.random() * 90) + 5;
            const left = Math.floor(Math.random() * 90) + 5;
            
            // Varied opacity and blur
            const opacity = (Math.random() * 0.3) + (window.innerWidth < 768 ? 0.05 : 0.1);
            const blur = Math.floor(Math.random() * 10) + (window.innerWidth < 768 ? 3 : 5);
            
            // Apply styles
            bubble.style.width = `${size}px`;
            bubble.style.height = `${size}px`;
            bubble.style.top = `${top}%`;
            bubble.style.left = `${left}%`;
            bubble.style.opacity = opacity;
            bubble.style.backdropFilter = `blur(${blur}px)`;
            
            // Random animation duration and delay (faster on mobile)
            const duration = Math.floor(Math.random() * 10) + (window.innerWidth < 768 ? 8 : 10);
            const delay = Math.random() * 5;
            bubble.style.animationDuration = `${duration}s`;
            bubble.style.animationDelay = `${delay}s`;
            
            // Add to hero background
            heroBackground.appendChild(bubble);
        }
    }
    
    // Add scroll indicator for hero section with responsive styling
    const heroSection = document.querySelector('.hero-section');
    const scrollIndicator = heroSection.querySelector('.scroll-indicator');
    
    if (scrollIndicator) {
        const style = document.createElement('style');
        style.textContent = `
            .scroll-indicator {
                position: absolute;
                bottom: 2rem;
                left: 50%;
                transform: translateX(-50%);
                color: white;
                font-size: 1.5rem;
                animation: bounce 2s infinite;
                cursor: pointer;
                z-index: 5;
                opacity: 0.7;
                transition: opacity 0.3s ease;
                text-align: center;
            }
            
            .scroll-indicator:hover {
                opacity: 1;
            }
            
            @keyframes bounce {
                0%, 20%, 50%, 80%, 100% {transform: translateY(0) translateX(-50%);}
                40% {transform: translateY(-20px) translateX(-50%);}
                60% {transform: translateY(-10px) translateX(-50%);}
            }
            
            @media (max-width: 768px) {
                .scroll-indicator {
                    bottom: 1.5rem;
                    font-size: 1.3rem;
                }
            }
            
            @media (max-width: 480px) {
                .scroll-indicator {
                    bottom: 1rem;
                    font-size: 1.2rem;
                }
            }
            
            @media (max-height: 700px) {
                .scroll-indicator {
                    display: none;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Add scroll functionality
        scrollIndicator.addEventListener('click', () => {
            const nextSection = heroSection.nextElementSibling;
            if (nextSection) {
                nextSection.scrollIntoView({ behavior: 'smooth' });
            } else {
                window.scrollTo({
                    top: window.innerHeight,
                    behavior: 'smooth'
                });
            }
        });
    }
    
    // Handle window resize events for responsive adjustments
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Adjust bubbles for current screen size
            const bubbles = document.querySelectorAll('.bubble');
            if (bubbles.length > 0 && window.innerWidth < 480) {
                // Hide some bubbles on very small screens
                bubbles.forEach((bubble, index) => {
                    bubble.style.display = index < 3 ? 'block' : 'none';
                });
            } else {
                // Show all bubbles on larger screens
                bubbles.forEach(bubble => {
                    bubble.style.display = 'block';
                });
            }
        }, 250);
    });
});
</script>