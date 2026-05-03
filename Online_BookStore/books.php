<?php
session_start();
require_once 'db.php';


if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}


if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$message = "";
$error_message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    $book_id = $_POST['book_id'];
    
    
    $stmt = $conn->prepare("SELECT stock FROM Books WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $stmt->bind_result($db_stock);
    
    if ($stmt->fetch()) {
        
        $current_cart_qty = isset($_SESSION['cart'][$book_id]) ? $_SESSION['cart'][$book_id] : 0;
        
        
        if ($current_cart_qty < $db_stock) {
            $_SESSION['cart'][$book_id] = $current_cart_qty + 1;
            $message = "Book added to cart successfully!";
        } else {
            $error_message = "Cannot add more. Maximum stock reached for this book!";
        }
    }
    $stmt->close();
}


$cart_count = array_sum($_SESSION['cart']);


$query = "SELECT * FROM book_details";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Books - Online Bookstore</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .navbar { background: #333; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; font-weight: bold; }
        .navbar a:hover { text-decoration: underline; }
        .container { padding: 20px; max-width: 1200px; margin: auto; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center; }
        .book-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .book-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; }
        .price { font-size: 1.2em; color: #28a745; font-weight: bold; margin: 10px 0; }
        .author { color: #555; font-style: italic; }
        .stock { font-weight: bold; color: #0056b3; }
        .out-of-stock-text { color: red; font-weight: bold; }
        button { background: #007bff; color: white; border: none; padding: 10px 15px; cursor: pointer; border-radius: 4px; width: 100%;}
        button:hover { background: #0056b3; }
        button:disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>

<div class="navbar">
    <div>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</div>
    <div>
        <a href="books.php">Books</a>
        <a href="cart.php">Cart (<?php echo $cart_count; ?>)</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Available Books</h2>
    
    <!-- Display Messages -->
    <?php if ($message) echo "<div class='success'>$message</div>"; ?>
    <?php if ($error_message) echo "<div class='error'>$error_message</div>"; ?>

    <div class="book-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <?php 
                    
                    $in_cart = isset($_SESSION['cart'][$row['book_id']]) ? $_SESSION['cart'][$row['book_id']] : 0;
                    $available_stock = $row['stock'] - $in_cart;
                ?>
                <div class="book-card">
                    <h3><?php echo htmlspecialchars($row['book_name']); ?></h3>
                    <p class="author">By <?php echo htmlspecialchars($row['author_name']); ?></p>
                    
                    <!-- Display dynamically adjusted stock -->
                    <?php if ($available_stock > 0): ?>
                        <p class="stock">Available Stock: <?php echo $available_stock; ?></p>
                    <?php else: ?>
                        <p class="out-of-stock-text">All available stock is in your cart!</p>
                    <?php endif; ?>
                    
                    <p>Format: <?php echo htmlspecialchars($row['cover_type']); ?></p>
                    <p class="price">$<?php echo number_format($row['price'], 2); ?></p>
                    
                    <form action="books.php" method="POST">
                        <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                        <!-- Disable button if available stock is 0 -->
                        <?php if ($available_stock > 0): ?>
                            <button type="submit" name="add_to_cart">Add to Cart</button>
                        <?php else: ?>
                            <button type="button" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No books available at the moment.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>