<?php
session_start();
include_once "db_connect.php";

// Only allow admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html?error=Access denied");
    exit;
}

// Fetch users
$users = $conn->query("SELECT user_id, username, email, role, status FROM users ORDER BY user_id DESC");

// Fetch products
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
th { background: #f4f4f4; }
.btn { padding: 6px 12px; border-radius:6px; text-decoration:none; background:#2b6cb0; color:#fff; }
img { max-width: 80px; max-height: 50px; }
</style>
</head>
<body>
<h1>Admin Dashboard</h1>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> | <a href="logout.php">Logout</a></p>

<hr>

<h2>Manage Users</h2>
<table>
<tr>
<th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th>
</tr>
<?php while($user = $users->fetch_assoc()): ?>
<tr>
<td><?php echo $user['user_id']; ?></td>
<td><?php echo htmlspecialchars($user['username']); ?></td>
<td><?php echo htmlspecialchars($user['email']); ?></td>
<td><?php echo $user['role']; ?></td>
<td><?php echo $user['status']; ?></td>
</tr>
<?php endwhile; ?>
</table>

<h2>Manage Products</h2>
<a class="btn" href="addproduct.php">Add New Product</a>
<table>
<tr>
<th>ID</th><th>Name</th><th>Description</th><th>Price</th><th>Stock</th><th>Image</th>
</tr>
<?php while($prod = $products->fetch_assoc()): ?>
<tr>
<td><?php echo $prod['id']; ?></td>
<td><?php echo htmlspecialchars($prod['name']); ?></td>
<td><?php echo htmlspecialchars($prod['description']); ?></td>
<td>$<?php echo number_format($prod['price'],2); ?></td>
<td><?php echo $prod['stock']; ?></td>
<td>
<?php if($prod['image_url']): ?>
<img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="Product Image">
<?php else: ?>
-
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>