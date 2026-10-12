<?php

session_start();

include "../includes/db.php";

// Only logged-in admins can access this page.
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";
$message_type = "";

// Handle approval or rejection.
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $request_id = filter_input(
        INPUT_POST,
        "request_id",
        FILTER_VALIDATE_INT
    );

    $action = $_POST["action"] ?? "";

    if (!$request_id || $request_id < 1) {

        $message = "Invalid adoption request.";
        $message_type = "danger";

    } elseif (!in_array($action, ["approve", "reject"], true)) {

        $message = "Invalid action.";
        $message_type = "danger";

    } else {

        mysqli_begin_transaction($conn);

        try {

            // Lock the request and its pet while processing.
            $sql = "SELECT ar.id,
                           ar.pet_id,
                           ar.status,
                           p.status AS pet_status
                    FROM adoption_requests ar
                    INNER JOIN pets p ON ar.pet_id = p.id
                    WHERE ar.id = ?
                    FOR UPDATE";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $request_id
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $request = mysqli_fetch_assoc($result);

            mysqli_stmt_close($stmt);

            if (!$request) {

                throw new Exception(
                    "The adoption request was not found."
                );

            } elseif ($request["status"] !== "Pending") {

                throw new Exception(
                    "This request has already been processed."
                );

            } elseif (
                $action === "approve" &&
                $request["pet_status"] !== "Available"
            ) {

                throw new Exception(
                    "This pet is no longer available for adoption."
                );

            } else {

                if ($action === "approve") {

                    // Approve this request.
                    $sql = "UPDATE adoption_requests
                            SET status = 'Approved'
                            WHERE id = ?
                            AND status = 'Pending'";

                    $stmt = mysqli_prepare($conn, $sql);

                    mysqli_stmt_bind_param(
                        $stmt,
                        "i",
                        $request_id
                    );

                    mysqli_stmt_execute($stmt);

                    $updated_rows = mysqli_stmt_affected_rows($stmt);

                    mysqli_stmt_close($stmt);

                    if ($updated_rows !== 1) {
                        throw new Exception(
                            "The request could not be approved."
                        );
                    }

                    // Mark the pet as adopted.
                    $sql = "UPDATE pets
                            SET status = 'Adopted'
                            WHERE id = ?
                            AND status = 'Available'";

                    $stmt = mysqli_prepare($conn, $sql);

                    mysqli_stmt_bind_param(
                        $stmt,
                        "i",
                        $request["pet_id"]
                    );

                    mysqli_stmt_execute($stmt);

                    $updated_rows = mysqli_stmt_affected_rows($stmt);

                    mysqli_stmt_close($stmt);

                    if ($updated_rows !== 1) {
                        throw new Exception(
                            "The pet could not be marked as adopted."
                        );
                    }

                    // Reject other pending requests for this pet.
                    $sql = "UPDATE adoption_requests
                            SET status = 'Rejected'
                            WHERE pet_id = ?
                            AND id <> ?
                            AND status = 'Pending'";

                    $stmt = mysqli_prepare($conn, $sql);

                    mysqli_stmt_bind_param(
                        $stmt,
                        "ii",
                        $request["pet_id"],
                        $request_id
                    );

                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    $message =
                        "Adoption request approved successfully.";

                } else {

                    // Reject only the selected pending request.
                    $sql = "UPDATE adoption_requests
                            SET status = 'Rejected'
                            WHERE id = ?
                            AND status = 'Pending'";

                    $stmt = mysqli_prepare($conn, $sql);

                    mysqli_stmt_bind_param(
                        $stmt,
                        "i",
                        $request_id
                    );

                    mysqli_stmt_execute($stmt);

                    $updated_rows = mysqli_stmt_affected_rows($stmt);

                    mysqli_stmt_close($stmt);

                    if ($updated_rows !== 1) {
                        throw new Exception(
                            "The request could not be rejected."
                        );
                    }

                    $message =
                        "Adoption request rejected successfully.";
                }
            }

            mysqli_commit($conn);

            $message_type = "success";

        } catch (Throwable $e) {

            mysqli_rollback($conn);

            if ($message === "") {
                $message = $e->getMessage();
            }

            $message_type = "danger";
        }
    }
}

// Retrieve all adoption requests.
$sql = "SELECT
            ar.id AS request_id,
            ar.message AS request_message,
            ar.status AS request_status,
            ar.request_date,
            u.full_name,
            u.email,
            u.phone,
            p.name AS pet_name,
            p.species,
            p.breed,
            p.image
        FROM adoption_requests ar
        INNER JOIN users u ON ar.user_id = u.id
        INNER JOIN pets p ON ar.pet_id = p.id
        ORDER BY ar.request_date DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Adoption Requests</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <a href="admin_dashboard.php"
           class="navbar-brand">
            Pet Adoption Admin
        </a>

        <a href="admin_dashboard.php"
           class="btn btn-outline-light btn-sm">
            Back to Dashboard
        </a>

    </div>

</nav>

<div class="container py-5">

    <h2 class="mb-4">Manage Adoption Requests</h2>

    <?php if ($message !== ""): ?>

        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>

    <div class="card shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-dark">

                        <tr>
                            <th>Request ID</th>
                            <th>User Details</th>
                            <th>Pet Details</th>
                            <th>Message</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($result && mysqli_num_rows($result) > 0): ?>

                        <?php while ($row = mysqli_fetch_assoc($result)): ?>

                            <tr>

                                <td>
                                    <?php echo (int)$row["request_id"]; ?>
                                </td>

                                <td>
                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $row["full_name"]
                                        );
                                        ?>
                                    </strong>
                                    <br>

                                    <?php
                                    echo htmlspecialchars($row["email"]);
                                    ?>
                                    <br>

                                    <?php
                                    echo htmlspecialchars(
                                        $row["phone"] ?? ""
                                    );
                                    ?>
                                </td>

                                <td>

                                    <?php if (!empty($row["image"])): ?>

                                        <img
                                            src="../assets/images/<?php echo rawurlencode(basename($row["image"])); ?>"
                                            alt="Pet image"
                                            style="width: 80px; height: 65px; object-fit: cover;"
                                            class="rounded mb-2"
                                        >

                                        <br>

                                    <?php endif; ?>

                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $row["pet_name"]
                                        );
                                        ?>
                                    </strong>

                                    <br>

                                    <?php
                                    echo htmlspecialchars($row["species"]);
                                    ?>

                                    <br>

                                    <?php
                                    echo htmlspecialchars($row["breed"]);
                                    ?>

                                </td>

                                <td>
                                    <?php
                                    echo nl2br(
                                        htmlspecialchars(
                                            $row["request_message"] ?? ""
                                        )
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $row["request_date"]
                                    );
                                    ?>
                                </td>

                                <td>

                                    <?php
                                    $badge_class = "bg-secondary";

                                    if ($row["request_status"] === "Pending") {
                                        $badge_class = "bg-warning text-dark";
                                    } elseif (
                                        $row["request_status"] === "Approved"
                                    ) {
                                        $badge_class = "bg-success";
                                    } elseif (
                                        $row["request_status"] === "Rejected"
                                    ) {
                                        $badge_class = "bg-danger";
                                    }
                                    ?>

                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php
                                        echo htmlspecialchars(
                                            $row["request_status"]
                                        );
                                        ?>
                                    </span>

                                </td>

                                <td>

                                    <?php if ($row["request_status"] === "Pending"): ?>

                                        <form method="POST" class="mb-2">

                                            <input
                                                type="hidden"
                                                name="request_id"
                                                value="<?php echo (int)$row["request_id"]; ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="action"
                                                value="approve"
                                                class="btn btn-success btn-sm w-100"
                                                onclick="return confirm('Approve this adoption request?');"
                                            >
                                                Approve
                                            </button>

                                        </form>

                                        <form method="POST">

                                            <input
                                                type="hidden"
                                                name="request_id"
                                                value="<?php echo (int)$row["request_id"]; ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="action"
                                                value="reject"
                                                class="btn btn-danger btn-sm w-100"
                                                onclick="return confirm('Reject this adoption request?');"
                                            >
                                                Reject
                                            </button>

                                        </form>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Processed
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="text-center">
                                No adoption requests found.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>