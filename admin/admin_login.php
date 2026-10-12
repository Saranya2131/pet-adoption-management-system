<?php

session_start();

include "../includes/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

        $message = "Please enter your email and password.";

    } else {

        $sql = "SELECT id, name, email, password
                FROM admins
                WHERE email = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {

            $admin = mysqli_fetch_assoc($result);

            if (password_verify($password, $admin["password"])) {

                session_regenerate_id(true);

                $_SESSION["admin_id"] = $admin["id"];
                $_SESSION["admin_name"] = $admin["name"];
                $_SESSION["admin_email"] = $admin["email"];

                header("Location: admin_dashboard.php");
                exit();

            } else {

                $message = "Invalid email or password.";

            }

        } else {

            $message = "Invalid email or password.";

        }

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login - Pet Adoption Management System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: linear-gradient(135deg, #e3f2fd, #f5f7fa);
            min-height: 100vh;
        }

        .login-container {
            max-width: 450px;
            margin: 80px auto;
        }

        .login-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .login-icon {
            font-size: 45px;
        }

        .login-title {
            font-weight: bold;
            color: #0d47a1;
        }

    </style>

</head>

<body>

<div class="container login-container">

    <div class="card login-card p-4 p-md-5">

        <div class="text-center mb-4">

            <div class="login-icon">🔐</div>

            <h2 class="login-title mt-2">
                Admin Login
            </h2>

            <p class="text-muted">
                Pet Adoption Management System
            </p>

        </div>

        <?php if (!empty($message)) { ?>

            <div class="alert alert-danger">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>

        <form method="POST" action="">

            <div class="mb-3">

                <label class="form-label">Admin Email</label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter admin email"
                    required
                >

            </div>

            <div class="mb-4">

                <label class="form-label">Password</label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Enter admin password"
                    required
                >

            </div>

            <button
                type="submit"
                class="btn btn-primary w-100"
            >
                Login as Admin
            </button>

        </form>

        <div class="text-center mt-4">

            <a href="../login.php" class="text-decoration-none">
                Back to User Login
            </a>

        </div>

    </div>

</div>

</body>
</html>