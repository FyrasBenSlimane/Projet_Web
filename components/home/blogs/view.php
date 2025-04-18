<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
$conn = $GLOBALS['pdo'];

// Function to generate a better placeholder image URL using DiceBear
function getPlaceholderImage($title) {
    $seed = substr(md5($title), 0, 10);
    return "https://api.dicebear.com/6.x/shapes/svg?" . http_build_query([
        'seed' => $seed,
        'backgroundColor' => '3498db,2980b9,2c3e50',
        'size' => 1200,
        'colors' => '3498db,2980b9,2c3e50,1abc9c,16a085',
        'radius' => 5,
        'colorful' => true,
        'rotate' => 0
    ]);
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$blog_id = (int)$_GET['id'];

// Fetch blog post with author information
$stmt = $conn->prepare("
    SELECT b.*, CONCAT(u.first_name, ' ', u.last_name) as username, u.profile_image,
           (SELECT COUNT(*) FROM blog_comments WHERE blog_id = b.id AND status = 'approved') as comment_count 
    FROM blogs b 
    JOIN users u ON b.user_id = u.id 
    WHERE b.id = :id AND b.status = 'published'
");
$stmt->bindParam(':id', $blog_id, PDO::PARAM_INT);
$stmt->execute();
$blog = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$blog) {
    header('Location: index.php');
    exit();
}

// Update view count
$stmt = $conn->prepare("UPDATE blogs SET views = views + 1 WHERE id = :id");
$stmt->bindParam(':id', $blog_id, PDO::PARAM_INT);
$stmt->execute();

// Fetch comments
$stmt = $conn->prepare("
    SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as username, u.profile_image 
    FROM blog_comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.blog_id = :blog_id AND c.status = 'approved' 
    ORDER BY c.created_at DESC
");
$stmt->bindParam(':blog_id', $blog_id, PDO::PARAM_INT);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user'])) {
    if (isset($_POST['comment']) && !empty(trim($_POST['comment']))) {
        // Get user ID from email since that's what we store in the session
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $_SESSION['user']['email'], PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch();
        
        if ($user) {
            $stmt = $conn->prepare("
                INSERT INTO blog_comments (blog_id, user_id, content, status) 
                VALUES (:blog_id, :user_id, :content, 'approved')
            ");
            $stmt->bindParam(':blog_id', $blog_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
            $stmt->bindParam(':content', $_POST['comment'], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                header("Location: view.php?id=$blog_id#comments");
                exit();
            }
        }
    }
}

// Handle comment deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment']) && isset($_SESSION['user'])) {
    $comment_id = (int)$_POST['delete_comment'];
    
    // Only allow deletion if the comment belongs to the current user
    $stmt = $conn->prepare("
        DELETE FROM blog_comments 
        WHERE id = :comment_id 
        AND user_id = (SELECT id FROM users WHERE email = :user_email)
    ");
    $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_email', $_SESSION['user']['email'], PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        header("Location: view.php?id=$blog_id#comments");
        exit();
    }
}

// Handle comment editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_comment']) && isset($_SESSION['user'])) {
    $comment_id = (int)$_POST['comment_id'];
    $comment_content = trim($_POST['edit_comment']);
    
    if (!empty($comment_content)) {
        // Only allow editing if the comment belongs to the current user
        $stmt = $conn->prepare("
            UPDATE blog_comments 
            SET content = :content 
            WHERE id = :comment_id 
            AND user_id = (SELECT id FROM users WHERE email = :user_email)
        ");
        $stmt->bindParam(':content', $comment_content, PDO::PARAM_STR);
        $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_email', $_SESSION['user']['email'], PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            header("Location: view.php?id=$blog_id#comment-" . $comment_id);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($blog['title']); ?> - LenSi Community</title>
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

        .blog-header {
            position: relative;
            background: linear-gradient(135deg, var(--primary), var(--accent-dark));
            padding: 6rem 0;
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

        .back-to-blogs {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
            opacity: 0.9;
            transition: var(--transition-default);
        }

        .back-to-blogs:hover {
            opacity: 1;
            transform: translateX(-5px);
            color: white;
        }

        .blog-title {
            font-size: 3rem;
            font-weight: 700;
            line-height: 1.2;
            margin: 1.5rem 0;
            color: white;
        }

        .blog-meta {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-top: 2rem;
        }

        .author-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.2);
        }

        .author-name {
            font-weight: 600;
            color: white;
            margin-bottom: 0.2rem;
        }

        .post-date {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.8);
        }

        .blog-stats {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 0.5rem 1.25rem;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            backdrop-filter: blur(8px);
        }

        .blog-stat {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            font-size: 0.9rem;
        }

        .blog-content-wrapper {
            padding: 4rem 0;
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.05), rgba(var(--primary-rgb), 0.1));
        }

        .blog-featured-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }

        .blog-content {
            background: var(--bs-body-bg);
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .blog-content-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--bs-body-color);
        }

        .comment-section {
            background: var(--bs-body-bg);
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .comment-form textarea {
            border: 1px solid var(--bs-border-color);
            border-radius: 12px;
            padding: 1rem;
            resize: vertical;
            min-height: 120px;
            transition: var(--transition-default);
            background: var(--bs-body-bg);
            color: var(--bs-body-color);
        }

        .comment-form textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        .comment-form .char-count {
            font-size: 0.85rem;
            color: var(--bs-secondary-color);
            text-align: right;
            margin-top: 0.5rem;
        }

        .comment-form .char-count.near-limit {
            color: #ffc107;
        }

        .comment-form .char-count.at-limit {
            color: #dc3545;
        }

        .btn-post-comment {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition-default);
            position: relative;
            overflow: hidden;
        }

        .btn-post-comment:hover {
            background: var(--accent-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .btn-post-comment:disabled {
            background: var(--bs-secondary);
            transform: none;
            cursor: not-allowed;
        }

        .btn-post-comment .spinner-border {
            display: none;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 1.2rem;
            height: 1.2rem;
        }

        .btn-post-comment.loading {
            color: transparent;
        }

        .btn-post-comment.loading .spinner-border {
            display: block;
        }

        .comment-item {
            padding: 1.5rem;
            border-radius: 12px;
            background: rgba(var(--primary-rgb), 0.05);
            margin-bottom: 1rem;
            transition: var(--transition-default);
            border: 1px solid transparent;
        }

        .comment-item:hover {
            background: rgba(var(--primary-rgb), 0.08);
            border-color: rgba(var(--primary-rgb), 0.1);
            transform: translateX(5px);
        }

        .comment-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            transition: var(--transition-default);
        }

        .comment-item:hover .comment-avatar {
            transform: scale(1.1);
        }

        .comment-meta {
            font-size: 0.85rem;
            color: var(--bs-secondary-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-delete-comment {
            background: none;
            border: none;
            color: var(--bs-danger);
            padding: 0;
            opacity: 0.7;
            transition: var(--transition-default);
        }

        .btn-delete-comment:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        .comment-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-edit-comment {
            background: none;
            border: none;
            color: var(--primary);
            padding: 0;
            opacity: 0.7;
            transition: var(--transition-default);
        }

        .btn-edit-comment:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        .comment-edit-form {
            display: none;
            margin-top: 1rem;
        }

        .comment-edit-form.active {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }

        .comment-edit-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .btn-cancel-edit {
            background: var(--bs-secondary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: var(--transition-default);
        }

        .btn-cancel-edit:hover {
            opacity: 0.9;
        }

        .btn-save-edit {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: var(--transition-default);
        }

        .btn-save-edit:hover {
            background: var(--accent-dark);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .blog-content { animation: fadeIn 0.6s ease forwards; }
        .comment-section { animation: fadeIn 0.6s ease 0.2s forwards; opacity: 0; }
    </style>
</head>
<body>
    <?php include_once '../navbar.php'; ?>

    <div class="blog-header">
        <div class="container blog-header-content">
            <a href="index.php" class="back-to-blogs">
                <i class="bi bi-arrow-left"></i> Back to Blogs
            </a>
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="blog-title"><?php echo htmlspecialchars($blog['title']); ?></h1>
                    <div class="blog-meta">
                        <div class="author-info">
                            <img src="<?php echo $blog['profile_image'] ?? '/web/assets/images/default-avatar.png'; ?>" 
                                 class="author-avatar" alt="Author">
                            <div>
                                <div class="author-name"><?php echo htmlspecialchars($blog['username']); ?></div>
                                <div class="post-date"><?php echo date('F j, Y', strtotime($blog['created_at'])); ?></div>
                            </div>
                        </div>
                        <div class="blog-stats">
                            <span class="blog-stat">
                                <i class="bi bi-eye"></i>
                                <span><?php echo $blog['views']; ?> views</span>
                            </span>
                            <span class="blog-stat">
                                <i class="bi bi-chat"></i>
                                <span><?php echo $blog['comment_count']; ?> comments</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="blog-content-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <img src="<?php echo $blog['thumbnail_url'] ?? getPlaceholderImage($blog['title']); ?>" 
                         class="blog-featured-image" 
                         alt="<?php echo htmlspecialchars($blog['title']); ?>"
                         onerror="this.src='<?php echo getPlaceholderImage($blog['title']); ?>'">

                    <article class="blog-content">
                        <div class="blog-content-text">
                            <?php echo nl2br(htmlspecialchars($blog['content'])); ?>
                        </div>
                    </article>

                    <section class="comment-section" id="comments">
                        <h3 class="mb-4">Comments (<?php echo count($comments); ?>)</h3>

                        <?php if(isset($_SESSION['user'])): ?>
                            <form action="" method="POST" class="comment-form mb-4" id="commentForm">
                                <div class="mb-3">
                                    <textarea name="comment" class="form-control" 
                                              placeholder="Share your thoughts..." required
                                              maxlength="1000" data-remaining="1000"></textarea>
                                    <div class="char-count">1000 characters remaining</div>
                                </div>
                                <button type="submit" class="btn-post-comment">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    <span class="button-text"><i class="bi bi-send me-2"></i>Post Comment</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle"></i>
                                Please <a href="/web/components/login/login.php" class="alert-link mx-1">login</a> to join the discussion.
                            </div>
                        <?php endif; ?>

                        <div class="comments-list">
                            <?php if(empty($comments)): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-chat-dots display-4 mb-3 d-block"></i>
                                    <p class="mb-0">Be the first to share your thoughts!</p>
                                </div>
                            <?php else: ?>
                                <?php 
                                // Get the current user's ID if they're logged in
                                $current_user_id = null;
                                if (isset($_SESSION['user'])) {
                                    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
                                    $stmt->bindParam(':email', $_SESSION['user']['email'], PDO::PARAM_STR);
                                    $stmt->execute();
                                    $current_user = $stmt->fetch();
                                    if ($current_user) {
                                        $current_user_id = (int)$current_user['id'];
                                    }
                                }
                                ?>
                                <?php foreach($comments as $comment): ?>
                                    <div class="comment-item">
                                        <div class="d-flex">
                                            <img src="<?php echo $comment['profile_image'] ?? '/web/assets/images/default-avatar.png'; ?>" 
                                                 class="comment-avatar me-3" alt="<?php echo htmlspecialchars($comment['username']); ?>">
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($comment['username']); ?></h6>
                                                    <div class="comment-actions">
                                                        <span class="comment-meta">
                                                            <i class="bi bi-clock me-1"></i>
                                                            <?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?>
                                                        </span>
                                                        <?php if(isset($_SESSION['user']) && $current_user_id === (int)$comment['user_id']): ?>
                                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this comment?');">
                                                                <input type="hidden" name="delete_comment" value="<?php echo $comment['id']; ?>">
                                                                <button type="submit" class="btn-delete-comment" title="Delete comment">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                            <button class="btn-edit-comment" title="Edit comment" onclick="toggleEditForm(<?php echo $comment['id']; ?>)">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                                <form method="POST" class="comment-edit-form" id="editForm-<?php echo $comment['id']; ?>">
                                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                    <textarea name="edit_comment" class="form-control mb-2"><?php echo htmlspecialchars($comment['content']); ?></textarea>
                                                    <div class="comment-edit-actions">
                                                        <button type="button" class="btn-cancel-edit" onclick="toggleEditForm(<?php echo $comment['id']; ?>)">Cancel</button>
                                                        <button type="submit" class="btn-save-edit">Save</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const commentForm = document.getElementById('commentForm');
                        if (!commentForm) return;

                        const textarea = commentForm.querySelector('textarea');
                        const charCount = commentForm.querySelector('.char-count');
                        const submitBtn = commentForm.querySelector('button[type="submit"]');
                        const maxLength = parseInt(textarea.getAttribute('maxlength'));

                        // Update character count
                        textarea.addEventListener('input', function() {
                            const remaining = maxLength - this.value.length;
                            charCount.textContent = `${remaining} characters remaining`;
                            
                            // Update visual feedback
                            charCount.classList.remove('near-limit', 'at-limit');
                            if (remaining <= 50) {
                                charCount.classList.add('at-limit');
                            } else if (remaining <= 100) {
                                charCount.classList.add('near-limit');
                            }

                            // Enable/disable submit button
                            submitBtn.disabled = this.value.trim().length === 0;
                        });

                        // Handle form submission
                        commentForm.addEventListener('submit', function(e) {
                            const button = this.querySelector('.btn-post-comment');
                            button.classList.add('loading');
                        });

                        // Auto-resize textarea
                        textarea.addEventListener('input', function() {
                            this.style.height = 'auto';
                            this.style.height = (this.scrollHeight) + 'px';
                        });
                    });

                    function toggleEditForm(commentId) {
                        const editForm = document.getElementById(`editForm-${commentId}`);
                        if (editForm) {
                            editForm.classList.toggle('active');
                        }
                    }
                    </script>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>