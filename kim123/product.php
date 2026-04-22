<?php
session_start();
include 'db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: store.php');
    exit;
}

$stmt = $conn->prepare("SELECT id, name, description, price, stock, image_url FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    header('Location: store.php');
    exit;
}
$product = $res->fetch_assoc();

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($product['name']) ?> — Baseball Store</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div style="max-width:900px;margin:28px auto;padding:18px">
    <a href="store.php">← Back to store</a>
    <div class="card" style="display:flex;gap:18px;margin-top:12px;align-items:flex-start">
      <div style="flex:1">
        <?php if(!empty($product['image_url'])): ?>
          <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100%;border-radius:8px">
        <?php else: ?>
          <div style="width:100%;height:280px;border-radius:8px;background:linear-gradient(90deg,#eef3fb,#fff);display:flex;align-items:center;justify-content:center;color:var(--muted)">No image</div>
        <?php endif; ?>
      </div>
      <div style="width:320px">
        <h2><?= htmlspecialchars($product['name']) ?></h2>
        <p style="color:var(--muted)"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        <p class="price" style="font-size:1.25rem;font-weight:700">$<?= number_format($product['price'],2) ?></p>
        <p style="color:var(--muted)">Stock: <?= (int)$product['stock'] ?></p>

        <form action="cart.php?action=add&id=<?= $product['id'] ?>" method="POST">
          <label for="qty">Quantity</label><br>
          <input id="qty" name="quantity" type="number" value="1" min="1" max="<?= (int)$product['stock'] ?>" style="width:90px;padding:6px;margin:6px 0;border-radius:6px;border:1px solid #e5e7eb"><br>
          <button class="btn" type="submit">Add to cart</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
