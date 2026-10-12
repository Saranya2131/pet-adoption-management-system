<?php

session_start();

include "../includes/db.php";

// Allow only logged-in admins
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";
$message_type = "danger";
$pet = null;

// Get the pet ID from the URL
$pet_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$pet_id || $pet_id < 1) {
    header("Location: manage_pets.php");
    exit();
}

// Fetch existing pet details
$sql = "SELECT * FROM pets WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $pet_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$pet = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$pet) {
    header("Location: manage_pets.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $species = trim($_POST["species"] ?? "");
    $breed = trim($_POST["breed"] ?? "");
    $age_input = trim($_POST["age"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $status = trim($_POST["status"] ?? "");

    $allowed_species = [
        "Dog",
        "Cat",
        "Rabbit",
        "Bird",
        "Other"
    ];

    $allowed_genders = [
        "Male",
        "Female"
    ];

    $allowed_statuses = [
        "Available",
        "Adopted"
    ];

    // Validate required fields
    if (
        $name === "" ||
        $species === "" ||
        $breed === "" ||
        $age_input === "" ||
        $gender === "" ||
        $description === "" ||
        $status === ""
    ) {
        $message = "Please fill in all required fields.";

    } elseif (
        !in_array($species, $allowed_species, true) ||
        !in_array($gender, $allowed_genders, true) ||
        !in_array($status, $allowed_statuses, true)
    ) {
        $message = "Please select valid pet details.";

    } elseif (
        filter_var($age_input, FILTER_VALIDATE_INT) === false ||
        (int)$age_input < 0 ||
        (int)$age_input > 100
    ) {
        $message = "Age must be a whole number between 0 and 100.";

    } elseif (strlen($name) > 100 || strlen($breed) > 100) {
        $message = "Name and breed must not exceed 100 characters.";

    } else {

        $age = (int)$age_input;
        $image_name = $pet["image"];
        $new_image_path = null;

        // Check whether a replacement image was uploaded
        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

                $message = "The image upload failed. Please try again.";

            } elseif ($_FILES["image"]["size"] > 5 * 1024 * 1024) {

                $message = "The image must be smaller than 5 MB.";

            } else {

                $image_info = getimagesize($_FILES["image"]["tmp_name"]);

                $allowed_mime_types = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];

                $mime_type = $image_info["mime"] ?? "";

                if (
                    $image_info === false ||
                    !isset($allowed_mime_types[$mime_type])
                ) {

                    $message = "Please upload a valid JPG, PNG, or WEBP image.";

                } else {

                    $extension = $allowed_mime_types[$mime_type];

                    $image_name = bin2hex(random_bytes(16))
                        . "." . $extension;

                    $upload_directory = "../assets/images/";

                    if (
                        !is_dir($upload_directory) ||
                        !is_writable($upload_directory)
                    ) {

                        $message = "The image upload folder is not writable.";

                    } else {

                        $new_image_path = $upload_directory . $image_name;

                        if (
                            !move_uploaded_file(
                                $_FILES["image"]["tmp_name"],
                                $new_image_path
                            )
                        ) {
                            $message = "Unable to save the uploaded image.";
                            $new_image_path = null;
                        }
                    }
                }
            }
        }

        // Update the database if validation succeeded
        if ($message === "") {

            $sql = "UPDATE pets
                    SET name = ?,
                        species = ?,
                        breed = ?,
                        age = ?,
                        gender = ?,
                        description = ?,
                        image = ?,
                        status = ?
                    WHERE id = ?";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "sssissssi",
                $name,
                $species,
                $breed,
                $age,
                $gender,
                $description,
                $image_name,
                $status,
                $pet_id
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                // Remove the previous image only if no other pet uses it.
                if (
                    $new_image_path !== null &&
                    !empty($pet["image"]) &&
                    $pet["image"] !== $image_name
                ) {

                    $old_image_name = basename($pet["image"]);

                    $old_image_path =
                        "../assets/images/" . $old_image_name;

                    $check_sql = "SELECT COUNT(*) AS total
                                  FROM pets
                                  WHERE image = ?";

                    $check_stmt = mysqli_prepare($conn, $check_sql);

                    mysqli_stmt_bind_param(
                        $check_stmt,
                        "s",
                        $old_image_name
                    );

                    mysqli_stmt_execute($check_stmt);

                    $check_result = mysqli_stmt_get_result($check_stmt);
                    $image_usage = mysqli_fetch_assoc($check_result);

                    mysqli_stmt_close($check_stmt);

                    if (
                        (int)$image_usage["total"] === 0 &&
                        is_file($old_image_path)
                    ) {
                        unlink($old_image_path);
                    }
                }

                header("Location: manage_pets.php?updated=1");
                exit();

            } else {

                mysqli_stmt_close($stmt);

                // Remove the new image if the database update failed.
                if (
                    $new_image_path !== null &&
                    is_file($new_image_path)
                ) {
                    unlink($new_image_path);
                }

                $message = "Unable to update the pet. Please try again.";
            }
        }
    }

    // Keep submitted values in the form if validation fails.
    $pet["name"] = $name;
    $pet["species"] = $species;
    $pet["breed"] = $breed;
    $pet["age"] = $age_input;
    $pet["gender"] = $gender;
    $pet["description"] = $description;
    $pet["status"] = $status;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Pet | Pet Adoption System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="admin_dashboard.php">
            Pet Adoption Admin
        </a>

        <a href="manage_pets.php" class="btn btn-outline-light btn-sm">
            Back to Manage Pets
        </a>
    </div>
</nav>

<div class="container py-5">

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Edit Pet Details</h4>
                </div>

                <div class="card-body p-4">

                    <?php if ($message !== ""): ?>
                        <div class="alert alert-<?php echo $message_type; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        <div class="mb-3">
                            <label class="form-label">Pet Name</label>

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                maxlength="100"
                                value="<?php echo htmlspecialchars($pet["name"]); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Species</label>

                            <select name="species" class="form-select" required>
                                <?php
                                $species_options = [
                                    "Dog",
                                    "Cat",
                                    "Rabbit",
                                    "Bird",
                                    "Other"
                                ];

                                foreach ($species_options as $option):
                                ?>
                                    <option
                                        value="<?php echo $option; ?>"
                                        <?php
                                        echo $pet["species"] === $option
                                            ? "selected"
                                            : "";
                                        ?>
                                    >
                                        <?php echo $option; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Breed</label>

                            <input
                                type="text"
                                name="breed"
                                class="form-control"
                                maxlength="100"
                                value="<?php echo htmlspecialchars($pet["breed"]); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Age</label>

                            <input
                                type="number"
                                name="age"
                                class="form-control"
                                min="0"
                                max="100"
                                step="1"
                                value="<?php echo htmlspecialchars((string)$pet["age"]); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Gender</label>

                            <select name="gender" class="form-select" required>
                                <?php foreach (["Male", "Female"] as $option): ?>
                                    <option
                                        value="<?php echo $option; ?>"
                                        <?php
                                        echo $pet["gender"] === $option
                                            ? "selected"
                                            : "";
                                        ?>
                                    >
                                        <?php echo $option; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="4"
                                required
                            ><?php echo htmlspecialchars($pet["description"]); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Current Image</label>
                            <br>

                            <?php if (!empty($pet["image"])): ?>
                                <img
                                    src="../assets/images/<?php echo rawurlencode(basename($pet["image"])); ?>"
                                    alt="Current pet image"
                                    style="width: 150px; height: 120px; object-fit: cover;"
                                    class="rounded border"
                                >
                            <?php else: ?>
                                <p>No image uploaded.</p>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Replace Image (Optional)
                            </label>

                            <input
                                type="file"
                                name="image"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp"
                            >

                            <small class="text-muted">
                                JPG, PNG, or WEBP. Maximum size: 5 MB.
                                Leave empty to keep the current image.
                            </small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Status</label>

                            <select name="status" class="form-select" required>
                                <?php foreach (["Available", "Adopted"] as $option): ?>
                                    <option
                                        value="<?php echo $option; ?>"
                                        <?php
                                        echo $pet["status"] === $option
                                            ? "selected"
                                            : "";
                                        ?>
                                    >
                                        <?php echo $option; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Update Pet
                        </button>

                        <a href="manage_pets.php" class="btn btn-secondary">
                            Cancel
                        </a>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>

</body>
</html>