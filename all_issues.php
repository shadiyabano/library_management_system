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


// Get all currently issued books

$sql = "SELECT
            issues.id AS issue_id,
            issues.issue_date,
            issues.due_date,
            issues.fine,
            issues.status,

            users.name AS student_name,
            users.email AS student_email,

            books.title AS book_title,
            books.author AS book_author

        FROM issues

        INNER JOIN users
            ON issues.user_id = users.id

        INNER JOIN books
            ON issues.book_id = books.id

        WHERE issues.status = 'Issued'

        ORDER BY issues.issue_date DESC";


$stmt = $pdo->query($sql);

$issues = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Issued Books - Library System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>


<!-- Sidebar -->

<div class="sidebar">

    <div class="logo-section">

        <div class="logo-icon">
            📚
        </div>

        <div>

            <h4>Library System</h4>

            <small>Librarian Panel</small>

        </div>

    </div>


    <div class="menu-title">
        LIBRARIAN MENU
    </div>


    <a href="librarian_dashboard.php" class="menu-item">
        🏠 <span>Dashboard</span>
    </a>


    <a href="manage_books.php" class="menu-item">
        📚 <span>Manage Books</span>
    </a>


    <a href="manage_students.php" class="menu-item">
        👥 <span>Students</span>
    </a>


    <a href="all_issues.php" class="menu-item active">
        📖 <span>Issued Books</span>
    </a>


    <a href="all_returns.php" class="menu-item">
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


    <!-- Header -->

    <div class="top-header">

        <div></div>

        <div class="user-info">

            <div class="user-icon">
                👤
            </div>

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


    <!-- Page Content -->

    <div class="container-fluid dashboard-container">


        <!-- Heading -->

        <div class="dashboard-heading">

            <h1>Issued Books</h1>

            <p>
                View all books currently borrowed by students.
            </p>

        </div>


        <!-- Issued Books -->

        <div class="card shadow-sm border-0">

            <div class="card-body">


                <div class="d-flex justify-content-between align-items-center mb-4">

                    <h4 class="mb-0">
                        Currently Issued Books
                    </h4>

                    <span class="badge bg-primary">

                        <?php echo count($issues); ?>

                        Issued

                    </span>

                </div>


                <?php if (count($issues) > 0): ?>


                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead>

                                <tr>

                                    <th>Student</th>

                                    <th>Book</th>

                                    <th>Issue Date</th>

                                    <th>Due Date</th>

                                    <th>Status</th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php foreach ($issues as $issue): ?>


                                    <?php

                                    // Check whether book is overdue

                                    $today = new DateTime();

                                    $due_date = new DateTime(
                                        $issue["due_date"]
                                    );

                                    $is_overdue = $today > $due_date;

                                    ?>


                                    <tr>


                                        <!-- Student -->

                                        <td>

                                            <strong>

                                                <?php
                                                echo htmlspecialchars(
                                                    $issue["student_name"]
                                                );
                                                ?>

                                            </strong>

                                            <br>

                                            <small class="text-muted">

                                                <?php
                                                echo htmlspecialchars(
                                                    $issue["student_email"]
                                                );
                                                ?>

                                            </small>

                                        </td>


                                        <!-- Book -->

                                        <td>

                                            <strong>

                                                <?php
                                                echo htmlspecialchars(
                                                    $issue["book_title"]
                                                );
                                                ?>

                                            </strong>

                                            <br>

                                            <small class="text-muted">

                                                <?php
                                                echo htmlspecialchars(
                                                    $issue["book_author"]
                                                );
                                                ?>

                                            </small>

                                        </td>


                                        <!-- Issue Date -->

                                        <td>

                                            <?php
                                            echo date(
                                                "d M Y",
                                                strtotime(
                                                    $issue["issue_date"]
                                                )
                                            );
                                            ?>

                                        </td>


                                        <!-- Due Date -->

                                        <td>

                                            <?php
                                            echo date(
                                                "d M Y",
                                                strtotime(
                                                    $issue["due_date"]
                                                )
                                            );
                                            ?>


                                            <?php if ($is_overdue): ?>

                                                <br>

                                                <span class="badge bg-danger">

                                                    Overdue

                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- Status -->

                                        <td>

                                            <?php if ($is_overdue): ?>

                                                <span class="badge bg-danger">

                                                    Overdue

                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-success">

                                                    Issued

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

                        📚 No books are currently issued.

                    </div>


                <?php endif; ?>


            </div>

        </div>


    </div>

</div>


</body>

</html>