<?php

$host = "localhost";
$dbname = "your_database_name";
$username = "your_database_username";
$password = "your_database_password";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {
    error_log("Database connection failed.");
    die("Database connection failed. Please check your configuration.");
}
