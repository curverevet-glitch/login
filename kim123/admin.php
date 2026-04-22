<?php
session_start();
include 'db_connect.php';

// Simple protection: require logged-in user 'admin'
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header('Location: login.html?error=' . urlencode('Admin access required'));
    exit;
}

// Handle product creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $desc = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.00;
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $image = isset($_POST['image_url']) ? trim($_POST['image_url']) : null;

    $stmt = $conn->prepare('INSERT INTO products (name, description, price, stock, image_url) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('ssdiss', $name, $desc, $price, $stock, $image);
    $stmt->execute();
    header('Location: admin.php?created=1');
    exit;
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Add product</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div style="max-width:900px;margin:28px auto;padding:18px">
    <h2>Admin - Add Product</h2>
    <?php if(isset($_GET['created'])): ?><div class="msg success" style="display:block">Product created</div><?php endif; ?>
    <div class="card">
      <form method="POST">
        <div class="field"><label for="name">Name</label><br><input id="name" name="name" required style="width:100%;padding:8px"></div>
        <div class="field"><label for="desc">Description</label><br><textarea id="desc" name="description" style="width:100%;padding:8px"></textarea></div>
        <div style="display:flex;gap:12px">
          <div class="field" style="flex:1"><label>Price</label><br><input name="price" type="number" step="0.01" required style="width:100%;padding:8px"></div>
          <div class="field" style="width:140px"><label>Stock</label><br><input name="stock" type="number" value="10" style="width:100%;padding:8px"></div>
        </div>
        <div class="field"><label>Image URL</label><br><input name="image_url" style="width:100%;padding:8px"></div>
        <div class="field" style="margin-top:12px"><button class="btn" type="submit">Create product</button></div>
      </form>
    </div>
  </div>
</body>
</html>
