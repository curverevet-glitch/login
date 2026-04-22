<?php
session_start();
include 'db_connect.php';

// Initialize cart in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
if ($action === 'add') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    if ($id > 0 && $qty > 0) {
        if (!isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] = 0;
        }
        $_SESSION['cart'][$id] += $qty;
    }
    header('Location: cart.php');
    exit;
}

if ($action === 'remove') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0 && isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
    header('Location: cart.php');
    exit;
}

if ($action === 'update') {
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'qty_') === 0) {
            $id = (int)substr($k,4);
            $qty = max(0, (int)$v);
            if ($qty <= 0) {
                unset($_SESSION['cart'][$id]);
            } else {
                $_SESSION['cart'][$id] = $qty;
            }
        }
    }
    header('Location: cart.php');
    exit;
}

// Load product details for items in cart
$cart_items = [];
$total = 0.00;
if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0,count($ids),'?'));
    $types = str_repeat('i', count($ids));
    $stmt = $conn->prepare("SELECT id, name, price, stock FROM products WHERE id IN ($placeholders)");
    if ($stmt) {
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($p = $res->fetch_assoc()) {
            $id = $p['id'];
            $qty = isset($_SESSION['cart'][$id]) ? (int)$_SESSION['cart'][$id] : 0;
            $subtotal = $qty * $p['price'];
            $total += $subtotal;
            $cart_items[] = ['product'=>$p, 'qty'=>$qty, 'subtotal'=>$subtotal];
        }
    }
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Your Cart</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div style="max-width:900px;margin:28px auto;padding:18px">
    <a href="store.php">← Continue shopping</a>
    <h2>Your Cart</h2>

    <?php if (empty($cart_items)): ?>
      <div class="card">Your cart is empty.</div>
    <?php else: ?>
      <form action="cart.php?action=update" method="POST">
        <div class="card">
          <?php foreach($cart_items as $item): $p=$item['product']; ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f1f5f9">
              <div>
                <strong><?= htmlspecialchars($p['name']) ?></strong><br>
                <small style="color:var(--muted)">$<?= number_format($p['price'],2) ?> each</small>
              </div>
              <div>
                <input name="qty_<?= $p['id'] ?>" value="<?= $item['qty'] ?>" type="number" min="0" style="width:80px;padding:6px;border-radius:6px;border:1px solid #e5e7eb">
                <a href="cart.php?action=remove&id=<?= $p['id'] ?>" style="margin-left:12px;color:#9b1c1c">Remove</a>
              </div>
            </div>
          <?php endforeach; ?>

          <div style="display:flex;justify-content:space-between;align-items:center;padding-top:12px">
            <div><strong>Total</strong></div>
            <div><strong>$<?= number_format($total,2) ?></strong></div>
          </div>

          <div style="margin-top:12px;display:flex;gap:10px">
            <button class="btn" type="submit">Update cart</button>
            <form action="checkout.php" method="POST" style="display:inline">
              <button class="btn" type="submit">Checkout</button>
            </form>
          </div>
        </div>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
