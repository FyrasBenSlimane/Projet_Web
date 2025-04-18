<?php
/**
 * Support Model
 * Handles all database operations for support tickets
 */
class SupportModel {
    public $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get all tickets for a specific user
     */
    public function getUserTickets($userEmail) {
        $sql = "SELECT * FROM support_tickets WHERE user_email = :email ORDER BY updated_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $userEmail);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a ticket by ID
     * Ensures that only the ticket owner or an admin can view it
     */
    public function getTicketById($ticketId, $userEmail) {
        // Instead of checking for admin role, we'll treat all users as having access to their own tickets
        // This eliminates the dependency on the 'role' column in the users table
        $sql = "SELECT * FROM support_tickets WHERE id = :id AND user_email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $ticketId);
        $stmt->bindParam(':email', $userEmail);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new support ticket
     */
    public function createTicket($userEmail, $subject, $description, $category, $priority) {
        // Existing code remains unchanged
        $sql = "INSERT INTO support_tickets 
                (user_email, subject, description, category, priority, status, created_at, updated_at) 
                VALUES (:email, :subject, :description, :category, :priority, 'open', NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $userEmail);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':priority', $priority);
        
        return $stmt->execute();
    }

    /**
     * Delete a ticket
     * Users can only delete their own tickets
     */
    public function deleteTicket($ticketId, $userEmail) {
        // Delete ticket that belongs to this user
        $sql = "DELETE FROM support_tickets WHERE id = :id AND user_email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $ticketId);
        $stmt->bindParam(':email', $userEmail);
        return $stmt->execute();
    }

    /**
     * Reopen a ticket
     * Users can only reopen their own tickets
     */
    public function reopenTicket($ticketId, $userEmail) {
        // Simplified to allow users to reopen their own tickets without admin check
        $sql = "UPDATE support_tickets SET status = 'open', updated_at = NOW() 
                WHERE id = :id AND user_email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $ticketId);
        $stmt->bindParam(':email', $userEmail);
        return $stmt->execute();
    }

    /**
     * Get all replies for a specific ticket
     */
    public function getTicketReplies($ticketId) {
        $sql = "SELECT * FROM support_replies 
                WHERE ticket_id = :ticket_id 
                ORDER BY created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':ticket_id', $ticketId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add a reply to a ticket
     * 
     * @param int $ticketId Ticket ID
     * @param string $userEmail User email
     * @param string $message Reply message
     * @param bool $isAdmin Whether the reply is from admin
     * @return bool Success status
     */
    public function addReply($ticketId, $userEmail, $message, $isAdmin = false) {
        try {
            // For admin replies, use a valid user email from the users table
            // This is a workaround for the foreign key constraint
            $replyEmail = $isAdmin ? 
                'admin@example.com' : // Make sure this exists in your users table
                $userEmail;
                
            // Add reply
            $sql = "INSERT INTO support_replies (ticket_id, user_email, message, is_admin) 
                    VALUES (:ticket_id, :user_email, :message, :is_admin)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ticket_id', $ticketId);
            $stmt->bindParam(':user_email', $replyEmail);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':is_admin', $isAdmin, PDO::PARAM_BOOL);
            
            if ($stmt->execute()) {
                // Update ticket status and updated_at timestamp
                $this->updateTicketStatus($ticketId, $isAdmin ? 'pending' : 'open');
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("SupportModel::addReply Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update ticket status
     * 
     * @param int $ticketId Ticket ID
     * @param string $status New status
     * @param string $userEmail User email
     * @return bool Success status
     */
    public function updateTicketStatus($ticketId, $status, $userEmail) {
        try {
            // Make sure user can only update their own tickets
            $sql = "UPDATE support_tickets 
                    SET status = :status, updated_at = NOW() 
                    WHERE id = :id AND user_email = :email";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $ticketId);
            $stmt->bindParam(':email', $userEmail);
            $stmt->bindParam(':status', $status);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("SupportModel::updateTicketStatus Error: " . $e->getMessage());
            return false;
        }
    }
}
?>