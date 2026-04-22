<?php
session_start();
include_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        header("Location: login.php?error=Please fill all fields");
        exit;
    }

    // Prepare SQL to include role
    $stmt = $conn->prepare("SELECT user_id, username, email, password_hash, role, status FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        // Check account status
        if ($user['status'] !== 'active') {
            header("Location: login.php?error=Account inactive");
            exit;
        }

        // Verify password
        if (password_verify($password, $user['password_hash'])) {

            // Create session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role']; // store role in session

            // Redirect based on role
            if ($user['role'] === 'client') {
                header("Location: dashboard.php");
            } else {
                header("Location: admindashboard.php");
            }
            exit;

        } else {
            header("Location: login.php?error=Invalid password");
            exit;
        }

    } else {
        header("Location: login.php?error=User not found");
        exit;
    }

} else {
    header("Location: login.php");
    exit;
}
?>