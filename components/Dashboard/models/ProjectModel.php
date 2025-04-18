<?php
/**
 * Project Model
 * Handles all project data operations
 */
class ProjectModel {
    private $db;

    public function __construct($db = null) {
        if ($db) {
            $this->db = $db;
        } else {
            // Get database connection
            require_once __DIR__ . '/../../../config/database.php';
            $this->db = $GLOBALS['pdo'] ?? getDBConnection();
        }
    }

    /**
     * Get all projects for a user
     * 
     * @param string $userEmail User email
     * @return array Array of projects
     */
    public function getUserProjects($userEmail) {
        $sql = "SELECT * FROM projects WHERE user_email = ? ORDER BY updated_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userEmail]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single project by ID
     * 
     * @param int $projectId Project ID
     * @param string $userEmail User email (for permission checking)
     * @return array|bool Project data or false if not found
     */
    public function getProjectById($projectId, $userEmail) {
        $sql = "SELECT * FROM projects WHERE id = ? AND user_email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$projectId, $userEmail]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new project
     * 
     * @param string $userEmail User email
     * @param string $title Project title
     * @param string $description Project description
     * @param string $clientName Client name
     * @param float $budget Project budget
     * @param string $priority Project priority
     * @param string $startDate Project start date
     * @param string $endDate Project end date
     * @return int|bool New project ID or false on failure
     */
    public function createProject($userEmail, $title, $description, $clientName, $budget, $priority = 'medium', $startDate = null, $endDate = null) {
        $sql = "INSERT INTO projects (title, description, user_email, client_name, budget, priority, start_date, end_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$title, $description, $userEmail, $clientName, $budget, $priority, $startDate, $endDate]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Update a project
     * 
     * @param int $projectId Project ID
     * @param string $userEmail User email (for permission checking)
     * @param array $data Project data to update
     * @return bool True on success, false on failure
     */
    public function updateProject($projectId, $userEmail, $data) {
        // First check if the project exists and belongs to the user
        $project = $this->getProjectById($projectId, $userEmail);
        if (!$project) {
            return false;
        }
        
        // Build the SQL query dynamically based on the provided data
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, ['title', 'description', 'client_name', 'budget', 'status', 'priority', 'start_date', 'end_date'])) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        if (empty($fields)) {
            return false; // No valid fields to update
        }
        
        // Add the project ID and user email to the values array
        $values[] = $projectId;
        $values[] = $userEmail;
        
        $sql = "UPDATE projects SET " . implode(', ', $fields) . " WHERE id = ? AND user_email = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Delete a project
     * 
     * @param int $projectId Project ID
     * @param string $userEmail User email (for permission checking)
     * @return bool True on success, false on failure
     */
    public function deleteProject($projectId, $userEmail) {
        $sql = "DELETE FROM projects WHERE id = ? AND user_email = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$projectId, $userEmail]);
    }

    /**
     * Change project status
     * 
     * @param int $projectId Project ID
     * @param string $status New status
     * @param string $userEmail User email (for permission checking)
     * @return bool True on success, false on failure
     */
    public function updateProjectStatus($projectId, $status, $userEmail) {
        $sql = "UPDATE projects SET status = ? WHERE id = ? AND user_email = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $projectId, $userEmail]);
    }

    /**
     * Add a task to a project
     * 
     * @param int $projectId Project ID
     * @param string $title Task title
     * @param string $description Task description
     * @param string $assignedTo Email of user assigned to the task
     * @param string $dueDate Due date for the task
     * @return int|bool New task ID or false on failure
     */
    public function addTask($projectId, $title, $description, $assignedTo = null, $dueDate = null) {
        $sql = "INSERT INTO project_tasks (project_id, title, description, assigned_to, due_date) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$projectId, $title, $description, $assignedTo, $dueDate]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Get tasks for a project
     * 
     * @param int $projectId Project ID
     * @return array Array of tasks
     */
    public function getProjectTasks($projectId) {
        $sql = "SELECT * FROM project_tasks WHERE project_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get project statistics for a user
     * 
     * @param string $userEmail User email
     * @return array Project statistics
     */
    public function getProjectStats($userEmail) {
        // Total projects
        $sql = "SELECT COUNT(*) as total FROM projects WHERE user_email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userEmail]);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Pending projects
        $sql = "SELECT COUNT(*) as pending FROM projects WHERE user_email = ? AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userEmail]);
        $pending = $stmt->fetch(PDO::FETCH_ASSOC)['pending'] ?? 0;
        
        // In-progress projects
        $sql = "SELECT COUNT(*) as in_progress FROM projects WHERE user_email = ? AND status = 'in-progress'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userEmail]);
        $inProgress = $stmt->fetch(PDO::FETCH_ASSOC)['in_progress'] ?? 0;
        
        // Completed projects
        $sql = "SELECT COUNT(*) as completed FROM projects WHERE user_email = ? AND status = 'completed'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userEmail]);
        $completed = $stmt->fetch(PDO::FETCH_ASSOC)['completed'] ?? 0;
        
        // Total budget
        $sql = "SELECT SUM(budget) as total_budget FROM projects WHERE user_email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userEmail]);
        $totalBudget = $stmt->fetch(PDO::FETCH_ASSOC)['total_budget'] ?? 0;
        
        return [
            'total' => $total,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'total_budget' => $totalBudget
        ];
    }
}