<?php

session_start();

include "../includes/db.php";

// Allow only logged-in administrators
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";
$message_type = "danger";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $species = trim($_POST["species"] ?? "");
    $breed = trim($_POST["breed"] ?? "");
    $age_input = $_POST["age"] ?? "";
    $gender = $_POST["gender"] ?? "";
    $description = trim($_POST["description"] ?? "");
    $status = $_POST["status"] ?? "Available";

    $allowed_genders = ["Male", "Female"];
    $allowed_statuses = ["Available", "Adopted"];

    // Validate input
    if (
        $name === "" ||
        $species === "" ||
        $breed === "" ||
        $age_input === "" ||
        $gender === "" ||
        $description === ""
    ) {

        $message = "Please fill in all required fields.";

    } elseif (
        !filter_var($age_input, FILTER_VALIDATE_INT) &&
        $age_input !== "0"
    ) {

        $message = "Please enter a valid age.";

    } elseif ((int) $age_input < 0 || (int) $age_input > 100) {

        $message = "Age must be between 0 and 100.";

    } elseif (!in_array($gender, $allowed_genders, true)) {

        $message = "Please select a valid gender.";

    } elseif (!in_array($status, $allowed_statuses, true)) {

        $message = "Please select a valid status.";

    } else {

        $age = (int) $age_input;
        $image_name = null;
        $upload_error = "";

        // Process an optional image upload
        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

                $upload_error = "The image upload failed.";

            } elseif ($_FILES["image"]["size"] > 5 * 1024 * 1024) {

                $upload_error = "The image must be 5 MB or smaller.";

            } else {

                $image_info = getimagesize($_FILES["image"]["tmp_name"]);

                if ($image_info === false) {

                    $upload_error = "Please upload a valid image.";

                } else {

                    $mime_type = $image_info["mime"];

                    $allowed_types = [
                        "image/jpeg" => "jpg",
                        "image/png" => "png",
                        "image/webp" => "webp"
                    ];

                    if (!isset($allowed_types[$mime_type])) {

                        $upload_error =
                            "Only JPG, PNG, and WEBP images are allowed.";

                    } else {

                        $upload_directory = "../assets/images/";

                        if (!is_dir($upload_directory)) {

                            $upload_error =
                                "The image upload directory does not exist.";

                        } elseif (!is_writable($upload_directory)) {

                            $upload_error =
                                "The image upload directory is not writable.";

                        } else {

                            // Generate a unique filename
                            $image_name =
                                bin2hex(random_bytes(16)) .
                                "." .
                                $allowed_types[$mime_type];

                            $destination =
                                $upload_directory . $image_name;

                            if (
                                !move_uploaded_file(
                                    $_FILES["image"]["tmp_name"],
                                    $destination
                                )
                            ) {

                                $upload_error =
                                    "Unable to save the uploaded image.";

                            }
                        }
                    }
                }
            }
        }

        if ($upload_error !== "") {

            $message = $upload_error;

        } else {

            $sql = "INSERT INTO pets
                    (name, species, breed, age, gender,
                     description, image, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            if ($stmt) {

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssissss",
                    $name,
                    $species,
                    $breed,
                    $age,
                    $gender,
                    $description,
                    $image_name,
                    $status
                );

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    header("Location: manage_pets.php?added=1");
                    exit();

                } else {

                    // Remove the uploaded file if the database insert fails
                    if ($image_name !== null) {

                        $uploaded_path =
                            "../assets/images/" . $image_name;

                        if (is_file($uploaded_path)) {
                            unlink($uploaded_path);
                        }
                    }

                    $message = "Unable to add the pet. Please try again.";

                    mysqli_stmt_close($stmt);
                }

            } else {

                $message = "Unable to prepare the database query.";

                if ($image_name !== null) {

                    $uploaded_path =
                        "../assets/images/" . $image_name;

                    if (is_file($uploaded_path)) {
                        unlink($uploaded_path);
                    }
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Pet - Admin Panel</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background-color: #f4f7fb;
        }

        .form-container {
            max-width: 750px;
            margin: 40px auto;
        }

        .form-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        }

        .page-title {
            color: #0d47a1;
            font-weight: bold;
        }

    </style>

</head>

<body>

<nav class="navbar navbar-dark" style="background-color: #0d47a1;">

    <div class="container">

        <a class="navbar-brand fw-bold" href="admin_dashboard.php">
            🐾 Pet Adoption Admin
        </a>

        <a href="admin_logout.php" class="btn btn-light btn-sm">
            Logout
        </a>

    </div>

</nav>

<div class="container form-container">

    <div class="card form-card p-4 p-md-5">

        <h2 class="page-title mb-2">Add New Pet</h2>

        <p class="text-muted mb-4">
            Enter the pet details below.
        </p>

        <?php if ($message !== "") { ?>

            <div class="alert alert-<?php
                echo htmlspecialchars($message_type);
            ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">Pet Name *</label>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        maxlength="100"
                        value="<?php echo htmlspecialchars($_POST["name"] ?? ""); ?>"
                        required
                    >

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">Species *</label>

                    <select name="species" class="form-select" required>

                        <option value="">Select Species</option>

                        <?php foreach (["Dog", "Cat", "Rabbit", "Bird", "Other"] as $option) { ?>

                            <option
                                value="<?php echo $option; ?>"
                                <?php echo (($_POST["species"] ?? "") === $option) ? "selected" : ""; ?>
                            >
                                <?php echo $option; ?>
                            </option>

                        <?php } ?>

                    </select>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">Breed *</label>

                    <input
                        type="text"
                        name="breed"
                        class="form-control"
                        maxlength="100"
                        value="<?php echo htmlspecialchars($_POST["breed"] ?? ""); ?>"
                        required
                    >

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">Age (years) *</label>

                    <input
                        type="number"
                        name="age"
                        class="form-control"
                        min="0"
                        max="100"
                        value="<?php echo htmlspecialchars($_POST["age"] ?? ""); ?>"
                        required
                    >

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">Gender *</label>

                    <select name="gender" class="form-select" required>

                        <option value="">Select Gender</option>

                        <option value="Male"
                            <?php echo (($_POST["gender"] ?? "") === "Male") ? "selected" : ""; ?>>
                            Male
                        </option>

                        <option value="Female"
                            <?php echo (($_POST["gender"] ?? "") === "Female") ? "selected" : ""; ?>>
                            Female
                        </option>

                    </select>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">Status *</label>

                    <select name="status" class="form-select" required>

                        <option value="Available"
                            <?php echo (($_POST["status"] ?? "Available") === "Available") ? "selected" : ""; ?>>
                            Available
                        </option>

                        <option value="Adopted"
                            <?php echo (($_POST["status"] ?? "") === "Adopted") ? "selected" : ""; ?>>
                            Adopted
                        </option>

                    </select>

                </div>

                <div class="col-12 mb-3">

                    <label class="form-label">Description *</label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="4"
                        required
                    ><?php echo htmlspecialchars($_POST["description"] ?? ""); ?></textarea>

                </div>

                <div class="col-12 mb-4">

                    <label class="form-label">Pet Image (Optional)</label>

                    <input
                        type="file"
                        name="image"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small class="text-muted">
                        Supported formats: JPG, PNG, WEBP. Maximum size: 5 MB.
                    </small>

                </div>

            </div>

            <button type="submit" class="btn btn-primary">
                Add Pet
            </button>

            <a href="manage_pets.php" class="btn btn-outline-secondary">
                Cancel
            </a>

        </form>

    </div>

</div>

</body>
</html>