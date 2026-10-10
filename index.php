<?php

header("Location: login.php");
exit;

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Library Management System</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

    <div class="container mt-5">

        <div class="card shadow-sm">
            <div class="card-body text-center p-5">

                <h1 class="text-primary">
                    Library Management System
                </h1>

                <p class="text-muted mt-3">
                    Welcome to our Library Management System.
                </p>

                <a href="login.php" class="btn btn-primary mt-3">
                    Login
                </a>

                <a href="register.php" class="btn btn-outline-primary mt-3">
                    Register
                </a>

            </div>
        </div>

    </div>

</body>
</html>