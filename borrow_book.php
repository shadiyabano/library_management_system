<?php

session_start();

require_once "config/db.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Only students can borrow books
if ($_SESSION["role"] !== "student") {
    header("Location: librarian_dashboard.php");
    exit;
}

// Get book ID from URL
$book_id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($book_id <= 0) {
    die("Invalid book.");
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

    // Book does not exist
    if (!$book) {
        die("Book not found.");
    }

    // Check availability
    if ($book["available"] <= 0) {
        die("Sorry, this book is currently not available.");
    }

    // Check if student already has this book
    $stmt = $pdo->prepare(
        "SELECT id
         FROM issues
         WHERE user_id = :user_id
         AND book_id = :book_id
         AND status = 'Issued'"
    );

    $stmt->execute([
        ":user_id" => $_SESSION["user_id"],
        ":book_id" => $book_id
    ]);

    $already_issued = $stmt->fetch();

    if ($already_issued) {
        die("You have already borrowed this book.");
    }

    // NEW FEATURE: Maximum 3 active books per student
    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM issues
         WHERE user_id = :user_id
         AND status = 'Issued'"
    );

    $stmt->execute([
        ":user_id" => $_SESSION["user_id"]
    ]);

    $borrowed_count = (int) $stmt->fetchColumn();

    // Stop borrowing if student already has 3 books
    if ($borrowed_count >= 3) {
        die("Borrowing limit reached! You can borrow a maximum of 3 books at a time. Please return a book before borrowing another.");
    }

    // Dates
    $issue_date = date("Y-m-d");
    $due_date = date("Y-m-d", strtotime("+7 days"));

    // Start transaction
    $pdo->beginTransaction();

    // Add issue record
    $stmt = $pdo->prepare(
        "INSERT INTO issues
        (book_id, user_id, issue_date, due_date, fine, status)
        VALUES
        (:book_id, :user_id, :issue_date, :due_date, 0, 'Issued')"
    );

    $stmt->execute([
        ":book_id" => $book_id,
        ":user_id" => $_SESSION["user_id"],
        ":issue_date" => $issue_date,
        ":due_date" => $due_date
    ]);

    // Reduce available books by 1
    $stmt = $pdo->prepare(
        "UPDATE books
         SET available = available - 1
         WHERE id = :id"
    );

    $stmt->execute([
        ":id" => $book_id
    ]);

    // Save changes
    $pdo->commit();

    // Redirect to My Books
    header("Location: my_books.php?borrowed=success");
    exit;

} catch (Exception $e) {

    // Cancel database changes if something goes wrong
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die("Something went wrong while borrowing the book.");
}

?>