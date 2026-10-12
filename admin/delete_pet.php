<?php

session_start();

include "../includes/db.php";

// Only logged-in admins can delete pets
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: manage_pets.php");
    exit();
}

// Get pet ID
$pet_id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$pet_id || $pet_id < 1) {
    header("Location: manage_pets.php?error=invalid");
    exit();
}

// Check whether the pet exists
$sql = "SELECT id, image FROM pets WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $pet_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$pet = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$pet) {
    header("Location: manage_pets.php?error=notfound");
    exit();
}

// Preserve adoption-request history.
// Do not delete a pet if any adoption requests reference it.
$sql = "SELECT COUNT(*) AS total
        FROM adoption_requests
        WHERE pet_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $pet_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$request_data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if ((int)$request_data["total"] > 0) {
    header("Location: manage_pets.php?error=requests");
    exit();
}

// Delete the pet using a prepared statement
$sql = "DELETE FROM pets WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $pet_id);

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    // Remove the image only if no other pet uses it.
    if (!empty($pet["image"])) {

        $image_name = basename($pet["image"]);

        $sql = "SELECT COUNT(*) AS total
                FROM pets
                WHERE image = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "s", $image_name);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $image_data = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        $image_path = "../assets/images/" . $image_name;

        if (
            (int)$image_data["total"] === 0 &&
            is_file($image_path)
        ) {
            unlink($image_path);
        }
    }

    header("Location: manage_pets.php?deleted=1");
    exit();

} else {

    mysqli_stmt_close($stmt);

    header("Location: manage_pets.php?error=deletefailed");
    exit();
}

?>