<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
$conn = $GLOBALS['pdo'];

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /web/components/login/login.php');
    exit();
}

// Create blog-thumbnails directory if it doesn't exist
$upload_dir = __DIR__ . '/../../../assets/images/blog-thumbnails/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $thumbnail_url = null;

    $errors = [];
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    if (empty($content)) {
        $errors[] = "Content is required";
    }

    // Handle image upload if provided
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['thumbnail']['type'], $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG and GIF are allowed.";
        } elseif ($_FILES['thumbnail']['size'] > $max_size) {
            $errors[] = "File is too large. Maximum size is 5MB.";
        } else {
            $upload_dir = __DIR__ . '/../../../assets/images/blog-thumbnails/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $filename = uniqid() . '_' . basename($_FILES['thumbnail']['name']);
            $upload_path = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $upload_path)) {
                $thumbnail_url = '/web/assets/images/blog-thumbnails/' . $filename;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO blogs (title, content, user_id, thumbnail_url) 
            VALUES (:title, :content, :user_id, :thumbnail_url)
        ");

        try {
            $stmt->execute([
                ':title' => $title,
                ':content' => $content,
                ':user_id' => $_SESSION['user_id'],
                ':thumbnail_url' => $thumbnail_url
            ]);
            
            header('Location: index.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Error creating blog post. Please try again.";
        }
    }
}

// Function to generate a dynamic placeholder image URL for preview
function getPlaceholderImage($title) {
    $seed = substr(md5($title), 0, 10);
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
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Blog Post - LenSi Community</title>
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

        .create-header {
            position: relative;
            background: linear-gradient(135deg, var(--primary), var(--accent-dark));
            padding: 4rem 0;
            color: white;
            overflow: hidden;
            margin-bottom: 3rem;
        }

        .create-header::after {
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

        .create-header-content {
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

        .preview-container {
            background: var(--bs-body-bg);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .preview-image {
            width: 100%;
            aspect-ratio: 16/9;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: var(--transition-default);
        }

        .preview-container:hover .preview-image {
            transform: scale(1.02);
        }

        .form-control {
            border: 1px solid var(--bs-border-color);
            border-radius: 12px;
            padding: 1rem;
            background: var(--bs-body-bg);
            color: var(--bs-body-color);
            transition: var(--transition-default);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        textarea.form-control {
            min-height: 200px;
            resize: vertical;
        }

        .form-label {
            font-weight: 500;
            color: var(--bs-heading-color);
            margin-bottom: 0.5rem;
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition-default);
        }

        .btn-primary:hover {
            background: var(--accent-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid var(--bs-border-color);
            color: var(--bs-body-color);
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition-default);
        }

        .btn-secondary:hover {
            background: var(--bs-border-color);
            transform: translateY(-2px);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .preview-container {
            animation: fadeIn 0.6s ease 0.2s forwards;
            opacity: 0;
        }
    </style>
</head>
<body>
    <?php include_once '../navbar.php'; ?>

    <div class="create-header">
        <div class="container create-header-content">
            <a href="index.php" class="back-to-blogs mb-4">
                <i class="bi bi-arrow-left"></i> Back to Blogs
            </a>
            <h1 class="mt-4">Create New Blog Post</h1>
            <p class="mb-0 opacity-75">Share your knowledge and experience with the community</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row">
            <div class="col-lg-8">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data" id="blogForm">
                    <div class="mb-4">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                               placeholder="Enter your blog post title" required>
                    </div>

                    <div class="mb-4">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" 
                                  placeholder="Write your blog post content here..." required
                                  ><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="thumbnail" class="form-label">Thumbnail Image (Optional)</label>
                        <input type="file" class="form-control" id="thumbnail" name="thumbnail" 
                               accept="image/jpeg,image/png,image/gif">
                        <div class="form-text">Maximum file size: 5MB. Supported formats: JPG, PNG, GIF</div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Publish Post
                        </button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
            
            <div class="col-lg-4">
                <div class="preview-container sticky-top" style="top: 2rem;">
                    <h5 class="mb-3">Preview</h5>
                    <img id="imagePreview" class="preview-image mb-3" 
                         src="<?php echo getPlaceholderImage(''); ?>" 
                         alt="Blog thumbnail preview">
                    <h5 id="titlePreview" class="mb-2">Your Blog Title</h5>
                    <p id="contentPreview" class="text-muted">Your blog content preview will appear here...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Live preview functionality
        document.getElementById('thumbnail').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Live title preview
        document.getElementById('title').addEventListener('input', function(e) {
            const preview = document.getElementById('titlePreview');
            preview.textContent = this.value || 'Your Blog Title';
            
            // Update placeholder image with title
            if (!document.getElementById('thumbnail').files.length) {
                document.getElementById('imagePreview').src = 
                    `<?php echo getPlaceholderImage(''); ?>`.replace('seed=', 'seed=' + encodeURIComponent(this.value));
            }
        });

        // Live content preview
        document.getElementById('content').addEventListener('input', function(e) {
            const preview = document.getElementById('contentPreview');
            const content = this.value || 'Your blog content preview will appear here...';
            preview.textContent = content.length > 150 ? content.substring(0, 150) + '...' : content;
        });
    </script>
</body>
</html>