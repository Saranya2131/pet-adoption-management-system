<?php

session_start();

include "includes/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

        $message = "Please enter email and password.";

    } else {

        $sql = "SELECT id, full_name, email, password FROM users WHERE email = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {

            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user["password"])) {

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["full_name"] = $user["full_name"];
                $_SESSION["email"] = $user["email"];

                header("Location: user_dashboard.php");
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

    <title>User Login - Pet Adoption Management System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background-color: #f5f7fa;
        }

        .login-container {
            max-width: 450px;
            margin: 80px auto;
        }

        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .login-title {
            font-weight: bold;
        }

    </style>

</head>

<body>

<div class="container login-container">

    <div class="card login-card p-4">

        <h2 class="text-center login-title mb-3">
            🐾 User Login
        </h2>

        <p class="text-center text-muted mb-4">
            Login to your Pet Adoption account
        </p>

        <?php if (!empty($message)) { ?>

            <div class="alert alert-danger">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>

        <form method="POST" action="">

            <div class="mb-3">

                <label class="form-label">Email</label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter your email"
                    required
                >

            </div>

            <div class="mb-3">

                <label class="form-label">Password</label>

                <div class="input-group">

                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        placeholder="Enter your password"
                        required
                    >

                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        onclick="togglePassword()"
                    >
                        Show
                    </button>

                </div>

            </div>

            <button
                type="submit"
                class="btn btn-primary w-100"
            >
                Login
            </button>

        </form>

        <p class="text-center mt-3 mb-0">

            Don't have an account?

            <a href="register.php">
                Register
            </a>

        </p>

    </div>

</div>

<script>

function togglePassword() {

    const password = document.getElementById("password");
    const button = event.target;

    if (password.type === "password") {

        password.type = "text";
        button.textContent = "Hide";

    } else {

        password.type = "password";
        button.textContent = "Show";

    }

}

</script>

</body>

</html>