<?php

$conn = mysqli_connect("localhost", "root", "", "pet_adoption");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

?>