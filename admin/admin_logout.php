<?php

session_start();

// Remove admin session data
unset(
    $_SESSION["admin_id"],
    $_SESSION["admin_name"],
    $_SESSION["admin_email"]
);

// Regenerate session ID after removing admin authentication
session_regenerate_id(true);

header("Location: admin_login.php");
exit();

?>