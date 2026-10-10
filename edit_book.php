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
    die("Invalid book ID.");
}


$message = "";
$error = "";


// Get existing book
$stmt = $pdo->prepare(
    "SELECT * FROM books WHERE id = :id"
);

$stmt->execute([
    ":id" => $book_id
]);

$book = $stmt->fetch();


if (!$book) {
    die("Book not found.");
}


// When form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);
    $author = trim($_POST["author"]);
    $category = trim($_POST["category"]);
    $quantity = (int) $_POST["quantity"];


    // Basic validation

    if ($title === "" || $author === "" || $category === "") {

        $error = "Please fill all fields.";

    } elseif ($quantity <= 0) {

        $error = "Quantity must be greater than 0.";

    } elseif ($quantity < $book["quantity"] - $book["available"]) {

        $error = "Quantity cannot be less than currently issued books.";

    } else {

        // Calculate currently issued copies

        $issued_books =
            $book["quantity"] - $book["available"];


        // Calculate new available quantity

        $new_available =
            $quantity - $issued_books;


        // Check whether a new image was selected

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] === UPLOAD_ERR_OK
        ) {

            $image = $_FILES["image"];

            $image_name = $image["name"];

            $image_tmp = $image["tmp_name"];

            $image_size = $image["size"];


            // Get extension

            $image_extension = strtolower(
                pathinfo(
                    $image_name,
                    PATHINFO_EXTENSION
                )
            );


            // Allowed extensions

            $allowed_extensions = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];


            if (
                !in_array(
                    $image_extension,
                    $allowed_extensions
                )
            ) {

                $error =
                    "Only JPG, JPEG, PNG and WEBP images are allowed.";

            } elseif (
                $image_size > 5 * 1024 * 1024
            ) {

                $error =
                    "Image size must be less than 5 MB.";

            } else {

                // Create new image name

                $new_image_name =
                    uniqid("book_", true)
                    . "."
                    . $image_extension;


                $upload_folder =
                    "uploads/books/";


                $upload_path =
                    $upload_folder
                    . $new_image_name;


                if (
                    move_uploaded_file(
                        $image_tmp,
                        $upload_path
                    )
                ) {

                    // Delete old image

                    if (
                        !empty($book["image"]) &&
                        file_exists(
                            $upload_folder
                            . $book["image"]
                        )
                    ) {

                        unlink(
                            $upload_folder
                            . $book["image"]
                        );

                    }


                    // Update with new image

                    $sql = "UPDATE books
                            SET title = :title,
                                author = :author,
                                category = :category,
                                image = :image,
                                quantity = :quantity,
                                available = :available
                            WHERE id = :id";

                    $stmt = $pdo->prepare($sql);

                    $stmt->execute([

                        ":title" => $title,

                        ":author" => $author,

                        ":category" => $category,

                        ":image" => $new_image_name,

                        ":quantity" => $quantity,

                        ":available" => $new_available,

                        ":id" => $book_id

                    ]);


                    $message =
                        "Book updated successfully!";


                    // Update displayed book data

                    $book["title"] = $title;

                    $book["author"] = $author;

                    $book["category"] = $category;

                    $book["image"] = $new_image_name;

                    $book["quantity"] = $quantity;

                    $book["available"] = $new_available;

                } else {

                    $error =
                        "Failed to upload image.";

                }

            }

        } else {

            // Update without changing image

            $sql = "UPDATE books
                    SET title = :title,
                        author = :author,
                        category = :category,
                        quantity = :quantity,
                        available = :available
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([

                ":title" => $title,

                ":author" => $author,

                ":category" => $category,

                ":quantity" => $quantity,

                ":available" => $new_available,

                ":id" => $book_id

            ]);


            $message =
                "Book updated successfully!";


            // Update displayed book data

            $book["title"] = $title;

            $book["author"] = $author;

            $book["category"] = $category;

            $book["quantity"] = $quantity;

            $book["available"] = $new_available;

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Edit Book - Library System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="assets/css/style.css">

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


    <a
        href="librarian_dashboard.php"
        class="menu-item">

        🏠 <span>Dashboard</span>

    </a>


    <a
        href="manage_books.php"
        class="menu-item active">

        📚 <span>Manage Books</span>

    </a>


    <a
        href="manage_students.php"
        class="menu-item">

        👥 <span>Students</span>

    </a>


    <a
        href="all_issues.php"
        class="menu-item">

        📖 <span>Issued Books</span>

    </a>


    <a
        href="all_returns.php"
        class="menu-item">

        ↩️ <span>Returned Books</span>

    </a>


    <a
        href="all_fines.php"
        class="menu-item">

        💰 <span>Fines</span>

    </a>


    <div class="sidebar-bottom">

        <a
            href="logout.php"
            class="menu-item">

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
                    echo htmlspecialchars(
                        $_SESSION["user_name"]
                    );
                    ?>

                </div>

                <small>Librarian</small>

            </div>

        </div>

    </div>


    <!-- Page Content -->

    <div
        class="container-fluid dashboard-container">


        <div class="dashboard-heading">

            <h1>Edit Book</h1>

            <p>
                Update the book information.
            </p>

        </div>


        <!-- Messages -->

        <?php if ($message !== ""): ?>

            <div class="alert alert-success">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <?php if ($error !== ""): ?>

            <div class="alert alert-danger">

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php endif; ?>


        <!-- Edit Form -->

        <div class="card shadow-sm border-0">

            <div class="card-body p-4">


                <form
                    method="POST"
                    enctype="multipart/form-data">


                    <!-- Title -->

                    <div class="mb-3">

                        <label class="form-label">
                            Book Title
                        </label>

                        <input
                            type="text"
                            name="title"
                            class="form-control"
                            value="<?php
                            echo htmlspecialchars(
                                $book["title"]
                            );
                            ?>"
                            required>

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
                            value="<?php
                            echo htmlspecialchars(
                                $book["author"]
                            );
                            ?>"
                            required>

                    </div>


                    <!-- Category -->

                    <div class="mb-3">

                        <label class="form-label">
                            Category
                        </label>

                        <input
                            type="text"
                            name="category"
                            class="form-control"
                            value="<?php
                            echo htmlspecialchars(
                                $book["category"]
                            );
                            ?>"
                            required>

                    </div>


                    <!-- Quantity -->

                    <div class="mb-3">

                        <label class="form-label">
                            Quantity
                        </label>

                        <input
                            type="number"
                            name="quantity"
                            class="form-control"
                            min="1"
                            value="<?php
                            echo $book["quantity"];
                            ?>"
                            required>

                        <small class="text-muted">

                            Currently issued:
                            <?php
                            echo
                                $book["quantity"]
                                -
                                $book["available"];
                            ?>

                        </small>

                    </div>


                    <!-- Current Image -->

                    <div class="mb-3">

                        <label class="form-label">
                            Current Image
                        </label>

                        <br>

                        <?php if (!empty($book["image"])): ?>

                            <img
                                src="uploads/books/<?php
                                echo htmlspecialchars(
                                    $book["image"]
                                );
                                ?>"
                                alt="Book Image"
                                style="
                                    width:120px;
                                    height:160px;
                                    object-fit:cover;
                                    border-radius:8px;
                                ">

                        <?php else: ?>

                            <p class="text-muted">
                                No image available.
                            </p>

                        <?php endif; ?>

                    </div>


                    <!-- New Image -->

                    <div class="mb-4">

                        <label class="form-label">

                            Change Image
                            <small class="text-muted">
                                (Optional)
                            </small>

                        </label>

                        <input
                            type="file"
                            name="image"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp">

                        <small class="text-muted">

                            JPG, JPEG, PNG or WEBP.
                            Maximum 5 MB.

                        </small>

                    </div>


                    <!-- Buttons -->

                    <button
                        type="submit"
                        class="btn btn-primary">

                        💾 Update Book

                    </button>


                    <a
                        href="manage_books.php"
                        class="btn btn-secondary">

                        Cancel

                    </a>


                </form>


            </div>

        </div>


    </div>

</div>


</body>

</html>