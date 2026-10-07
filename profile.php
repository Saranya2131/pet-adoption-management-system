<?php

session_start();

include "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$sql = "SELECT id, full_name, email, phone, address, created_at
        FROM users
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) != 1) {
    die("User profile not found.");
}

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile - Pet Adoption Management System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <a href="user_dashboard.php" class="navbar-brand">
            🐾 Pet Adoption Management System
        </a>

        <a href="logout.php" class="btn btn-light">
            Logout
        </a>

    </div>

</nav>


<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-7">

            <div class="card shadow-sm">

                <div class="card-body p-4">

                    <div class="text-center mb-4">

                        <div class="fs-1">
                            👤
                        </div>

                        <h2>
                            My Profile
                        </h2>

                        <p class="text-muted">
                            Your account information
                        </p>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            <strong>Full Name</strong>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?php echo htmlspecialchars($user["full_name"]); ?>"
                            readonly
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            <strong>Email</strong>
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            value="<?php echo htmlspecialchars($user["email"]); ?>"
                            readonly
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            <strong>Phone</strong>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?php echo htmlspecialchars($user["phone"]); ?>"
                            readonly
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            <strong>Address</strong>
                        </label>

                        <textarea
                            class="form-control"
                            rows="3"
                            readonly
                        ><?php echo htmlspecialchars($user["address"]); ?></textarea>

                    </div>


                    <div class="mb-4">

                        <label class="form-label">
                            <strong>Account Created</strong>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?php echo date("d M Y, h:i A", strtotime($user["created_at"])); ?>"
                            readonly
                        >

                    </div>


                    <div class="text-center">

                        <a
                            href="user_dashboard.php"
                            class="btn btn-secondary"
                        >
                            ← Back to Dashboard
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>