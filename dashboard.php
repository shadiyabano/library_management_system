<?php

session_start();

require_once "config/db.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Only students can access this page
if ($_SESSION["role"] !== "student") {
    header("Location: librarian_dashboard.php");
    exit;
}

$user_name = $_SESSION["user_name"];

// Total books
$stmt = $pdo->query("SELECT COUNT(*) FROM books");
$total_books = $stmt->fetchColumn();

// Available books
$stmt = $pdo->query("SELECT COUNT(*) FROM books WHERE available > 0");
$available_books = $stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard - Library System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

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
            <small>Student Panel</small>
        </div>

    </div>


    <div class="menu-title">
        STUDENT MENU
    </div>


    <a href="dashboard.php" class="menu-item active">
        🏠
        <span>Dashboard</span>
    </a>

    <a href="books.php" class="menu-item">
        📚
        <span>Browse Books</span>
    </a>

    <a href="my_books.php" class="menu-item">
        📖
        <span>My Books</span>
    </a>

    <!-- <a href="return_book.php" class="menu-item">
        ↩️
        <span>Return Book</span>
    </a> -->

    <a href="my_fines.php" class="menu-item">
        💰
        <span>My Fines</span>
    </a>

    <a href="#" class="menu-item">
        🔔
        <span>Notifications</span>

        <span class="notification-badge">
            0
        </span>

    </a>


    <div class="sidebar-bottom">

        <a href="logout.php" class="menu-item">
            🚪
            <span>Logout</span>
        </a>

    </div>

</div>



<!-- Main Content -->

<div class="main-content">


    <!-- Top Header -->

    <div class="top-header">

        <div></div>

        <div class="user-info">

            <div class="user-icon">
                👤
            </div>

            <div>

                <div class="welcome-text">
                    Welcome, <?php echo htmlspecialchars($user_name); ?>
                </div>

                <small>
                    Student
                </small>

            </div>

        </div>

    </div>



    <div class="container-fluid dashboard-container">


        <!-- Heading -->

        <div class="dashboard-heading">

            <h1>Student Dashboard</h1>

            <p>
                Find books, borrow, read and return — all in one place.
            </p>

        </div>



        <!-- Search -->

        <div class="search-box">

            <div class="search-input">

                🔍

                <input
                    type="text"
                    placeholder="Search books by title, author or category..."
                >

            </div>

            <button class="search-button">
                Search
            </button>

        </div>



        <!-- Action Cards -->

        <div class="row g-3 mt-4">


            <!-- Browse Books -->

            <div class="col-lg-4 col-md-6">

                <a href="books.php" class="action-card blue-card">

                    <div class="card-icon">
                        📚
                    </div>

                    <h4>Browse Books</h4>

                    <p>
                        View available books in the library.
                    </p>

                    <span class="arrow">
                        →
                    </span>

                </a>

            </div>



            <!-- My Books -->

            <div class="col-lg-4 col-md-6">

                <a href="my_books.php" class="action-card green-card">

                    <div class="card-icon">
                        📖
                    </div>

                    <h4>My Books</h4>

                    <p>
                        See your borrowed books and due dates.
                    </p>

                    <span class="arrow">
                        →
                    </span>

                </a>

            </div>



            <!-- Return Book -->

            <div class="col-lg-4 col-md-6">

                <a href="return_book.php" class="action-card purple-card">

                    <div class="card-icon">
                        ↩️
                    </div>

                    <h4>Return Book</h4>

                    <p>
                        Return books before or after the due date.
                    </p>

                    <span class="arrow">
                        →
                    </span>

                </a>

            </div>



            <!-- My Fines -->

            <div class="col-lg-4 col-md-6">

                <a href="my_fines.php" class="action-card orange-card">

                    <div class="card-icon">
                        💰
                    </div>

                    <h4>My Fines</h4>

                    <p>
                        Check your pending library fines.
                    </p>

                    <span class="arrow">
                        →
                    </span>

                </a>

            </div>



            <!-- Available Books -->

            <div class="col-lg-4 col-md-6">

                <a href="books.php" class="action-card teal-card">

                    <div class="card-icon">
                        📗
                    </div>

                    <h4>Available Books</h4>

                    <p>
                        <?php echo $available_books; ?>
                        books are currently available.
                    </p>

                    <span class="arrow">
                        →
                    </span>

                </a>

            </div>



            <!-- Notifications -->

            <div class="col-lg-4 col-md-6">

                <div class="action-card red-card">

                    <div class="card-icon">
                        🔔
                    </div>

                    <h4>Notifications</h4>

                    <p>
                        No new notifications right now.
                    </p>

                    <span class="arrow">
                        →
                    </span>

                </div>

            </div>

        </div>



        <!-- Quick Information -->

        <div class="quick-info mt-4">

            <h3>
                Quick Information
            </h3>

            <div class="row text-center">


                <div class="col-md-4 info-item">

                    <div class="info-icon">
                        📚
                    </div>

                    <p>
                        Total Books
                    </p>

                    <h2>
                        <?php echo $total_books; ?>
                    </h2>

                </div>



                <div class="col-md-4 info-item">

                    <div class="info-icon">
                        📖
                    </div>

                    <p>
                        My Borrowed Books
                    </p>

                    <h2>
                        0
                    </h2>

                </div>



                <div class="col-md-4 info-item">

                    <div class="info-icon">
                        💰
                    </div>

                    <p>
                        Pending Fine
                    </p>

                    <h2>
                        ₹0
                    </h2>

                </div>

            </div>

        </div>



        <!-- Library Rules -->

        <div class="library-note mt-4">

            <div class="note-icon">
                💡
            </div>

            <div>

                <h5>
                    Library Reminder
                </h5>

                <p>
                    Books should be returned before the due date.
                    A fine of <strong>₹100 per day</strong>
                    will be charged for late returns.
                </p>

            </div>

        </div>


    </div>

</div>


</body>

</html>