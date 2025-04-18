<?php
/**
 * Support Controller
 * Handles all support ticket operations
 */
class SupportController {
    private $supportModel;
    private $userModel;

    public function __construct() {
        require_once __DIR__ . '/../models/SupportModel.php';
        require_once __DIR__ . '/../models/UserModel.php';
        
        // Get database connection
        require_once __DIR__ . '/../../../config/database.php';
        $db = $GLOBALS['pdo'];
        
        $this->supportModel = new SupportModel($db);
        $this->userModel = new UserModel($db);
    }

    /**
     * Display user's tickets
     */
    public function userTickets() {
        // Get logged in user
        $userEmail = $_SESSION['user']['email'] ?? null;
        if (!$userEmail) {
            header('Location: ../Login/login.php');
            exit;
        }

        // Set page title and content variables used in layout.php
        $pageTitle = 'Support Tickets';
        
        // Get user tickets from the model
        $tickets = $this->supportModel->getUserTickets($userEmail);
        
        // Set current view for the support page
        $currentView = isset($_GET['ticket_id']) ? 'view_ticket' : (isset($_GET['new']) ? 'new_ticket' : 'list_tickets');
        
        // Get single ticket details if viewing a specific ticket
        $selectedTicket = null;
        $ticketReplies = [];
        if (isset($_GET['ticket_id']) && !empty($_GET['ticket_id'])) {
            $ticketId = $_GET['ticket_id'];
            $selectedTicket = $this->supportModel->getTicketById($ticketId, $userEmail);
            if ($selectedTicket) {
                $ticketReplies = $this->supportModel->getTicketReplies($ticketId);
            }
        }
        
        // Define ticket categories
        $categories = [
            'technical' => 'Technical Support',
            'billing' => 'Billing & Payments',
            'account' => 'Account Issues',
            'feature' => 'Feature Requests',
            'other' => 'Other Inquiries'
        ];
        
        // Make the support model available to the view
        $supportModel = $this->supportModel;
        
        // Load view - this will output the content that gets captured by ob_get_clean() in index.php
        include __DIR__ . '/../support/support.php';
    }

    /**
     * Display ticket creation form
     */
    public function createTicketForm() {
        // Get logged in user
        $userEmail = $_SESSION['user']['email'] ?? null;
        if (!$userEmail) {
            header('Location: ../Login/login.php');
            exit;
        }
        
        // Load view
        include __DIR__ . '/../support/create-ticket.php';
    }

    /**
     * Process ticket creation
     */
    public function createTicket() {
        // Get logged in user
        $userEmail = $_SESSION['user']['email'] ?? null;
        if (!$userEmail) {
            header('Location: ../Login/login.php');
            exit;
        }

        // Validate and sanitize input
        $subject = htmlspecialchars($_POST['subject'] ?? '');
        $description = htmlspecialchars($_POST['description'] ?? '');
        $category = htmlspecialchars($_POST['category'] ?? 'General');
        $priority = htmlspecialchars($_POST['priority'] ?? 'medium');
        
        $errors = [];
        if (empty($subject)) {
            $errors[] = "Subject is required";
        }
        if (empty($description)) {
            $errors[] = "Description is required";
        }
        
        if (!empty($errors)) {
            // If there are errors, go back to form
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?page=support');
            exit;
        }
        
        // Create ticket
        $result = $this->supportModel->createTicket($userEmail, $subject, $description, $category, $priority);
        
        if ($result) {
            $_SESSION['success'] = "Your support ticket has been submitted successfully";
        } else {
            $_SESSION['errors'] = ["Failed to submit your ticket. Please try again."];
        }
        
        header('Location: index.php?page=support');
        exit;
    }

    /**
     * Display admin tickets panel
     */
    public function adminTickets() {
        // Check if admin
        $userEmail = $_SESSION['user']['email'] ?? null;
        $user = $this->userModel->getUserByEmail($userEmail);
        
        if (!$userEmail || $user['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }
        
        // For admin, we need to get all tickets from all users
        $sql = "SELECT * FROM support_tickets ORDER BY updated_at DESC";
        $stmt = $this->supportModel->db->prepare($sql);
        $stmt->execute();
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Load view
        include __DIR__ . '/../support/admin-tickets.php';
    }

    /**
     * View specific ticket
     */
    public function viewTicket() {
        // Get logged in user
        $userEmail = $_SESSION['user']['email'] ?? null;
        if (!$userEmail) {
            header('Location: ../Login/login.php');
            exit;
        }
        
        $ticketId = $_GET['id'] ?? null;
        if (!$ticketId) {
            header('Location: index.php?page=support');
            exit;
        }
        
        // Get ticket
        $ticket = $this->supportModel->getTicketById($ticketId, $userEmail);
        
        if (!$ticket) {
            $_SESSION['errors'] = ["Ticket not found or you don't have permission to view it"];
            header('Location: index.php?page=support');
            exit;
        }
        
        // Get replies for this ticket
        $replies = $this->supportModel->getTicketReplies($ticketId);
        
        // Load view
        include __DIR__ . '/../support/view-ticket.php';
    }

    /**
     * Add a reply to a ticket
     */
    public function addReply() {
        // Get logged in user
        $userEmail = $_SESSION['user']['email'] ?? null;
        if (!$userEmail) {
            header('Location: ../Login/login.php');
            exit;
        }
        
        $ticketId = $_POST['ticket_id'] ?? null;
        $message = htmlspecialchars($_POST['message'] ?? '');
        
        if (!$ticketId || empty($message)) {
            $_SESSION['errors'] = ["Message cannot be empty"];
            header('Location: index.php?page=support&action=view&id=' . $ticketId);
            exit;
        }
        
        // Check if user is admin
        $user = $this->userModel->getUserByEmail($userEmail);
        $isAdmin = ($user['role'] === 'admin');
        
        // Add reply
        $result = $this->supportModel->addReply($ticketId, $userEmail, $message, $isAdmin);
        
        if ($result) {
            $_SESSION['success'] = "Your reply has been submitted";
        } else {
            $_SESSION['errors'] = ["Failed to submit your reply"];
        }
        
        header('Location: index.php?page=support&action=view&id=' . $ticketId);
        exit;
    }
    
    /**
     * Close a ticket
     */
    public function closeTicket() {
        // Get logged in user
        $userEmail = $_SESSION['user']['email'] ?? null;
        if (!$userEmail) {
            header('Location: ../Login/login.php');
            exit;
        }
        
        $ticketId = $_GET['id'] ?? null;
        if (!$ticketId) {
            header('Location: index.php?page=support');
            exit;
        }
        
        // Close ticket
        $result = $this->supportModel->closeTicket($ticketId, $userEmail);
        
        if ($result) {
            $_SESSION['success'] = "Ticket has been closed";
        } else {
            $_SESSION['errors'] = ["Failed to close ticket or you don't have permission"];
        }
        
        header('Location: index.php?page=support');
        exit;
    }
    
    /**
     * Reopen a ticket
     */
    public function reopenTicket() {
        // Get logged in user
        $userEmail = $_SESSION['user']['email'] ?? null;
        if (!$userEmail) {
            header('Location: ../Login/login.php');
            exit;
        }
        
        $ticketId = $_GET['id'] ?? null;
        if (!$ticketId) {
            header('Location: index.php?page=support');
            exit;
        }
        
        // Reopen ticket
        $result = $this->supportModel->reopenTicket($ticketId, $userEmail);
        
        if ($result) {
            $_SESSION['success'] = "Ticket has been reopened";
        } else {
            $_SESSION['errors'] = ["Failed to reopen ticket or you don't have permission"];
        }
        
        header('Location: index.php?page=support');
        exit;
    }

    /**
     * Delete a ticket
     */
    public function deleteTicket() {
        // Get logged in user
        $userEmail = $_SESSION['user']['email'] ?? null;
        if (!$userEmail) {
            header('Location: ../Login/login.php');
            exit;
        }
        
        $ticketId = $_GET['id'] ?? null;
        if (!$ticketId) {
            header('Location: index.php?page=support-tickets');
            exit;
        }
        
        // Delete ticket
        $result = $this->supportModel->deleteTicket($ticketId, $userEmail);
        
        if ($result) {
            $_SESSION['success'] = "Ticket has been deleted";
        } else {
            $_SESSION['errors'] = ["Failed to delete ticket or you don't have permission"];
        }
        
        header('Location: index.php?page=support-tickets');
        exit;
    }

    /**
     * Update ticket status via AJAX
     * Returns JSON response for asynchronous UI updates
     */
    public function updateTicketStatus() {
        // Check for AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        $response = ['success' => false, 'message' => ''];
        
        // Get user email and check if logged in
        $userEmail = $_SESSION['user']['email'] ?? null;
        if (!$userEmail) {
            $response['message'] = 'User not authenticated';
            echo json_encode($response);
            exit;
        }
        
        // Get request data
        $ticketId = $_POST['ticket_id'] ?? null;
        $status = $_POST['status'] ?? null;
        
        if (!$ticketId || !$status || !in_array($status, ['open', 'pending', 'closed'])) {
            $response['message'] = 'Invalid request data';
            echo json_encode($response);
            exit;
        }
        
        // Update ticket status based on the requested status
        $result = false;
        if ($status === 'open') {
            $result = $this->supportModel->reopenTicket($ticketId, $userEmail);
        } elseif ($status === 'closed') {
            $result = $this->supportModel->closeTicket($ticketId, $userEmail);
        } else {
            // Update to pending status
            $result = $this->supportModel->updateTicketStatus($ticketId, $status, $userEmail);
        }
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Ticket status updated successfully';
        } else {
            $response['message'] = 'Failed to update ticket status';
        }
        
        echo json_encode($response);
        exit;
    }
    
    /**
     * Handle AJAX deletion of tickets
     * Returns JSON response for asynchronous UI updates
     */
    public function ajaxDeleteTicket() {
        // Check for AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        $response = ['success' => false, 'message' => ''];
        
        // Get user email and check if logged in
        $userEmail = $_SESSION['user']['email'] ?? null;
        if (!$userEmail) {
            $response['message'] = 'User not authenticated';
            echo json_encode($response);
            exit;
        }
        
        // Get request data
        $ticketId = $_POST['ticket_id'] ?? null;
        
        if (!$ticketId) {
            $response['message'] = 'Invalid ticket ID';
            echo json_encode($response);
            exit;
        }
        
        // Delete the ticket
        $result = $this->supportModel->deleteTicket($ticketId, $userEmail);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Ticket deleted successfully';
        } else {
            $response['message'] = 'Failed to delete ticket';
        }
        
        echo json_encode($response);
        exit;
    }
}
?>