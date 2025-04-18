<?php
require_once __DIR__ . '/../../../config/database.php';

// Initialize message variable
$message = '';
$error = '';

function validateJobOffer($data) {
    $errors = [];
    
    // Title validation
    if (empty($data['title'])) {
        $errors['title'] = "Title is required";
    } elseif (strlen($data['title']) < 3 || strlen($data['title']) > 100) {
        $errors['title'] = "Title must be between 3 and 100 characters";
    }
    
    // Description validation
    if (empty($data['description'])) {
        $errors['description'] = "Description is required";
    } elseif (strlen($data['description']) < 10) {
        $errors['description'] = "Description must be at least 10 characters";
    }
    
    // Category validation
    if (empty($data['category'])) {
        $errors['category'] = "Category is required";
    }
    
    // Salary validation
    if (empty($data['salary_min']) || empty($data['salary_max'])) {
        $errors['salary'] = "Both minimum and maximum salary are required";
    } elseif ($data['salary_min'] > $data['salary_max']) {
        $errors['salary'] = "Minimum salary cannot be greater than maximum salary";
    } elseif ($data['salary_min'] < 0 || $data['salary_max'] < 0) {
        $errors['salary'] = "Salary cannot be negative";
    }
    
    // Location validation
    if (empty($data['location'])) {
        $errors['location'] = "Location is required";
    }
    
    // Image URL validation
    if (empty($data['image_url'])) {
        $errors['image_url'] = "Image URL is required";
    } elseif (!filter_var($data['image_url'], FILTER_VALIDATE_URL)) {
        $errors['image_url'] = "Please enter a valid URL";
    }
    
    return $errors;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
            case 'update':
                $validation_errors = validateJobOffer($_POST);
                
                if (empty($validation_errors)) {
                    $title = $_POST['title'];
                    $description = $_POST['description'];
                    $category = $_POST['category'];
                    $salary_min = $_POST['salary_min'];
                    $salary_max = $_POST['salary_max'];
                    $location = $_POST['location'];
                    $image_url = $_POST['image_url'];
                    
                    // Generate slug from title
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
                    
                    if ($_POST['action'] === 'create') {
                        $sql = "INSERT INTO job_offers (title, description, category_id, salary_min, salary_max, location_id, image_url, slug) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $pdo->prepare($sql);
                        if ($stmt->execute([$title, $description, $category, $salary_min, $salary_max, $location, $image_url, $slug])) {
                            $message = "Job offer created successfully!";
                        } else {
                            $error = "Error creating job offer";
                        }
                    } else {
                        $id = $_POST['job_id'];
                        $sql = "UPDATE job_offers SET title=?, description=?, category_id=?, salary_min=?, salary_max=?, location_id=?, image_url=?, slug=? WHERE job_id=?";
                        $stmt = $pdo->prepare($sql);
                        if ($stmt->execute([$title, $description, $category, $salary_min, $salary_max, $location, $image_url, $slug, $id])) {
                            $message = "Job offer updated successfully!";
                        } else {
                            $error = "Error updating job offer";
                        }
                    }
                } else {
                    $error = implode("<br>", $validation_errors);
                }
                break;

            case 'delete':
                $id = $_POST['job_id'];
                if (!empty($id)) {
                    $sql = "DELETE FROM job_offers WHERE job_id = ?";
                    $stmt = $pdo->prepare($sql);
                    if ($stmt->execute([$id])) {
                        $message = "Job offer deleted successfully!";
                    } else {
                        $error = "Error deleting job offer";
                    }
                }
                break;
        }
    }
}

// Fetch all job offers
$sql = "SELECT jo.*, jc.name as category_name, l.city, l.country, l.is_remote,
        (SELECT COUNT(*) FROM job_applications WHERE job_id = jo.job_id) as applicant_count
        FROM job_offers jo
        LEFT JOIN job_categories jc ON jo.category_id = jc.category_id
        LEFT JOIN locations l ON jo.location_id = l.location_id
        ORDER BY jo.created_at DESC";
$stmt = $pdo->query($sql);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories for dropdown
$sql = "SELECT * FROM job_categories";
$stmt = $pdo->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch locations for dropdown
$sql = "SELECT * FROM locations";
$stmt = $pdo->query($sql);
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Offers Management - LenSi</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #5D8BB3;
            --accent-color: #8FB3DE;
            --background-light: #F7F8FA;
            --background-dark: #121518;
            --text-dark: #1D2D44;
            --text-light: #A4C2E5;
        }

        body {
            background-color: var(--background-light);
            color: var(--text-dark);
            min-height: 100vh;
            padding-top: 60px;
        }

        [data-bs-theme="dark"] body {
            background-color: var(--background-dark);
            color: var(--text-light);
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .job-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        [data-bs-theme="dark"] .job-card {
            background: rgba(31,32,40,0.8);
            border: 1px solid rgba(70,90,120,0.2);
        }

        .job-card-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .job-card-content {
            padding: 1rem;
        }

        .job-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin: 1rem 0;
        }

        .job-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--primary-color);
        }

        .job-actions {
            padding: 1rem;
            background: rgba(0,0,0,0.02);
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        [data-bs-theme="dark"] .job-actions {
            background: rgba(255,255,255,0.02);
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, var(--accent-color), var (--primary-color));
        }

        .floating-add-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
        }

        .alert {
            margin-bottom: 2rem;
        }

        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        [data-bs-theme="dark"] .modal-content {
            background: var(--background-dark);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <?php include_once(__DIR__ . '/../navbar.php'); ?>

    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-0">Job Offers</h1>
                    <p class="mb-0">Manage and view all job opportunities</p>
                </div>
                <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addJobModal">
                    <i class="bi bi-plus-lg"></i> Add New Job
                </button>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($jobs as $job): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="job-card">
                        <div class="job-card-header">
                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($job['title']); ?></h5>
                            <span class="badge bg-primary"><?php echo htmlspecialchars($job['category_name']); ?></span>
                        </div>
                        <div class="job-card-content">
                            <p class="card-text"><?php echo htmlspecialchars(substr($job['description'], 0, 150)) . '...'; ?></p>
                            
                            <div class="job-meta">
                                <div class="job-meta-item">
                                    <i class="bi bi-cash-stack"></i>
                                    <span>$<?php echo number_format($job['salary_min']); ?> - $<?php echo number_format($job['salary_max']); ?></span>
                                </div>
                                <div class="job-meta-item">
                                    <i class="bi bi-geo-alt"></i>
                                    <span><?php echo $job['is_remote'] ? 'Remote' : htmlspecialchars($job['city'] . ', ' . $job['country']); ?></span>
                                </div>
                                <div class="job-meta-item">
                                    <i class="bi bi-people"></i>
                                    <span><?php echo $job['applicant_count']; ?> applicants</span>
                                </div>
                            </div>
                        </div>
                        <div class="job-actions">
                            <button class="btn btn-sm btn-outline-info show-job" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#showJobModal"
                                    data-job='<?php echo json_encode($job); ?>'>
                                <i class="bi bi-eye"></i> Show
                            </button>
                            <button class="btn btn-sm btn-outline-primary edit-job" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editJobModal"
                                    data-job='<?php echo json_encode($job); ?>'>
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <form action="" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this job?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Show Job Modal -->
    <div class="modal fade" id="showJobModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Job Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <h3 id="show_title" class="mb-3"></h3>
                        <span id="show_category" class="badge bg-primary mb-3 d-inline-block"></span>
                    </div>
                    
                    <div class="mb-4">
                        <h5 class="text-muted mb-3">Description</h5>
                        <p id="show_description" class="mb-4"></p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Salary Range</h5>
                            <p id="show_salary" class="mb-0"></p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Location</h5>
                            <p id="show_location" class="mb-0"></p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5 class="text-muted mb-3">Statistics</h5>
                        <p id="show_applicants" class="mb-0"></p>
                    </div>

                    <div class="text-center mt-4">
                        <img id="show_image" class="img-fluid rounded" style="max-height: 300px;" alt="Job image">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Job Modal -->
    <div class="modal fade" id="addJobModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label for="title" class="form-label">Job Title</label>
                            <input type="text" class="form-control" id="title" name="title">
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category">
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="salary_min" class="form-label">Minimum Salary</label>
                                    <input type="number" class="form-control" id="salary_min" name="salary_min">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="salary_max" class="form-label">Maximum Salary</label>
                                    <input type="number" class="form-control" id="salary_max" name="salary_max">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <select class="form-control" id="location" name="location">
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?php echo $location['location_id']; ?>">
                                        <?php echo $location['is_remote'] ? 'Remote' : htmlspecialchars($location['city'] . ', ' . $location['country']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="image_url" class="form-label">Image URL</label>
                            <input type="url" class="form-control" id="image_url" name="image_url">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Job</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Job Modal -->
    <div class="modal fade" id="editJobModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="job_id" id="edit_job_id">
                        <div class="mb-3">
                            <label for="edit_title" class="form-label">Job Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title">
                        </div>
                        <div class="mb-3">
                            <label for="edit_category" class="form-label">Category</label>
                            <select class="form-control" id="edit_category" name="category">
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_salary_min" class="form-label">Minimum Salary</label>
                                    <input type="number" class="form-control" id="edit_salary_min" name="salary_min">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_salary_max" class="form-label">Maximum Salary</label>
                                    <input type="number" class="form-control" id="edit_salary_max" name="salary_max">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_location" class="form-label">Location</label>
                            <select class="form-control" id="edit_location" name="location">
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?php echo $location['location_id']; ?>">
                                        <?php echo $location['is_remote'] ? 'Remote' : htmlspecialchars($location['city'] . ', ' . $location['country']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_image_url" class="form-label">Image URL</label>
                            <input type="url" class="form-control" id="edit_image_url" name="image_url">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Job</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        function validateForm(form) {
            const formData = new FormData(form);
            const errors = [];
            
            // Title validation
            const title = formData.get('title');
            if (!title) {
                errors.push('Title is required');
            } else if (title.length < 3 || title.length > 100) {
                errors.push('Title must be between 3 and 100 characters');
            }
            
            // Description validation
            const description = formData.get('description');
            if (!description) {
                errors.push('Description is required');
            } else if (description.length < 10) {
                errors.push('Description must be at least 10 characters');
            }
            
            // Category validation
            if (!formData.get('category')) {
                errors.push('Category is required');
            }
            
            // Salary validation
            const salaryMin = parseInt(formData.get('salary_min'));
            const salaryMax = parseInt(formData.get('salary_max'));
            
            if (!salaryMin || !salaryMax) {
                errors.push('Both minimum and maximum salary are required');
            } else if (salaryMin > salaryMax) {
                errors.push('Minimum salary cannot be greater than maximum salary');
            } else if (salaryMin < 0 || salaryMax < 0) {
                errors.push('Salary cannot be negative');
            }
            
            // Location validation
            if (!formData.get('location')) {
                errors.push('Location is required');
            }
            
            // Image URL validation
            const imageUrl = formData.get('image_url');
            if (!imageUrl) {
                errors.push('Image URL is required');
            } else {
                try {
                    new URL(imageUrl);
                } catch {
                    errors.push('Please enter a valid URL');
                }
            }
            
            return errors;
        }

        // Handle form submissions
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (form.querySelector('input[name="action"]').value !== 'delete') {
                    const errors = validateForm(this);
                    if (errors.length > 0) {
                        e.preventDefault();
                        const errorMessage = errors.join('<br>');
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                        alertDiv.innerHTML = `
                            ${errorMessage}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                        this.insertBefore(alertDiv, this.firstChild);
                        
                        // Auto-hide the error after 5 seconds
                        setTimeout(() => {
                            const bsAlert = new bootstrap.Alert(alertDiv);
                            bsAlert.close();
                        }, 5000);
                    }
                }
            });
        });

        // Edit job functionality
        document.querySelectorAll('.edit-job').forEach(button => {
            button.addEventListener('click', function() {
                const job = JSON.parse(this.dataset.job);
                document.getElementById('edit_job_id').value = job.job_id;
                document.getElementById('edit_title').value = job.title;
                document.getElementById('edit_category').value = job.category_id;
                document.getElementById('edit_description').value = job.description;
                document.getElementById('edit_salary_min').value = job.salary_min;
                document.getElementById('edit_salary_max').value = job.salary_max;
                document.getElementById('edit_location').value = job.location_id;
                document.getElementById('edit_image_url').value = job.image_url;
            });
        });

        // Show job functionality
        document.querySelectorAll('.show-job').forEach(button => {
            button.addEventListener('click', function() {
                const job = JSON.parse(this.dataset.job);
                document.getElementById('show_title').textContent = job.title;
                document.getElementById('show_category').textContent = job.category_name;
                document.getElementById('show_description').textContent = job.description;
                document.getElementById('show_salary').textContent = `$${Number(job.salary_min).toLocaleString()} - $${Number(job.salary_max).toLocaleString()}`;
                document.getElementById('show_location').textContent = job.is_remote ? 'Remote' : `${job.city}, ${job.country}`;
                document.getElementById('show_applicants').textContent = `${job.applicant_count} applicants`;
                document.getElementById('show_image').src = job.image_url;
            });
        });

        // Real-time validation feedback
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                const formData = new FormData();
                formData.append(this.name, this.value);
                
                let error = '';
                switch(this.name) {
                    case 'title':
                        if (this.value.length > 0 && (this.value.length < 3 || this.value.length > 100)) {
                            error = 'Title must be between 3 and 100 characters';
                        }
                        break;
                    case 'description':
                        if (this.value.length > 0 && this.value.length < 10) {
                            error = 'Description must be at least 10 characters';
                        }
                        break;
                    case 'salary_min':
                    case 'salary_max':
                        const salaryMin = parseInt(document.querySelector('[name="salary_min"]').value) || 0;
                        const salaryMax = parseInt(document.querySelector('[name="salary_max"]').value) || 0;
                        if (salaryMin > salaryMax && salaryMax !== 0) {
                            error = 'Minimum salary cannot be greater than maximum salary';
                        } else if (salaryMin < 0 || salaryMax < 0) {
                            error = 'Salary cannot be negative';
                        }
                        break;
                    case 'image_url':
                        if (this.value) {
                            try {
                                new URL(this.value);
                            } catch {
                                error = 'Please enter a valid URL';
                            }
                        }
                        break;
                }
                
                // Show or clear error feedback
                const feedback = this.nextElementSibling;
                if (error) {
                    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        errorDiv.textContent = error;
                        this.parentNode.appendChild(errorDiv);
                        this.classList.add('is-invalid');
                    } else {
                        feedback.textContent = error;
                    }
                } else {
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.remove();
                    }
                    this.classList.remove('is-invalid');
                }
            });
        });

        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
    </script>
</body>
</html>