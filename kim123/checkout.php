<?php
session_start();
include 'db_connect.php';

// Require login to checkout
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html?error=' . urlencode('Please login to checkout'));
    exit;
}

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Calculate total and create order
$cart = $_SESSION['cart'];
$ids = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

$stmt = $conn->prepare("SELECT id, price, stock FROM products WHERE id IN ($placeholders)");
if (!$stmt) {
    die('DB error');
}
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
$total = 0.0;
while ($p = $res->fetch_assoc()) {
    $id = $p['id'];
    $qty = isset($cart[$id]) ? (int)$cart[$id] : 0;
    if ($qty <= 0) continue;
    // don't allow purchase beyond stock
    $qty = min($qty, (int)$p['stock']);
    $items[] = ['id'=>$id, 'qty'=>$qty, 'unit_price'=> $p['price']];
    $total += $qty * $p['price'];
}

// Insert order
$stmt = $conn->prepare('INSERT INTO orders (user_id, total) VALUES (?, ?)');
$uid = $_SESSION['user_id'];
$stmt->bind_param('id', $uid, $total);
$stmt->execute();
$order_id = $stmt->insert_id;

// Insert order_items and decrement stock
$stmtItem = $conn->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
$updStock = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
foreach ($items as $it) {
    $stmtItem->bind_param('iiid', $order_id, $it['id'], $it['qty'], $it['unit_price']);
    $stmtItem->execute();
    // reduce stock (best-effort)
    $updStock->bind_param('iii', $it['qty'], $it['id'], $it['qty']);
    $updStock->execute();
}

// Clear cart
$_SESSION['cart'] = [];

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Order Confirmed</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div style="max-width:700px;margin:32px auto;padding:18px">
    <div class="card">
      <h2>Thank you — order placed</h2>
      <p>Your order <strong>#<?= $order_id ?></strong> was received. Total: <strong>$<?= number_format($total,2) ?></strong>.</p>
      <p><a href="store.php">Continue shopping</a> | <a href="dashboard.php">Account</a></p>
    </div>
  </div>
</body>
</html>
