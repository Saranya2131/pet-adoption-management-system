<?php

session_start();

include "../includes/db.php";

// Protect the dashboard from unauthorized access
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

// Get dashboard statistics

// Total registered users
$user_sql = "SELECT COUNT(*) AS total FROM users";
$user_result = mysqli_query($conn, $user_sql);
$total_users = mysqli_fetch_assoc($user_result)["total"];

// Total pets
$pet_sql = "SELECT COUNT(*) AS total FROM pets";
$pet_result = mysqli_query($conn, $pet_sql);
$total_pets = mysqli_fetch_assoc($pet_result)["total"];

// Available pets
$available_sql = "SELECT COUNT(*) AS total
                  FROM pets
                  WHERE status = 'Available'";
$available_result = mysqli_query($conn, $available_sql);
$available_pets = mysqli_fetch_assoc($available_result)["total"];

// Pending adoption requests
$request_sql = "SELECT COUNT(*) AS total
                FROM adoption_requests
                WHERE status = 'Pending'";
$request_result = mysqli_query($conn, $request_sql);
$pending_requests = mysqli_fetch_assoc($request_result)["total"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - Pet Adoption Management System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background-color: #f4f7fb;
        }

        .navbar {
            background-color: #0d47a1;
        }

        .dashboard-title {
            color: #0d47a1;
            font-weight: bold;
        }

        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
        }

        .action-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

    </style>

</head>

<body>

<!-- Navigation bar -->

<nav class="navbar navbar-dark navbar-expand-lg">

    <div class="container">

        <a class="navbar-brand fw-bold" href="admin_dashboard.php">
            🐾 Pet Adoption Admin
        </a>

        <div class="ms-auto d-flex align-items-center gap-3">

            <span class="text-white">
                Welcome,
                <?php echo htmlspecialchars($_SESSION["admin_name"]); ?>
            </span>

            <a href="admin_logout.php" class="btn btn-light btn-sm">
                Logout
            </a>

        </div>

    </div>

</nav>

<!-- Dashboard -->

<div class="container py-5">

    <div class="mb-4">

        <h2 class="dashboard-title">
            Admin Dashboard
        </h2>

        <p class="text-muted">
            Manage users, pets, and adoption requests.
        </p>

    </div>

    <!-- Statistics -->

    <div class="row g-4 mb-5">

        <div class="col-md-6 col-lg-3">

            <div class="card stat-card p-4">

                <p class="text-muted mb-2">Registered Users</p>

                <div class="stat-number text-primary">
                    <?php echo (int) $total_users; ?>
                </div>

            </div>

        </div>

        <div class="col-md-6 col-lg-3">

            <div class="card stat-card p-4">

                <p class="text-muted mb-2">Total Pets</p>

                <div class="stat-number text-success">
                    <?php echo (int) $total_pets; ?>
                </div>

            </div>

        </div>

        <div class="col-md-6 col-lg-3">

            <div class="card stat-card p-4">

                <p class="text-muted mb-2">Available Pets</p>

                <div class="stat-number text-info">
                    <?php echo (int) $available_pets; ?>
                </div>

            </div>

        </div>

        <div class="col-md-6 col-lg-3">

            <div class="card stat-card p-4">

                <p class="text-muted mb-2">Pending Requests</p>

                <div class="stat-number text-warning">
                    <?php echo (int) $pending_requests; ?>
                </div>

            </div>

        </div>

    </div>

    <!-- Management options -->

    <h4 class="mb-3">Management Options</h4>

    <div class="row g-4">

        <div class="col-md-6 col-lg-4">

            <div class="card action-card p-4 h-100">

                <h5>🐕 Manage Pets</h5>

                <p class="text-muted">
                    Add new pets, update pet information, and remove pet records.
                </p>

                <a href="manage_pets.php" class="btn btn-primary mt-auto">
                    Manage Pets
                </a>

            </div>

        </div>

        <div class="col-md-6 col-lg-4">

            <div class="card action-card p-4 h-100">

                <h5>📋 Adoption Requests</h5>

                <p class="text-muted">
                    View requests and manage their approval status.
                </p>

                <a href="manage_requests.php" class="btn btn-success mt-auto">
                    Manage Adoption Requests
                </a>

            </div>

        </div>

        <div class="col-md-6 col-lg-4">

            <div class="card action-card p-4 h-100">

                <h5>👥 Registered Users</h5>

                <p class="text-muted">
                    View registered users in the system.
                </p>

                <a href="manage_users.php" class="btn btn-secondary mt-auto">
                    Manage Users
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>