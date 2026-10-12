<?php

session_start();

include "../includes/db.php";

// Protect the admin page
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all pets
$sql = "SELECT * FROM pets ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Unable to retrieve pets.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Pets - Admin Panel</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background-color: #f4f7fb;
        }

        .page-title {
            color: #0d47a1;
            font-weight: bold;
        }

        .pet-image {
            width: 75px;
            height: 65px;
            object-fit: cover;
            border-radius: 8px;
        }

        .table-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07);
        }
    </style>
</head>

<body>

<?php if (isset($_GET["deleted"])): ?>

    <div class="alert alert-success">
        Pet deleted successfully.
    </div>

<?php elseif (isset($_GET["error"])): ?>

    <div class="alert alert-danger">

        <?php
        $errors = [
            "invalid" => "Invalid pet ID.",
            "notfound" => "The selected pet was not found.",
            "requests" => "This pet cannot be deleted because adoption requests exist for it. Update its status instead.",
            "deletefailed" => "Unable to delete the pet. Please try again."
        ];

        $error_code = $_GET["error"];

        echo htmlspecialchars(
            $errors[$error_code] ?? "An unexpected error occurred."
        );
        ?>

    </div>

<?php endif; ?>

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

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

        <div>
            <h2 class="page-title">Manage Pets</h2>
            <p class="text-muted mb-0">
                View and manage the pets registered in the system.
            </p>
        </div>

        <a href="add_pet.php" class="btn btn-primary">
            + Add New Pet
        </a>

    </div>

    <div class="card table-card">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Species</th>
                        <th>Breed</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (mysqli_num_rows($result) > 0) { ?>

                    <?php while ($pet = mysqli_fetch_assoc($result)) { ?>

                        <tr>

                            <td>
                                <?php echo (int) $pet["id"]; ?>
                            </td>

                            <td>
                                <?php if (!empty($pet["image"])) { ?>

                                    <img
                                        src="../assets/images/<?php
                                            echo rawurlencode(basename($pet["image"]));
                                        ?>"
                                        class="pet-image"
                                        alt="<?php
                                            echo htmlspecialchars($pet["name"]);
                                        ?>"
                                    >

                                <?php } else { ?>

                                    <span class="text-muted">No image</span>

                                <?php } ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($pet["name"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($pet["species"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($pet["breed"]); ?>
                            </td>

                            <td>
                                <?php echo (int) $pet["age"]; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($pet["gender"]); ?>
                            </td>

                            <td>
                                <?php if ($pet["status"] === "Available") { ?>

                                    <span class="badge bg-success">
                                        Available
                                    </span>

                                <?php } elseif ($pet["status"] === "Adopted") { ?>

                                    <span class="badge bg-secondary">
                                        Adopted
                                    </span>

                                <?php } else { ?>

                                    <span class="badge bg-warning text-dark">
                                        <?php echo htmlspecialchars($pet["status"]); ?>
                                    </span>

                                <?php } ?>
                            </td>

                            <td>
                                <div class="d-flex gap-2">

                                    <a
                                        href="edit_pet.php?id=<?php
                                            echo (int) $pet["id"];
                                        ?>"
                                        class="btn btn-sm btn-warning"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        action="delete_pet.php"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this pet?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?php
                                                echo (int) $pet["id"];
                                            ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-danger"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </div>
                            </td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>
                        <td colspan="9" class="text-center py-4">
                            No pets found. Click Add New Pet to register a pet.
                        </td>
                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

    <a
        href="admin_dashboard.php"
        class="btn btn-outline-secondary mt-4"
    >
        ← Back to Dashboard
    </a>

</div>

</body>
</html>