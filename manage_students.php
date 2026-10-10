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

// Search
$search = trim($_GET["search"] ?? "");

// Get students
if ($search !== "") {

    $sql = "SELECT id, name, email, created_at
            FROM users
            WHERE role = 'student'
            AND (name LIKE :search OR email LIKE :search)
            ORDER BY id DESC";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":search" => "%" . $search . "%"
    ]);

} else {

    $sql = "SELECT id, name, email, created_at
            FROM users
            WHERE role = 'student'
            ORDER BY id DESC";

    $stmt = $pdo->query($sql);
}

$students = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Students - Library System</title>

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

        <div class="logo-icon">📚</div>

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


    <a href="manage_students.php" class="menu-item active">
        👥 <span>Students</span>
    </a>


    <a href="all_issues.php" class="menu-item">
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

            <h1>Students</h1>

            <p>
                View all registered students.
            </p>

        </div>


        <!-- Search -->

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-body">

                <form method="GET" class="row g-2">

                    <div class="col-md-10">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search student by name or email..."
                            value="<?php
                            echo htmlspecialchars($search);
                            ?>"
                        >

                    </div>

                    <div class="col-md-2">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            🔍 Search
                        </button>

                    </div>

                </form>

            </div>

        </div>


        <!-- Students Table -->

        <div class="card shadow-sm border-0">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <h4 class="mb-0">
                        Registered Students
                    </h4>

                    <span class="badge bg-primary">

                        <?php echo count($students); ?>

                        Student<?php echo count($students) != 1 ? "s" : ""; ?>

                    </span>

                </div>


                <?php if (count($students) > 0): ?>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead>

                                <tr>

                                    <th>ID</th>

                                    <th>Student Name</th>

                                    <th>Email</th>

                                    <th>Registered On</th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php foreach ($students as $student): ?>

                                    <tr>

                                        <td>
                                            <?php
                                            echo $student["id"];
                                            ?>
                                        </td>


                                        <td>

                                            <strong>

                                                <?php
                                                echo htmlspecialchars(
                                                    $student["name"]
                                                );
                                                ?>

                                            </strong>

                                        </td>


                                        <td>

                                            <?php
                                            echo htmlspecialchars(
                                                $student["email"]
                                            );
                                            ?>

                                        </td>


                                        <td>

                                            <?php
                                            echo date(
                                                "d M Y",
                                                strtotime(
                                                    $student["created_at"]
                                                )
                                            );
                                            ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="alert alert-info mb-0">

                        No students found.

                    </div>

                <?php endif; ?>


            </div>

        </div>


    </div>

</div>


</body>

</html>