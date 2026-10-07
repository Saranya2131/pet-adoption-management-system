<?php

session_start();

include "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["pet_id"])) {
    header("Location: pets.php");
    exit();
}

$pet_id = intval($_GET["pet_id"]);
$user_id = $_SESSION["user_id"];

$message = "";
$message_type = "";


// Get pet information
$sql = "SELECT * FROM pets WHERE id = ? AND status = 'Available'";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $pet_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) != 1) {

    die("Pet not found or no longer available.");

}

$pet = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// Process adoption request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $request_message = trim($_POST["message"]);

    // Check whether the user already requested this pet
    $check_sql = "SELECT id FROM adoption_requests
                  WHERE user_id = ? AND pet_id = ?
                  AND status = 'Pending'";

    $check_stmt = mysqli_prepare($conn, $check_sql);

    mysqli_stmt_bind_param(
        $check_stmt,
        "ii",
        $user_id,
        $pet_id
    );

    mysqli_stmt_execute($check_stmt);

    $check_result = mysqli_stmt_get_result($check_stmt);


    if (mysqli_num_rows($check_result) > 0) {

        $message = "You have already requested to adopt this pet.";
        $message_type = "warning";

    } else {

        $insert_sql = "INSERT INTO adoption_requests
                       (user_id, pet_id, message, status)
                       VALUES (?, ?, ?, 'Pending')";

        $insert_stmt = mysqli_prepare($conn, $insert_sql);

        mysqli_stmt_bind_param(
            $insert_stmt,
            "iis",
            $user_id,
            $pet_id,
            $request_message
        );

        if (mysqli_stmt_execute($insert_stmt)) {

            $message = "Adoption request submitted successfully! ❤️";
            $message_type = "success";

        } else {

            $message = "Failed to submit adoption request.";
            $message_type = "danger";

        }

        mysqli_stmt_close($insert_stmt);
    }

    mysqli_stmt_close($check_stmt);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Request Adoption</title>

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

            <div class="card shadow">

                <div class="card-body p-4">

                    <h2 class="text-center mb-4">
                        ❤️ Request Adoption
                    </h2>


                    <?php if (!empty($message)) { ?>

                        <div class="alert alert-<?php echo $message_type; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>

                    <?php } ?>


                    <div class="text-center mb-4">

                        <?php if (!empty($pet["image"])) { ?>

                            <img
                                src="assets/images/<?php echo htmlspecialchars($pet["image"]); ?>"
                                alt="<?php echo htmlspecialchars($pet["name"]); ?>"
                                class="img-fluid rounded"
                                style="height: 250px; width: 100%; object-fit: cover;"
                            >

                        <?php } ?>

                    </div>


                    <h3>
                        <?php echo htmlspecialchars($pet["name"]); ?>
                    </h3>

                    <p>

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

                    <hr>


                    <form method="POST">

                        <div class="mb-3">

                            <label class="form-label">
                                Why would you like to adopt this pet?
                            </label>

                            <textarea
                                name="message"
                                class="form-control"
                                rows="5"
                                placeholder="Write a short message..."
                            ></textarea>

                        </div>


                        <button
                            type="submit"
                            class="btn btn-success w-100"
                        >
                            ❤️ Submit Adoption Request
                        </button>

                    </form>


                    <div class="text-center mt-3">

                        <a href="pets.php" class="btn btn-secondary">
                            ← Back to Available Pets
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>