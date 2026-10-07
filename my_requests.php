<?php

session_start();

include "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$sql = "SELECT 
            adoption_requests.id,
            adoption_requests.message,
            adoption_requests.status,
            adoption_requests.request_date,
            pets.name AS pet_name,
            pets.species,
            pets.breed,
            pets.age,
            pets.gender,
            pets.image
        FROM adoption_requests
        INNER JOIN pets
            ON adoption_requests.pet_id = pets.id
        WHERE adoption_requests.user_id = ?
        ORDER BY adoption_requests.request_date DESC";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Adoption Requests</title>

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

        <h1>❤️ My Adoption Requests</h1>

        <p class="text-muted">
            View the pets you have requested to adopt.
        </p>

    </div>


    <?php if (mysqli_num_rows($result) > 0) { ?>

        <div class="row">

            <?php while ($request = mysqli_fetch_assoc($result)) { ?>

                <div class="col-md-6 mb-4">

                    <div class="card shadow-sm h-100">

                        <div class="row g-0">

                            <div class="col-md-5">

                                <?php if (!empty($request["image"])) { ?>

                                    <img
                                        src="assets/images/<?php echo htmlspecialchars($request["image"]); ?>"
                                        class="img-fluid rounded-start h-100"
                                        alt="<?php echo htmlspecialchars($request["pet_name"]); ?>"
                                        style="object-fit: cover; min-height: 250px;"
                                    >

                                <?php } else { ?>

                                    <div
                                        class="d-flex align-items-center justify-content-center bg-light rounded-start"
                                        style="height: 250px;"
                                    >
                                        <span class="fs-1">🐾</span>
                                    </div>

                                <?php } ?>

                            </div>


                            <div class="col-md-7">

                                <div class="card-body">

                                    <h4 class="card-title">
                                        <?php echo htmlspecialchars($request["pet_name"]); ?>
                                    </h4>

                                    <p class="card-text">

                                        <strong>Species:</strong>
                                        <?php echo htmlspecialchars($request["species"]); ?>

                                        <br>

                                        <strong>Breed:</strong>
                                        <?php echo htmlspecialchars($request["breed"]); ?>

                                        <br>

                                        <strong>Age:</strong>
                                        <?php echo htmlspecialchars($request["age"]); ?> years

                                        <br>

                                        <strong>Gender:</strong>
                                        <?php echo htmlspecialchars($request["gender"]); ?>

                                    </p>

                                    <hr>

                                    <p>

                                        <strong>Your Message:</strong><br>

                                        <?php

                                        if (!empty($request["message"])) {

                                            echo htmlspecialchars($request["message"]);

                                        } else {

                                            echo "No message provided.";

                                        }

                                        ?>

                                    </p>


                                    <?php

                                    $status = $request["status"];

                                    if ($status == "Pending") {

                                        $badge_class = "bg-warning text-dark";

                                    } elseif ($status == "Approved") {

                                        $badge_class = "bg-success";

                                    } elseif ($status == "Rejected") {

                                        $badge_class = "bg-danger";

                                    } else {

                                        $badge_class = "bg-secondary";

                                    }

                                    ?>


                                    <p>

                                        <strong>Status:</strong>

                                        <span class="badge <?php echo $badge_class; ?>">

                                            <?php echo htmlspecialchars($status); ?>

                                        </span>

                                    </p>


                                    <p class="text-muted small">

                                        Requested on:

                                        <?php

                                        echo date(
                                            "d M Y, h:i A",
                                            strtotime($request["request_date"])
                                        );

                                        ?>

                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            <?php } ?>

        </div>


    <?php } else { ?>

        <div class="alert alert-info text-center">

            <h5>No Adoption Requests Yet</h5>

            <p class="mb-3">
                You haven't requested to adopt any pets yet.
            </p>

            <a href="pets.php" class="btn btn-primary">
                🐶 View Available Pets
            </a>

        </div>

    <?php } ?>


    <div class="text-center mt-4 mb-5">

        <a href="user_dashboard.php" class="btn btn-secondary">
            ← Back to Dashboard
        </a>

    </div>

</div>


</body>

</html>

<?php

mysqli_stmt_close($stmt);

?>