<?php

include "includes/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $address = trim($_POST["address"]);

    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($address)) {

        $message = "Please fill in all fields.";

    } else {

        $check_email = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check_email, "s", $email);
        mysqli_stmt_execute($check_email);
        mysqli_stmt_store_result($check_email);

        if (mysqli_stmt_num_rows($check_email) > 0) {

            $message = "Email already registered.";

        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (full_name, email, phone, password, address)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "sssss",
                $full_name,
                $email,
                $phone,
                $hashed_password,
                $address
            );

            if (mysqli_stmt_execute($stmt)) {
                $message = "Registration successful!";
            } else {
                $message = "Registration failed. Please try again.";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($check_email);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Registration - Pet Adoption Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5f7fa;
        }

        .register-container {
            max-width: 550px;
            margin: 50px auto;
        }

        .register-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .register-title {
            font-weight: bold;
        }
    </style>
</head>

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

<body>

<div class="container register-container">

    <div class="card register-card p-4">

        <h2 class="text-center register-title mb-4">
            🐾 Create Account
        </h2>

        <p class="text-center text-muted">
            Join the Pet Adoption Management System
        </p>

        <?php if (!empty($message)) { ?>

            <div class="alert alert-info">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>

        <form method="POST" action="">

            <div class="mb-3">
                <label class="form-label">Full Name</label>

                <input
                    type="text"
                    name="full_name"
                    class="form-control"
                    placeholder="Enter your full name"
                    required
                >
            </div>

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
                <label class="form-label">Phone</label>

                <input
                    type="tel"
                    name="phone"
                    class="form-control"
                    placeholder="Enter your phone number"
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
            placeholder="Create a password"
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

            <div class="mb-3">
                <label class="form-label">Address</label>

                <textarea
                    name="address"
                    class="form-control"
                    rows="3"
                    placeholder="Enter your address"
                    required
                ></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Register
            </button>

        </form>

        <p class="text-center mt-3 mb-0">
            Already have an account?
            <a href="login.php">Login</a>
        </p>

    </div>

</div>

</body>
</html>