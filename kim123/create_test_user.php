<?php
// Creates a test user (idempotent). Run this once via browser or CLI to seed a user for testing login.
include_once 'db_connect.php';

$username = 'testuser';
$password = 'Password123!';
$full_name = 'Test User';

$stmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    echo "User '$username' already exists. You can log in using that account.<br>";
    echo "<a href=\"login.html\">Go to login</a>";
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$ins = $conn->prepare('INSERT INTO users (username, password_hash, full_name) VALUES (?, ?, ?)');
$ins->bind_param('sss', $username, $hash, $full_name);
if ($ins->execute()) {
    echo "Created test user: <strong>$username</strong> with password: <strong>$password</strong>.<br>";
    echo "<a href=\"login.html\">Go to login</a>";
} else {
    echo "Failed to create user: " . htmlspecialchars($conn->error);
}
