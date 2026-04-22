<?php
session_start();
include_once "db_connect.php";

// Only admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html?error=Access denied");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);
    $image_url = trim($_POST['image_url']);

    // Validation
    if (empty($name) || !is_numeric($price) || !is_numeric($stock)) {
        die("Name, valid price and stock are required.");
    }

    // Prepare statement
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $name, $desc, $price, $stock, $image_url);

    if ($stmt->execute()) {
        header("Location: admindashboard.php?success=Product added successfully");
        exit;
    } else {
        die("Error: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}
?>

<!doctype html>
<html>
<head>
<title>Add Product</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<h1>Add Product</h1>
<form method="POST">
<label>Name</label><br>
<input type="text" name="name" required><br><br>

<label>Description</label><br>
<textarea name="description"></textarea><br><br>

<label>Price</label><br>
<input type="number" step="0.01" name="price" required><br><br>

<label>Stock</label><br>
<input type="number" name="stock" required><br><br>

<label>Image URL</label><br>
<input type="text" name="image_url"><br><br>

<button type="submit">Add Product</button>
</form>
<a href="admindashboard.php">Back to Dashboard</a>
</body>
</html>