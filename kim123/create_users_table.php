<?php
// One-click script to create a conventional `users` table for the app.
// Run in browser: http://localhost/kim123/create_users_table.php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once 'db_connect.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    echo "Database connection not available. Check db_connect.php.";
    exit;
}

$check = $conn->query("SHOW TABLES LIKE 'users'");
if ($check && $check->num_rows > 0) {
    echo "Table `users` already exists.\n";
    echo "<a href=\"set_kimadam_user.php\">Create/update user 'kimadam'</a>";
    exit;
}

$sql = file_get_contents(__DIR__ . '/sql/create_users_table.sql');
if ($sql === false) {
    echo "Failed to read SQL file: sql/create_users_table.sql";
    exit;
}

if ($conn->multi_query($sql)) {
    // consume results if any
    do { if ($res = $conn->store_result()) { $res->free(); } } while ($conn->more_results() && $conn->next_result());
    echo "Created `users` table successfully.<br>";
    echo "<a href=\"set_kimadam_user.php\">Create/update user 'kimadam'</a>";
} else {
    echo "Failed to create `users` table: " . htmlspecialchars($conn->error);
}
