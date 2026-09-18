<?php

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $address = trim($_POST["address"]);


    // Check required fields

    if (empty($name) || empty($email) || empty($password)) {

        die("Please fill in all required fields.");

    }


    // Check if email already exists

    $check = $conn->prepare(
        "SELECT id FROM users WHERE email = ?"
    );

    $check->bind_param("s", $email);

    $check->execute();

    $result = $check->get_result();


    if ($result->num_rows > 0) {

        die("Email already registered.");

    }


    // Hash password

    $hashed_password = password_hash(
        $password,
        PASSWORD_DEFAULT
    );


    // Insert user

    $stmt = $conn->prepare(
        "INSERT INTO users
        (name, email, phone, password, address)
        VALUES (?, ?, ?, ?, ?)"
    );


    $stmt->bind_param(
        "sssss",
        $name,
        $email,
        $phone,
        $hashed_password,
        $address
    );


    if ($stmt->execute()) {

        echo "Registration successful!";

        echo "<br><br>";

        echo '<a href="login.php">Go to Login</a>';

    } else {

        echo "Registration failed.";

    }


    $stmt->close();
    $check->close();
    $conn->close();

}

?>