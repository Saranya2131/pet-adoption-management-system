<?php

session_start();

include "../includes/db.php";

// Allow only logged-in admins
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch registered users
$sql = "SELECT id, full_name, email, phone, address, created_at
        FROM users
        ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Users | Pet Adoption System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <a href="admin_dashboard.php"
           class="navbar-brand">
            Pet Adoption Admin
        </a>

        <a href="admin_dashboard.php"
           class="btn btn-outline-light btn-sm">
            Back to Dashboard
        </a>

    </div>

</nav>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>Manage Users</h2>

        <span class="badge bg-primary fs-6">
            Registered Users:
            <?php echo $result ? mysqli_num_rows($result) : 0; ?>
        </span>

    </div>

    <div class="card shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-dark">

                        <tr>
                            <th>User ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Registration Date</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($result && mysqli_num_rows($result) > 0): ?>

                        <?php while ($user = mysqli_fetch_assoc($result)): ?>

                            <tr>

                                <td>
                                    <?php echo (int)$user["id"]; ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $user["full_name"] ?? ""
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $user["email"] ?? ""
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $user["phone"] ?? ""
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $user["address"] ?? ""
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $user["created_at"] ?? ""
                                    );
                                    ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="text-center">
                                No registered users found.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>