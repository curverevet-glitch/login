<?php
// Temporary diagnostics enabled to show PHP errors in the browser while debugging
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Create or update user 'kimadam' with the password 'kimadam123'
// Run in browser: http://localhost/kim123/set_kimadam_user.php
try {
    include_once 'db_connect.php';

    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new Exception('Database connection ($conn) is not available or not a mysqli instance.');
    }

    $username = 'kimadam';
    $password = 'kimadam123';
    $full_name = 'Kim Adam';

// Ensure users table exists
$check = $conn->query("SHOW TABLES LIKE 'users'");
if (!$check || $check->num_rows === 0) {
    echo "Database error: 'users' table not found. Please create your users table or run the setup SQL.<br>";
    echo "MySQL error: " . htmlspecialchars($conn->error);
    exit;
}

} catch (Exception $e) {
    echo '<strong>Exception:</strong> ' . htmlspecialchars($e->getMessage());
    if (isset($conn)) {
        echo '<br><strong>MySQL error:</strong> ' . htmlspecialchars($conn->error);
    }
}


// Inspect columns so we adapt to different schemas
$colsRes = $conn->query("SHOW COLUMNS FROM users");
$cols = [];
while ($c = $colsRes->fetch_assoc()) {
    $cols[] = $c['Field'];
}

// Ensure password_hash column exists; if not, create it
if (!in_array('password_hash', $cols)) {
    $alter = $conn->query("ALTER TABLE users ADD COLUMN password_hash VARCHAR(255) NULL");
    if (!$alter) {
        echo "Failed to add password_hash column: " . htmlspecialchars($conn->error);
        exit;
    }
    $cols[] = 'password_hash';
}

// We'll look up user by username (works even if id column is missing)
$stmt = $conn->prepare('SELECT username FROM users WHERE username = ? LIMIT 1');
if (!$stmt) {
    echo "Failed to prepare statement: " . htmlspecialchars($conn->error);
    exit;
}
$stmt->bind_param('s', $username);
$stmt->execute();

$found = false;
if (method_exists($stmt, 'get_result')) {
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $found = true;
    }
} else {
    $stmt->bind_result($foundUser);
    if ($stmt->fetch()) {
        $found = true;
    }
}

// Build update/insert dynamically based on available columns
$hash = password_hash($password, PASSWORD_DEFAULT);
if ($found) {
    // Update: prefer updating full_name if column exists
    if (in_array('full_name', $cols)) {
        $upd = $conn->prepare('UPDATE users SET password_hash = ?, full_name = ? WHERE username = ?');
        $upd->bind_param('sss', $hash, $full_name, $username);
    } elseif (in_array('email', $cols)) {
        // no full_name, but email exists -> update password only
        $upd = $conn->prepare('UPDATE users SET password_hash = ? WHERE username = ?');
        $upd->bind_param('ss', $hash, $username);
    } else {
        // minimal: update password_hash
        $upd = $conn->prepare('UPDATE users SET password_hash = ? WHERE username = ?');
        $upd->bind_param('ss', $hash, $username);
    }
    if (!$upd) {
        echo "Failed to prepare update: " . htmlspecialchars($conn->error);
        exit;
    }
    if ($upd->execute()) {
        echo "Updated user '<strong>" . htmlspecialchars($username) . "</strong>' with new password.<br>";
        echo "<a href=\"login.html\">Go to login</a>";
        exit;
    } else {
        echo "Failed to update user: " . htmlspecialchars($conn->error);
        exit;
    }
} else {
    // Insert: include available columns
    $fields = ['username', 'password_hash'];
    $placeholders = ['?', '?'];
    $types = 'ss';
    $values = [$username, $hash];
    if (in_array('full_name', $cols)) {
        $fields[] = 'full_name';
        $placeholders[] = '?';
        $types .= 's';
        $values[] = $full_name;
    }
    if (in_array('email', $cols) && !in_array('full_name', $cols)) {
        // if email exists but full_name doesn't, set email to username@example.com to satisfy NOT NULL
        $fields[] = 'email';
        $placeholders[] = '?';
        $types .= 's';
        $values[] = $username . '@example.com';
    }
    $sql = 'INSERT INTO users (' . implode(',', $fields) . ') VALUES (' . implode(',', $placeholders) . ')';
    $ins = $conn->prepare($sql);
    if (!$ins) {
        echo "Failed to prepare insert: " . htmlspecialchars($conn->error) . '<br>SQL: ' . htmlspecialchars($sql);
        exit;
    }
    // bind dynamically
    $bind_names[] = $types;
    for ($i=0; $i<count($values); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $values[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array(array($ins, 'bind_param'), $bind_names);
    if ($ins->execute()) {
        echo "Created user '<strong>" . htmlspecialchars($username) . "</strong>' with password '<strong>" . htmlspecialchars($password) . "</strong>'.<br>";
        echo "<a href=\"login.html\">Go to login</a>";
        exit;
    } else {
        echo "Failed to create user: " . htmlspecialchars($conn->error);
        exit;
    }
}
