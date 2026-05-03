<?php
session_start();
require_once 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Remove from Cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_item'])) {
    $book_id = $_POST['book_id'];
    if (isset($_SESSION['cart'][$book_id])) {
        unset($_SESSION['cart'][$book_id]);
    }
    // Redirect to self to prevent form resubmission on refresh
    header("Location: cart.php");
    exit();
}

$cart_count = array_sum($_SESSION['cart']);
$total_price = 0.00;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart - Online Bookstore</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .navbar { background: #333; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; font-weight: bold; }
        .navbar a:hover { text-decoration: underline; }
        .container { padding: 20px; max-width: 800px; margin: auto; background: white; margin-top: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; }
        .remove-btn { background: #dc3545; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 4px; }
        .remove-btn:hover { background: #c82333; }
        .checkout-btn { background: #28a745; color: white; border: none; padding: 15px 20px; cursor: pointer; font-size: 16px; width: 100%; border-radius: 4px; text-decoration: none; display: block; text-align: center; box-sizing: border-box;}
        .checkout-btn:hover { background: #218838; }
        .total-row { font-weight: bold; font-size: 1.2em; background: #f8f9fa; }
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
    <h2>Your Shopping Cart</h2>

    <?php if (empty($_SESSION['cart'])): ?>
        <p>Your cart is empty. <a href="books.php">Go browse some books!</a></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Book Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get all book IDs from the cart session
                $book_ids = array_keys($_SESSION['cart']);
                
                // Create placeholders for the SQL IN clause based on the number of items
                $placeholders = implode(',', array_fill(0, count($book_ids), '?'));
                
                // Prepare the statement to fetch book details
                $stmt = $conn->prepare("SELECT book_id, book_name, price FROM Books WHERE book_id IN ($placeholders)");
                
                // Dynamically bind the parameters
                $types = str_repeat('i', count($book_ids));
                $stmt->bind_param($types, ...$book_ids);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    $book_id = $row['book_id'];
                    $quantity = $_SESSION['cart'][$book_id];
                    $price = $row['price'];
                    $subtotal = $price * $quantity;
                    $total_price += $subtotal;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['book_name']); ?></td>
                        <td>$<?php echo number_format($price, 2); ?></td>
                        <td><?php echo $quantity; ?></td>
                        <td>$<?php echo number_format($subtotal, 2); ?></td>
                        <td>
                            <form action="cart.php" method="POST" style="margin:0;">
                                <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                                <button type="submit" name="remove_item" class="remove-btn">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php } $stmt->close(); ?>
                
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">Grand Total:</td>
                    <td colspan="2">$<?php echo number_format($total_price, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- The button to take them to the next phase: Checkout -->
        <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
    <?php endif; ?>
</div>

</body>
</html>