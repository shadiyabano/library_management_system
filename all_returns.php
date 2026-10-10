<?php

session_start();

require_once "config/db.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Only librarian can access
if ($_SESSION["role"] !== "librarian") {
    header("Location: dashboard.php");
    exit;
}

// Get all returned books
$sql = "SELECT
            issues.id AS issue_id,
            issues.issue_date,
            issues.due_date,
            issues.return_date,
            issues.fine,

            users.name AS student_name,
            users.email AS student_email,

            books.title AS book_title,
            books.author AS book_author

        FROM issues

        INNER JOIN users
            ON issues.user_id = users.id

        INNER JOIN books
            ON issues.book_id = books.id

        WHERE issues.status = 'Returned'

        ORDER BY issues.return_date DESC";

$stmt = $pdo->query($sql);

$returns = $stmt->fetchAll();

// Calculate total fines
$total_fines = 0;

foreach ($returns as $return) {
    $total_fines += $return["fine"];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Returned Books - Library System</title>

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
                <small>Librarian Panel</small>
            </div>

        </div>

        <div class="menu-title">LIBRARIAN MENU</div>

        <a href="librarian_dashboard.php" class="menu-item">
            🏠 <span>Dashboard</span>
        </a>

        <a href="manage_books.php" class="menu-item">
            📚 <span>Manage Books</span>
        </a>

        <a href="manage_students.php" class="menu-item">
            👥 <span>Students</span>
        </a>

        <a href="all_issues.php" class="menu-item">
            📖 <span>Issued Books</span>
        </a>

        <a href="all_returns.php" class="menu-item active">
            ↩️ <span>Returned Books</span>
        </a>

        <a href="all_fines.php" class="menu-item">
            💰 <span>Fines</span>
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

                <div class="user-icon">👤</div>

                <div>
                    <div class="welcome-text">
                        Welcome,
                        <?php
                        echo htmlspecialchars($_SESSION["user_name"]);
                        ?>
                    </div>

                    <small>Librarian</small>
                </div>

            </div>

        </div>

        <div class="container-fluid dashboard-container">

            <!-- Page Heading -->

            <div class="dashboard-heading">

                <h1>Returned Books</h1>

                <p>
                    View the history of books returned by students.
                </p>

            </div>

            <!-- Summary Cards -->

            <div class="row g-3 mb-4">

                <div class="col-md-6">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <p class="text-muted mb-1">
                                Total Returned Books
                            </p>

                            <h2 class="text-primary mb-0">
                                <?php echo count($returns); ?>
                            </h2>

                        </div>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <p class="text-muted mb-1">
                                Total Recorded Fines
                            </p>

                            <h2 class="text-danger mb-0">
                                NPR <?php echo number_format($total_fines, 2); ?>
                            </h2>

                        </div>

                    </div>

                </div>

            </div>

            <!-- Returned Books Table -->

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h4 class="mb-4">Return History</h4>

                    <?php if (count($returns) > 0): ?>

                        <div class="table-responsive">

                            <table class="table table-hover align-middle">

                                <thead>

                                    <tr>
                                        <th>Student</th>
                                        <th>Book</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Return Date</th>
                                        <th>Fine</th>
                                    </tr>

                                </thead>

                                <tbody>

                                    <?php foreach ($returns as $return): ?>

                                        <tr>

                                            <td>
                                                <strong>
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $return["student_name"]
                                                    );
                                                    ?>
                                                </strong>

                                                <br>

                                                <small class="text-muted">
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $return["student_email"]
                                                    );
                                                    ?>
                                                </small>
                                            </td>

                                            <td>
                                                <strong>
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $return["book_title"]
                                                    );
                                                    ?>
                                                </strong>

                                                <br>

                                                <small class="text-muted">
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $return["book_author"]
                                                    );
                                                    ?>
                                                </small>
                                            </td>

                                            <td>
                                                <?php
                                                echo date(
                                                    "d M Y",
                                                    strtotime($return["issue_date"])
                                                );
                                                ?>
                                            </td>

                                            <td>
                                                <?php
                                                echo date(
                                                    "d M Y",
                                                    strtotime($return["due_date"])
                                                );
                                                ?>
                                            </td>

                                            <td>
                                                <?php
                                                echo date(
                                                    "d M Y",
                                                    strtotime($return["return_date"])
                                                );
                                                ?>
                                            </td>

                                            <td>
                                                <?php if ($return["fine"] > 0): ?>

                                                    <span class="badge bg-danger">
                                                        NPR <?php
                                                        echo number_format(
                                                            $return["fine"],
                                                            2
                                                        );
                                                        ?>
                                                    </span>

                                                <?php else: ?>

                                                    <span class="badge bg-success">
                                                        No Fine
                                                    </span>

                                                <?php endif; ?>
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="alert alert-info mb-0">
                            No returned books found.
                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</body>

</html>