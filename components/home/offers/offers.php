<?php
require_once __DIR__ . '/../../../components/Dashboard/controllers/JobOffersController.php';

// Initialize controller
$jobOffersController = new JobOffersController();

// Initialize message variable
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                if ($jobOffersController->createJob($_POST)) {
                    $message = "Job offer created successfully!";
                }
                break;

            case 'update':
                if ($jobOffersController->updateJob($_POST)) {
                    $message = "Job offer updated successfully!";
                }
                break;

            case 'delete':
                if ($jobOffersController->deleteJob($_POST['job_id'])) {
                    $message = "Job offer deleted successfully!";
                }
                break;
        }
    }
}

// Fetch data using controller
$jobs = $jobOffersController->getAllJobs();
$categories = $jobOffersController->getCategories();
$locations = $jobOffersController->getLocations();
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
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="salary_min" class="form-label">Minimum Salary</label>
                                    <input type="number" class="form-control" id="salary_min" name="salary_min" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="salary_max" class="form-label">Maximum Salary</label>
                                    <input type="number" class="form-control" id="salary_max" name="salary_max" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <select class="form-control" id="location" name="location" required>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?php echo $location['location_id']; ?>">
                                        <?php echo $location['is_remote'] ? 'Remote' : htmlspecialchars($location['city'] . ', ' . $location['country']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="image_url" class="form-label">Image URL</label>
                            <input type="url" class="form-control" id="image_url" name="image_url" required>
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
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_category" class="form-label">Category</label>
                            <select class="form-control" id="edit_category" name="category" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_salary_min" class="form-label">Minimum Salary</label>
                                    <input type="number" class="form-control" id="edit_salary_min" name="salary_min" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_salary_max" class="form-label">Maximum Salary</label>
                                    <input type="number" class="form-control" id="edit_salary_max" name="salary_max" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_location" class="form-label">Location</label>
                            <select class="form-control" id="edit_location" name="location" required>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?php echo $location['location_id']; ?>">
                                        <?php echo $location['is_remote'] ? 'Remote' : htmlspecialchars($location['city'] . ', ' . $location['country']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_image_url" class="form-label">Image URL</label>
                            <input type="url" class="form-control" id="edit_image_url" name="image_url" required>
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