<?php
// Database connection parameters
$db_host = 'localhost';
$db_name = 'lensi_db';
$db_user = 'root';
$db_pass = '';

// Establish the database connection
try {
    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    $GLOBALS['pdo'] = $pdo;
    
    // Check if the database exists, create it if it doesn't
    try {
        $pdo->query("USE {$db_name}");
    } catch (PDOException $e) {
        // Database doesn't exist, create it
        $tempPdo = new PDO("mysql:host={$db_host}", $db_user, $db_pass);
        $tempPdo->exec("CREATE DATABASE IF NOT EXISTS {$db_name} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $tempPdo = null; // Close connection
        
        // Reconnect with the new database
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        $GLOBALS['pdo'] = $pdo;
    }
    
} catch (PDOException $e) {
    // Log error and display user-friendly message
    error_log('Database connection error: ' . $e->getMessage());
    die('Database connection failed. Please try again later.');
}
?>