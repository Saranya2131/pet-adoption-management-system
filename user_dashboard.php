<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Dashboard - Pet Adoption Management System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <span class="navbar-brand">
            🐾 Pet Adoption Management System
        </span>

        <a href="logout.php" class="btn btn-light">
            Logout
        </a>

    </div>

</nav>


<div class="container mt-5">

    <div class="text-center">

        <h1>
            Welcome, <?php echo htmlspecialchars($_SESSION["full_name"]); ?>! 🐾
        </h1>

        <p class="text-muted">
            Welcome to your Pet Adoption Dashboard
        </p>

    </div>


    <div class="row mt-4">


        <div class="col-md-4 mb-3">

            <div class="card text-center shadow-sm">

                <div class="card-body">

                    <h3>🐶</h3>

                    <h5 class="card-title">
                        View Pets
                    </h5>

                    <p class="card-text">
                        Browse available pets for adoption.
                    </p>

                    <a href="pets.php" class="btn btn-primary">
                     View Pets
                    </a>

                </div>

            </div>

        </div>


        <div class="col-md-4 mb-3">

            <div class="card text-center shadow-sm">

                <div class="card-body">

                    <h3>❤️</h3>

                    <h5 class="card-title">
                        Adoption Requests
                    </h5>

                    <p class="card-text">
                        View your adoption requests.
                    </p>

                    <a href="my_requests.php" class="btn btn-success">
                        My Requests
                    </a>

                </div>

            </div>

        </div>


        <div class="col-md-4 mb-3">

            <div class="card text-center shadow-sm">

                <div class="card-body">

                    <h3>👤</h3>

                    <h5 class="card-title">
                        My Profile
                    </h5>

                    <p class="card-text">
                        View your account information.
                    </p>

                    <a href="profile.php" class="btn btn-secondary">
                        My Profile
                    </a>

                </div>

            </div>

        </div>


    </div>


    <div class="card mt-4 shadow-sm">

        <div class="card-body">

            <h4>Account Information</h4>

            <hr>

            <p>
                <strong>Name:</strong>
                <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
            </p>

            <p>
                <strong>Email:</strong>
                <?php echo htmlspecialchars($_SESSION["email"]); ?>
            </p>

        </div>

    </div>

</div>

</body>

</html>