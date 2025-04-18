<?php
require_once __DIR__ . '/data/gigs.php';

// Set up basic error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the components with absolute paths
$componentsPath = __DIR__;
$components = [
    'navbar' => $componentsPath . '/navbar.php',
    'footer' => $componentsPath . '/footer.php'
];

// Handle filtering and sorting
$searchQuery = $_GET['search'] ?? '';
$selectedCategory = $_GET['category'] ?? 'all';
$sortBy = $_GET['sort'] ?? 'recommended';
$minPrice = $_GET['min_price'] ?? 0;
$maxPrice = $_GET['max_price'] ?? 1000;

// Filter gigs based on search, category, and price
$filteredGigs = array_filter($gigs, function($gig) use ($searchQuery, $selectedCategory, $minPrice, $maxPrice) {
    $matchesSearch = empty($searchQuery) || 
        stripos($gig['title'], $searchQuery) !== false || 
        stripos($gig['description'], $searchQuery) !== false;
    
    $matchesCategory = $selectedCategory === 'all' || $gig['category'] === $selectedCategory;
    $matchesPrice = $gig['price'] >= $minPrice && $gig['price'] <= $maxPrice;
    
    return $matchesSearch && $matchesCategory && $matchesPrice;
});

// Sort gigs
usort($filteredGigs, function($a, $b) use ($sortBy) {
    switch ($sortBy) {
        case 'price_low':
            return $a['price'] <=> $b['price'];
        case 'price_high':
            return $b['price'] <=> $a['price'];
        case 'rating':
            return $b['rating'] <=> $a['rating'];
        default:
            // Default (recommended) - featured first, then by rating
            $featuredDiff = ($b['featured'] ? 1 : 0) - ($a['featured'] ? 1 : 0);
            return $featuredDiff !== 0 ? $featuredDiff : ($b['rating'] <=> $a['rating']);
    }
});
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0">
    <meta name="description" content="Explore Services - LenSi Freelance Marketplace">
    <meta name="theme-color" content="#3E5C76">
    <title>Services | LenSi</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/logo_white.svg" sizes="any">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Inter:wght@300;400;500&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Preload critical images -->
    <link rel="preload" as="image" href="assets/images/logo_white.svg">
    <link rel="preload" as="image" href="assets/images/logo_dark.svg">
    
    <!-- Preload critical fonts -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" as="style">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" as="style">
    
    <style>
    /* Core Variables */
    :root {
        --primary: #3E5C76;
        --primary-rgb: 62, 92, 118;
        --secondary: #748CAB;
        --accent: #1D2D44;
        --accent-dark: #0D1B2A;
        --light: #F9F7F0;
        --dark: #0D1B2A;
        --brand-purple: #7c3aed;
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
    }

    /* Layout & Spacing */
    .page-section {
        padding: 2rem 0;
    }

    .section-header {
        margin-bottom: 2.5rem;
        animation: fadeInUp 0.8s ease-out forwards;
    }

    .services-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        padding: calc(4rem + 70px) 0 4rem;
        color: white;
        margin-bottom: 4rem;
        position: relative;
        overflow: hidden;
    }
    
    .services-header::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        z-index: 1;
    }
    
    .services-header::after {
        content: '';
        position: absolute;
        bottom: -30px;
        left: 10%;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
        z-index: 1;
    }

    .services-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1.5rem;
        position: relative;
        z-index: 2;
    }

    /* Enhanced Search & Filters */
    .search-filters {
        background: white;
        border-radius: var(--border-radius-lg);
        padding: 2rem;
        margin-top: -6rem;
        margin-bottom: 3rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        position: relative;
        z-index: 10;
        transition: var(--transition-default);
        animation: slideInUp 0.6s ease-out forwards;
        border: 1px solid rgba(0,0,0,0.05);
    }
    
    .search-filters:hover {
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .search-input-wrapper {
        position: relative;
        flex: 1;
    }

    .search-input {
        padding-left: 3rem !important;
        height: 3.25rem;
        font-size: 1rem;
        border-radius: var(--border-radius-md);
        border: 1px solid rgba(0,0,0,0.1);
        transition: var(--transition-default);
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .search-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.2);
    }

    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--secondary);
        font-size: 1.2rem;
    }

    /* Improved Grid Layout */
    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
        margin: 2rem 0;
    }
    
    /* Animation Keyframes */
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
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    /* Enhanced Filters Sidebar */
    .filters-sidebar {
        background: white;
        border-radius: var(--border-radius-lg);
        padding: 2rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        position: sticky;
        top: 2rem;
        transition: var(--transition-default);
        border: 1px solid rgba(0,0,0,0.05);
        animation: fadeIn 0.8s ease-out forwards;
    }
    
    .filters-sidebar:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }

    .filter-section {
        padding: 1.5rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .filter-section:first-child {
        padding-top: 0;
    }

    .filter-section:last-child {
        padding-bottom: 0;
        border-bottom: none;
    }

    .filter-heading {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        color: var(--accent);
    }

    /* Enhanced Service Cards */
    .service-card {
        background: white;
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: var(--transition-bounce);
        text-decoration: none;
        color: inherit;
        border: 1px solid rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        height: 100%;
        opacity: 0;
        transform: translateY(30px);
    }

    .service-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 30px rgba(0,0,0,0.15);
    }
    
    .service-card:hover .service-image {
        transform: scale(1.05);
    }

    .service-image-container {
        position: relative;
        aspect-ratio: 16/9;
        overflow: hidden;
    }
    
    .service-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .featured-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: var(--brand-purple);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.8rem;
        font-weight: 500;
        box-shadow: 0 3px 10px rgba(124, 58, 237, 0.3);
        backdrop-filter: blur(5px);
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(124, 58, 237, 0.4);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(124, 58, 237, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(124, 58, 237, 0);
        }
    }
    
    .provider-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(var(--primary-rgb), 0.2);
        transition: var(--transition-default);
    }
    
    .service-card:hover .provider-avatar {
        border-color: var(--primary);
    }
    
    .provider-name {
        font-weight: 600;
        margin: 0;
        font-size: 0.9rem;
        font-family: var(--font-primary);
    }
    
    .provider-level {
        color: var(--secondary);
        font-size: 0.8rem;
        margin: 0;
        font-family: var(--font-secondary);
    }
    
    .service-rating {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        transition: var(--transition-default);
    }
    
    .service-card:hover .service-rating {
        transform: scale(1.05);
    }
    
    .star-icon {
        color: #FFD700;
        filter: drop-shadow(0 2px 3px rgba(255, 215, 0, 0.3));
    }
    
    .rating-count {
        color: var(--secondary);
        font-size: 0.8rem;
    }
    
    .price-label {
        font-size: 0.8rem;
        color: var(--secondary);
        display: block;
        margin-bottom: 0.25rem;
        font-family: var(--font-secondary);
    }
    
    .service-price {
        font-weight: 700;
        font-size: 1.2rem;
        color: var(--primary);
        font-family: var(--font-primary);
        transition: var(--transition-default);
    }
    
    .service-card:hover .service-price {
        color: var(--brand-purple);
        transform: scale(1.05);
    }

    .service-content {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .service-provider {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .service-title {
        font-size: 1.1rem;
        line-height: 1.5;
        font-weight: 600;
        color: var(--accent);
        margin: 0;
    }

    .service-footer {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid rgba(0,0,0,0.05);
    }

    /* Results Counter */
    .results-counter {
        background: rgba(var(--primary-rgb), 0.05);
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius-md);
        margin-bottom: 2rem;
        font-family: var(--font-secondary);
        border-left: 3px solid var(--primary);
        animation: fadeIn 0.8s ease-out forwards;
    }
    
    /* Filter Tags */
    .filter-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }
    
    .filter-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(var(--primary-rgb), 0.1);
        border-radius: 2rem;
        font-size: 0.85rem;
        color: var(--primary);
        transition: var(--transition-default);
        cursor: pointer;
    }
    
    .filter-tag:hover {
        background: rgba(var(--primary-rgb), 0.2);
    }
    
    .filter-tag i {
        font-size: 0.75rem;
    }

    /* Category Radio Buttons */
    .category-radio {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        margin: 0.25rem -0.75rem;
        border-radius: var(--border-radius-md);
        cursor: pointer;
        transition: var(--transition-default);
        font-family: var(--font-secondary);
    }

    .category-radio:hover {
        background-color: rgba(var(--primary-rgb), 0.05);
        transform: translateX(5px);
    }

    .category-radio input[type="radio"] {
        margin-right: 0.75rem;
        accent-color: var(--primary);
        cursor: pointer;
    }
    
    .category-list {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    /* Price Range Inputs */
    .price-range {
        display: flex;
        gap: 1rem;
        align-items: center;
        margin-top: 1rem;
    }

    .price-input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: var(--border-radius-md);
        font-size: 0.9rem;
        transition: var(--transition-default);
        font-family: var(--font-secondary);
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .price-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.2);
        outline: none;
    }
    
    .price-input:hover {
        border-color: var(--primary);
    }

    /* Sort Dropdown */
    .sort-select {
        padding: 0.75rem 1rem;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: var(--border-radius-md);
        background: white;
        min-width: 200px;
        height: 3.25rem;
        font-family: var(--font-secondary);
        transition: var(--transition-default);
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%233E5C76' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        padding-right: 2.5rem;
    }
    
    .sort-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.2);
        outline: none;
    }
    
    .sort-select:hover {
        border-color: var(--primary);
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .services-header {
            padding: calc(3rem + 70px) 0 3rem;
        }

        .search-filters {
            margin-top: -3rem;
            padding: 1.5rem;
        }

        .filters-sidebar {
            margin-bottom: 2rem;
        }

        .services-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Dark Mode Adjustments */
    [data-bs-theme="dark"] {
        .service-card,
        .search-filters,
        .filters-sidebar {
            background: rgba(31, 32, 40, 0.8);
            border-color: rgba(255, 255, 255, 0.05);
        }

        .filter-heading {
            color: var(--light);
        }

        .category-radio:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .results-counter {
            background: rgba(255, 255, 255, 0.05);
        }

        .sort-select,
        .price-input {
            background: rgba(31, 32, 40, 0.8);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--light);
        }
    }
    </style>

    <?php include $components['navbar']; ?>

    <div class="services-header">
        <div class="services-container">
            <div class="section-header">
                <h1 class="display-4 fw-bold mb-3">Explore Services</h1>
                <p class="lead mb-0 opacity-90">Find the perfect service for your business needs</p>
            </div>
        </div>
    </div>

    <div class="services-container">
        <div class="search-filters">
            <div class="row g-4 align-items-center">
                <div class="col-12 col-md">
                    <div class="search-input-wrapper">
                        <i class="bi bi-search search-icon"></i>
                        <input 
                            type="search" 
                            class="form-control search-input" 
                            placeholder="Search for services..." 
                            value="<?php echo htmlspecialchars($searchQuery); ?>"
                            id="searchInput"
                        >
                    </div>
                </div>
                <div class="col-12 col-md-auto">
                    <select class="sort-select" id="sortSelect">
                        <option value="recommended" <?php echo $sortBy === 'recommended' ? 'selected' : ''; ?>>Recommended</option>
                        <option value="rating" <?php echo $sortBy === 'rating' ? 'selected' : ''; ?>>Top Rated</option>
                        <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Filters Sidebar -->
            <div class="col-12 col-lg-3">
                <div class="filters-sidebar">
                    <div class="filter-section">
                        <h3 class="filter-heading">Category</h3>
                        <div class="category-list">
                            <label class="category-radio">
                                <input 
                                    type="radio" 
                                    name="category" 
                                    value="all"
                                    <?php echo $selectedCategory === 'all' ? 'checked' : ''; ?>
                                >
                                All Categories
                            </label>
                            <?php foreach ($categories as $category): ?>
                            <label class="category-radio">
                                <input 
                                    type="radio" 
                                    name="category" 
                                    value="<?php echo htmlspecialchars($category['id']); ?>"
                                    <?php echo $selectedCategory === $category['id'] ? 'checked' : ''; ?>
                                >
                                <?php echo htmlspecialchars($category['name']); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="filter-section">
                        <h3 class="filter-heading">Price Range</h3>
                        <div class="price-range">
                            <input 
                                type="number" 
                                class="price-input" 
                                placeholder="Min" 
                                value="<?php echo $minPrice; ?>"
                                min="0"
                                id="minPrice"
                            >
                            <span>-</span>
                            <input 
                                type="number" 
                                class="price-input" 
                                placeholder="Max" 
                                value="<?php echo $maxPrice; ?>"
                                min="0"
                                id="maxPrice"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services Grid -->
            <div class="col-12 col-lg-9">
                <div class="results-counter mb-4">
                    <p class="mb-0">
                        <strong><?php echo count($filteredGigs); ?></strong> services available
                    </p>
                </div>
                
                <?php if ($searchQuery || $selectedCategory !== 'all' || $minPrice > 0 || $maxPrice < 1000): ?>
                <div class="filter-tags">
                    <?php if ($searchQuery): ?>
                    <span class="filter-tag" onclick="document.getElementById('searchInput').value = ''; document.getElementById('searchInput').dispatchEvent(new Event('input'))">
                        Search: <?php echo htmlspecialchars($searchQuery); ?> <i class="bi bi-x-circle"></i>
                    </span>
                    <?php endif; ?>
                    
                    <?php if ($selectedCategory !== 'all'): ?>
                    <?php foreach ($categories as $category): ?>
                        <?php if ($category['id'] === $selectedCategory): ?>
                        <span class="filter-tag" onclick="document.querySelector('input[name=\'category\'][value=\'all\']').checked = true; document.querySelector('input[name=\'category\'][value=\'all\']').dispatchEvent(new Event('change'))">
                            Category: <?php echo htmlspecialchars($category['name']); ?> <i class="bi bi-x-circle"></i>
                        </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if ($minPrice > 0): ?>
                    <span class="filter-tag" onclick="document.getElementById('minPrice').value = ''; document.getElementById('minPrice').dispatchEvent(new Event('input'))">
                        Min Price: $<?php echo $minPrice; ?> <i class="bi bi-x-circle"></i>
                    </span>
                    <?php endif; ?>
                    
                    <?php if ($maxPrice < 1000): ?>
                    <span class="filter-tag" onclick="document.getElementById('maxPrice').value = ''; document.getElementById('maxPrice').dispatchEvent(new Event('input'))">
                        Max Price: $<?php echo $maxPrice; ?> <i class="bi bi-x-circle"></i>
                    </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="services-grid">
                    <?php foreach ($filteredGigs as $gig): ?>
                    <a href="service-detail.php?id=<?php echo $gig['id']; ?>" class="service-card">
                        <div class="service-image-container">
                            <img 
                                src="<?php echo htmlspecialchars($gig['image']); ?>" 
                                alt="<?php echo htmlspecialchars($gig['title']); ?>" 
                                class="service-image"
                                loading="lazy"
                            >
                            <?php if ($gig['featured']): ?>
                            <span class="featured-badge">Featured</span>
                            <?php endif; ?>
                        </div>
                        <div class="service-content">
                            <div class="service-provider">
                                <img 
                                    src="<?php echo htmlspecialchars($gig['freelancer']['avatar']); ?>" 
                                    alt="<?php echo htmlspecialchars($gig['freelancer']['name']); ?>" 
                                    class="provider-avatar"
                                >
                                <div class="provider-info">
                                    <p class="provider-name"><?php echo htmlspecialchars($gig['freelancer']['name']); ?></p>
                                    <p class="provider-level"><?php echo htmlspecialchars($gig['freelancer']['level']); ?></p>
                                </div>
                            </div>
                            <h3 class="service-title"><?php echo htmlspecialchars($gig['title']); ?></h3>
                            <div class="service-rating">
                                <i class="bi bi-star-fill star-icon"></i>
                                <span><?php echo number_format($gig['rating'], 1); ?></span>
                                <span class="rating-count">(<?php echo $gig['ratingCount']; ?>)</span>
                            </div>
                            <div class="service-footer">
                                <span class="price-label">Starting at</span>
                                <div class="service-price">$<?php echo number_format($gig['price']); ?></div>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include $components['footer']; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const sortSelect = document.getElementById('sortSelect');
        const categoryInputs = document.querySelectorAll('input[name="category"]');
        const minPriceInput = document.getElementById('minPrice');
        const maxPriceInput = document.getElementById('maxPrice');
        const serviceCards = document.querySelectorAll('.service-card');
        const filterTags = document.querySelectorAll('.filter-tag');
        
        // Add stagger animation to service cards
        serviceCards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        // Add hover effect to service cards
        serviceCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
                this.style.boxShadow = '0 20px 30px rgba(0,0,0,0.15)';
                const image = this.querySelector('.service-image');
                if (image) image.style.transform = 'scale(1.05)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.05)';
                const image = this.querySelector('.service-image');
                if (image) image.style.transform = 'scale(1)';
            });
        });
        
        const updateURL = () => {
            const params = new URLSearchParams(window.location.search);
            
            if (searchInput.value) params.set('search', searchInput.value);
            else params.delete('search');
            
            if (sortSelect.value !== 'recommended') params.set('sort', sortSelect.value);
            else params.delete('sort');
            
            const selectedCategory = document.querySelector('input[name="category"]:checked').value;
            if (selectedCategory !== 'all') params.set('category', selectedCategory);
            else params.delete('category');
            
            if (minPriceInput.value) params.set('min_price', minPriceInput.value);
            else params.delete('min_price');
            
            if (maxPriceInput.value) params.set('max_price', maxPriceInput.value);
            else params.delete('max_price');
            
            window.location.search = params.toString();
        };

        // Debounce function
        const debounce = (func, wait) => {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        };

        // Add event listeners with debouncing for text inputs
        searchInput.addEventListener('input', debounce(updateURL, 500));
        minPriceInput.addEventListener('input', debounce(updateURL, 500));
        maxPriceInput.addEventListener('input', debounce(updateURL, 500));

        // Immediate update for select and radio inputs
        sortSelect.addEventListener('change', updateURL);
        categoryInputs.forEach(input => {
            input.addEventListener('change', updateURL);
        });
        
        // Add animation to filter tags
        filterTags.forEach(tag => {
            tag.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
            });
            
            tag.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });
        
        // Intersection Observer for scroll animations
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.filters-sidebar, .results-counter, .filter-tags').forEach(el => {
                observer.observe(el);
            });
        }
    });
    </script>
</body>
</html>