<?php

session_start();

require_once "config/db.php";


// Check if user is logged in

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit;

}


// Check if user is librarian

if ($_SESSION["role"] !== "librarian") {

    header("Location: dashboard.php");
    exit;

}


// Get total books

$stmt = $pdo->query(
    "SELECT COUNT(*) FROM books"
);

$total_books = $stmt->fetchColumn();


// Get total students

$stmt = $pdo->query(
    "SELECT COUNT(*) FROM users WHERE role = 'student'"
);

$total_students = $stmt->fetchColumn();


// Get total issued books

$stmt = $pdo->query(
    "SELECT COUNT(*) FROM issues WHERE status = 'Issued'"
);

$total_issued = $stmt->fetchColumn();


// Get returned books

$stmt = $pdo->query(
    "SELECT COUNT(*) FROM issues WHERE status = 'Returned'"
);

$total_returned = $stmt->fetchColumn();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Librarian Dashboard</title>

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

            <small>
                Librarian Panel
            </small>

        </div>

    </div>


    <div class="menu-title">
        LIBRARIAN MENU
    </div>


    <a
        href="librarian_dashboard.php"
        class="menu-item active"
    >
        🏠
        <span>Dashboard</span>
    </a>


    <a
        href="manage_books.php"
        class="menu-item"
    >
        📚
        <span>Manage Books</span>
    </a>


    <a
        href="manage_students.php"
        class="menu-item"
    >
        👥
        <span>Students</span>
    </a>


    <a
        href="all_issues.php"
        class="menu-item"
    >
        📖
        <span>Issued Books</span>
    </a>


    <a
        href="all_returns.php"
        class="menu-item"
    >
        ↩️
        <span>Returned Books</span>
    </a>


    <a
        href="all_fines.php"
        class="menu-item"
    >
        💰
        <span>Fines</span>
    </a>


    <div class="sidebar-bottom">

        <a
            href="logout.php"
            class="menu-item"
        >
            🚪
            <span>Logout</span>
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
                👩‍💼
            </div>

            <div>

                <div class="welcome-text">

                    Welcome,
                    <?php
                    echo htmlspecialchars(
                        $_SESSION["user_name"]
                    );
                    ?>

                </div>

                <small>
                    Librarian
                </small>

            </div>

        </div>

    </div>



    <!-- Dashboard -->

    <div class="container-fluid dashboard-container">


        <div class="dashboard-heading">

            <h1>
                Librarian Dashboard
            </h1>

            <p>
                Manage books, students and library activities.
            </p>

        </div>



        <!-- Statistics -->

        <div class="row g-4 mt-3">


            <div class="col-md-3">

                <div class="quick-info text-center">

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

            </div>



            <div class="col-md-3">

                <div class="quick-info text-center">

                    <div class="info-icon">
                        👥
                    </div>

                    <p>
                        Students
                    </p>

                    <h2>
                        <?php echo $total_students; ?>
                    </h2>

                </div>

            </div>



            <div class="col-md-3">

                <div class="quick-info text-center">

                    <div class="info-icon">
                        📖
                    </div>

                    <p>
                        Issued Books
                    </p>

                    <h2>
                        <?php echo $total_issued; ?>
                    </h2>

                </div>

            </div>



            <div class="col-md-3">

                <div class="quick-info text-center">

                    <div class="info-icon">
                        ↩️
                    </div>

                    <p>
                        Returned Books
                    </p>

                    <h2>
                        <?php echo $total_returned; ?>
                    </h2>

                </div>

            </div>

        </div>



        <!-- Quick Actions -->

        <div class="quick-info mt-4">

            <h3>
                Quick Actions
            </h3>


            <div class="d-flex gap-2 flex-wrap mt-3">

                <a
                    href="manage_books.php"
                    class="btn btn-primary"
                >
                    + Add / Manage Books
                </a>


                <a
                    href="manage_students.php"
                    class="btn btn-success"
                >
                    View Students
                </a>


                <a
                    href="all_issues.php"
                    class="btn btn-warning"
                >
                    View Issued Books
                </a>


                <a
                    href="all_fines.php"
                    class="btn btn-danger"
                >
                    View Fines
                </a>

            </div>

        </div>


    </div>

</div>


</body>

</html>