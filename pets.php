<?php

session_start();

include "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$sql = "SELECT * FROM pets WHERE status = 'Available' ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Available Pets - Pet Adoption Management System</title>

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

    <div class="text-center mb-4">

        <h1>🐶 Available Pets</h1>

        <p class="text-muted">
            Find your new friend and give them a loving home ❤️
        </p>

    </div>


    <div class="row">

        <?php

        if (mysqli_num_rows($result) > 0) {

            while ($pet = mysqli_fetch_assoc($result)) {

        ?>

                <div class="col-md-4 mb-4">

                    <div class="card h-100 shadow-sm">

                        <?php if (!empty($pet["image"])) { ?>

                            <img
                                src="assets/images/<?php echo htmlspecialchars($pet["image"]); ?>"
                                class="card-img-top"
                                alt="<?php echo htmlspecialchars($pet["name"]); ?>"
                                style="height: 220px; object-fit: cover;"
                            >

                        <?php } else { ?>

                            <div
                                class="d-flex align-items-center justify-content-center bg-light"
                                style="height: 220px;"
                            >
                                <span class="fs-1">🐾</span>
                            </div>

                        <?php } ?>


                        <div class="card-body">

                            <h4 class="card-title">
                                <?php echo htmlspecialchars($pet["name"]); ?>
                            </h4>

                            <p class="card-text">

                                <strong>Species:</strong>
                                <?php echo htmlspecialchars($pet["species"]); ?>

                                <br>

                                <strong>Breed:</strong>
                                <?php echo htmlspecialchars($pet["breed"]); ?>

                                <br>

                                <strong>Age:</strong>
                                <?php echo htmlspecialchars($pet["age"]); ?> years

                                <br>

                                <strong>Gender:</strong>
                                <?php echo htmlspecialchars($pet["gender"]); ?>

                            </p>

                            <p class="card-text">

                                <?php echo htmlspecialchars($pet["description"]); ?>

                            </p>

                            <span class="badge bg-success">
                                <?php echo htmlspecialchars($pet["status"]); ?>
                            </span>

                        </div>


                        <div class="card-footer bg-white border-0">

                            <a
                                href="adopt.php?pet_id=<?php echo $pet["id"]; ?>"
                                class="btn btn-primary w-100"
                            >
                                ❤️ Request Adoption
                            </a>

                        </div>

                    </div>

                </div>

        <?php

            }

        } else {

        ?>

            <div class="col-12">

                <div class="alert alert-info text-center">

                    No pets are currently available for adoption.

                </div>

            </div>

        <?php

        }

        ?>

    </div>


    <div class="text-center mt-3 mb-5">

        <a href="user_dashboard.php" class="btn btn-secondary">
            ← Back to Dashboard
        </a>

    </div>

</div>

</body>

</html>