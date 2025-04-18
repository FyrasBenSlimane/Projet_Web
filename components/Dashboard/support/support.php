<?php
/**
 * Support Tickets Dashboard View
 * Allows users to view, create and manage their support tickets
 */

// Start output buffering to capture content for the layout
ob_start();

// Variables should already be set by the controller:
// - $tickets: All user tickets
// - $currentView: The current view (list_tickets, new_ticket, view_ticket)
// - $selectedTicket: The selected ticket (if viewing a ticket)
// - $ticketReplies: Replies for the selected ticket
// - $categories: Ticket categories
// - $userEmail: The current user's email
// - $supportModel: The support model instance

// Define message variables for displaying alerts
$message = '';
$alertType = '';

// Handle form submissions - this would typically be in the controller
// but we'll keep it here for now to maintain compatibility
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if supportModel is not defined (happens when form is submitted directly)
    if (!isset($supportModel) || $supportModel === null) {
        // Get database connection and initialize supportModel
        require_once __DIR__ . '/../../../config/database.php';
        $pdo = $GLOBALS['pdo'] ?? getDBConnection();
        require_once __DIR__ . '/../models/SupportModel.php';
        $supportModel = new SupportModel($pdo);
    }
    
    // Create new ticket
    if (isset($_POST['action']) && $_POST['action'] === 'create_ticket') {
        $subject = $_POST['subject'] ?? '';
        $category = $_POST['category'] ?? '';
        $priority = $_POST['priority'] ?? 'medium';
        $description = $_POST['description'] ?? '';
        
        if (empty($subject) || empty($description) || empty($category)) {
            $message = 'Please fill all required fields.';
            $alertType = 'danger';
        } else {
            $result = $supportModel->createTicket($userEmail, $subject, $description, $category, $priority);
            if ($result) {
                $message = 'Ticket created successfully.';
                $alertType = 'success';
            } else {
                $message = 'Failed to create ticket. Please try again.';
                $alertType = 'danger';
            }
        }
    }

    // Add reply to ticket
    if (isset($_POST['action']) && $_POST['action'] === 'add_reply') {
        $ticketId = $_POST['ticket_id'] ?? '';
        $reply = $_POST['reply'] ?? '';
        
        if (empty($reply)) {
            $message = 'Reply cannot be empty.';
            $alertType = 'danger';
        } else {
            $result = $supportModel->addReply($ticketId, $userEmail, $reply);
            if ($result) {
                $message = 'Reply added successfully.';
                $alertType = 'success';
            } else {
                $message = 'Failed to add reply. Please try again.';
                $alertType = 'danger';
            }
        }
    }

    // Close ticket
    if (isset($_POST['action']) && $_POST['action'] === 'close_ticket') {
        $ticketId = $_POST['ticket_id'] ?? '';
        
        if (empty($ticketId)) {
            $message = 'Invalid ticket.';
            $alertType = 'danger';
        } else {
            $result = $supportModel->closeTicket($ticketId, $userEmail);
            if ($result) {
                $message = 'Ticket closed successfully.';
                $alertType = 'success';
            } else {
                $message = 'Failed to close ticket. Please try again.';
                $alertType = 'danger';
            }
        }
    }

    // Reopen ticket
    if (isset($_POST['action']) && $_POST['action'] === 'reopen_ticket') {
        $ticketId = $_POST['ticket_id'] ?? '';
        
        if (empty($ticketId)) {
            $message = 'Invalid ticket.';
            $alertType = 'danger';
        } else {
            $result = $supportModel->reopenTicket($ticketId, $userEmail);
            if ($result) {
                $message = 'Ticket reopened successfully.';
                $alertType = 'success';
            } else {
                $message = 'Failed to reopen ticket. Please try again.';
                $alertType = 'danger';
            }
        }
    }

    // Delete ticket
    if (isset($_POST['action']) && $_POST['action'] === 'delete_ticket') {
        $ticketId = $_POST['ticket_id'] ?? '';
        
        if (empty($ticketId)) {
            $message = 'Invalid ticket.';
            $alertType = 'danger';
        } else {
            $result = $supportModel->deleteTicket($ticketId, $userEmail);
            if ($result) {
                $message = 'Ticket deleted successfully.';
                $alertType = 'success';
            } else {
                $message = 'Failed to delete ticket. Please try again.';
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

<!-- Support Tickets Dashboard Content -->
<div class="container-fluid py-4">
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if ($currentView === 'list_tickets'): ?>
    <!-- Tickets List View - Enhanced UI -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="support-header">
                    <h5 class="support-title"><i class="bi bi-ticket-perforated"></i> My Support Tickets</h5>
                    <a href="?page=support-tickets&new=true" class="create-ticket-btn">
                        <i class="bi bi-plus-circle"></i> New Ticket
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($tickets)): ?>
                    <div class="no-tickets">
                        <i class="bi bi-ticket-detailed-fill no-tickets-icon"></i>
                        <h4>No Support Tickets</h4>
                        <p>You haven't created any support tickets yet. If you need assistance with anything, feel free to create your first ticket.</p>
                        <a href="?page=support-tickets&new=true" class="create-ticket-btn">
                            <i class="bi bi-plus-circle"></i> Create Your First Ticket
                        </a>
                    </div>
                    <?php else: ?>
                    <!-- Card-based ticket layout -->
                    <div class="tickets-container" id="tickets-container">
                        <?php foreach ($tickets as $index => $ticket): ?>
                        <div class="ticket-card" id="ticket-<?php echo $ticket['id']; ?>">
                            <div class="ticket-header">
                                <h5 class="ticket-title">
                                    <a href="?page=support-tickets&ticket_id=<?php echo $ticket['id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($ticket['subject']); ?>
                                    </a>
                                </h5>
                                <div class="ticket-badges">
                                    <?php
                                    $priorityBadge = 'badge-info';
                                    if ($ticket['priority'] === 'high') {
                                        $priorityBadge = 'badge-danger';
                                    } elseif ($ticket['priority'] === 'medium') {
                                        $priorityBadge = 'badge-warning';
                                    }
                                    
                                    $statusBadge = 'badge-success';
                                    if ($ticket['status'] === 'pending') {
                                        $statusBadge = 'badge-warning';
                                    } elseif ($ticket['status'] === 'closed') {
                                        $statusBadge = 'badge-secondary';
                                    }
                                    ?>
                                    <span class="ticket-badge status-badge <?php echo $statusBadge; ?>">
                                        <span class="status-text"><?php echo ucfirst($ticket['status']); ?></span>
                                        <div class="status-dropdown dropdown">
                                            <button class="btn btn-sm dropdown-toggle p-0 ms-1" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-chevron-down"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item status-option" data-ticket-id="<?php echo $ticket['id']; ?>" data-status="open" href="#">Open</a></li>
                                                <li><a class="dropdown-item status-option" data-ticket-id="<?php echo $ticket['id']; ?>" data-status="pending" href="#">Pending</a></li>
                                                <li><a class="dropdown-item status-option" data-ticket-id="<?php echo $ticket['id']; ?>" data-status="closed" href="#">Closed</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger delete-ticket" data-ticket-id="<?php echo $ticket['id']; ?>" href="#">Delete</a></li>
                                            </ul>
                                        </div>
                                    </span>
                                    <span class="ticket-badge <?php echo $priorityBadge; ?>">
                                        <?php echo ucfirst($ticket['priority']); ?>
                                    </span>
                                    <span class="ticket-badge badge-primary">
                                        <?php echo $categories[$ticket['category']] ?? ucfirst($ticket['category']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="ticket-content">
                                <p class="ticket-excerpt">
                                    <?php 
                                    // Display a short excerpt of the description
                                    $excerpt = strlen($ticket['description']) > 120 ? 
                                        substr($ticket['description'], 0, 120) . '...' : 
                                        $ticket['description'];
                                    echo htmlspecialchars($excerpt); 
                                    ?>
                                </p>
                            </div>
                            <div class="ticket-footer">
                                <div class="ticket-meta">
                                    <div class="ticket-date">
                                        <i class="bi bi-calendar-event"></i> 
                                        <span>Created: <?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></span>
                                    </div>
                                    <div class="ticket-date">
                                        <i class="bi bi-clock-history"></i> 
                                        <span>Updated: <?php echo date('M d, Y', strtotime($ticket['updated_at'])); ?></span>
                                    </div>
                                </div>
                                <div class="ticket-actions">
                                    <a href="?page=support-tickets&ticket_id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-outline-primary" title="View Ticket">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger delete-ticket" data-ticket-id="<?php echo $ticket['id']; ?>" title="Delete Ticket">
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
    
    <?php elseif ($currentView === 'new_ticket'): ?>
    <!-- Enhanced New Ticket Form -->
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <div class="card ticket-form-card">
                <div class="card-header ticket-form-header">
                    <h5 class="ticket-form-title"><i class="bi bi-ticket-perforated"></i> Create New Support Ticket</h5>
                </div>
                <div class="card-body ticket-form-body">
                    <form method="post" action="">
                        <input type="hidden" name="action" value="create_ticket">
                        
                        <div class="ticket-form-section">
                            <label for="subject" class="ticket-form-label"><i class="bi bi-pencil-square"></i> Subject</label>
                            <input type="text" class="form-control ticket-form-control" id="subject" name="subject" required 
                                placeholder="Brief description of your issue">
                        </div>
                        
                        <div class="row ticket-form-section">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="category" class="ticket-form-label"><i class="bi bi-tag"></i> Category</label>
                                <select class="form-select ticket-form-control ticket-form-select" id="category" name="category" required>
                                    <option value="" disabled selected>Select a category</option>
                                    <?php foreach ($categories as $key => $value): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
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
                        </div>
                        
                        <div class="ticket-form-section">
                            <label for="description" class="ticket-form-label"><i class="bi bi-chat-left-text"></i> Description</label>
                            <textarea class="form-control ticket-form-control ticket-form-textarea" id="description" name="description" rows="6" required
                                placeholder="Please provide detailed information about your issue"></textarea>
                        </div>
                        
                        <div class="ticket-form-footer">
                            <a href="?page=support-tickets" class="btn cancel-btn">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn ticket-submit-btn">
                                <i class="bi bi-send"></i> Submit Ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php elseif ($currentView === 'view_ticket' && $selectedTicket): ?>
    <!-- Enhanced View Ticket Details -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card ticket-view-card">
                <div class="ticket-view-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="ticket-view-title"><?php echo htmlspecialchars($selectedTicket['subject']); ?></h5>
                        <div class="ticket-view-badges">
                            <span class="badge bg-<?php echo $selectedTicket['status'] === 'open' ? 'success' : ($selectedTicket['status'] === 'pending' ? 'warning' : 'secondary'); ?>">
                                <?php echo ucfirst($selectedTicket['status']); ?>
                            </span>
                            <span class="badge bg-<?php echo $selectedTicket['priority'] === 'high' ? 'danger' : ($selectedTicket['priority'] === 'medium' ? 'warning' : 'info'); ?>">
                                <?php echo ucfirst($selectedTicket['priority']); ?> Priority
                            </span>
                            <span class="badge bg-primary">
                                <?php echo $categories[$selectedTicket['category']] ?? ucfirst($selectedTicket['category']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="d-flex">
                        <form method="post" class="me-2">
                            <input type="hidden" name="ticket_id" value="<?php echo $selectedTicket['id']; ?>">
                            <input type="hidden" name="action" value="delete_ticket">
                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this ticket? This action cannot be undone.')">
                                <i class="bi bi-trash"></i> Delete Ticket
                            </button>
                        </form>
                        <a href="?page=support-tickets" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Tickets
                        </a>
                    </div>
                </div>
                <div class="ticket-view-body">
                    <div class="ticket-conversation">
                        <!-- Original Ticket -->
                        <div class="ticket-message">
                            <div class="ticket-message-header">
                                <div class="message-user">
                                    <div class="message-user-avatar">
                                        <?php echo substr($userName, 0, 1); ?>
                                    </div>
                                    <div>
                                        <div class="message-user-name"><?php echo $userName; ?></div>
                                        <div class="message-date"><?php echo date('M d, Y g:i A', strtotime($selectedTicket['created_at'])); ?></div>
                                    </div>
                                </div>
                                <span class="badge bg-secondary">Ticket Created</span>
                            </div>
                            <div class="message-content">
                                <?php echo nl2br(htmlspecialchars($selectedTicket['description'])); ?>
                            </div>
                        </div>
                        
                        <!-- Ticket Replies -->
                        <?php if (!empty($ticketReplies)): ?>
                            <?php foreach ($ticketReplies as $reply): ?>
                            <div class="ticket-message">
                                <div class="ticket-message-header">
                                    <div class="message-user">
                                        <div class="message-user-avatar" style="background-color: <?php echo $reply['is_admin'] ? '#FFC107' : '#3E5C76'; ?>">
                                            <?php echo $reply['is_admin'] ? 'S' : substr($userName, 0, 1); ?>
                                        </div>
                                        <div>
                                            <div class="message-user-name">
                                                <?php 
                                                if ($reply['user_email'] === $userEmail) {
                                                    echo $userName . ' (You)';
                                                } else {
                                                    echo $reply['is_admin'] ? 'Support Team' : $reply['user_name'];
                                                }
                                                ?>
                                            </div>
                                            <div class="message-date"><?php echo date('M d, Y g:i A', strtotime($reply['created_at'])); ?></div>
                                        </div>
                                    </div>
                                    <?php if ($reply['is_admin']): ?>
                                    <span class="badge bg-info">Support Response</span>
                                    <?php else: ?>
                                    <span class="badge bg-primary">User Reply</span>
                                    <?php endif; ?>
                                </div>
                                <div class="message-content <?php echo $reply['is_admin'] ? 'bg-light-blue' : ''; ?>">
                                    <?php echo nl2br(htmlspecialchars($reply['message'])); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Reply Form (only for open tickets) -->
                        <?php if ($selectedTicket['status'] !== 'closed'): ?>
                        <div class="reply-form mt-4 pt-4 border-top">
                            <h6 class="mb-3 ticket-form-label"><i class="bi bi-reply"></i> Add Reply</h6>
                            <form method="post" action="">
                                <input type="hidden" name="action" value="add_reply">
                                <input type="hidden" name="ticket_id" value="<?php echo $selectedTicket['id']; ?>">
                                
                                <div class="mb-3">
                                    <textarea class="form-control ticket-form-control ticket-form-textarea" id="reply" name="reply" rows="4" required
                                        placeholder="Type your reply here..."></textarea>
                                </div>
                                
                                <div class="text-end">
                                    <button type="submit" class="btn ticket-submit-btn">
                                        <i class="bi bi-send"></i> Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                        <?php else: ?>
                        <div class="mt-4 pt-4 border-top text-center">
                            <div class="alert alert-secondary">
                                <i class="bi bi-lock"></i> This ticket is closed. Reopen it to add more replies.
                            </div>
                            <form method="post" class="mt-2">
                                <input type="hidden" name="ticket_id" value="<?php echo $selectedTicket['id']; ?>">
                                <input type="hidden" name="action" value="reopen_ticket">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reopen Ticket
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php elseif ($currentView === 'view_ticket'): ?>
    <!-- Invalid Ticket -->
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i> The requested ticket was not found or you don't have permission to view it.
        <a href="?page=support-tickets" class="alert-link">Return to ticket list</a>
    </div>
    <?php endif; ?>
</div>

<style>
/* Support Ticket Styles */
.ticket-conversation {
    max-width: 100%;
}

.ticket-message {
    margin-bottom: 1.5rem;
}

.message-content {
    white-space: pre-line;
}

.bg-light-blue {
    background-color: #e7f1ff;
}

[data-bs-theme="dark"] .bg-light {
    background-color: rgba(255, 255, 255, 0.05) !important;
}

[data-bs-theme="dark"] .bg-light-blue {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

.original-message .message-content {
    border-left: 4px solid var(--primary);
}

.original-message.system-message .message-content {
    border-left-color: #6c757d;
}

/* Ticket status and priority badges */
.status-badge {
    padding: 0.35rem 0.65rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.priority-badge {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 5px;
}

.priority-high {
    background-color: #dc3545;
}

.priority-medium {
    background-color: #ffc107;
}

.priority-low {
    background-color: #0dcaf0;
}

/* Ticket list hover effect */
.table tbody tr:hover {
    background-color: rgba(var(--primary-rgb), 0.05);
}

[data-bs-theme="dark"] .table tbody tr:hover {
    background-color: rgba(143, 179, 222, 0.1);
}

/* Card-based ticket layout */
.tickets-container {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.ticket-card {
    background: var(--bs-light);
    border: 1px solid var(--bs-border-color);
    border-radius: 0.5rem;
    padding: 1rem;
    flex: 1 1 calc(33.333% - 1rem);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.ticket-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ticket-title {
    font-size: 1.25rem;
    font-weight: 500;
    margin: 0;
}

.ticket-badges {
    display: flex;
    gap: 0.5rem;
}

.ticket-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.ticket-content {
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
}

.ticket-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ticket-meta {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.ticket-actions {
    display: flex;
    gap: 0.5rem;
}

/* Additional styles for ticket cards and animations */
.ticket-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    padding: 1.25rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

[data-bs-theme="dark"] .ticket-card {
    background-color: var(--accent-dark);
    border-color: rgba(255,255,255,0.05);
}

.ticket-card:hover {
    box-shadow: 0 6px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.ticket-card.updating::after, 
.ticket-card.deleting::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

[data-bs-theme="dark"] .ticket-card.updating::after, 
[data-bs-theme="dark"] .ticket-card.deleting::after {
    background: rgba(0,0,0,0.5);
}

.ticket-card.updating::before, 
.ticket-card.deleting::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 30px;
    height: 30px;
    border: 3px solid rgba(0,0,0,0.2);
    border-top-color: var(--primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 11;
}

@keyframes spin {
    to { transform: translate(-50%, -50%) rotate(360deg); }
}

.tickets-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.25rem;
}

.ticket-header {
    display: flex;
    flex-direction: column;
    margin-bottom: 1rem;
}

.ticket-title {
    margin-bottom: 0.75rem;
    font-size: 1.15rem;
}

.ticket-title a {
    color: var(--accent-dark);
    text-decoration: none;
}

[data-bs-theme="dark"] .ticket-title a {
    color: var(--light);
}

.ticket-title a:hover {
    color: var(--primary);
    text-decoration: underline;
}

.ticket-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.ticket-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 20px;
    padding: 0.25rem 0.6rem;
    font-size: 0.7rem;
    font-weight: 500;
}

.badge-primary {
    background-color: var(--primary);
    color: white;
}

.badge-secondary {
    background-color: #6c757d;
    color: white;
}

.badge-success {
    background-color: #198754;
    color: white;
}

.badge-danger {
    background-color: #dc3545;
    color: white;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge-info {
    background-color: #0dcaf0;
    color: #212529;
}

.status-dropdown .dropdown-toggle {
    background: transparent;
    border: none;
    color: inherit;
    padding: 0;
    font-size: 0.7rem;
}

.status-dropdown .dropdown-toggle::after {
    display: none;
}

.status-dropdown .dropdown-menu {
    min-width: 8rem;
    padding: 0.5rem 0;
    margin: 0.125rem 0 0;
    font-size: 0.875rem;
}

.ticket-content {
    margin-bottom: 1rem;
}

.ticket-excerpt {
    color: var(--accent);
    font-size: 0.875rem;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.ticket-footer {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    border-top: 1px solid rgba(0,0,0,0.05);
    padding-top: 1rem;
}

[data-bs-theme="dark"] .ticket-footer {
    border-top-color: rgba(255,255,255,0.05);
}

.ticket-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.ticket-date {
    display: flex;
    align-items: center;
    font-size: 0.75rem;
    color: var(--accent);
}

.ticket-date i {
    margin-right: 0.35rem;
}

.ticket-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

/* Toast notifications */
.notification-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 250px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: opacity 0.5s ease;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .tickets-container {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    }
}

@media (max-width: 768px) {
    .tickets-container {
        grid-template-columns: 1fr;
    }
    
    .ticket-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .ticket-badges {
        margin-top: 0.5rem;
    }
    
    .ticket-footer {
        flex-direction: column;
    }
    
    .ticket-actions {
        margin-top: 0.75rem;
        justify-content: flex-start;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-close alert messages after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-secondary)');
    
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
    
    // Initialize any tooltip or popover
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Real-time status update functionality
    const statusOptions = document.querySelectorAll('.status-option');
    statusOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const ticketId = this.getAttribute('data-ticket-id');
            const newStatus = this.getAttribute('data-status');
            updateTicketStatus(ticketId, newStatus);
        });
    });

    // Delete ticket functionality
    const deleteButtons = document.querySelectorAll('.delete-ticket');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const ticketId = this.getAttribute('data-ticket-id');
            if (confirm('Are you sure you want to delete this ticket? This action cannot be undone.')) {
                deleteTicket(ticketId);
            }
        });
    });

    // Function to update ticket status via AJAX
    function updateTicketStatus(ticketId, status) {
        // Show loading indicator
        const ticketCard = document.getElementById(`ticket-${ticketId}`);
        if (ticketCard) {
            ticketCard.classList.add('updating');
        }

        // Create form data for the request
        const formData = new FormData();
        formData.append('ticket_id', ticketId);
        formData.append('status', status);
        formData.append('action', 'update_status');

        // Send AJAX request
        fetch('index.php?page=support-tickets', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const statusBadge = ticketCard.querySelector('.status-badge');
                const statusText = statusBadge.querySelector('.status-text');
                
                // Remove old status classes
                statusBadge.classList.remove('badge-success', 'badge-warning', 'badge-secondary');
                
                // Add new status class
                if (status === 'open') {
                    statusBadge.classList.add('badge-success');
                } else if (status === 'pending') {
                    statusBadge.classList.add('badge-warning');
                } else if (status === 'closed') {
                    statusBadge.classList.add('badge-secondary');
                }
                
                // Update text
                statusText.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                
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
            if (ticketCard) {
                ticketCard.classList.remove('updating');
            }
        });
    }

    // Function to delete ticket via AJAX
    function deleteTicket(ticketId) {
        // Show loading indicator
        const ticketCard = document.getElementById(`ticket-${ticketId}`);
        if (ticketCard) {
            ticketCard.classList.add('deleting');
        }

        // Create form data for the request
        const formData = new FormData();
        formData.append('ticket_id', ticketId);
        formData.append('action', 'delete_ticket');

        // Send AJAX request
        fetch('index.php?page=support-tickets', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Fade out and remove the ticket card with animation
                if (ticketCard) {
                    ticketCard.style.transition = 'all 0.5s ease';
                    ticketCard.style.opacity = '0';
                    ticketCard.style.transform = 'scale(0.8)';
                    
                    setTimeout(() => {
                        ticketCard.remove();
                        
                        // Check if there are no more tickets and show empty state if needed
                        const ticketsContainer = document.getElementById('tickets-container');
                        if (ticketsContainer && ticketsContainer.children.length === 0) {
                            const emptyState = `
                                <div class="text-center py-5">
                                    <i class="bi bi-ticket-detailed-fill text-muted" style="font-size: 3rem;"></i>
                                    <h4 class="mt-3">No Support Tickets</h4>
                                    <p class="text-muted">You haven't created any support tickets yet.</p>
                                    <a href="?page=support-tickets&new=true" class="btn btn-primary mt-2">Create Your First Ticket</a>
                                </div>
                            `;
                            ticketsContainer.parentNode.innerHTML = emptyState;
                        }
                    }, 500);
                }
                
                // Show success message
                showNotification('Ticket deleted successfully', 'success');
            } else {
                // Show error message
                showNotification('Failed to delete ticket', 'danger');
                if (ticketCard) {
                    ticketCard.classList.remove('deleting');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'danger');
            if (ticketCard) {
                ticketCard.classList.remove('deleting');
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

    // Handle ticket form submission
    const ticketForm = document.querySelector('form[action=""][method="post"]');
    if (ticketForm) {
        ticketForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Submitting...';
            
            const formData = new FormData(this);
            
            // Send AJAX request
            fetch('index.php?page=support-tickets', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification(data.message, 'success');
                    
                    // Reset form and close panel
                    setTimeout(() => {
                        window.location.href = '?page=support-tickets';
                    }, 1000);
                } else {
                    // Show error message
                    showNotification(data.message || 'Failed to create ticket', 'danger');
                    
                    // Reset button
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while creating the ticket', 'danger');
                
                // Reset button
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
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

    // Rest of your existing JavaScript code...
});
</script>

<?php
// The content is captured by ob_get_clean() in the index.php file and passed to layout.php
// No need to capture or include layout.php here
?>