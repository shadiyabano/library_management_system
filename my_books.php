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

// Get student's borrowed books
$sql = "SELECT 
            issues.id AS issue_id,
            issues.issue_date,
            issues.due_date,
            issues.fine,
            issues.status,
            books.title,
            books.author,
            books.category,
            books.image
        FROM issues
        INNER JOIN books 
            ON issues.book_id = books.id
        WHERE issues.user_id = :user_id
        AND issues.status = 'Issued'
        ORDER BY issues.issue_date DESC";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":user_id" => $user_id
]);

$books = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Books - Library System</title>

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


    <a href="my_books.php" class="menu-item active">
        📖 <span>My Books</span>
    </a>


    <!-- <a href="return_book.php" class="menu-item">
        ↩️ <span>Return Book</span>
    </a> -->


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

            <h1>My Books</h1>

            <p>
                Books currently borrowed by you.
            </p>

        </div>


        <?php if (isset($_GET["borrowed"])): ?>

            <div class="alert alert-success">

                ✅ Book borrowed successfully!

            </div>

        <?php endif; ?>


        <?php if (count($books) > 0): ?>

            <div class="row g-4">

                <?php foreach ($books as $book): ?>

                    <div class="col-lg-4 col-md-6">

                        <div class="card h-100 shadow-sm border-0">


                            <!-- Book Image -->

                            <?php if (!empty($book["image"])): ?>

                                <img
                                    src="uploads/books/<?php echo htmlspecialchars($book["image"]); ?>"
                                    class="card-img-top"
                                    alt="Book Cover"
                                    style="height: 220px; object-fit: cover;"
                                >

                            <?php else: ?>

                                <div
                                    class="d-flex align-items-center justify-content-center bg-light"
                                    style="height: 220px;"
                                >
                                    📚 No Image
                                </div>

                            <?php endif; ?>


                            <div class="card-body">

                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($book["title"]); ?>
                                </h5>

                                <p class="text-muted mb-2">

                                    By
                                    <?php echo htmlspecialchars($book["author"]); ?>

                                </p>

                                <p class="mb-2">

                                    <strong>Category:</strong>

                                    <?php echo htmlspecialchars($book["category"]); ?>

                                </p>


                                <hr>


                                <p class="mb-2">

                                    <strong>Borrowed:</strong>

                                    <?php echo htmlspecialchars($book["issue_date"]); ?>

                                </p>


                                <p class="mb-2">

                                    <strong>Due Date:</strong>

                                    <span class="text-danger">

                                        <?php echo htmlspecialchars($book["due_date"]); ?>

                                    </span>

                                </p>


                                <p class="mb-0">

                                    <strong>Fine:</strong>

                                    ₹<?php echo number_format($book["fine"], 2); ?>

                                </p>

                            </div>


                            <div class="card-footer bg-white border-0 p-3">

                                <a
                                    href="return_book.php?id=<?php echo $book["issue_id"]; ?>"
                                    class="btn btn-primary w-100"
                                >
                                    ↩️ Return Book
                                </a>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>


        <?php else: ?>

            <div class="alert alert-info">

                📚 You have not borrowed any books yet.

                <br><br>

                <a href="books.php" class="btn btn-primary">
                    Browse Books
                </a>

            </div>

        <?php endif; ?>


    </div>

</div>

</body>

</html>