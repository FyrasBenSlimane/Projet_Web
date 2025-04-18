<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
$conn = $GLOBALS['pdo'];

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9; // Show 9 posts per page for a better grid
$offset = ($page - 1) * $limit;

// Get total number of blogs
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM blogs WHERE status = 'published'");
$stmt->execute();
$total_blogs = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_blogs / $limit);

// Function to generate a dynamic placeholder image URL
function getPlaceholderImage($title) {
    // Generate a consistent seed from the title
    $seed = substr(md5($title), 0, 10);
    // Use DiceBear's abstract art generator with custom styling
    return "https://api.dicebear.com/6.x/shapes/svg?" . http_build_query([
        'seed' => $seed,
        'backgroundColor' => '3498db,2980b9,2c3e50',
        'size' => 800,
        'colors' => '3498db,2980b9,2c3e50,1abc9c,16a085',
        'radius' => 5,
        'colorful' => true,
        'rotate' => 0
    ]);
}

// Get blogs with author information
$stmt = $conn->prepare("
    SELECT b.*, CONCAT(u.first_name, ' ', u.last_name) as username, u.profile_image,
           (SELECT COUNT(*) FROM blog_comments WHERE blog_id = b.id AND status = 'approved') as comment_count
    FROM blogs b 
    JOIN users u ON b.user_id = u.id 
    WHERE b.status = 'published' 
    ORDER BY b.created_at DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Blog - LenSi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #3E5C76;
            --primary-rgb: 62, 92, 118;
            --secondary: #748CAB;
            --accent: #1D2D44;
            --accent-dark: #0D1B2A;
            --font-primary: 'Montserrat', sans-serif;
            --font-secondary: 'Inter', sans-serif;
            --transition-default: all 0.3s ease;
        }

        [data-bs-theme="dark"] {
            --accent: #A4C2E5;
            --accent-dark: #171821;
            --primary: #5D8BB3;
            --secondary: #8FB3DE;
        }

        .blog-section {
            padding: 4rem 0;
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.05), rgba(var(--primary-rgb), 0.1));
        }

        .blog-header {
            position: relative;
            padding: 6rem 0;
            background: linear-gradient(135deg, var(--primary), var(--accent-dark));
            margin-bottom: -2rem;
            color: white;
            overflow: hidden;
        }

        .blog-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,128C672,128,768,160,864,176C960,192,1056,192,1152,170.7C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-position: bottom;
            background-repeat: no-repeat;
            opacity: 0.6;
            z-index: 1;
        }

        .blog-header-content {
            position: relative;
            z-index: 2;
        }

        .blog-card {
            background: var(--bs-body-bg);
            border-radius: 15px;
            overflow: hidden;
            transition: var(--transition-default);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.1);
        }

        .blog-thumbnail {
            position: relative;
            padding-top: 56.25%;
            overflow: hidden;
            display: block;
        }

        .blog-thumbnail img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .blog-card:hover .blog-thumbnail img {
            transform: scale(1.1);
        }

        .blog-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .blog-title {
            color: var(--bs-heading-color);
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            line-height: 1.4;
            transition: color 0.2s ease;
        }

        .blog-card:hover .blog-title {
            color: var(--primary);
        }

        .blog-excerpt {
            color: var(--bs-secondary-color);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }

        .blog-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1rem;
            border-top: 1px solid var(--bs-border-color);
            margin-top: auto;
        }

        .blog-author {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .author-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .author-info {
            font-size: 0.9rem;
            line-height: 1.3;
        }

        .author-name {
            color: var(--bs-heading-color);
            font-weight: 500;
        }

        .post-date {
            color: var(--bs-secondary-color);
            font-size: 0.85rem;
        }

        .blog-stats {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--bs-secondary-color);
            font-size: 0.85rem;
        }

        .blog-stat {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .create-post-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition-default);
            backdrop-filter: blur(8px);
        }

        .create-post-btn:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-2px);
        }

        .page-link {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin: 0 0.2rem;
            color: var(--bs-body-color);
            border: none;
            font-weight: 500;
            background: transparent;
        }

        .page-link:hover {
            background: var(--primary);
            color: white;
        }

        .page-item.active .page-link {
            background: var(--primary);
            color: white;
        }

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

        .blog-card {
            animation: fadeInUp 0.6s ease backwards;
        }

        .blog-card:nth-child(3n+1) { animation-delay: 0.1s; }
        .blog-card:nth-child(3n+2) { animation-delay: 0.2s; }
        .blog-card:nth-child(3n+3) { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <?php include_once '../navbar.php'; ?>

    <div class="blog-header">
        <div class="container blog-header-content">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">Community Blog</h1>
                    <p class="lead mb-0 opacity-90">Discover insights, share knowledge, and connect with fellow freelancers</p>
                </div>
                <?php if(isset($_SESSION['user_id'])): ?>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <a href="create.php" class="create-post-btn">
                        <i class="bi bi-plus-circle-fill me-2"></i>Create New Post
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <section class="blog-section">
        <div class="container">
            <div class="row g-4">
                <?php foreach($blogs as $blog): ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="blog-card">
                            <a href="view.php?id=<?php echo $blog['id']; ?>" class="blog-thumbnail">
                                <img src="<?php echo $blog['thumbnail_url'] ?? getPlaceholderImage($blog['title']); ?>" 
                                     alt="<?php echo htmlspecialchars($blog['title']); ?>"
                                     onerror="this.src='<?php echo getPlaceholderImage($blog['title']); ?>'">
                            </a>
                            <div class="blog-content">
                                <a href="view.php?id=<?php echo $blog['id']; ?>" class="text-decoration-none">
                                    <h2 class="blog-title"><?php echo htmlspecialchars($blog['title']); ?></h2>
                                </a>
                                <p class="blog-excerpt">
                                    <?php 
                                    $preview = strip_tags($blog['content']);
                                    echo strlen($preview) > 120 ? substr($preview, 0, 120) . '...' : $preview;
                                    ?>
                                </p>
                                <div class="blog-meta">
                                    <div class="blog-author">
                                        <img src="<?php echo $blog['profile_image'] ?? '/web/assets/images/default-avatar.png'; ?>" 
                                             class="author-avatar" 
                                             alt="<?php echo htmlspecialchars($blog['username']); ?>">
                                        <div class="author-info">
                                            <div class="author-name"><?php echo htmlspecialchars($blog['username']); ?></div>
                                            <div class="post-date"><?php echo date('M j, Y', strtotime($blog['created_at'])); ?></div>
                                        </div>
                                    </div>
                                    <div class="blog-stats">
                                        <span class="blog-stat" title="Views">
                                            <i class="bi bi-eye"></i>
                                            <?php echo $blog['views']; ?>
                                        </span>
                                        <span class="blog-stat" title="Comments">
                                            <i class="bi bi-chat"></i>
                                            <?php echo $blog['comment_count']; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if($total_pages > 1): ?>
            <nav aria-label="Blog pagination" class="mt-5">
                <ul class="pagination justify-content-center">
                    <?php if($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>" aria-label="Previous">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>" aria-label="Next">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>