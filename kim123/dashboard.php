<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

include_once "db_connect.php";

$displayName = htmlspecialchars($_SESSION['username']);
$user_id = $_SESSION['user_id'];

// Fetch products
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");
if (!$products) {
    die("Error fetching products: " . $conn->error);
}

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'], $_POST['product_id'])) {
    $comment = trim($_POST['comment']);
    $product_id = intval($_POST['product_id']);

    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO comments (product_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $product_id, $user_id, $comment);
        $stmt->execute();
        $stmt->close();
        header("Location: dashboard.php");
        exit;
    }
}

// Handle buy action (simulated)
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_product_id'])) {
    $buy_product_id = intval($_POST['buy_product_id']);
    // Optional: Decrement stock here or add orders table
    $success_message = "You bought product ID $buy_product_id successfully!";
}

// Fetch comments grouped by product
$comments = [];
$commentRes = $conn->query("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id=u.user_id ORDER BY c.created_at DESC");
while ($row = $commentRes->fetch_assoc()) {
    $comments[$row['product_id']][] = $row;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.card { border:1px solid #ddd; padding:12px; margin:12px 0; border-radius:6px; }
.product-img { max-width:150px; max-height:100px; display:block; margin-bottom:8px; }
.comment-box { margin-top:8px; }
.comment { background:#f4f4f4; padding:6px; margin-bottom:4px; border-radius:4px; }
.btn { padding:6px 12px; border-radius:4px; text-decoration:none; background:#2b6cb0; color:#fff; border:none; cursor:pointer; }
</style>
</head>
<body>

<h1>Welcome back, <?php echo $displayName; ?></h1>
<a href="logout.php">Logout</a>
<hr>

<?php if($success_message): ?>
<p style="color:green;"><?php echo $success_message; ?></p>
<?php endif; ?>

<h2>Products</h2>

<?php while($prod = $products->fetch_assoc()): ?>
<div class="card">
    <h3><?php echo htmlspecialchars($prod['name']); ?></h3>
    <?php if($prod['image_url']): ?>
        <img class="product-img" src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="Product Image">
    <?php endif; ?>
    <p><?php echo htmlspecialchars($prod['description']); ?></p>
    <p>Price: $<?php echo number_format($prod['price'],2); ?> | Stock: <?php echo $prod['stock']; ?></p>

    <!-- Buy Form -->
    <form method="POST" style="display:inline-block;">
        <input type="hidden" name="buy_product_id" value="<?php echo $prod['id']; ?>">
        <button type="submit" class="btn">Buy</button>
    </form>

    <!-- Comment Form -->
    <div class="comment-box">
        <form method="POST">
            <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
            <input type="text" name="comment" placeholder="Write a comment..." style="width:70%;" required>
            <button type="submit" class="btn">Comment</button>
        </form>

        <?php if(isset($comments[$prod['id']])): ?>
            <?php foreach($comments[$prod['id']] as $c): ?>
                <div class="comment">
                    <strong><?php echo htmlspecialchars($c['username']); ?>:</strong> <?php echo htmlspecialchars($c['comment']); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php endwhile; ?>

</body>
</html>