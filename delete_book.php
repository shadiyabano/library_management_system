<?php

session_start();

require_once "config/db.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Only librarian can archive books
if ($_SESSION["role"] !== "librarian") {
    header("Location: dashboard.php");
    exit;
}

// Get book ID
$book_id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($book_id <= 0) {
    header("Location: manage_books.php");
    exit;
}

try {

    // Find the book
    $stmt = $pdo->prepare(
        "SELECT * FROM books WHERE id = :id"
    );

    $stmt->execute([
        ":id" => $book_id
    ]);

    $book = $stmt->fetch();

    if (!$book) {
        header("Location: manage_books.php");
        exit;
    }

    // Check whether any student still has this book issued
    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM issues
         WHERE book_id = :book_id
         AND status = 'Issued'"
    );

    $stmt->execute([
        ":book_id" => $book_id
    ]);

    $currently_issued = (int) $stmt->fetchColumn();

    if ($currently_issued > 0) {
        die(
            "This book cannot be archived because one or more students have not returned it."
        );
    }

    // Archive the book without deleting its history
    $stmt = $pdo->prepare(
        "UPDATE books
         SET is_archived = 1
         WHERE id = :id"
    );

    $stmt->execute([
        ":id" => $book_id
    ]);

    // Return to Manage Books
    header("Location: manage_books.php?archived=success");
    exit;

} catch (PDOException $e) {

    error_log($e->getMessage());

    die(
        "Unable to archive this book. Please try again."
    );
}

?>