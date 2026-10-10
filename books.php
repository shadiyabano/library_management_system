<?php

session_start();

require_once "config/db.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Only students can access this page
if ($_SESSION["role"] !== "student") {
    header("Location: librarian_dashboard.php");
    exit;
}


// Search
$search = $_GET["search"] ?? "";


// Category
$category = $_GET["category"] ?? "";


// Get categories
$category_stmt = $pdo->query(
    "SELECT DISTINCT category
     FROM books
     WHERE category IS NOT NULL
     AND category != ''
     ORDER BY category"
);

$categories = $category_stmt->fetchAll();


// Get books
$sql = "SELECT * FROM books
        WHERE available > 0
        AND is_archived = 0";

$params = [];


// Search filter
if ($search !== "") {

    $sql .= " AND (
        title LIKE :search
        OR author LIKE :search
        OR category LIKE :search
    )";

    $params[":search"] = "%" . $search . "%";
}


// Category filter
if ($category !== "") {

    $sql .= " AND category = :category";

    $params[":category"] = $category;
}


$sql .= " ORDER BY id DESC";


$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$books = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Browse Books - Library System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <style>

        .books-page {
            padding: 35px;
        }

        .page-title h1 {
            font-size: 30px;
            margin-bottom: 5px;
        }

        .page-title p {
            color: #6c7a91;
        }

        .filter-box {
            background: white;
            border: 1px solid #e1e7ef;
            border-radius: 12px;
            padding: 18px;
            margin-top: 25px;
        }

        .book-card {
            background: white;
            border: 1px solid #e1e7ef;
            border-radius: 14px;
            padding: 20px;
            height: 100%;
            transition: 0.2s;
        }

        .book-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }

        .book-cover {
            height: 130px;
            background: #edf5ff;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 55px;
            margin-bottom: 18px;
        }

        .book-title {
            font-size: 19px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .book-author {
            color: #6c7a91;
            margin-bottom: 10px;
        }

        .book-category {
            display: inline-block;
            background: #eef4ff;
            color: #1769ff;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .available-text {
            font-size: 13px;
            color: #198754;
        }

    </style>

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


    <a href="dashboard.php" class="menu-item">
        🏠
        <span>Dashboard</span>
    </a>


    <a href="books.php" class="menu-item active">
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


    <!-- Header -->

    <div class="top-header">

        <div></div>

        <div class="user-info">

            <div class="user-icon">
                👤
            </div>

            <div>

                <div class="welcome-text">
                    <?php echo htmlspecialchars($_SESSION["user_name"]); ?>
                </div>

                <small>
                    Student
                </small>

            </div>

        </div>

    </div>



    <!-- Books -->

    <div class="books-page">


        <div class="page-title">

            <h1>Browse Books</h1>

            <p>
                Find a book you want to read and borrow it.
            </p>

        </div>



        <!-- Search and Filter -->

        <div class="filter-box">

            <form method="GET">

                <div class="row g-3">


                    <div class="col-md-6">

                        <label class="form-label">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search by title, author or category..."
                            value="<?php echo htmlspecialchars($search); ?>"
                        >

                    </div>


                    <div class="col-md-4">

                        <label class="form-label">
                            Category
                        </label>

                        <select
                            name="category"
                            class="form-select"
                        >

                            <option value="">
                                All Categories
                            </option>

                            <?php foreach ($categories as $item): ?>

                                <option
                                    value="<?php echo htmlspecialchars($item["category"]); ?>"
                                    <?php
                                    if ($category === $item["category"]) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    <?php echo htmlspecialchars($item["category"]); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="col-md-2 d-flex align-items-end">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Search
                        </button>

                    </div>

                </div>

            </form>

        </div>



        <!-- Number of Books -->

        <div class="d-flex justify-content-between align-items-center mt-4 mb-3">

            <h5>
                Available Books
            </h5>

            <span class="text-muted">
                <?php echo count($books); ?> books found
            </span>

        </div>



        <!-- Book Cards -->

        <div class="row g-4">


            <?php if (count($books) > 0): ?>


                <?php foreach ($books as $book): ?>


                    <div class="col-xl-3 col-lg-4 col-md-6">


                        <div class="book-card">


                        

                            <?php if (!empty($book["image"])): ?>

    <img
        src="uploads/books/<?php echo htmlspecialchars($book["image"]); ?>"
        alt="Book Cover"
        style="width: 100%; height: 250px; object-fit: cover;"
    >

<?php else: ?>

    <div class="text-center p-5 bg-light">
        📚 No Image
    </div>

<?php endif; ?>


                            <div class="book-title">

                                <?php
                                echo htmlspecialchars($book["title"]);
                                ?>

                            </div>


                            <div class="book-author">

                                By
                                <?php
                                echo htmlspecialchars($book["author"]);
                                ?>

                            </div>


                            <div class="book-category">

                                <?php
                                echo htmlspecialchars($book["category"]);
                                ?>

                            </div>


                            <div class="available-text mb-3">

                                ✓
                                <?php echo $book["available"]; ?>
                                available

                            </div>


                            <a
                                href="borrow_book.php?id=<?php echo $book["id"]; ?>"
                                class="btn btn-primary w-100"
                            >
                                Borrow Book
                            </a>


                        </div>


                    </div>


                <?php endforeach; ?>


            <?php else: ?>


                <div class="col-12">

                    <div class="alert alert-light border text-center">

                        <h5>
                            No books found
                        </h5>

                        <p class="text-muted mb-0">

                            There are currently no available books
                            matching your search.

                        </p>

                    </div>

                </div>


            <?php endif; ?>


        </div>


    </div>

</div>


</body>

</html>