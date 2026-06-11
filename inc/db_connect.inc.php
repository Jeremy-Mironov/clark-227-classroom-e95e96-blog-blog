<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'blog'; // you need to create this database if it does not exist

// DSN - Data Source Name
$dsn = 'mysql:host=' . $host . ';dbname=' . $dbname;

// Create a PDO Instance
try {
    $db = new PDO($dsn, $user, $password);
    // Set PDO default data type to be returned
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    // Enable error mode
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

function h($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
