-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS lensi_db;

-- Use the lensi_db database
USE lensi_db;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('freelancer', 'employer', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(100) DEFAULT NULL,
    reset_token VARCHAR(100) DEFAULT NULL,
    reset_token_expires_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create an index on the email column for faster lookups
CREATE INDEX users_email_idx ON users(email);

-- Create user_profiles table
CREATE TABLE IF NOT EXISTS user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(100) NOT NULL,
    bio TEXT,
    skills TEXT,
    hourly_rate DECIMAL(10,2) DEFAULT 0,
    location VARCHAR(100),
    website VARCHAR(255),
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_email) REFERENCES users(email) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create an index on the user_email column for faster lookups
CREATE INDEX user_profiles_email_idx ON user_profiles(user_email);

-- Create user_settings table
CREATE TABLE IF NOT EXISTS user_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(100) NOT NULL,
    email_notifications TINYINT(1) DEFAULT 1,
    project_notifications TINYINT(1) DEFAULT 1,
    message_notifications TINYINT(1) DEFAULT 1,
    marketing_emails TINYINT(1) DEFAULT 0,
    profile_visibility ENUM('public', 'private', 'contacts') DEFAULT 'public',
    show_earnings TINYINT(1) DEFAULT 0,
    show_projects TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_email) REFERENCES users(email) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create an index on the user_email column for faster lookups in user_settings
CREATE INDEX user_settings_email_idx ON user_settings(user_email);

CREATE TABLE blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('published', 'draft', 'archived') DEFAULT 'published',
    thumbnail_url VARCHAR(255),
    views INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE blog_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    parent_id INT DEFAULT NULL,
    status ENUM('approved', 'pending', 'spam') DEFAULT 'approved',
    FOREIGN KEY (blog_id) REFERENCES blogs(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (parent_id) REFERENCES blog_comments(id)
);

-- Clear existing data for this user (if any)
DELETE FROM `support_replies` WHERE `user_email` = 'firaszx232@gmail.com';
DELETE FROM `support_tickets` WHERE `user_email` = 'firaszx232@gmail.com';

-- Insert new tickets for Firasss
INSERT INTO `support_tickets` (`user_email`, `subject`, `description`, `category`, `priority`, `status`, `created_at`, `updated_at`) VALUES
('firaszx232@gmail.com', 'Unable to submit proposal', 'I tried to submit a proposal for project ID #1045 but keep getting an error message saying \"Submission failed\". I\'ve tried multiple times over the past 2 days.', 'technical', 'high', 'open', '2025-04-16 14:23:45', '2025-04-16 14:23:45'),

('firaszx232@gmail.com', 'Payment not showing in balance', 'I received a notification that a client released payment for my completed project, but the funds aren\'t showing in my available balance. Transaction ID: TRX-45982.', 'billing', 'medium', 'pending', '2025-04-15 09:10:22', '2025-04-17 11:45:33'),

('firaszx232@gmail.com', 'Profile verification question', 'I submitted my ID for verification 3 days ago but haven\'t received any update. How long does the verification process typically take?', 'account', 'low', 'answered', '2025-04-12 17:33:21', '2025-04-13 10:20:15'),

('firaszx232@gmail.com', 'Feature request: Filter by skills', 'I\'d like to suggest adding a feature to filter projects by required skills. This would make it easier to find projects that match my expertise without scrolling through irrelevant listings.', 'feature', 'medium', 'closed', '2025-04-10 20:45:12', '2025-04-14 16:22:45');

-- Support Tickets table
CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('technical', 'billing', 'account', 'feature', 'other') NOT NULL,
    priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    status ENUM('open', 'pending', 'answered', 'closed') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_email) REFERENCES users(email) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Support Ticket Replies table
CREATE TABLE IF NOT EXISTS support_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_email) REFERENCES users(email) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create indexes for faster lookups
CREATE INDEX support_tickets_user_email_idx ON support_tickets(user_email);
CREATE INDEX support_tickets_status_idx ON support_tickets(status);
CREATE INDEX support_replies_ticket_id_idx ON support_replies(ticket_id);


CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    user_email VARCHAR(255) NOT NULL,
    client_name VARCHAR(255),
    budget DECIMAL(10, 2),
    status ENUM('pending', 'in-progress', 'completed', 'cancelled') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS project_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_by VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS project_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in-progress', 'completed') DEFAULT 'pending',
    assigned_to VARCHAR(255),
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);