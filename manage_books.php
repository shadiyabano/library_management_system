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

// Get success messages
$success_message = "";

if (isset($_GET["archived"])) {
    $success_message = "Book archived successfully. Borrowing history has been preserved.";
} elseif (isset($_GET["restored"])) {
    $success_message = "Book restored successfully.";
} elseif (isset($_GET["deleted"])) {
    $success_message = "Book deleted successfully.";
}

// Get all books, including archived books
$stmt = $pdo->query(
    "SELECT * FROM books ORDER BY id DESC"
);

$books = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Books - Library System</title>

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

        <div class="menu-title">
            LIBRARIAN MENU
        </div>

        <a href="librarian_dashboard.php" class="menu-item">
            🏠 <span>Dashboard</span>
        </a>

        <a href="manage_books.php" class="menu-item active">
            📚 <span>Manage Books</span>
        </a>

        <a href="manage_students.php" class="menu-item">
            👨‍🎓 <span>Students</span>
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

                <div class="user-icon">👤</div>

                <div>

                    <div class="welcome-text">
                        Welcome,
                        <?php echo htmlspecialchars($_SESSION["user_name"]); ?>
                    </div>

                    <small>Librarian</small>

                </div>

            </div>

        </div>


        <!-- Page Content -->

        <div class="container-fluid dashboard-container">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h1>Manage Books</h1>

                    <p class="text-muted">
                        Add, edit, archive and manage library books.
                    </p>

                </div>

                <a href="add_book.php" class="btn btn-primary">
                    ➕ Add New Book
                </a>

            </div>


            <!-- Success Message -->

            <?php if ($success_message !== ""): ?>

                <div class="alert alert-success alert-dismissible fade show" role="alert">

                    <?php echo htmlspecialchars($success_message); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    </button>

                </div>

            <?php endif; ?>


            <!-- Books Table -->

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead>

                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Category</th>
                                    <th>Quantity</th>
                                    <th>Available</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php if (count($books) > 0): ?>

                                    <?php foreach ($books as $book): ?>

                                        <tr>

                                            <td>
                                                <?php echo (int) $book["id"]; ?>
                                            </td>

                                            <td>

                                                <?php if (!empty($book["image"])): ?>

                                                    <img src="uploads/books/<?php echo htmlspecialchars($book["image"]); ?>"
                                                        alt="Book Cover"
                                                        style="width:60px;height:80px;object-fit:cover;border-radius:5px;">

                                                <?php else: ?>

                                                    <span class="text-muted">
                                                        No image
                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                            <td>
                                                <strong>
                                                    <?php
                                                    echo htmlspecialchars($book["title"]);
                                                    ?>
                                                </strong>
                                            </td>

                                            <td>
                                                <?php
                                                echo htmlspecialchars($book["author"]);
                                                ?>
                                            </td>

                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php
                                                    echo htmlspecialchars($book["category"]);
                                                    ?>
                                                </span>
                                            </td>

                                            <td>
                                                <?php echo (int) $book["quantity"]; ?>
                                            </td>

                                            <td>

                                                <?php if ((int) $book["available"] > 0): ?>

                                                    <span class="badge bg-success">
                                                        <?php echo (int) $book["available"]; ?>
                                                    </span>

                                                <?php else: ?>

                                                    <span class="badge bg-danger">0</span>

                                                <?php endif; ?>

                                            </td>

                                            <!-- Book Status -->

                                            <td>

                                                <?php if ((int) $book["is_archived"] === 1): ?>

                                                    <span class="badge bg-secondary">
                                                        Archived
                                                    </span>

                                                <?php else: ?>

                                                    <span class="badge bg-success">
                                                        Active
                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                            <!-- Actions -->

                                            <td>

                                                <?php if ((int) $book["is_archived"] === 1): ?>

                                                    <!-- Restore archived book -->

                                                    <a href="restore_book.php?id=<?php echo (int) $book["id"]; ?>"
                                                        class="btn btn-sm btn-success mb-1"
                                                        onclick="return confirm('Restore this book to the active library?');">
                                                        ♻️ Restore
                                                    </a>

                                                    <!-- Permanently delete archived book -->

                                                    <a href="permanent_delete_book.php?id=<?php echo (int) $book["id"]; ?>"
                                                        class="btn btn-sm btn-danger mb-1">
                                                        🗑️ Permanent Delete
                                                    </a>

                                                <?php else: ?>

                                                    <!-- Edit active book -->

                                                    <a href="edit_book.php?id=<?php echo (int) $book["id"]; ?>"
                                                        class="btn btn-sm btn-warning mb-1">
                                                        ✏️ Edit
                                                    </a>

                                                    <!-- Archive active book -->

                                                    <a href="delete_book.php?id=<?php echo (int) $book["id"]; ?>"
                                                        class="btn btn-sm btn-warning mb-1"
                                                        onclick="return confirm('Archive this book? It will be hidden from students.');">
                                                        📦 Archive
                                                    </a>

                                                    <!-- Permanently delete book -->

                                                    <a href="permanent_delete_book.php?id=<?php echo (int) $book["id"]; ?>"
                                                        class="btn btn-sm btn-danger mb-1">
                                                        🗑️ Permanent Delete
                                                    </a>

                                                <?php endif; ?>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>

                                        <td colspan="9" class="text-center py-4">
                                            📚 No books have been added yet.
                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>