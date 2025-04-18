<?php
/**
 * Content Sections Component with Frame-based Design
 * Contains the HTML and CSS for the main content sections of the homepage
 */
?>
<style>
/* Frame-based Section Design System */
.section-frame {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: 6rem 0;
    overflow: hidden;
    scroll-margin-top: 80px; /* Ensures proper scroll positioning with fixed navbar */
}

.section-frame:nth-child(even) {
    background-color: rgba(247, 248, 250, 0.5);
}

[data-bs-theme="dark"] .section-frame:nth-child(even) {
    background-color: rgba(18, 21, 30, 0.5);
}

.section-frame-content {
    width: 100%;
    max-width: 1400px; /* Increased from 1200px to make content wider */
    margin: 0 auto;
    padding: 0 1rem; /* Reduced from 2rem to decrease empty space on sides */
    z-index: 2;
    position: relative;
    transition: max-width 0.3s ease;
}

/* Adjust section frame width when scrolled past hero for consistency with navbar */
.section-frame:not(#home) + .section-frame .section-frame-content {
    max-width: 1200px; /* Increased from 1000px to make content wider */
}

.section-frame-header {
    text-align: center;
    margin-bottom: 3rem;
    position: relative;
}

.section-frame-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    position: relative;
    display: inline-block;
    color: var(--accent);
}

.section-frame-title::after {
    content: '';
    position: absolute;
    left: 50%;
    bottom: -0.5rem;
    width: 50px;
    height: 3px;
    background: linear-gradient(90deg, #5D8BB3, #8FB3DE);
    transform: translateX(-50%);
}

[data-bs-theme="dark"] .section-frame-title::after {
    background: linear-gradient(90deg, #7BA4CD, #A8C8E8);
}

.section-frame-subtitle {
    font-size: 1.2rem;
    max-width: 700px;
    margin: 0 auto;
    color: var(--secondary);
}

.section-corner-decoration {
    position: absolute;
    width: 300px;
    height: 300px;
    z-index: 1;
    opacity: 0.1;
    pointer-events: none;
}

.section-corner-decoration-1 {
    top: -100px;
    right: -100px;
    background: linear-gradient(135deg, transparent, rgba(93, 139, 179, 0.3));
    transform: rotate(45deg);
    border-radius: 50px;
}

.section-corner-decoration-2 {
    bottom: -100px;
    left: -100px;
    background: linear-gradient(45deg, rgba(93, 139, 179, 0.3), transparent);
    transform: rotate(45deg);
    border-radius: 50px;
}

/* Section transition indicator */
.section-transition {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 60px;
    z-index: 5;
    pointer-events: none;
}

.section-transition-indicator {
    position: absolute;
    left: 50%;
    bottom: 1.5rem;
    transform: translateX(-50%);
    color: var(--primary);
    font-size: 1.5rem;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: white;
    border-radius: 50%;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    animation: float 2s ease-in-out infinite;
    z-index: 10;
    pointer-events: auto;
    cursor: pointer;
    transition: all 0.3s ease;
}

[data-bs-theme="dark"] .section-transition-indicator {
    background-color: rgba(30, 35, 45, 0.95);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3), 0 0 10px rgba(93, 139, 179, 0.2);
    color: #8FB3DE;
}

.section-transition-indicator:hover {
    transform: translateX(-50%) translateY(-5px);
    box-shadow: 0 8px 20px rgba(93, 139, 179, 0.2);
    color: #8FB3DE;
}

[data-bs-theme="dark"] .section-transition-indicator:hover {
    color: #A8C8E8;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4), 0 0 15px rgba(93, 139, 179, 0.3);
}

/* Animation for section components */
.section-animate {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.8s ease-out;
}

.section-animate.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Category Grid Styles (enhanced for frame design) */
.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.category-card {
    background: white;
    border-radius: 15px;
    padding: 2rem 1.5rem;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    text-decoration: none;
    color: #1D2D44;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.03);
    backdrop-filter: blur(5px);
}

.category-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(116, 140, 171, 0.1), rgba(62, 92, 118, 0.1));
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 0;
}

.category-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.category-card:hover::before {
    opacity: 1;
}

.category-icon {
    font-size: 2.5rem;
    color: #3E5C76;
    margin-bottom: 1rem;
    position: relative;
    z-index: 1;
    transition: transform 0.3s ease;
}

.category-card:hover .category-icon {
    transform: scale(1.1);
}

.category-card h3 {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 1;
}

.category-card p {
    color: #748CAB;
    font-size: 0.9rem;
    position: relative;
    z-index: 1;
}

/* Dark mode adjustments */
[data-bs-theme="dark"] .category-card {
    background: rgba(31, 32, 40, 0.8);
    border-color: rgba(70, 90, 120, 0.2);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

[data-bs-theme="dark"] .category-card h3 {
    color: #FFFFFF;
}

[data-bs-theme="dark"] .category-card p {
    color: #A4C2E5;
}

[data-bs-theme="dark"] .category-icon {
    color: #8FB3DE;
}

/* Service Grid Styles */
.service-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.service-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    text-decoration: none;
    border: 1px solid rgba(0, 0, 0, 0.03);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.service-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.service-card-img {
    height: 200px;
    background-size: cover;
    background-position: center;
    position: relative;
    overflow: hidden;
}

.service-card-img::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, transparent 70%, rgba(0, 0, 0, 0.7));
    z-index: 1;
}

.service-card-rating {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(255, 255, 255, 0.9);
    padding: 0.3rem 0.5rem;
    border-radius: 20px;
    display: flex;
    align-items: center;
    font-size: 0.9rem;
    font-weight: 600;
    color: #1D2D44;
    z-index: 2;
}

.service-card-rating i {
    color: #FFD700;
    margin-right: 0.3rem;
}

.service-card-content {
    padding: 1.5rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.service-card-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.8rem;
    color: #1D2D44;
}

.service-card-description {
    font-size: 0.9rem;
    color: #748CAB;
    margin-bottom: 1rem;
    flex-grow: 1;
}

.service-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.service-card-price {
    font-weight: 700;
    font-size: 1.1rem;
    color: #3E5C76;
}

.service-card-seller {
    display: flex;
    align-items: center;
    font-size: 0.85rem;
    color: #748CAB;
}

.service-card-seller img {
    width: 25px;
    height: 25px;
    border-radius: 50%;
    margin-right: 0.5rem;
    object-fit: cover;
}

/* Dark mode adjustments for services */
[data-bs-theme="dark"] .service-card {
    background: rgba(31, 32, 40, 0.8);
    border-color: rgba(70, 90, 120, 0.2);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

[data-bs-theme="dark"] .service-card-title {
    color: #FFFFFF;
}

[data-bs-theme="dark"] .service-card-description {
    color: #A4C2E5;
}

[data-bs-theme="dark"] .service-card-price {
    color: #8FB3DE;
}

[data-bs-theme="dark"] .service-card-rating {
    background: rgba(30, 35, 45, 0.9);
    color: #A4C2E5;
}

/* Auto-scrolling services carousel with touch/mouse scroll */
.services-scroll-container {
    position: relative;
    width: 100%;
    overflow: hidden;
    margin-top: 2rem;
    padding: 0.5rem 0;
    cursor: grab;
}

.services-scroll-container:active {
    cursor: grabbing;
}

.services-scroll-wrapper {
    display: flex;
    animation: scrollServices 40s linear infinite;
    width: max-content;
    gap: 2rem;
    transition: transform 0.3s ease;
}

.services-scroll-wrapper .service-card {
    width: 350px;
    flex: 0 0 auto;
    pointer-events: auto;
}

@keyframes scrollServices {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(calc(-350px * 3 - 6rem)); /* Width of 3 cards + gaps */
    }
}

/* Timeline Styles for How It Works */
.timeline {
    position: relative;
    margin: 3rem 0;
    padding: 0;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    width: 2px;
    height: 100%;
    background: linear-gradient(to bottom, rgba(93, 139, 179, 0.3), rgba(93, 139, 179, 0.7), rgba(93, 139, 179, 0.3));
    transform: translateX(-50%);
}

.timeline-item {
    position: relative;
    width: 50%;
    padding: 2rem;
    box-sizing: border-box;
    display: flex;
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.8s ease-out;
}

.timeline-item.visible {
    opacity: 1;
    transform: translateY(0);
}

.timeline-item:nth-child(odd) {
    margin-left: auto;
    text-align: left;
    padding-left: 3rem;
}

.timeline-item:nth-child(even) {
    margin-right: auto;
    text-align: right;
    padding-right: 3rem;
}

.timeline-item-content {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    position: relative;
    border: 1px solid rgba(0, 0, 0, 0.03);
    transition: all 0.3s ease;
    width: 100%;
}

.timeline-item-content:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.timeline-item:nth-child(odd) .timeline-item-content::before {
    content: '';
    position: absolute;
    top: 50%;
    left: -15px;
    width: 0;
    height: 0;
    border-top: 15px solid transparent;
    border-bottom: 15px solid transparent;
    border-right: 15px solid white;
    transform: translateY(-50%);
}

.timeline-item:nth-child(even) .timeline-item-content::before {
    content: '';
    position: absolute;
    top: 50%;
    right: -15px;
    width: 0;
    height: 0;
    border-top: 15px solid transparent;
    border-bottom: 15px solid transparent;
    border-left: 15px solid white;
    transform: translateY(-50%);
}

.timeline-item-circle {
    position: absolute;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #5D8BB3, #8FB3DE);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    z-index: 2;
    box-shadow: 0 0 0 5px rgba(255, 255, 255, 0.8), 0 5px 15px rgba(0, 0, 0, 0.1);
}

.timeline-item:nth-child(odd) .timeline-item-circle {
    left: -20px;
    top: 50%;
    transform: translateY(-50%);
}

.timeline-item:nth-child(even) .timeline-item-circle {
    right: -20px;
    top: 50%;
    transform: translateY(-50%);
}

.timeline-item-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #1D2D44;
    margin-bottom: 1rem;
}

.timeline-item-description {
    color: #748CAB;
}

/* Dark mode adjustments for timeline */
[data-bs-theme="dark"] .timeline-item-content {
    background: rgba(31, 32, 40, 0.8);
    border-color: rgba(70, 90, 120, 0.2);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

[data-bs-theme="dark"] .timeline-item:nth-child(odd) .timeline-item-content::before {
    border-right-color: rgba(31, 32, 40, 0.8);
}

[data-bs-theme="dark"] .timeline-item:nth-child(even) .timeline-item-content::before {
    border-left-color: rgba(31, 32, 40, 0.8);
}

[data-bs-theme="dark"] .timeline-item-title {
    color: #FFFFFF;
}

[data-bs-theme="dark"] .timeline-item-description {
    color: #A4C2E5;
}

[data-bs-theme="dark"] .timeline-item-circle {
    background: linear-gradient(135deg, #7BA4CD, #A8C8E8);
    box-shadow: 0 0 0 5px rgba(30, 35, 45, 0.8), 0 5px 15px rgba(0, 0, 0, 0.2);
}

/* Features Grid Styles (Why Choose Us) */
.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.feature-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.03);
    text-align: center;
    position: relative;
    overflow: hidden;
}

.feature-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(to bottom, #5D8BB3, #8FB3DE);
    transition: all 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.feature-card:hover::before {
    width: 100%;
    opacity: 0.1;
}

.feature-icon {
    font-size: 2.5rem;
    color: #3E5C76;
    margin-bottom: 1.5rem;
    position: relative;
    display: inline-block;
}

.feature-icon::after {
    content: '';
    position: absolute;
    width: 60px;
    height: 60px;
    background-color: rgba(93, 139, 179, 0.1);
    border-radius: 50%;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: -1;
    transition: all 0.3s ease;
}

.feature-card:hover .feature-icon::after {
    width: 70px;
    height: 70px;
    background-color: rgba(93, 139, 179, 0.2);
}

.feature-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #1D2D44;
    margin-bottom: 1rem;
}

.feature-description {
    color: #748CAB;
}

/* Dark mode adjustments for features */
[data-bs-theme="dark"] .feature-card {
    background: rgba(31, 32, 40, 0.8);
    border-color: rgba(70, 90, 120, 0.2);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

[data-bs-theme="dark"] .feature-card::before {
    background: linear-gradient(to bottom, #7BA4CD, #A8C8E8);
}

[data-bs-theme="dark"] .feature-title {
    color: #FFFFFF;
}

[data-bs-theme="dark"] .feature-description {
    color: #A4C2E5;
}

[data-bs-theme="dark"] .feature-icon {
    color: #8FB3DE;
}

[data-bs-theme="dark"] .feature-icon::after {
    background-color: rgba(122, 164, 205, 0.1);
}

[data-bs-theme="dark"] .feature-card:hover .feature-icon::after {
    background-color: rgba(122, 164, 205, 0.2);
}

/* Responsive adjustments for frame layout */
@media (max-width: 1200px) {
    .section-frame {
        padding: 5rem 0;
    }
    
    .section-frame-content {
        padding: 0 1.5rem;
    }
    
    .section-frame-title {
        font-size: 2.2rem;
    }
}

@media (max-width: 992px) {
    .timeline:before {
        left: 30px;
    }
    
    .timeline-item {
        width: 100%;
        padding-left: 5rem !important;
        padding-right: 1rem !important;
        text-align: left !important;
    }
    
    .timeline-item:nth-child(even) .timeline-item-content::before,
    .timeline-item:nth-child(odd) .timeline-item-content::before {
        left: -15px;
        right: auto;
        border-right: 15px solid white;
        border-left: none;
    }
    
    .timeline-item:nth-child(even) .timeline-item-circle,
    .timeline-item:nth-child(odd) .timeline-item-circle {
        left: 10px;
        right: auto;
    }
    
    [data-bs-theme="dark"] .timeline-item:nth-child(even) .timeline-item-content::before,
    [data-bs-theme="dark"] .timeline-item:nth-child(odd) .timeline-item-content::before {
        border-right-color: rgba(31, 32, 40, 0.8);
    }
    
    .service-grid,
    .features-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
}

@media (max-width: 768px) {
    .section-frame {
        min-height: auto;
        padding: 5rem 0;
    }
    
    .section-frame-title {
        font-size: 2rem;
    }
    
    .section-frame-subtitle {
        font-size: 1.1rem;
    }
    
    .category-grid, 
    .service-grid, 
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .section-frame-content {
        padding: 0 1rem;
    }
    
    .timeline-item {
        padding: 1.5rem 1rem 1.5rem 5rem !important;
    }
}

@media (max-width: 480px) {
    .section-frame {
        padding: 4rem 0;
    }
    
    .section-frame-title {
        font-size: 1.8rem;
    }
    
    .section-frame-subtitle {
        font-size: 1rem;
    }
}
</style>

<!-- Categories Section Frame -->
<section class="section-frame section-animate" id="categories">
    <div class="section-corner-decoration section-corner-decoration-1"></div>
    <div class="section-frame-content">
        <div class="section-frame-header">
            <h2 class="section-frame-title">Explore Popular Categories</h2>
            <p class="section-frame-subtitle">Browse through our most in-demand service categories</p>
        </div>
        <div class="category-grid">
            <a href="#" class="category-card stagger-item">
                <div class="category-icon"><i class="bi bi-code-slash"></i></div>
                <h3>Web Development</h3>
                <p>2,345 services available</p>
            </a>
            <a href="#" class="category-card stagger-item">
                <div class="category-icon"><i class="bi bi-brush"></i></div>
                <h3>Design & Creative</h3>
                <p>1,879 services available</p>
            </a>
            <a href="#" class="category-card stagger-item">
                <div class="category-icon"><i class="bi bi-megaphone"></i></div>
                <h3>Digital Marketing</h3>
                <p>1,653 services available</p>
            </a>
            <a href="#" class="category-card stagger-item">
                <div class="category-icon"><i class="bi bi-translate"></i></div>
                <h3>Writing & Translation</h3>
                <p>982 services available</p>
            </a>
            <a href="#" class="category-card stagger-item">
                <div class="category-icon"><i class="bi bi-camera-video"></i></div>
                <h3>Video & Animation</h3>
                <p>756 services available</p>
            </a>
            <a href="#" class="category-card stagger-item">
                <div class="category-icon"><i class="bi bi-graph-up"></i></div>
                <h3>Data & Analytics</h3>
                <p>543 services available</p>
            </a>
            <a href="#" class="category-card stagger-item">
                <div class="category-icon"><i class="bi bi-phone"></i></div>
                <h3>Mobile Development</h3>
                <p>897 services available</p>
            </a>
            <a href="#" class="category-card stagger-item">
                <div class="category-icon"><i class="bi bi-music-note-beamed"></i></div>
                <h3>Music & Audio</h3>
                <p>432 services available</p>
            </a>
        </div>
    </div>
    <div class="section-transition">
        <a href="#featured-services" class="section-transition-indicator">
            <i class="bi bi-chevron-down"></i>
        </a>
    </div>
    <div class="section-corner-decoration section-corner-decoration-2"></div>
</section>

<!-- Featured Services Section Frame -->
<section class="section-frame section-animate" id="featured-services">
    <div class="section-corner-decoration section-corner-decoration-1"></div>
    <div class="section-frame-content">
        <div class="section-frame-header">
            <h2 class="section-frame-title">Featured Services</h2>
            <p class="section-frame-subtitle">Discover our most popular and highly-rated freelance services</p>
        </div>
        <div class="services-scroll-container">
            <div class="services-scroll-wrapper">
                <a href="service-detail.php?id=1" class="service-card stagger-item" data-service-id="1">
                    <div class="service-card-img" style="background-image: url('https://images.unsplash.com/photo-1587440871875-191322ee64b0?auto=format&fit=crop&w=600&q=80')">
                        <div class="service-card-rating">
                            <i class="bi bi-star-fill"></i> 4.9
                        </div>
                    </div>
                    <div class="service-card-content">
                        <h3 class="service-card-title">Professional Website Development</h3>
                        <p class="service-card-description">I will create a responsive, modern website for your business or personal brand using the latest technologies.</p>
                        <div class="service-card-footer">
                            <div class="service-card-price">From $299</div>
                            <div class="service-card-seller">
                                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Alex M.">
                                <span>Alex M.</span>
                            </div>
                        </div>
                    </div>
                </a>
                <a href="service-detail.php?id=2" class="service-card stagger-item" data-service-id="2">
                    <div class="service-card-img" style="background-image: url('https://images.unsplash.com/photo-1542744173-05336fcc7ad4?auto=format&fit=crop&w=600&q=80')">
                        <div class="service-card-rating">
                            <i class="bi bi-star-fill"></i> 4.8
                        </div>
                    </div>
                    <div class="service-card-content">
                        <h3 class="service-card-title">SEO Optimization Package</h3>
                        <p class="service-card-description">Boost your website's search engine rankings with our comprehensive SEO package including keyword research and optimization.</p>
                        <div class="service-card-footer">
                            <div class="service-card-price">From $159</div>
                            <div class="service-card-seller">
                                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sara K.">
                                <span>Sara K.</span>
                            </div>
                        </div>
                    </div>
                </a>
                <a href="service-detail.php?id=3" class="service-card stagger-item" data-service-id="3">
                    <div class="service-card-img" style="background-image: url('https://images.unsplash.com/photo-1545239351-ef35f43d514b?auto=format&fit=crop&w=600&q=80')">
                        <div class="service-card-rating">
                            <i class="bi bi-star-fill"></i> 5.0
                        </div>
                    </div>
                    <div class="service-card-content">
                        <h3 class="service-card-title">Brand Identity Design</h3>
                        <p class="service-card-description">Complete brand identity package including logo design, color palette, typography, and brand guidelines.</p>
                        <div class="service-card-footer">
                            <div class="service-card-price">From $349</div>
                            <div class="service-card-seller">
                                <img src="https://randomuser.me/api/portraits/men/67.jpg" alt="Marcus T.">
                                <span>Marcus T.</span>
                            </div>
                        </div>
                    </div>
                </a>
                <!-- Duplicate cards for infinite scrolling effect -->
                <a href="service-detail.php?id=1" class="service-card stagger-item" data-service-id="1">
                    <div class="service-card-img" style="background-image: url('https://images.unsplash.com/photo-1587440871875-191322ee64b0?auto=format&fit=crop&w=600&q=80')">
                        <div class="service-card-rating">
                            <i class="bi bi-star-fill"></i> 4.9
                        </div>
                    </div>
                    <div class="service-card-content">
                        <h3 class="service-card-title">Professional Website Development</h3>
                        <p class="service-card-description">I will create a responsive, modern website for your business or personal brand using the latest technologies.</p>
                        <div class="service-card-footer">
                            <div class="service-card-price">From $299</div>
                            <div class="service-card-seller">
                                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Alex M.">
                                <span>Alex M.</span>
                            </div>
                        </div>
                    </div>
                </a>
                <a href="service-detail.php?id=2" class="service-card stagger-item" data-service-id="2">
                    <div class="service-card-img" style="background-image: url('https://images.unsplash.com/photo-1542744173-05336fcc7ad4?auto=format&fit=crop&w=600&q=80')">
                        <div class="service-card-rating">
                            <i class="bi bi-star-fill"></i> 4.8
                        </div>
                    </div>
                    <div class="service-card-content">
                        <h3 class="service-card-title">SEO Optimization Package</h3>
                        <p class="service-card-description">Boost your website's search engine rankings with our comprehensive SEO package including keyword research and optimization.</p>
                        <div class="service-card-footer">
                            <div class="service-card-price">From $159</div>
                            <div class="service-card-seller">
                                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sara K.">
                                <span>Sara K.</span>
                            </div>
                        </div>
                    </div>
                </a>
                <a href="service-detail.php?id=3" class="service-card stagger-item" data-service-id="3">
                    <div class="service-card-img" style="background-image: url('https://images.unsplash.com/photo-1545239351-ef35f43d514b?auto=format&fit=crop&w=600&q=80')">
                        <div class="service-card-rating">
                            <i class="bi bi-star-fill"></i> 5.0
                        </div>
                    </div>
                    <div class="service-card-content">
                        <h3 class="service-card-title">Brand Identity Design</h3>
                        <p class="service-card-description">Complete brand identity package including logo design, color palette, typography, and brand guidelines.</p>
                        <div class="service-card-footer">
                            <div class="service-card-price">From $349</div>
                            <div class="service-card-seller">
                                <img src="https://randomuser.me/api/portraits/men/67.jpg" alt="Marcus T.">
                                <span>Marcus T.</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div class="section-transition">
        <a href="#how-it-works" class="section-transition-indicator">
            <i class="bi bi-chevron-down"></i>
        </a>
    </div>
    <div class="section-corner-decoration section-corner-decoration-2"></div>
</section>

<!-- How It Works Section Frame -->
<section class="section-frame section-animate" id="how-it-works">
    <div class="section-corner-decoration section-corner-decoration-1"></div>
    <div class="section-frame-content">
        <div class="section-frame-header">
            <h2 class="section-frame-title">How It Works</h2>
            <p class="section-frame-subtitle">Our simple process to connect you with the perfect freelancer</p>
        </div>
        <div class="timeline">
            <div class="timeline-item section-animate">
                <div class="timeline-item-circle">1</div>
                <div class="timeline-item-content">
                    <h3 class="timeline-item-title">Find the Perfect Service</h3>
                    <p class="timeline-item-description">Browse through our categories or search for specific skills. Filter by price, delivery time, or seller rating to find exactly what you need.</p>
                </div>
            </div>
            <div class="timeline-item section-animate">
                <div class="timeline-item-circle">2</div>
                <div class="timeline-item-content">
                    <h3 class="timeline-item-title">Contact the Freelancer</h3>
                    <p class="timeline-item-description">Discuss your project details, requirements, and expectations directly with the freelancer before placing your order.</p>
                </div>
            </div>
            <div class="timeline-item section-animate">
                <div class="timeline-item-circle">3</div>
                <div class="timeline-item-content">
                    <h3 class="timeline-item-title">Place Your Order</h3>
                    <p class="timeline-item-description">Once you're satisfied with the details, place your order securely. Your payment will be held in escrow until you approve the work.</p>
                </div>
            </div>
            <div class="timeline-item section-animate">
                <div class="timeline-item-circle">4</div>
                <div class="timeline-item-content">
                    <h3 class="timeline-item-title">Receive & Review</h3>
                    <p class="timeline-item-description">Get regular updates on your project. Once delivered, review the work and provide feedback. Release payment when you're 100% satisfied.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="section-transition">
        <a href="#features" class="section-transition-indicator">
            <i class="bi bi-chevron-down"></i>
        </a>
    </div>
    <div class="section-corner-decoration section-corner-decoration-2"></div>
</section>

<!-- About/Features Section Frame -->
<section class="section-frame section-animate" id="features">
    <div class="section-corner-decoration section-corner-decoration-1"></div>
    <div class="section-frame-content">
        <div class="section-frame-header">
            <h2 class="section-frame-title">Why Choose Us</h2>
            <p class="section-frame-subtitle">Discover the benefits of our freelance marketplace platform</p>
        </div>
        <div class="features-grid">
            <div class="feature-card stagger-item">
                <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                <h3 class="feature-title">Secure Payments</h3>
                <p class="feature-description">Your payments are held in escrow until you're completely satisfied with the delivered work, ensuring secure transactions.</p>
            </div>
            <div class="feature-card stagger-item">
                <div class="feature-icon"><i class="bi bi-person-check"></i></div>
                <h3 class="feature-title">Verified Freelancers</h3>
                <p class="feature-description">All our freelancers undergo a strict verification process to ensure they have the skills and experience they claim.</p>
            </div>
            <div class="feature-card stagger-item">
                <div class="feature-icon"><i class="bi bi-clock-history"></i></div>
                <h3 class="feature-title">24/7 Support</h3>
                <p class="feature-description">Our customer support team is available around the clock to assist with any questions or issues you may have.</p>
            </div>
            <div class="feature-card stagger-item">
                <div class="feature-icon"><i class="bi bi-hand-thumbs-up"></i></div>
                <h3 class="feature-title">Satisfaction Guaranteed</h3>
                <p class="feature-description">Not happy with the delivered work? Our revision policy ensures you get exactly what you need from your freelancer.</p>
            </div>
            <div class="feature-card stagger-item">
                <div class="feature-icon"><i class="bi bi-lightning-charge"></i></div>
                <h3 class="feature-title">Fast Delivery</h3>
                <p class="feature-description">Many of our services come with quick turnaround times, perfect for those urgent projects that can't wait.</p>
            </div>
            <div class="feature-card stagger-item">
                <div class="feature-icon"><i class="bi bi-currency-dollar"></i></div>
                <h3 class="feature-title">Competitive Pricing</h3>
                <p class="feature-description">Find services that fit any budget, with transparent pricing and no hidden fees or charges.</p>
            </div>
        </div>
    </div>
    <div class="section-corner-decoration section-corner-decoration-2"></div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize section transition indicators
    const transitionIndicators = document.querySelectorAll('.section-transition-indicator');
    
    transitionIndicators.forEach(indicator => {
        indicator.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.parentElement.getAttribute('href');
            const targetSection = document.querySelector(href);
            
            if (targetSection) {
                // Get the navbar height if it's visible
                const navbar = document.querySelector('.navbar');
                const navbarHeight = navbar && navbar.classList.contains('visible') ? navbar.offsetHeight : 0;
                
                // Calculate the scroll position
                const targetPosition = targetSection.getBoundingClientRect().top + window.pageYOffset - navbarHeight;
                
                // Add visual clicked state to the indicator
                this.style.transform = 'translateX(-50%) scale(0.9)';
                setTimeout(() => {
                    this.style.transform = 'translateX(-50%) translateY(0)';
                }, 200);
                
                // Scroll to the target section smoothly
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Services auto-scroll controls
    const servicesScrollContainer = document.querySelector('.services-scroll-container');
    const servicesScrollWrapper = document.querySelector('.services-scroll-wrapper');

    if (servicesScrollWrapper && servicesScrollContainer) {
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
            
            // Pause the animation while dragging
            if (!animationPaused) {
                servicesScrollWrapper.style.animationPlayState = 'paused';
                animationPaused = true;
            }
        });

        servicesScrollContainer.addEventListener('mouseleave', () => {
            isDown = false;
            servicesScrollContainer.style.cursor = 'grab';
            
            // Resume animation after a short delay
            if (animationPaused) {
                setTimeout(() => {
                    servicesScrollWrapper.style.animationPlayState = 'running';
                    animationPaused = false;
                }, 1000);
            }
        });

        servicesScrollContainer.addEventListener('mouseup', () => {
            isDown = false;
            servicesScrollContainer.style.cursor = 'grab';
            
            // Resume animation after a short delay
            if (animationPaused) {
                setTimeout(() => {
                    servicesScrollWrapper.style.animationPlayState = 'running';
                    animationPaused = false;
                }, 1000);
            }
        });

        servicesScrollContainer.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - servicesScrollContainer.offsetLeft;
            const walk = (x - startX) * 2; // Scroll speed
            servicesScrollContainer.scrollLeft = scrollLeft - walk;
        });
        
        // Touch events for mobile
        servicesScrollContainer.addEventListener('touchstart', (e) => {
            isDown = true;
            startX = e.touches[0].pageX - servicesScrollContainer.offsetLeft;
            scrollLeft = servicesScrollContainer.scrollLeft;
            
            // Pause the animation while dragging
            if (!animationPaused) {
                servicesScrollWrapper.style.animationPlayState = 'paused';
                animationPaused = true;
            }
        });
        
        servicesScrollContainer.addEventListener('touchend', () => {
            isDown = false;
            
            // Resume animation after a short delay
            if (animationPaused) {
                setTimeout(() => {
                    servicesScrollWrapper.style.animationPlayState = 'running';
                    animationPaused = false;
                }, 1000);
            }
        });
        
        servicesScrollContainer.addEventListener('touchmove', (e) => {
            if (!isDown) return;
            const x = e.touches[0].pageX - servicesScrollContainer.offsetLeft;
            const walk = (x - startX) * 2; // Scroll speed
            servicesScrollContainer.scrollLeft = scrollLeft - walk;
        });
    }
    
    // Enhance section animations with Intersection Observer
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
    
    // Observe all section-animate elements and timeline items
    document.querySelectorAll('.section-animate, .timeline-item').forEach(el => {
        sectionObserver.observe(el);
    });
    
    // Add stagger animation styles to items
    document.querySelectorAll('.stagger-item').forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(30px)';
        item.style.transition = 'opacity 0.6s ease-out, transform 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        item.style.transitionDelay = `${index * 0.1}s`;
    });
});
</script>
