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

$user_id = $_SESSION["user_id"];

// Get fine records
$sql = "SELECT
            issues.id AS issue_id,
            issues.issue_date,
            issues.due_date,
            issues.return_date,
            issues.fine,
            books.title
        FROM issues
        INNER JOIN books
            ON issues.book_id = books.id
        WHERE issues.user_id = :user_id
        AND issues.fine > 0
        ORDER BY issues.return_date DESC";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":user_id" => $user_id
]);

$fines = $stmt->fetchAll();


// Calculate total fine
$total_fine = 0;

foreach ($fines as $fine) {
    $total_fine += $fine["fine"];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Fines - Library System</title>

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


        <!-- <a href="return_book.php" class="menu-item">
        ↩️ <span>Return Book</span>
    </a> -->


        <a href="my_fines.php" class="menu-item active">
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


        <!-- Header -->

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


        <!-- Page Content -->

        <div class="container-fluid dashboard-container">


            <div class="dashboard-heading">

                <h1>My Fines</h1>

                <p>
                    View your library fine history.
                </p>

            </div>


            <!-- Total Fine -->

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-body text-center p-4">

                    <div style="font-size: 40px;">
                        💰
                    </div>

                    <p class="text-muted mb-1">
                        Total Fine
                    </p>

                    <h1 class="text-danger">

                        NPR <?php echo number_format($total_fine, 2); ?>

                    </h1>

                    <?php if ($total_fine == 0): ?>

                        <p class="text-success mb-0">
                            🎉 You have no fines.
                        </p>

                    <?php else: ?>

                        <p class="text-danger mb-0">
                            Please contact the librarian regarding your fine.
                        </p>

                    <?php endif; ?>

                </div>

            </div>


            <!-- Fine History -->

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h4 class="mb-4">
                        Fine History
                    </h4>


                    <?php if (count($fines) > 0): ?>

                        <div class="table-responsive">

                            <table class="table table-hover align-middle">

                                <thead>

                                    <tr>

                                        <th>Book</th>

                                        <th>Issue Date</th>

                                        <th>Due Date</th>

                                        <th>Return Date</th>

                                        <th>Fine</th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <?php foreach ($fines as $fine): ?>

                                        <tr>

                                            <td>

                                                <strong>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $fine["title"]
                                                    );
                                                    ?>

                                                </strong>

                                            </td>


                                            <td>
                                                <?php
                                                echo htmlspecialchars(
                                                    $fine["issue_date"]
                                                );
                                                ?>
                                            </td>


                                            <td>
                                                <?php
                                                echo htmlspecialchars(
                                                    $fine["due_date"]
                                                );
                                                ?>
                                            </td>


                                            <td>
                                                <?php
                                                echo htmlspecialchars(
                                                    $fine["return_date"]
                                                );
                                                ?>
                                            </td>


                                            <td>

                                                <span class="badge bg-danger">

                                                    NPR <?php
                                                    echo number_format(
                                                        $fine["fine"],
                                                        2
                                                    );
                                                    ?>

                                                </span>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="alert alert-success mb-0">

                            🎉 No fine records found.

                        </div>

                    <?php endif; ?>

                </div>

            </div>


        </div>

    </div>

</body>

</html>