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

$message = "";
$error = "";

// When form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);
    $author = trim($_POST["author"]);
    $category = trim($_POST["category"]);
    $quantity = (int) $_POST["quantity"];

    // Check basic fields
    if ($title === "" || $author === "" || $category === "") {

        $error = "Please fill all fields.";

    } elseif ($quantity <= 0) {

        $error = "Quantity must be greater than 0.";

    } elseif (!isset($_FILES["image"]) || $_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

        $error = "Please select a book image.";

    } else {

        // Image information
        $image = $_FILES["image"];

        $image_name = $image["name"];
        $image_tmp = $image["tmp_name"];
        $image_size = $image["size"];

        // Get file extension
        $image_extension = strtolower(
            pathinfo($image_name, PATHINFO_EXTENSION)
        );

        // Allowed image types
        $allowed_extensions = ["jpg", "jpeg", "png", "webp"];

        if (!in_array($image_extension, $allowed_extensions)) {

            $error = "Only JPG, JPEG, PNG and WEBP images are allowed.";

        } elseif ($image_size > 5 * 1024 * 1024) {

            $error = "Image size must be less than 5 MB.";

        } else {

            // Create a unique image name
            $new_image_name = uniqid("book_", true) . "." . $image_extension;

            // Upload folder
            $upload_folder = "uploads/books/";

            // Complete file path
            $upload_path = $upload_folder . $new_image_name;

            // Move image to uploads/books
            if (move_uploaded_file($image_tmp, $upload_path)) {

                // Insert book into database
                $sql = "INSERT INTO books
                        (title, author, category, image, quantity, available)
                        VALUES
                        (:title, :author, :category, :image, :quantity, :available)";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ":title" => $title,
                    ":author" => $author,
                    ":category" => $category,
                    ":image" => $new_image_name,
                    ":quantity" => $quantity,
                    ":available" => $quantity
                ]);

                $message = "Book added successfully!";

            } else {

                $error = "Failed to upload image.";

            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Book - Library System</title>

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

                <small>Librarian</small>

            </div>

        </div>

    </div>


    <div class="container-fluid dashboard-container">

        <div class="mb-4">

            <h1>Add New Book</h1>

            <p class="text-muted">
                Add a new book with its cover image.
            </p>

        </div>


        <?php if ($message !== ""): ?>

            <div class="alert alert-success">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <?php if ($error !== ""): ?>

            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <div class="card shadow-sm border-0">

            <div class="card-body p-4">

                <form method="POST" enctype="multipart/form-data">


                    <!-- Book Title -->

                    <div class="mb-3">

                        <label class="form-label">
                            Book Title
                        </label>

                        <input
                            type="text"
                            name="title"
                            class="form-control"
                            placeholder="Enter book title"
                            required
                        >

                    </div>


                    <!-- Author -->

                    <div class="mb-3">

                        <label class="form-label">
                            Author
                        </label>

                        <input
                            type="text"
                            name="author"
                            class="form-control"
                            placeholder="Enter author name"
                            required
                        >

                    </div>


                    <!-- Category -->

                    <div class="mb-3">

                        <label class="form-label">
                            Category
                        </label>

                        <select
                            name="category"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select Category
                            </option>

                            <option value="Programming">
                                Programming
                            </option>

                            <option value="Science">
                                Science
                            </option>

                            <option value="Mathematics">
                                Mathematics
                            </option>

                            <option value="History">
                                History
                            </option>

                            <option value="Literature">
                                Literature
                            </option>

                            <option value="Other">
                                Other
                            </option>

                        </select>

                    </div>


                    <!-- Book Image -->

                    <div class="mb-3">

                        <label class="form-label">
                            Book Cover Image
                        </label>

                        <input
                            type="file"
                            name="image"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp"
                            required
                        >

                        <small class="text-muted">
                            JPG, JPEG, PNG or WEBP. Maximum 5 MB.
                        </small>

                    </div>


                    <!-- Quantity -->

                    <div class="mb-4">

                        <label class="form-label">
                            Quantity
                        </label>

                        <input
                            type="number"
                            name="quantity"
                            class="form-control"
                            min="1"
                            placeholder="Enter number of copies"
                            required
                        >

                    </div>


                    <!-- Buttons -->

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        ➕ Add Book
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