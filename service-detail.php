<?php
require_once __DIR__ . '/data/gigs.php';

// Set up basic error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the components with absolute paths
$componentsPath = __DIR__ . '/components';
$components = [
    'navbar' => $componentsPath . '/navbar.php',
    'footer' => $componentsPath . '/footer.php'
];

// Get gig ID from URL
$gigId = $_GET['id'] ?? null;

// Find the gig with the matching ID
$gig = null;
$freelancer = null;
foreach ($gigs as $g) {
    if ($g['id'] == $gigId) {
        $gig = $g;
        $freelancer = $g['freelancer'];
        break;
    }
}

// If gig is not found, prepare error state
$notFound = !$gig || !$freelancer;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $notFound ? 'Service Not Found' : htmlspecialchars($gig['title']); ?> - LenSi">
    <title><?php echo $notFound ? 'Service Not Found' : htmlspecialchars($gig['title']); ?> | LenSi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
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
    }

    .service-detail {
        padding-top: 70px;
    }

    .service-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    .service-header {
        margin-bottom: 2rem;
    }

    .service-image {
        width: 100%;
        height: auto;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .provider-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }

    .provider-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .provider-avatar {
        width: 4rem;
        height: 4rem;
        border-radius: 50%;
        object-fit: cover;
    }

    .provider-info h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
    }

    .provider-level {
        color: var(--secondary);
        font-size: 0.9rem;
    }

    .provider-rating {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .star-icon {
        color: #FFD700;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin: 1.5rem 0;
    }

    .stat-item {
        background: rgba(0,0,0,0.02);
        padding: 1rem;
        border-radius: 0.5rem;
    }

    .stat-label {
        color: var(--secondary);
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
    }

    .stat-value {
        font-weight: 600;
        font-size: 1.1rem;
    }

    .skills-container {
        margin-top: 1.5rem;
    }

    .skill-badge {
        display: inline-block;
        background: rgba(0,0,0,0.05);
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        margin: 0.25rem;
        font-size: 0.9rem;
    }

    .price-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
        position: sticky;
        top: 90px;
    }

    .price-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .price-amount {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
    }

    .delivery-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--secondary);
        margin-bottom: 1rem;
    }

    .features-list {
        list-style: none;
        padding: 0;
        margin: 2rem 0;
    }

    .features-list li {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .check-icon {
        color: #22C55E;
        flex-shrink: 0;
    }

    .nav-tabs {
        border: none;
        margin-bottom: 1.5rem;
    }

    .nav-tabs .nav-link {
        border: none;
        color: var(--secondary);
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        position: relative;
    }

    .nav-tabs .nav-link.active {
        color: var(--primary);
        background: none;
    }

    .nav-tabs .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background: var(--primary);
    }

    .review-item {
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .review-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .reviewer-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        object-fit: cover;
    }

    .reviewer-info h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
    }

    .review-rating {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .review-date {
        font-size: 0.9rem;
        color: var(--secondary);
    }

    /* Dark mode adjustments */
    [data-bs-theme="dark"] {
        .provider-card,
        .price-card {
            background: rgba(31, 32, 40, 0.8);
            border-color: rgba(255, 255, 255, 0.05);
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.05);
        }

        .skill-badge {
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-tabs .nav-link {
            color: rgba(255, 255, 255, 0.6);
        }

        .nav-tabs .nav-link.active {
            color: white;
        }
    }
    </style>
</head>
<body>
    <?php include $components['navbar']; ?>

    <main class="service-detail">
        <?php if ($notFound): ?>
        <div class="container py-8">
            <div class="text-center py-8">
                <h1 class="text-2xl font-bold mb-4">Service Not Found</h1>
                <p class="text-muted-foreground mb-6">
                    The service you're looking for doesn't exist or has been removed.
                </p>
                <a href="services.php" class="btn btn-primary">Browse Services</a>
            </div>
        </div>
        <?php else: ?>
        <div class="service-container py-5">
            <div class="row g-5">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <div class="service-header">
                        <h1 class="h2 fw-bold mb-4"><?php echo htmlspecialchars($gig['title']); ?></h1>
                        
                        <div class="d-flex align-items-center gap-4 mb-4">
                            <div class="d-flex align-items-center">
                                <img 
                                    src="<?php echo htmlspecialchars($freelancer['avatar']); ?>" 
                                    alt="<?php echo htmlspecialchars($freelancer['name']); ?>"
                                    class="rounded-circle me-3" 
                                    width="40" 
                                    height="40"
                                >
                                <div>
                                    <p class="fw-medium mb-0"><?php echo htmlspecialchars($freelancer['name']); ?></p>
                                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($freelancer['level']); ?></p>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center ms-auto">
                                <i class="bi bi-star-fill text-warning me-1"></i>
                                <span class="fw-medium me-1"><?php echo number_format($gig['rating'], 1); ?></span>
                                <span class="text-muted">(<?php echo $gig['ratingCount']; ?>)</span>
                            </div>
                        </div>
                        
                        <div class="service-image mb-5">
                            <img 
                                src="<?php echo htmlspecialchars($gig['image']); ?>" 
                                alt="<?php echo htmlspecialchars($gig['title']); ?>"
                                class="img-fluid rounded" 
                            >
                        </div>
                    </div>
                    
                    <ul class="nav nav-tabs" id="serviceTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#description" type="button">
                                Description
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#about" type="button">
                                About the Seller
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews" type="button">
                                Reviews
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="description">
                            <div class="mb-5">
                                <h2 class="h4 fw-semibold mb-4">About This Service</h2>
                                <p class="text-muted mb-4">
                                    <?php echo nl2br(htmlspecialchars($gig['description'])); ?>
                                </p>
                                
                                <h3 class="h5 fw-semibold mb-3">What's Included:</h3>
                                <ul class="features-list">
                                    <li>
                                        <i class="bi bi-check-circle-fill check-icon"></i>
                                        <span>Professional and unique design tailored to your needs</span>
                                    </li>
                                    <li>
                                        <i class="bi bi-check-circle-fill check-icon"></i>
                                        <span>Multiple revision rounds until you're completely satisfied</span>
                                    </li>
                                    <li>
                                        <i class="bi bi-check-circle-fill check-icon"></i>
                                        <span>Source files included in the final delivery</span>
                                    </li>
                                    <li>
                                        <i class="bi bi-check-circle-fill check-icon"></i>
                                        <span>Full commercial usage rights</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="about">
                            <div class="provider-card">
                                <div class="provider-header">
                                    <img 
                                        src="<?php echo htmlspecialchars($freelancer['avatar']); ?>" 
                                        alt="<?php echo htmlspecialchars($freelancer['name']); ?>"
                                        class="provider-avatar" 
                                    >
                                    <div class="provider-info">
                                        <h3><?php echo htmlspecialchars($freelancer['name']); ?></h3>
                                        <p class="provider-level mb-0"><?php echo htmlspecialchars($freelancer['level']); ?></p>
                                        <div class="provider-rating">
                                            <i class="bi bi-star-fill star-icon"></i>
                                            <span class="fw-medium"><?php echo number_format($freelancer['rating'], 1); ?></span>
                                            <span class="text-muted">(<?php echo $freelancer['ratingCount']; ?> reviews)</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="stats-grid">
                                    <div class="stat-item">
                                        <div class="stat-label">From</div>
                                        <div class="stat-value"><?php echo htmlspecialchars($freelancer['country']); ?></div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-label">Member since</div>
                                        <div class="stat-value"><?php echo htmlspecialchars($freelancer['memberSince']); ?></div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-label">Languages</div>
                                        <div class="stat-value"><?php echo htmlspecialchars(implode(', ', $freelancer['languages'])); ?></div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-label">Completed projects</div>
                                        <div class="stat-value"><?php echo htmlspecialchars($freelancer['completedProjects']); ?></div>
                                    </div>
                                </div>
                                
                                <div class="skills-container">
                                    <h4 class="h6 mb-3">Skills</h4>
                                    <?php foreach ($freelancer['skills'] as $skill): ?>
                                        <span class="skill-badge"><?php echo htmlspecialchars($skill); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="reviews">
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-4 border-bottom">
                                <div>
                                    <h2 class="h4 fw-semibold mb-2"><?php echo $gig['ratingCount']; ?> Reviews</h2>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-star-fill text-warning me-2"></i>
                                        <span class="fw-medium me-2"><?php echo number_format($gig['rating'], 1); ?></span>
                                        <span class="text-muted">overall</span>
                                    </div>
                                </div>
                                
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-hand-thumbs-up me-2"></i>
                                    Most Relevant
                                </button>
                            </div>
                            
                            <div class="reviews-list">
                                <!-- Sample reviews -->
                                <div class="review-item">
                                    <div class="review-header">
                                        <img 
                                            src="https://randomuser.me/api/portraits/men/42.jpg" 
                                            alt="Robert Williams"
                                            class="reviewer-avatar" 
                                        >
                                        <div class="reviewer-info">
                                            <h4>Robert Williams</h4>
                                            <div class="review-rating">
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                    <i class="bi bi-star-fill text-warning"></i>
                                                <?php endfor; ?>
                                                <span class="review-date ms-2">2 weeks ago</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-0">
                                        Absolutely amazing work! The delivery was on time and the quality exceeded my expectations. 
                                        I'll definitely be working with this freelancer again in the future.
                                    </p>
                                </div>
                                
                                <div class="review-item">
                                    <div class="review-header">
                                        <img 
                                            src="https://randomuser.me/api/portraits/women/23.jpg" 
                                            alt="Jennifer Lopez"
                                            class="reviewer-avatar" 
                                        >
                                        <div class="reviewer-info">
                                            <h4>Jennifer Lopez</h4>
                                            <div class="review-rating">
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                    <i class="bi bi-star-fill text-warning"></i>
                                                <?php endfor; ?>
                                                <span class="review-date ms-2">1 month ago</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-0">
                                        Great communication throughout the project. The seller was very responsive and
                                        incorporated all my feedback. Very professional service!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="price-card">
                        <div class="price-header">
                            <div class="price-amount">$<?php echo number_format($gig['price']); ?></div>
                            <span class="text-muted">per project</span>
                        </div>
                        
                        <div class="delivery-info-list mb-4">
                            <div class="delivery-info">
                                <i class="bi bi-clock"></i>
                                <span><?php echo $gig['deliveryTime']; ?> days delivery</span>
                            </div>
                            <div class="delivery-info">
                                <i class="bi bi-calendar-check"></i>
                                <span>Available now</span>
                            </div>
                            <div class="delivery-info">
                                <i class="bi bi-award"></i>
                                <span><?php echo htmlspecialchars($freelancer['level']); ?></span>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary">
                                Order Now
                            </button>
                            <button class="btn btn-outline-primary">
                                <i class="bi bi-chat-square-text me-2"></i>
                                Contact Seller
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <?php include $components['footer']; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>