
<?php

session_start();

require_once "config/db.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Only librarian
if ($_SESSION["role"] !== "librarian") {
    header("Location: dashboard.php");
    exit;
}

// Get book ID
$book_id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($book_id <= 0) {
    header("Location: manage_books.php");
    exit;
}

// Get book details
$stmt = $pdo->prepare(
    "SELECT * FROM books WHERE id = :id"
);

$stmt->execute([
    ":id" => $book_id
]);

$book = $stmt->fetch();

if (!$book) {
    header("Location: manage_books.php");
    exit;
}

// Handle confirmation
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST["confirm_delete"])) {

        $error = "Please confirm permanent deletion.";

    } else {

        try {

            $pdo->beginTransaction();

            // Lock the book record
            $stmt = $pdo->prepare(
                "SELECT id
                 FROM books
                 WHERE id = :id
                 FOR UPDATE"
            );

            $stmt->execute([
                ":id" => $book_id
            ]);

            if (!$stmt->fetch()) {
                throw new Exception("Book not found.");
            }

            // Check whether any borrowing entry is not returned
            // This checks issues for ALL students
            $stmt = $pdo->prepare(
                "SELECT COUNT(*)
                 FROM issues
                 WHERE book_id = :book_id
                 AND (status <> 'Returned' OR status IS NULL)"
            );

            $stmt->execute([
                ":book_id" => $book_id
            ]);

            $unreturned_issues = (int) $stmt->fetchColumn();

            if ($unreturned_issues > 0) {

                $pdo->rollBack();

                $error =
                    "This book cannot be permanently deleted because "
                    . "one or more students have not returned it.";

            } else {

                // Delete all issue, return and fine history for this book
                $stmt = $pdo->prepare(
                    "DELETE FROM issues
                     WHERE book_id = :book_id"
                );

                $stmt->execute([
                    ":book_id" => $book_id
                ]);

                // Delete the book record
                $stmt = $pdo->prepare(
                    "DELETE FROM books
                     WHERE id = :id"
                );

                $stmt->execute([
                    ":id" => $book_id
                ]);

                // Save database changes
                $pdo->commit();

                // Delete the book image after successful deletion
                if (!empty($book["image"])) {

                    $image_name = basename($book["image"]);

                    $image_path =
                        __DIR__ . "/uploads/books/" . $image_name;

                    if (is_file($image_path)) {
                        unlink($image_path);
                    }
                }

                header(
                    "Location: manage_books.php?deleted=success"
                );

                exit;
            }

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($e->getMessage());

            $error =
                "Unable to delete this book. Please try again.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Permanent Delete - Library System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="main-content">

    <div class="container py-5">

        <div class="card shadow-sm border-0 mx-auto"
             style="max-width: 650px;">

            <div class="card-body p-4">

                <h2 class="text-danger mb-3">
                    ⚠️ Permanently Delete Book
                </h2>

                <p>
                    You are about to permanently delete:
                </p>

                <h4>
                    <?php echo htmlspecialchars($book["title"]); ?>
                </h4>

                <p class="text-muted">
                    Author:
                    <?php echo htmlspecialchars($book["author"]); ?>
                </p>

                <div class="alert alert-danger">
                    <strong>Warning:</strong>

                    This action will permanently delete the book
                    and all its issue, return and fine records.

                    This cannot be undone.
                </div>

                <?php if ($error !== ""): ?>

                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>

                <?php endif; ?>

                <form method="POST">

                    <div class="form-check mb-4">

                        <input
                            type="checkbox"
                            class="form-check-input"
                            id="confirm_delete"
                            name="confirm_delete"
                            value="1"
                            required
                        >

                        <label
                            class="form-check-label"
                            for="confirm_delete"
                        >
                            I understand that all records for this book
                            will be permanently deleted.
                        </label>

                    </div>

                    <button
                        type="submit"
                        class="btn btn-danger"
                    >
                        Permanently Delete
                    </button>

                    <a
                        href="manage_books.php"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                </form>

            </div>

        </div>

    </div>

</div>

</body>
</html>
