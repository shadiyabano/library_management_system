<?php

session_start();

require_once "config/db.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Only students
if ($_SESSION["role"] !== "student") {
    header("Location: librarian_dashboard.php");
    exit;
}

// Get issue ID
$issue_id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($issue_id <= 0) {
    die("Invalid book issue.");
}

$user_id = $_SESSION["user_id"];

try {

    // Find the borrowed book
    $sql = "SELECT
                issues.id,
                issues.book_id,
                issues.due_date,
                issues.status,
                books.title,
                books.image
            FROM issues
            INNER JOIN books
                ON issues.book_id = books.id
            WHERE issues.id = :issue_id
            AND issues.user_id = :user_id
            AND issues.status = 'Issued'";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":issue_id" => $issue_id,
        ":user_id" => $user_id
    ]);

    $issue = $stmt->fetch();

    // Check if issue exists
    if (!$issue) {
        die("Borrowed book not found.");
    }


    // Today's date
    $return_date = date("Y-m-d");

    // Calculate late days
    $today = new DateTime($return_date);
    $due_date = new DateTime($issue["due_date"]);

    $late_days = 0;

    if ($today > $due_date) {

        $difference = $due_date->diff($today);

        $late_days = $difference->days;

    }


    // Fine = NPR 10 per late day
    $fine = $late_days * 10;


    // Start transaction
    $pdo->beginTransaction();


    // Update issue
    $sql = "UPDATE issues
            SET return_date = :return_date,
                fine = :fine,
                status = 'Returned'
            WHERE id = :issue_id
            AND user_id = :user_id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":return_date" => $return_date,
        ":fine" => $fine,
        ":issue_id" => $issue_id,
        ":user_id" => $user_id
    ]);


    // Increase available book quantity
    $sql = "UPDATE books
            SET available = available + 1
            WHERE id = :book_id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":book_id" => $issue["book_id"]
    ]);


    // Save everything
    $pdo->commit();

} catch (Exception $e) {

    // Undo changes if something goes wrong
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die("Something went wrong while returning the book.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Book Returned - Library System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

    <!-- Sidebar -->

    <div class="sidebar">

        <div class="logo-section">

            <div class="logo-icon">📚</div>

            <div>
                <h4>Library System</h4>
                <small>Student Panel</small>
            </div>

        </div>


        <div class="menu-title">
            STUDENT MENU
        </div>


        <a href="dashboard.php" class="menu-item">
            🏠 <span>Dashboard</span>
        </a>


        <a href="books.php" class="menu-item">
            📚 <span>Browse Books</span>
        </a>


        <a href="my_books.php" class="menu-item">
            📖 <span>My Books</span>
        </a>


        <a href="return_book.php" class="menu-item active">
            ↩️ <span>Return Book</span>
        </a>


        <a href="my_fines.php" class="menu-item">
            💰 <span>My Fines</span>
        </a>


        <a href="#" class="menu-item">
            🔔 <span>Notifications</span>
            <span class="notification-badge">0</span>
        </a>


        <div class="sidebar-bottom">

            <a href="logout.php" class="menu-item">
                🚪 <span>Logout</span>
            </a>

        </div>

    </div>


    <!-- Main Content -->

    <div class="main-content">

        <div class="top-header">

            <div></div>

            <div class="user-info">

                <div class="user-icon">
                    👤
                </div>

                <div>

                    <div class="welcome-text">
                        Welcome, <?php echo htmlspecialchars($_SESSION["user_name"]); ?>
                    </div>

                    <small>Student</small>

                </div>

            </div>

        </div>


        <div class="container-fluid dashboard-container">

            <div class="dashboard-heading">

                <h1>Book Returned</h1>

                <p>
                    Your book has been successfully returned.
                </p>

            </div>


            <div class="card shadow-sm border-0">

                <div class="card-body p-4">

                    <div class="alert alert-success">

                        ✅ <strong>Book returned successfully!</strong>

                    </div>


                    <h4 class="mb-3">
                        <?php echo htmlspecialchars($issue["title"]); ?>
                    </h4>


                    <p>
                        <strong>Return Date:</strong>
                        <?php echo $return_date; ?>
                    </p>


                    <p>
                        <strong>Due Date:</strong>
                        <?php echo htmlspecialchars($issue["due_date"]); ?>
                    </p>


                    <?php if ($late_days > 0): ?>

                        <div class="alert alert-danger">

                            ⚠️ This book was returned
                            <strong><?php echo $late_days; ?> day(s)</strong>
                            late.

                            <br>

                            Fine:
                            <strong>
                                NPR <?php echo number_format($fine, 2); ?>
                            </strong>

                        </div>

                    <?php else: ?>

                        <div class="alert alert-success">

                            🎉 Book was returned on time.

                            <br>

                            Fine:
                            <strong>NPR 0.00</strong>

                        </div>

                    <?php endif; ?>


                    <a href="my_books.php" class="btn btn-primary">
                        📖 My Books
                    </a>


                    <a href="books.php" class="btn btn-secondary">
                        📚 Browse Books
                    </a>

                </div>

            </div>

        </div>

    </div>

</body>

</html>