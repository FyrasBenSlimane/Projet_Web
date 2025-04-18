<?php
/**
 * Projects Dashboard View
 * Allows users to view, create and manage their projects
 */

// Start output buffering to capture content for the layout
ob_start();

// Variables should already be set by the controller:
// - $projects: All user projects
// - $currentView: The current view (list_projects, new_project, view_project)
// - $selectedProject: The selected project (if viewing a project)
// - $projectTasks: Tasks for the selected project
// - $userEmail: The current user's email
// - $projectModel: The project model instance

// Define message variables for displaying alerts
$message = '';
$alertType = '';

// Handle form submissions - this would typically be in the controller
// but we'll keep it here for compatibility
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if projectModel is not defined (happens when form is submitted directly)
    if (!isset($projectModel) || $projectModel === null) {
        // Get database connection and initialize projectModel
        require_once __DIR__ . '/../../../config/database.php';
        $pdo = $GLOBALS['pdo'] ?? getDBConnection();
        require_once __DIR__ . '/../models/ProjectModel.php';
        $projectModel = new ProjectModel($pdo);
    }
    
    // Create new project
    if (isset($_POST['action']) && $_POST['action'] === 'create_project') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $clientName = $_POST['client_name'] ?? '';
        $budget = filter_var($_POST['budget'] ?? 0, FILTER_VALIDATE_FLOAT);
        $priority = $_POST['priority'] ?? 'medium';
        $startDate = $_POST['start_date'] ?? null;
        $endDate = $_POST['end_date'] ?? null;
        
        if (empty($title) || empty($description)) {
            $message = 'Please fill all required fields.';
            $alertType = 'danger';
        } else {
            $result = $projectModel->createProject($userEmail, $title, $description, $clientName, $budget, $priority, $startDate, $endDate);
            if ($result) {
                $message = 'Project created successfully.';
                $alertType = 'success';
            } else {
                $message = 'Failed to create project. Please try again.';
                $alertType = 'danger';
            }
        }
    }

    // Update project
    if (isset($_POST['action']) && $_POST['action'] === 'update_project') {
        $projectId = $_POST['project_id'] ?? '';
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $clientName = $_POST['client_name'] ?? '';
        $budget = filter_var($_POST['budget'] ?? 0, FILTER_VALIDATE_FLOAT);
        $priority = $_POST['priority'] ?? 'medium';
        $status = $_POST['status'] ?? 'pending';
        $startDate = $_POST['start_date'] ?? null;
        $endDate = $_POST['end_date'] ?? null;
        
        if (empty($projectId) || empty($title) || empty($description)) {
            $message = 'Please fill all required fields.';
            $alertType = 'danger';
        } else {
            $data = [
                'title' => $title,
                'description' => $description,
                'client_name' => $clientName,
                'budget' => $budget,
                'priority' => $priority,
                'status' => $status,
                'start_date' => $startDate,
                'end_date' => $endDate
            ];
            
            $result = $projectModel->updateProject($projectId, $userEmail, $data);
            if ($result) {
                $message = 'Project updated successfully.';
                $alertType = 'success';
            } else {
                $message = 'Failed to update project. Please try again.';
                $alertType = 'danger';
            }
        }
    }

    // Add task to project
    if (isset($_POST['action']) && $_POST['action'] === 'add_task') {
        $projectId = $_POST['project_id'] ?? '';
        $taskTitle = $_POST['task_title'] ?? '';
        $taskDescription = $_POST['task_description'] ?? '';
        $assignedTo = $_POST['assigned_to'] ?? '';
        $dueDate = $_POST['due_date'] ?? null;
        
        if (empty($projectId) || empty($taskTitle)) {
            $message = 'Task title is required.';
            $alertType = 'danger';
        } else {
            $result = $projectModel->addTask($projectId, $taskTitle, $taskDescription, $assignedTo, $dueDate);
            if ($result) {
                $message = 'Task added successfully.';
                $alertType = 'success';
            } else {
                $message = 'Failed to add task. Please try again.';
                $alertType = 'danger';
            }
        }
    }

    // Delete project
    if (isset($_POST['action']) && $_POST['action'] === 'delete_project') {
        $projectId = $_POST['project_id'] ?? '';
        
        if (empty($projectId)) {
            $message = 'Invalid project.';
            $alertType = 'danger';
        } else {
            $result = $projectModel->deleteProject($projectId, $userEmail);
            if ($result) {
                $message = 'Project deleted successfully.';
                $alertType = 'success';
            } else {
                $message = 'Failed to delete project. Please try again.';
                $alertType = 'danger';
            }
        }
    }
}

// Make sure user data is available
$userName = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
$userType = $_SESSION['user']['user_type'] ?? 'freelancer';

// The HTML content starts below
?>

<!-- Projects Dashboard Content -->
<div class="container-fluid py-4">
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if ($currentView === 'list_projects'): ?>
    <!-- Projects List View - Enhanced UI -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="project-header-section">
                    <h5 class="project-section-title"><i class="bi bi-briefcase-fill"></i> My Projects</h5>
                    <a href="?page=projects&new=true" class="create-project-btn">
                        <i class="bi bi-plus-circle"></i> New Project
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($projects)): ?>
                    <div class="no-projects">
                        <i class="bi bi-briefcase-fill no-projects-icon"></i>
                        <h4>No Projects</h4>
                        <p>You haven't created any projects yet. Start by creating your first project.</p>
                        <a href="?page=projects&new=true" class="create-project-btn">
                            <i class="bi bi-plus-circle"></i> Create Your First Project
                        </a>
                    </div>
                    <?php else: ?>
                    <!-- Card-based project layout -->
                    <div class="projects-container" id="projects-container">
                        <?php foreach ($projects as $index => $project): ?>
                        <div class="project-card" id="project-<?php echo $project['id']; ?>">
                            <div class="project-header">
                                <h5 class="project-title">
                                    <a href="?page=projects&project_id=<?php echo $project['id']; ?>">
                                        <?php echo htmlspecialchars($project['title']); ?>
                                    </a>
                                </h5>
                                <div class="project-badges">
                                    <?php
                                    $priorityBadge = 'badge-info';
                                    if ($project['priority'] === 'high') {
                                        $priorityBadge = 'badge-danger';
                                    } elseif ($project['priority'] === 'medium') {
                                        $priorityBadge = 'badge-warning';
                                    }
                                    
                                    $statusBadge = 'badge-success';
                                    if ($project['status'] === 'pending') {
                                        $statusBadge = 'badge-secondary';
                                    } elseif ($project['status'] === 'in-progress') {
                                        $statusBadge = 'badge-warning';
                                    } elseif ($project['status'] === 'cancelled') {
                                        $statusBadge = 'badge-danger';
                                    }
                                    ?>
                                    <span class="project-badge status-badge <?php echo $statusBadge; ?>">
                                        <span class="status-text"><?php echo ucfirst($project['status']); ?></span>
                                        <div class="status-dropdown dropdown">
                                            <button class="btn btn-sm dropdown-toggle p-0 ms-1" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-chevron-down"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item status-option" data-project-id="<?php echo $project['id']; ?>" data-status="pending" href="#">Pending</a></li>
                                                <li><a class="dropdown-item status-option" data-project-id="<?php echo $project['id']; ?>" data-status="in-progress" href="#">In Progress</a></li>
                                                <li><a class="dropdown-item status-option" data-project-id="<?php echo $project['id']; ?>" data-status="completed" href="#">Completed</a></li>
                                                <li><a class="dropdown-item status-option" data-project-id="<?php echo $project['id']; ?>" data-status="cancelled" href="#">Cancelled</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger delete-project" data-project-id="<?php echo $project['id']; ?>" href="#">Delete</a></li>
                                            </ul>
                                        </div>
                                    </span>
                                    <span class="project-badge <?php echo $priorityBadge; ?>">
                                        <?php echo ucfirst($project['priority']); ?>
                                    </span>
                                    <?php if (!empty($project['budget'])): ?>
                                    <span class="project-badge badge-primary">
                                        $<?php echo number_format($project['budget'], 2); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="project-content">
                                <p class="project-excerpt">
                                    <?php 
                                    // Display a short excerpt of the description
                                    $excerpt = strlen($project['description']) > 120 ? 
                                        substr($project['description'], 0, 120) . '...' : 
                                        $project['description'];
                                    echo htmlspecialchars($excerpt); 
                                    ?>
                                </p>
                                <?php if (!empty($project['client_name'])): ?>
                                <div class="client-info">
                                    <i class="bi bi-person"></i> Client: <?php echo htmlspecialchars($project['client_name']); ?>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Add progress bar for visual interest -->
                                <div class="project-progress">
                                    <?php 
                                    // Determine progress percentage based on status
                                    $progressPercent = 0;
                                    if ($project['status'] === 'in-progress') {
                                        $progressPercent = 50;
                                    } elseif ($project['status'] === 'completed') {
                                        $progressPercent = 100;
                                    } elseif ($project['status'] === 'pending') {
                                        $progressPercent = 10;
                                    }
                                    ?>
                                    <div class="project-progress-bar" style="width: <?php echo $progressPercent; ?>%"></div>
                                </div>
                            </div>
                            <div class="project-footer">
                                <div class="project-meta">
                                    <div class="project-date">
                                        <i class="bi bi-calendar-event"></i> 
                                        <span>Created: <?php echo date('M d, Y', strtotime($project['created_at'])); ?></span>
                                    </div>
                                    <?php if (!empty($project['end_date'])): ?>
                                    <div class="project-date">
                                        <i class="bi bi-calendar-check"></i> 
                                        <span>Due: <?php echo date('M d, Y', strtotime($project['end_date'])); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="project-actions">
                                    <a href="?page=projects&project_id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline-primary" title="View Project">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger delete-project" data-project-id="<?php echo $project['id']; ?>" title="Delete Project">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php elseif ($currentView === 'new_project'): ?>
    <!-- New Project Form -->
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <div class="card ticket-form-card">
                <div class="card-header ticket-form-header">
                    <h5 class="ticket-form-title"><i class="bi bi-briefcase-fill"></i> Create New Project</h5>
                </div>
                <div class="card-body ticket-form-body">
                    <form method="post" action="">
                        <input type="hidden" name="action" value="create_project">
                        
                        <div class="ticket-form-section">
                            <label for="title" class="ticket-form-label"><i class="bi bi-pencil-square"></i> Title</label>
                            <input type="text" class="form-control ticket-form-control" id="title" name="title" required 
                                placeholder="Project title">
                        </div>
                        
                        <div class="row ticket-form-section">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="client_name" class="ticket-form-label"><i class="bi bi-person"></i> Client Name</label>
                                <input type="text" class="form-control ticket-form-control" id="client_name" name="client_name" 
                                    placeholder="Client name (optional)">
                            </div>
                            <div class="col-md-6">
                                <label for="budget" class="ticket-form-label"><i class="bi bi-cash"></i> Budget</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control ticket-form-control" id="budget" name="budget" 
                                        placeholder="0.00" step="0.01" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row ticket-form-section">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="start_date" class="ticket-form-label"><i class="bi bi-calendar-event"></i> Start Date</label>
                                <input type="date" class="form-control ticket-form-control" id="start_date" name="start_date">
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="ticket-form-label"><i class="bi bi-calendar-check"></i> Due Date</label>
                                <input type="date" class="form-control ticket-form-control" id="end_date" name="end_date">
                            </div>
                        </div>
                        
                        <div class="ticket-form-section">
                            <label class="ticket-form-label"><i class="bi bi-flag"></i> Priority</label>
                            <div class="priority-options">
                                <div class="priority-option">
                                    <input type="radio" class="priority-radio" name="priority" id="priority-low" value="low">
                                    <label for="priority-low" class="priority-label priority-low">
                                        <span class="priority-indicator"></span>
                                        <span class="priority-label-text">Low</span>
                                    </label>
                                </div>
                                <div class="priority-option">
                                    <input type="radio" class="priority-radio" name="priority" id="priority-medium" value="medium" checked>
                                    <label for="priority-medium" class="priority-label priority-medium">
                                        <span class="priority-indicator"></span>
                                        <span class="priority-label-text">Medium</span>
                                    </label>
                                </div>
                                <div class="priority-option">
                                    <input type="radio" class="priority-radio" name="priority" id="priority-high" value="high">
                                    <label for="priority-high" class="priority-label priority-high">
                                        <span class="priority-indicator"></span>
                                        <span class="priority-label-text">High</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ticket-form-section">
                            <label for="description" class="ticket-form-label"><i class="bi bi-chat-left-text"></i> Description</label>
                            <textarea class="form-control ticket-form-control ticket-form-textarea" id="description" name="description" rows="6" required
                                placeholder="Detailed project description"></textarea>
                        </div>
                        
                        <div class="ticket-form-footer">
                            <a href="?page=projects" class="btn cancel-btn">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn ticket-submit-btn">
                                <i class="bi bi-save"></i> Create Project
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php elseif ($currentView === 'view_project' && $selectedProject): ?>
    <!-- View Project Details -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card ticket-view-card">
                <div class="ticket-view-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="ticket-view-title"><?php echo htmlspecialchars($selectedProject['title']); ?></h5>
                        <div class="ticket-view-badges">
                            <span class="badge bg-<?php echo $selectedProject['status'] === 'completed' ? 'success' : ($selectedProject['status'] === 'in-progress' ? 'warning' : ($selectedProject['status'] === 'cancelled' ? 'danger' : 'secondary')); ?>">
                                <?php echo ucfirst($selectedProject['status']); ?>
                            </span>
                            <span class="badge bg-<?php echo $selectedProject['priority'] === 'high' ? 'danger' : ($selectedProject['priority'] === 'medium' ? 'warning' : 'info'); ?>">
                                <?php echo ucfirst($selectedProject['priority']); ?> Priority
                            </span>
                            <?php if (!empty($selectedProject['budget'])): ?>
                            <span class="badge bg-primary">
                                Budget: $<?php echo number_format($selectedProject['budget'], 2); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="d-flex">
                        <form method="post" class="me-2">
                            <input type="hidden" name="project_id" value="<?php echo $selectedProject['id']; ?>">
                            <input type="hidden" name="action" value="delete_project">
                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this project? This action cannot be undone.')">
                                <i class="bi bi-trash"></i> Delete Project
                            </button>
                        </form>
                        <a href="?page=projects" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Projects
                        </a>
                    </div>
                </div>
                <div class="ticket-view-body">
                    <!-- Project Details Section -->
                    <div class="project-details mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3"><i class="bi bi-info-circle"></i> Project Information</h6>
                                <div class="project-info-item">
                                    <strong>Client:</strong> <?php echo !empty($selectedProject['client_name']) ? htmlspecialchars($selectedProject['client_name']) : 'N/A'; ?>
                                </div>
                                <div class="project-info-item">
                                    <strong>Created:</strong> <?php echo date('F j, Y', strtotime($selectedProject['created_at'])); ?>
                                </div>
                                <?php if (!empty($selectedProject['start_date'])): ?>
                                <div class="project-info-item">
                                    <strong>Start Date:</strong> <?php echo date('F j, Y', strtotime($selectedProject['start_date'])); ?>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($selectedProject['end_date'])): ?>
                                <div class="project-info-item">
                                    <strong>Due Date:</strong> <?php echo date('F j, Y', strtotime($selectedProject['end_date'])); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-3"><i class="bi bi-gear"></i> Project Actions</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editProjectModal">
                                        <i class="bi bi-pencil"></i> Edit Project
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                                        <i class="bi bi-plus-circle"></i> Add Task
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="statusDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-flag"></i> Change Status
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="statusDropdown">
                                            <li><a class="dropdown-item status-option" data-project-id="<?php echo $selectedProject['id']; ?>" data-status="pending" href="#">Pending</a></li>
                                            <li><a class="dropdown-item status-option" data-project-id="<?php echo $selectedProject['id']; ?>" data-status="in-progress" href="#">In Progress</a></li>
                                            <li><a class="dropdown-item status-option" data-project-id="<?php echo $selectedProject['id']; ?>" data-status="completed" href="#">Completed</a></li>
                                            <li><a class="dropdown-item status-option" data-project-id="<?php echo $selectedProject['id']; ?>" data-status="cancelled" href="#">Cancelled</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="project-description mt-4">
                            <h6 class="mb-3"><i class="bi bi-file-text"></i> Description</h6>
                            <div class="p-3 bg-light-gray rounded">
                                <?php echo nl2br(htmlspecialchars($selectedProject['description'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Project Tasks Section -->
                    <div class="project-tasks mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="bi bi-list-check"></i> Project Tasks</h5>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                                <i class="bi bi-plus-circle"></i> Add Task
                            </button>
                        </div>
                        
                        <?php if (empty($projectTasks)): ?>
                        <div class="empty-tasks-container">
                            <div class="empty-tasks-illustration">
                                <i class="bi bi-check2-square"></i>
                            </div>
                            <h6>No Tasks Yet</h6>
                            <p>Start adding tasks to track progress on this project</p>
                        </div>
                        <?php else: ?>
                        <div class="task-filter-bar mb-3">
                            <div class="btn-group" role="group" aria-label="Task filters">
                                <button type="button" class="btn btn-outline-secondary btn-sm active task-filter" data-filter="all">
                                    All Tasks <span class="task-count"><?php echo count($projectTasks); ?></span>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm task-filter" data-filter="pending">
                                    Pending
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm task-filter" data-filter="in-progress">
                                    In Progress
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm task-filter" data-filter="completed">
                                    Completed
                                </button>
                            </div>
                            <div class="task-sort">
                                <select class="form-select form-select-sm" id="taskSort">
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="due-date">Due Date</option>
                                </select>
                            </div>
                        </div>

                        <div class="task-list">
                            <?php foreach ($projectTasks as $task): ?>
                            <div class="task-item p-3 mb-3 border rounded task-status-<?php echo $task['status']; ?>" data-task-status="<?php echo $task['status']; ?>" data-task-id="<?php echo $task['id']; ?>">
                                <div class="task-header d-flex align-items-start">
                                    <div class="task-checkbox me-2">
                                        <div class="form-check">
                                            <input class="form-check-input task-check" type="checkbox" value="" id="task-<?php echo $task['id']; ?>" <?php echo $task['status'] === 'completed' ? 'checked' : ''; ?> data-task-id="<?php echo $task['id']; ?>">
                                            <label class="form-check-label" for="task-<?php echo $task['id']; ?>"></label>
                                        </div>
                                    </div>
                                    <div class="task-content flex-grow-1">
                                        <h6 class="task-title mb-1 <?php echo $task['status'] === 'completed' ? 'text-decoration-line-through' : ''; ?>"><?php echo htmlspecialchars($task['title']); ?></h6>
                                        <?php if (!empty($task['description'])): ?>
                                        <div class="task-description text-muted mb-2 small">
                                            <?php 
                                            $shortDesc = strlen($task['description']) > 120 ? substr($task['description'], 0, 120) . '...' : $task['description'];
                                            echo nl2br(htmlspecialchars($shortDesc)); 
                                            ?>
                                            <?php if (strlen($task['description']) > 120): ?>
                                            <a href="#" class="view-more-link small" data-bs-toggle="modal" data-bs-target="#taskDetailModal" data-task-id="<?php echo $task['id']; ?>" data-task-title="<?php echo htmlspecialchars($task['title']); ?>" data-task-description="<?php echo htmlspecialchars($task['description']); ?>">Read more</a>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="task-controls ms-2">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-icon" type="button" id="taskDropdown<?php echo $task['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="taskDropdown<?php echo $task['id']; ?>">
                                                <li><a class="dropdown-item task-status-option" href="#" data-task-id="<?php echo $task['id']; ?>" data-status="pending"><i class="bi bi-pause-circle me-2"></i>Mark as Pending</a></li>
                                                <li><a class="dropdown-item task-status-option" href="#" data-task-id="<?php echo $task['id']; ?>" data-status="in-progress"><i class="bi bi-play-circle me-2"></i>Mark as In Progress</a></li>
                                                <li><a class="dropdown-item task-status-option" href="#" data-task-id="<?php echo $task['id']; ?>" data-status="completed"><i class="bi bi-check-circle me-2"></i>Mark as Completed</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item task-edit" href="#" data-task-id="<?php echo $task['id']; ?>"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                                <li><a class="dropdown-item task-delete text-danger" href="#" data-task-id="<?php echo $task['id']; ?>"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="task-meta d-flex flex-wrap gap-3 mt-2">
                                    <?php if (!empty($task['assigned_to'])): ?>
                                    <div class="task-meta-item">
                                        <i class="bi bi-person text-primary"></i>
                                        <span class="small"><?php echo htmlspecialchars($task['assigned_to']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($task['due_date'])): ?>
                                    <div class="task-meta-item">
                                        <i class="bi bi-calendar-check text-danger"></i>
                                        <span class="small"><?php echo date('M d, Y', strtotime($task['due_date'])); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="task-meta-item">
                                        <i class="bi bi-clock-history text-secondary"></i>
                                        <span class="small"><?php echo date('M d, Y', strtotime($task['created_at'])); ?></span>
                                    </div>
                                    
                                    <div class="task-meta-item ms-auto">
                                        <span class="badge bg-<?php echo $task['status'] === 'completed' ? 'success' : ($task['status'] === 'in-progress' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($task['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Task pagination and summary -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="task-summary small text-muted">
                                <span id="taskCount"><?php echo count($projectTasks); ?></span> tasks total
                            </div>
                            <div class="task-pagination">
                                <button class="btn btn-sm btn-outline-secondary disabled">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <span class="mx-2 small">Page 1</span>
                                <button class="btn btn-sm btn-outline-secondary disabled">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Task Detail Modal -->
                    <div class="modal fade" id="taskDetailModal" tabindex="-1" aria-labelledby="taskDetailModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="taskDetailModalLabel">Task Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <h6 id="taskDetailTitle" class="mb-3"></h6>
                                    <div class="task-detail-description mb-3">
                                        <p id="taskDetailDescription"></p>
                                    </div>
                                    <div class="task-detail-meta">
                                        <!-- Task metadata will be inserted here dynamically -->
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary task-detail-edit">Edit Task</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Project Modal -->
    <div class="modal fade" id="editProjectModal" tabindex="-1" aria-labelledby="editProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProjectModalLabel">Edit Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="" id="editProjectForm">
                        <input type="hidden" name="action" value="update_project">
                        <input type="hidden" name="project_id" value="<?php echo $selectedProject['id']; ?>">
                        
                        <div class="mb-3">
                            <label for="edit_title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title" value="<?php echo htmlspecialchars($selectedProject['title']); ?>" required>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_client_name" class="form-label">Client Name</label>
                                <input type="text" class="form-control" id="edit_client_name" name="client_name" value="<?php echo htmlspecialchars($selectedProject['client_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_budget" class="form-label">Budget</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="edit_budget" name="budget" value="<?php echo htmlspecialchars($selectedProject['budget'] ?? ''); ?>" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="edit_start_date" name="start_date" value="<?php echo $selectedProject['start_date'] ?? ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_end_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="edit_end_date" name="end_date" value="<?php echo $selectedProject['end_date'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_status" name="status">
                                    <option value="pending" <?php echo $selectedProject['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="in-progress" <?php echo $selectedProject['status'] === 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo $selectedProject['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $selectedProject['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_priority" class="form-label">Priority</label>
                                <select class="form-select" id="edit_priority" name="priority">
                                    <option value="low" <?php echo $selectedProject['priority'] === 'low' ? 'selected' : ''; ?>>Low</option>
                                    <option value="medium" <?php echo $selectedProject['priority'] === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="high" <?php echo $selectedProject['priority'] === 'high' ? 'selected' : ''; ?>>High</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="5" required><?php echo htmlspecialchars($selectedProject['description']); ?></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="editProjectForm" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Task Modal -->
    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTaskModalLabel">Add New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="" id="addTaskForm">
                        <input type="hidden" name="action" value="add_task">
                        <input type="hidden" name="project_id" value="<?php echo $selectedProject['id']; ?>">
                        
                        <div class="mb-3">
                            <label for="task_title" class="form-label">Task Title</label>
                            <input type="text" class="form-control" id="task_title" name="task_title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="task_description" class="form-label">Description</label>
                            <textarea class="form-control" id="task_description" name="task_description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Assign To (Email)</label>
                            <input type="email" class="form-control" id="assigned_to" name="assigned_to">
                        </div>
                        
                        <div class="mb-3">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="addTaskForm" class="btn btn-primary">Add Task</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php elseif ($currentView === 'view_project'): ?>
    <!-- Invalid Project -->
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i> The requested project was not found or you don't have permission to view it.
        <a href="?page=projects" class="alert-link">Return to projects list</a>
    </div>
    <?php endif; ?>
</div>

<style>
/* Project specific styles */
.client-info {
    font-size: 0.85rem;
    color: var(--accent);
    margin-bottom: 0.5rem;
}

.project-info-item {
    margin-bottom: 0.5rem;
}

.bg-light-gray {
    background-color: var(--light-gray);
}

[data-bs-theme="dark"] .bg-light-gray {
    background-color: rgba(255,255,255,0.05);
}

/* Project Cards - Dedicated styling separate from support tickets */
.projects-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.25rem;
    padding: 1.5rem;
}

.project-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: var(--radius-md);
    overflow: hidden;
    border: 1px solid rgba(0,0,0,0.08);
    background: white;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
}

[data-bs-theme="dark"] .project-card {
    background-color: var(--accent-dark);
    border-color: rgba(255,255,255,0.05);
}

.project-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

[data-bs-theme="dark"] .project-card:hover {
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}

.project-card::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 0 40px 40px 0;
    border-color: transparent var(--light-gray) transparent transparent;
    transition: border-color 0.3s ease;
}

.project-card:hover::after {
    border-color: transparent var(--primary) transparent transparent;
}

[data-bs-theme="dark"] .project-card:hover::after {
    border-color: transparent var(--secondary) transparent transparent;
}

.project-header {
    padding: 1.25rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

[data-bs-theme="dark"] .project-header {
    border-bottom-color: rgba(255,255,255,0.05);
}

.project-title {
    font-family: var(--font-heading);
    font-weight: 600;
    font-size: 1.2rem;
    color: var(--accent-dark);
    margin-bottom: 0.75rem;
}

[data-bs-theme="dark"] .project-title {
    color: var(--light);
}

.project-title a {
    color: inherit;
    text-decoration: none;
}

.project-title a:hover {
    color: var(--primary);
}

[data-bs-theme="dark"] .project-title a:hover {
    color: var(--secondary);
}

.project-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.project-badge {
    padding: 0.35rem 0.65rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

.badge-primary {
    background: linear-gradient(135deg, var(--primary), #4a6f91);
    color: white;
}

.badge-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
}

.badge-success {
    background: linear-gradient(135deg, #198754, #157347);
    color: white;
}

.badge-danger {
    background: linear-gradient(135deg, #dc3545, #bb2d3b);
    color: white;
}

.badge-warning {
    background: linear-gradient(135deg, #ffc107, #e5ac06);
    color: #212529;
}

.badge-info {
    background: linear-gradient(135deg, #0dcaf0, #0bacca);
    color: #212529;
}

.project-content {
    padding: 1.25rem;
    flex: 1;
}

.project-excerpt {
    color: var(--accent);
    margin-bottom: 1rem;
    font-size: 0.9rem;
    line-height: 1.5;
}

.project-footer {
    padding: 1.25rem;
    border-top: 1px solid rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

[data-bs-theme="dark"] .project-footer {
    border-top-color: rgba(255,255,255,0.05);
}

.project-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.project-date {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.8rem;
    color: var(--accent);
}

.project-date i {
    color: var(--primary);
    font-size: 0.9rem;
}

[data-bs-theme="dark"] .project-date i {
    color: var(--secondary);
}

.project-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

.no-projects {
    text-align: center;
    padding: 3rem 1.5rem;
    background-color: white;
    border-radius: 0 0 var(--radius-md) var(--radius-md);
}

[data-bs-theme="dark"] .no-projects {
    background-color: var(--accent-dark);
}

.no-projects-icon {
    font-size: 3.5rem;
    color: var(--secondary);
    opacity: 0.4;
    margin-bottom: 1rem;
}

.project-header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    background-color: white;
    border-radius: var(--radius-md) var(--radius-md) 0 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

[data-bs-theme="dark"] .project-header-section {
    background-color: var(--accent-dark);
    border-bottom-color: rgba(255,255,255,0.05);
}

.project-section-title {
    font-family: var(--font-heading);
    font-weight: 600;
    font-size: 1.3rem;
    color: var(--accent-dark);
    margin: 0;
    display: flex;
    align-items: center;
}

.project-section-title i {
    margin-right: 0.5rem;
    color: var(--primary);
}

[data-bs-theme="dark"] .project-section-title {
    color: var(--light);
}

[data-bs-theme="dark"] .project-section-title i {
    color: var(--secondary);
}

.create-project-btn {
    background-color: var(--primary);
    color: white;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 50px;
    font-weight: 500;
    font-size: 0.95rem;
    box-shadow: 0 4px 10px rgba(var(--primary-rgb), 0.25);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    text-decoration: none;
}

.create-project-btn i {
    margin-right: 0.5rem;
    font-size: 1.1rem;
}

.create-project-btn:hover {
    background-color: var(--accent);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(var(--primary-rgb), 0.3);
    color: white;
}

[data-bs-theme="dark"] .create-project-btn {
    background-color: var(--secondary);
    box-shadow: 0 4px 10px rgba(143, 179, 222, 0.25);
}

[data-bs-theme="dark"] .create-project-btn:hover {
    background-color: var(--primary);
    box-shadow: 0 6px 15px rgba(143, 179, 222, 0.3);
}

/* Progress Bar for projects */
.project-progress {
    height: 8px;
    border-radius: 4px;
    background-color: rgba(0,0,0,0.05);
    margin: 0.75rem 0;
    overflow: hidden;
}

[data-bs-theme="dark"] .project-progress {
    background-color: rgba(255,255,255,0.05);
}

.project-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--primary), #4a6f91);
    transition: width 0.5s ease;
}

[data-bs-theme="dark"] .project-progress-bar {
    background: linear-gradient(90deg, var(--secondary), #4a80b3);
}

/* Enhanced Task UI */
.task-filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(0,0,0,0.08);
    margin-bottom: 1.25rem;
}

[data-bs-theme="dark"] .task-filter-bar {
    border-bottom-color: rgba(255,255,255,0.05);
}

.task-sort {
    width: 150px;
}

.task-count {
    background-color: var(--primary);
    color: white;
    border-radius: 50px;
    padding: 0.1rem 0.5rem;
    font-size: 0.7rem;
    margin-left: 0.5rem;
}

[data-bs-theme="dark"] .task-count {
    background-color: var(--secondary);
}

.task-item {
    border-radius: var(--radius-md) !important;
    transition: all 0.3s ease;
    border-color: rgba(0,0,0,0.08) !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    position: relative;
    overflow: hidden;
}

.task-item:hover {
    border-color: rgba(var(--primary-rgb), 0.3) !important;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

[data-bs-theme="dark"] .task-item {
    border-color: rgba(255,255,255,0.05) !important;
    background-color: var(--accent-dark);
}

[data-bs-theme="dark"] .task-item:hover {
    border-color: rgba(143, 179, 222, 0.3) !important;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.task-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background-color: #6c757d;
}

.task-status-completed::before {
    background-color: #198754;
}

.task-status-in-progress::before {
    background-color: #ffc107;
}

.task-status-completed {
    background-color: rgba(25, 135, 84, 0.05);
}

.task-checkbox .form-check-input {
    border-radius: 50%;
    width: 1.2rem;
    height: 1.2rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.task-checkbox .form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.task-checkbox .form-check-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
}

.btn-icon {
    background: transparent;
    border: none;
    color: var(--accent);
    padding: 0.25rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.btn-icon:hover {
    background-color: var(--light-gray);
}

[data-bs-theme="dark"] .btn-icon:hover {
    background-color: rgba(255,255,255,0.1);
}

.task-meta-item {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    color: var(--accent);
}

.task-meta-item i {
    font-size: 0.85rem;
}

.view-more-link {
    color: var(--primary);
    text-decoration: none;
    white-space: nowrap;
    margin-left: 0.35rem;
}

[data-bs-theme="dark"] .view-more-link {
    color: var(--secondary);
}

.empty-tasks-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 3rem;
    background-color: rgba(0,0,0,0.02);
    border-radius: var(--radius-md);
    margin: 1rem 0;
}

[data-bs-theme="dark"] .empty-tasks-container {
    background-color: rgba(255,255,255,0.02);
}

.empty-tasks-illustration {
    font-size: 3.5rem;
    color: var(--primary);
    opacity: 0.2;
    margin-bottom: 1rem;
}

[data-bs-theme="dark"] .empty-tasks-illustration {
    color: var(--secondary);
}

.task-pagination button {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Task Details Modal styling */
.task-detail-description {
    background-color: var(--light-gray);
    padding: 1rem;
    border-radius: var(--radius-sm);
}

[data-bs-theme="dark"] .task-detail-description {
    background-color: rgba(255,255,255,0.05);
}

/* Add animation for notification toasts */
.notification-toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    min-width: 250px;
    z-index: 2000;
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    border-radius: var(--radius-md);
    transition: opacity 0.5s ease;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-close alert messages after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-secondary):not(.alert-info)');
    
    // Set timeout to auto-dismiss each alert
    alerts.forEach(function(alert) {
        setTimeout(function() {
            // Create fadeout effect
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            
            // Remove alert after fadeout
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Real-time status update functionality
    const statusOptions = document.querySelectorAll('.status-option');
    statusOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const projectId = this.getAttribute('data-project-id');
            const newStatus = this.getAttribute('data-status');
            updateProjectStatus(projectId, newStatus);
        });
    });

    // Delete project functionality
    const deleteButtons = document.querySelectorAll('.delete-project');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const projectId = this.getAttribute('data-project-id');
            if (confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
                deleteProject(projectId);
            }
        });
    });

    // Function to update project status via AJAX
    function updateProjectStatus(projectId, status) {
        // Show loading indicator
        const projectCard = document.getElementById(`project-${projectId}`);
        if (projectCard) {
            projectCard.classList.add('updating');
        }

        // Create form data for the request
        const formData = new FormData();
        formData.append('project_id', projectId);
        formData.append('status', status);
        formData.append('action', 'update_status');

        // Send AJAX request
        fetch('index.php?page=projects', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                if (projectCard) {
                    const statusBadge = projectCard.querySelector('.status-badge');
                    const statusText = statusBadge.querySelector('.status-text');
                    
                    // Remove old status classes
                    statusBadge.classList.remove('badge-success', 'badge-warning', 'badge-secondary', 'badge-danger');
                    
                    // Add new status class
                    if (status === 'completed') {
                        statusBadge.classList.add('badge-success');
                    } else if (status === 'in-progress') {
                        statusBadge.classList.add('badge-warning');
                    } else if (status === 'cancelled') {
                        statusBadge.classList.add('badge-danger');
                    } else {
                        statusBadge.classList.add('badge-secondary');
                    }
                    
                    // Update text
                    statusText.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                } else {
                    // We're on the project view page, reload to reflect changes
                    window.location.reload();
                }
                
                // Show success message
                showNotification('Status updated successfully', 'success');
            } else {
                // Show error message
                showNotification('Failed to update status', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'danger');
        })
        .finally(() => {
            // Remove loading indicator
            if (projectCard) {
                projectCard.classList.remove('updating');
            }
        });
    }

    // Function to delete project via AJAX
    function deleteProject(projectId) {
        // Show loading indicator
        const projectCard = document.getElementById(`project-${projectId}`);
        if (projectCard) {
            projectCard.classList.add('deleting');
        }

        // Create form data for the request
        const formData = new FormData();
        formData.append('project_id', projectId);
        formData.append('action', 'delete_project');

        // Send AJAX request
        fetch('index.php?page=projects', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (projectCard) {
                    // Fade out and remove the project card with animation
                    projectCard.style.transition = 'all 0.5s ease';
                    projectCard.style.opacity = '0';
                    projectCard.style.transform = 'scale(0.8)';
                    
                    setTimeout(() => {
                        projectCard.remove();
                        
                        // Check if there are no more projects and show empty state if needed
                        const projectsContainer = document.getElementById('projects-container');
                        if (projectsContainer && projectsContainer.children.length === 0) {
                            const emptyState = `
                                <div class="text-center py-5">
                                    <i class="bi bi-briefcase-fill text-muted" style="font-size: 3rem;"></i>
                                    <h4 class="mt-3">No Projects</h4>
                                    <p class="text-muted">You haven't created any projects yet.</p>
                                    <a href="?page=projects&new=true" class="btn btn-primary mt-2">Create Your First Project</a>
                                </div>
                            `;
                            projectsContainer.parentNode.innerHTML = emptyState;
                        }
                    }, 500);
                } else {
                    // We're on the project view page, redirect to projects list
                    window.location.href = '?page=projects';
                }
                
                // Show success message
                showNotification('Project deleted successfully', 'success');
            } else {
                // Show error message
                showNotification('Failed to delete project', 'danger');
                if (projectCard) {
                    projectCard.classList.remove('deleting');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'danger');
            if (projectCard) {
                projectCard.classList.remove('deleting');
            }
        });
    }

    // Function to show notification
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show notification-toast`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 500);
        }, 3000);
    }

    // Task filtering functionality
    const taskFilters = document.querySelectorAll('.task-filter');
    if (taskFilters.length > 0) {
        taskFilters.forEach(filter => {
            filter.addEventListener('click', function(e) {
                e.preventDefault();
                // Remove active class from all filters
                taskFilters.forEach(f => f.classList.remove('active'));
                // Add active class to clicked filter
                this.classList.add('active');
                
                const filterValue = this.getAttribute('data-filter');
                filterTasks(filterValue);
            });
        });
    }

    // Task sorting functionality
    const taskSort = document.getElementById('taskSort');
    if (taskSort) {
        taskSort.addEventListener('change', function() {
            sortTasks(this.value);
        });
    }

    // Task checkbox functionality
    const taskCheckboxes = document.querySelectorAll('.task-check');
    if (taskCheckboxes.length > 0) {
        taskCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const taskId = this.getAttribute('data-task-id');
                const newStatus = this.checked ? 'completed' : 'pending';
                const taskItem = document.querySelector(`.task-item[data-task-id="${taskId}"]`);
                const taskTitle = taskItem.querySelector('.task-title');
                
                // Toggle strikethrough style
                if (this.checked) {
                    taskTitle.classList.add('text-decoration-line-through');
                    taskItem.classList.remove('task-status-pending', 'task-status-in-progress');
                    taskItem.classList.add('task-status-completed');
                } else {
                    taskTitle.classList.remove('text-decoration-line-through');
                    taskItem.classList.remove('task-status-completed');
                    taskItem.classList.add('task-status-pending');
                }
                
                updateTaskStatus(taskId, newStatus);
            });
        });
    }

    // Task status update from dropdown
    const taskStatusOptions = document.querySelectorAll('.task-status-option');
    if (taskStatusOptions.length > 0) {
        taskStatusOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                const taskId = this.getAttribute('data-task-id');
                const newStatus = this.getAttribute('data-status');
                updateTaskStatus(taskId, newStatus);
            });
        });
    }

    // Task detail modal
    const taskDetailLinks = document.querySelectorAll('.view-more-link');
    if (taskDetailLinks.length > 0) {
        taskDetailLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const taskId = this.getAttribute('data-task-id');
                const taskTitle = this.getAttribute('data-task-title');
                const taskDescription = this.getAttribute('data-task-description');
                
                document.getElementById('taskDetailTitle').textContent = taskTitle;
                document.getElementById('taskDetailDescription').textContent = taskDescription;
                
                // Add edit button functionality
                const editBtn = document.querySelector('.task-detail-edit');
                if (editBtn) {
                    editBtn.setAttribute('data-task-id', taskId);
                    editBtn.addEventListener('click', function() {
                        // Close the detail modal and open edit modal
                        const detailModal = bootstrap.Modal.getInstance(document.getElementById('taskDetailModal'));
                        detailModal.hide();
                        
                        // You would implement task editing functionality here
                        showNotification('Edit task functionality will be implemented soon', 'info');
                    });
                }
            });
        });
    }

    // Function to filter tasks
    function filterTasks(filter) {
        const taskItems = document.querySelectorAll('.task-item');
        let visibleCount = 0;
        
        taskItems.forEach(task => {
            const taskStatus = task.getAttribute('data-task-status');
            
            if (filter === 'all' || taskStatus === filter) {
                task.style.display = '';
                visibleCount++;
            } else {
                task.style.display = 'none';
            }
        });
        
        // Update the task count display
        const taskCount = document.getElementById('taskCount');
        if (taskCount) {
            taskCount.textContent = visibleCount;
        }
    }

    // Function to sort tasks
    function sortTasks(sortBy) {
        const taskList = document.querySelector('.task-list');
        const tasks = Array.from(document.querySelectorAll('.task-item'));
        
        tasks.sort((a, b) => {
            if (sortBy === 'newest') {
                // Sort by creation date, newest first
                const dateA = new Date(a.querySelector('.task-meta-item:nth-child(3) .small').textContent);
                const dateB = new Date(b.querySelector('.task-meta-item:nth-child(3) .small').textContent);
                return dateB - dateA;
            } else if (sortBy === 'oldest') {
                // Sort by creation date, oldest first
                const dateA = new Date(a.querySelector('.task-meta-item:nth-child(3) .small').textContent);
                const dateB = new Date(b.querySelector('.task-meta-item:nth-child(3) .small').textContent);
                return dateA - dateB;
            } else if (sortBy === 'due-date') {
                // Sort by due date
                const dueDateElemA = a.querySelector('.task-meta-item:nth-child(2) .small');
                const dueDateElemB = b.querySelector('.task-meta-item:nth-child(2) .small');
                
                // If no due date, put at the end
                if (!dueDateElemA) return 1;
                if (!dueDateElemB) return -1;
                
                const dateA = new Date(dueDateElemA.textContent);
                const dateB = new Date(dueDateElemB.textContent);
                
                return dateA - dateB;
            }
        });
        
        // Remove all tasks
        tasks.forEach(task => task.remove());
        
        // Add sorted tasks back
        tasks.forEach(task => taskList.appendChild(task));
        
        // Apply current filter
        const activeFilter = document.querySelector('.task-filter.active');
        if (activeFilter) {
            filterTasks(activeFilter.getAttribute('data-filter'));
        }
    }

    // Function to update task status via AJAX
    function updateTaskStatus(taskId, status) {
        // Create form data for the request
        const formData = new FormData();
        formData.append('task_id', taskId);
        formData.append('status', status);
        formData.append('action', 'update_task_status');
        
        // Get current project ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const projectId = urlParams.get('project_id');
        formData.append('project_id', projectId);
        
        // Send AJAX request
        fetch('index.php?page=projects', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const taskItem = document.querySelector(`.task-item[data-task-id="${taskId}"]`);
                if (taskItem) {
                    // Remove all status classes
                    taskItem.classList.remove('task-status-pending', 'task-status-in-progress', 'task-status-completed');
                    // Add new status class
                    taskItem.classList.add(`task-status-${status}`);
                    // Update data attribute
                    taskItem.setAttribute('data-task-status', status);
                    
                    // Update badge
                    const statusBadge = taskItem.querySelector('.task-meta-item:last-child .badge');
                    if (statusBadge) {
                        // Remove all status classes
                        statusBadge.classList.remove('bg-secondary', 'bg-warning', 'bg-success');
                        
                        // Add new status class
                        if (status === 'completed') {
                            statusBadge.classList.add('bg-success');
                        } else if (status === 'in-progress') {
                            statusBadge.classList.add('bg-warning');
                        } else {
                            statusBadge.classList.add('bg-secondary');
                        }
                        
                        // Update text
                        statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    }
                    
                    // Update task title (strikethrough for completed)
                    const taskTitle = taskItem.querySelector('.task-title');
                    if (taskTitle) {
                        if (status === 'completed') {
                            taskTitle.classList.add('text-decoration-line-through');
                        } else {
                            taskTitle.classList.remove('text-decoration-line-through');
                        }
                    }
                    
                    // Update checkbox
                    const checkbox = taskItem.querySelector('.task-check');
                    if (checkbox) {
                        checkbox.checked = status === 'completed';
                    }
                }
                
                // Show success message
                showNotification('Task updated successfully', 'success');
                
                // Reapply current filter
                const activeFilter = document.querySelector('.task-filter.active');
                if (activeFilter) {
                    filterTasks(activeFilter.getAttribute('data-filter'));
                }
            } else {
                // Show error message
                showNotification('Failed to update task status', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'danger');
        });
    }
});
</script>