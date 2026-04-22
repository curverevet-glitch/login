<?php
session_start();
include 'db_connect.php';

$res = $conn->query("SELECT id, name, description, price, stock, image_url FROM products ORDER BY created_at DESC");

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Baseball Store</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .hero{max-width:1100px;margin:18px auto;padding:18px}
    .product-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}
    .product-card img{width:100%;height:160px;object-fit:cover;border-radius:8px}
    .product-card .price{font-weight:700;color:var(--accent)}
  </style>
</head>
<body>
  <div class="hero">
    <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <h1>Baseball Store</h1>
      <div>
        <a href="cart.php">Cart</a>
        <?php if(isset($_SESSION['username'])): ?>
          | <a href="dashboard.php">Account</a>
        <?php else: ?>
          | <a href="login.html">Login</a>
        <?php endif; ?>
      </div>
    </header>

    <div class="product-grid">
      <?php while($row = $res->fetch_assoc()): ?>
        <div class="card product-card">
          <?php if(!empty($row['image_url'])): ?>
            <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
          <?php else: ?>
            <div style="width:100%;height:160px;border-radius:8px;background:linear-gradient(90deg,#eef3fb,#fff);display:flex;align-items:center;justify-content:center;color:var(--muted)">No image</div>
          <?php endif; ?>
          <h3><?= htmlspecialchars($row['name']) ?></h3>
          <p style="color:var(--muted);font-size:0.95rem;min-height:44px"><?= htmlspecialchars(substr($row['description'],0,120)) ?></p>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px">
            <div class="price">$<?= number_format($row['price'],2) ?></div>
            <a href="product.php?id=<?= $row['id'] ?>" class="btn">View</a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</body>
</html>
