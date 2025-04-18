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