<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Registration</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<!-- Navigation Bar -->

<nav class="navbar navbar-expand-lg bg-dark navbar-dark">

    <div class="container">

        <a class="navbar-brand" href="index.php">
            🐾 Pet Adoption
        </a>

        <a href="index.php" class="btn btn-outline-light">
            Home
        </a>

    </div>

</nav>


<!-- Registration Form -->

<div class="register-container">

    <div class="register-box">

        <h2>🐾 Create Account</h2>

        <p>Register to adopt your new friend</p>


        <form action="register_process.php" method="POST">

            <div class="mb-3">

                <label class="form-label">
                    Full Name
                </label>

                <input
                    type="text"
                    name="name"
                    class="form-control"
                    placeholder="Enter your full name"
                    required
                >

            </div>


            <div class="mb-3">

                <label class="form-label">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter your email"
                    required
                >

            </div>


            <div class="mb-3">

                <label class="form-label">
                    Phone
                </label>

                <input
                    type="tel"
                    name="phone"
                    class="form-control"
                    placeholder="Enter your phone number"
                >

            </div>


            <div class="mb-3">

                <label class="form-label">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Create a password"
                    required
                >

            </div>


            <div class="mb-3">

                <label class="form-label">
                    Address
                </label>

                <textarea
                    name="address"
                    class="form-control"
                    rows="3"
                    placeholder="Enter your address"
                ></textarea>

            </div>


            <button type="submit" class="btn btn-success w-100">
                Register
            </button>

        </form>


        <p class="login-link">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </p>

    </div>

</div>

</body>

</html>