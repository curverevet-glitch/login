<?php
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']); // role from dropdown

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        header("Location: register.html?error=Please fill all fields");
        exit;
    }

    // Validate role
    $allowed_roles = ['admin','client'];
    if (!in_array($role, $allowed_roles)) {
        header("Location: register.html?error=Invalid role selected");
        exit;
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Use prepared statement
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password_hash, $role);

    if ($stmt->execute()) {
        // ✅ Redirect to login.html after successful registration
        header("Location: login.html?success=Registration successful! You can now login");
        exit;
    } else {
        // Duplicate username/email handling
        if (strpos($stmt->error, 'username') !== false) {
            header("Location: register.html?error=Username already exists");
        } elseif (strpos($stmt->error, 'email') !== false) {
            header("Location: register.html?error=Email already exists");
        } else {
            header("Location: register.html?error=Registration failed");
        }
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>