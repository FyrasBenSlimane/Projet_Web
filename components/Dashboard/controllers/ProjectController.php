<?php
/**
 * Project Controller
 * Handles all project operations
 */
class ProjectController {
    private $projectModel;
    private $userModel;

    public function __construct() {
        require_once __DIR__ . '/../models/ProjectModel.php';
        require_once __DIR__ . '/../models/UserModel.php';
        
        // Get database connection
        require_once __DIR__ . '/../../../config/database.php';
        $db = $GLOBALS['pdo'];
        
        $this->projectModel = new ProjectModel($db);
        $this->userModel = new UserModel($db);
    }

    /**
     * Display user's projects
     */
    public function userProjects() {
        // Get logged in user
        $userEmail = $_SESSION['user']['email'] ?? null;
        if (!$userEmail) {
            header('Location: ../Login/login.php');
            exit;
        }

        // Set page title and content variables used in layout.php
        $pageTitle = 'My Projects';
        
        // Get user projects from the model
        $projects = $this->projectModel->getUserProjects($userEmail);
        
        // Set current view for the projects page
        $currentView = isset($_GET['project_id']) ? 'view_project' : (isset($_GET['new']) ? 'new_project' : 'list_projects');
        
        // Get single project details if viewing a specific project
        $selectedProject = null;
        $projectTasks = [];
        if (isset($_GET['project_id']) && !empty($_GET['project_id'])) {
            $projectId = $_GET['project_id'];
            $selectedProject = $this->projectModel->getProjectById($projectId, $userEmail);
            if ($selectedProject) {
                $projectTasks = $this->projectModel->getProjectTasks($projectId);
            }
        }
        
        // Make the project model available to the view
        $projectModel = $this->projectModel;
        
        // Load view - this will output the content that gets captured by ob_get_clean() in index.php
        include __DIR__ . '/../projects/project.php';
    }

    /**
     * Process project creation
     */
    public function createProject() {
        // Get logged in user
        $userEmail = $_SESSION['user']['email'] ?? null;
        if (!$userEmail) {
            header('Location: ../Login/login.php');
            exit;
        }

        // Validate and sanitize input
        $title = htmlspecialchars($_POST['title'] ?? '');
        $description = htmlspecialchars($_POST['description'] ?? '');
        $clientName = htmlspecialchars($_POST['client_name'] ?? '');
        $budget = filter_var($_POST['budget'] ?? 0, FILTER_VALIDATE_FLOAT);
        $priority = htmlspecialchars($_POST['priority'] ?? 'medium');
        $startDate = $_POST['start_date'] ?? null;
        $endDate = $_POST['end_date'] ?? null;
        
        $errors = [];
        if (empty($title)) {
            $errors[] = "Title is required";
        }
        if (empty($description)) {
            $errors[] = "Description is required";
        }
        
        if (!empty($errors)) {
            // If there are errors, go back to form
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?page=projects&new=true');
            exit;
        }
        
        // Create project
        $result = $this->projectModel->createProject($userEmail, $title, $description, $clientName, $budget, $priority, $startDate, $endDate);
        
        if ($result) {
            $_SESSION['success'] = "Your project has been created successfully";
        } else {
            $_SESSION['errors'] = ["Failed to create your project. Please try again."];
        }
        
        header('Location: index.php?page=projects');
        exit;
    }

    /**
     * Process project update
     */
    public function updateProject() {
        // Get logged in user
        $userEmail = $_SESSION['user']['email'] ?? null;
        if (!$userEmail) {
            header('Location: ../Login/login.php');
            exit;
        }

        // Get project ID
        $projectId = $_POST['project_id'] ?? '';
        if (empty($projectId)) {
            $_SESSION['errors'] = ["Project ID is required"];
            header('Location: index.php?page=projects');
            exit;
        }
        
        // Get project data
        $data = [
            'title' => htmlspecialchars($_POST['title'] ?? ''),
            'description' => htmlspecialchars($_POST['description'] ?? ''),
            'client_name' => htmlspecialchars($_POST['client_name'] ?? ''),
            'budget' => filter_var($_POST['budget'] ?? 0, FILTER_VALIDATE_FLOAT),
            'priority' => htmlspecialchars($_POST['priority'] ?? 'medium'),
            'status' => htmlspecialchars($_POST['status'] ?? 'pending'),
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null
        ];
        
        // Update project
        $result = $this->projectModel->updateProject($projectId, $userEmail, $data);
        
        if ($result) {
            $_SESSION['success'] = "Project updated successfully";
        } else {
            $_SESSION['errors'] = ["Failed to update project. Please try again."];
        }
        
        header('Location: index.php?page=projects&project_id=' . $projectId);
        exit;
    }

    /**
     * Process project deletion
     */
    public function deleteProject() {
        // Get logged in user
        $userEmail = $_SESSION['user']['email'] ?? null;
        if (!$userEmail) {
            header('Location: ../Login/login.php');
            exit;
        }

        // Get project ID
        $projectId = $_POST['project_id'] ?? '';
        if (empty($projectId)) {
            $_SESSION['errors'] = ["Project ID is required"];
            header('Location: index.php?page=projects');
            exit;
        }
        
        // Delete project
        $result = $this->projectModel->deleteProject($projectId, $userEmail);
        
        if ($result) {
            $_SESSION['success'] = "Project deleted successfully";
        } else {
            $_SESSION['errors'] = ["Failed to delete project. Please try again."];
        }
        
        header('Location: index.php?page=projects');
        exit;
    }

    /**
     * Process adding a task to a project
     */
    public function addTask() {
        // Get logged in user
        $userEmail = $_SESSION['user']['email'] ?? null;
        if (!$userEmail) {
            header('Location: ../Login/login.php');
            exit;
        }

        // Get project ID
        $projectId = $_POST['project_id'] ?? '';
        if (empty($projectId)) {
            $_SESSION['errors'] = ["Project ID is required"];
            header('Location: index.php?page=projects');
            exit;
        }
        
        // Check if the project belongs to the user
        $project = $this->projectModel->getProjectById($projectId, $userEmail);
        if (!$project) {
            $_SESSION['errors'] = ["Project not found or you don't have permission to access it"];
            header('Location: index.php?page=projects');
            exit;
        }
        
        // Get task data
        $title = htmlspecialchars($_POST['task_title'] ?? '');
        $description = htmlspecialchars($_POST['task_description'] ?? '');
        $assignedTo = htmlspecialchars($_POST['assigned_to'] ?? '');
        $dueDate = $_POST['due_date'] ?? null;
        
        if (empty($title)) {
            $_SESSION['errors'] = ["Task title is required"];
            header('Location: index.php?page=projects&project_id=' . $projectId);
            exit;
        }
        
        // Add task
        $result = $this->projectModel->addTask($projectId, $title, $description, $assignedTo, $dueDate);
        
        if ($result) {
            $_SESSION['success'] = "Task added successfully";
        } else {
            $_SESSION['errors'] = ["Failed to add task. Please try again."];
        }
        
        header('Location: index.php?page=projects&project_id=' . $projectId);
        exit;
    }

    /**
     * Update project status via AJAX
     */
    public function updateProjectStatus() {
        // Set response header
        header('Content-Type: application/json');
        
        // Initialize response array
        $response = [
            'success' => false,
            'message' => 'Invalid request'
        ];
        
        // Get logged in user
        $userEmail = $_SESSION['user']['email'] ?? null;
        if (!$userEmail) {
            $response['message'] = 'User not authenticated';
            echo json_encode($response);
            exit;
        }
        
        // Get request data
        $projectId = $_POST['project_id'] ?? null;
        $status = $_POST['status'] ?? null;
        
        if (!$projectId || !$status || !in_array($status, ['pending', 'in-progress', 'completed', 'cancelled'])) {
            $response['message'] = 'Invalid request data';
            echo json_encode($response);
            exit;
        }
        
        // Update project status
        $result = $this->projectModel->updateProjectStatus($projectId, $status, $userEmail);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Project status updated successfully';
        } else {
            $response['message'] = 'Failed to update project status';
        }
        
        echo json_encode($response);
        exit;
    }

    /**
     * Delete project via AJAX
     */
    public function ajaxDeleteProject() {
        // Set response header
        header('Content-Type: application/json');
        
        // Initialize response array
        $response = [
            'success' => false,
            'message' => 'Invalid request'
        ];
        
        // Get logged in user
        $userEmail = $_SESSION['user']['email'] ?? null;
        if (!$userEmail) {
            $response['message'] = 'User not authenticated';
            echo json_encode($response);
            exit;
        }
        
        // Get request data
        $projectId = $_POST['project_id'] ?? null;
        
        if (!$projectId) {
            $response['message'] = 'Project ID is required';
            echo json_encode($response);
            exit;
        }
        
        // Delete project
        $result = $this->projectModel->deleteProject($projectId, $userEmail);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Project deleted successfully';
        } else {
            $response['message'] = 'Failed to delete project';
        }
        
        echo json_encode($response);
        exit;
    }
}