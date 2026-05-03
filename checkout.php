<?php
session_start();
require_once 'db.php';

// 1. Ensure the user is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Ensure cart is not empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Enable strict MySQLi error reporting so the try-catch block catches any DB query failures
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$customer_id = $_SESSION['customer_id'];
$cart = $_SESSION['cart'];
$checkout_success = false;
$error_message = "";
$order_id = 0;
$total_price = 0.00;

try {
    // 3. START TRANSACTION
    // Guarantees all queries succeed together, or fail together (ACID Compliance)
    $conn->begin_transaction();

    $order_items_data = [];

    // 4. Lock rows, verify stock, and calculate price from DB
    $stmt_check = $conn->prepare("SELECT book_name, price, stock FROM Books WHERE book_id = ? FOR UPDATE");

    foreach ($cart as $book_id => $quantity) {
        $stmt_check->bind_param("i", $book_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Concurrency check: If someone else bought the last copy before they clicked checkout
            if ($row['stock'] < $quantity) {
                throw new Exception("Not enough stock for '" . $row['book_name'] . "'. Only " . $row['stock'] . " left.");
            }
            
            $unit_price = $row['price'];
            $total_price += ($unit_price * $quantity);
            
            // Save data for insertion later
            $order_items_data[] = [
                'book_id' => $book_id,
                'quantity' => $quantity,
                'unit_price' => $unit_price
            ];
        } else {
            throw new Exception("Book ID $book_id is no longer available.");
        }
    }
    $stmt_check->close();

    // 5. Insert into Orders table
    $status = 'Confirmed'; 
    $stmt_order = $conn->prepare("INSERT INTO Orders (customer_id, status, total_price) VALUES (?, ?, ?)");
    $stmt_order->bind_param("isd", $customer_id, $status, $total_price);
    $stmt_order->execute();
    
    // 6. Get the newly generated Order ID
    $order_id = $conn->insert_id;
    $stmt_order->close();

    // 7. Insert into Order_items AND Update Books stock
    $stmt_item = $conn->prepare("INSERT INTO Order_items (order_id, book_id, quantity, unit_price) VALUES (?, ?, ?, ?)");

    foreach ($order_items_data as $item) {
        // Insert Item
        $stmt_item->bind_param("iiid", $order_id, $item['book_id'], $item['quantity'], $item['unit_price']);
        $stmt_item->execute();
    }
    
    $stmt_item->close();

    // 8. COMMIT TRANSACTION - Everything was successful
    $conn->commit();
    $checkout_success = true;

    // 9. Clear the cart
    unset($_SESSION['cart']);

} catch (Exception $e) {
    // SOMETHING FAILED! Rollback all changes to the database
    $conn->rollback();
    $error_message = $e->getMessage();
}

// Calculate cart count for navbar (will be 0 if checkout succeeded)
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Online Bookstore</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .navbar { background: #333; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; font-weight: bold; }
        .navbar a:hover { text-decoration: underline; }
        .container { padding: 30px; max-width: 600px; margin: 40px auto; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; }
        
        .success-text { color: #28a745; margin-bottom: 20px; }
        .error-text { color: #dc3545; margin-bottom: 20px; }
        
        .order-details { background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #ddd; margin-bottom: 20px; text-align: left; }
        .order-details p { margin: 10px 0; font-size: 1.1em; }
        
        .btn { display: inline-block; background: #007bff; color: white; border: none; padding: 12px 20px; cursor: pointer; font-size: 16px; border-radius: 4px; text-decoration: none; }
        .btn:hover { background: #0056b3; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
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
    <?php if ($checkout_success): ?>
        <h2 class="success-text">Order Placed Successfully! 🎉</h2>
        
        <div class="order-details">
            <p><strong>Order ID:</strong> #<?php echo htmlspecialchars($order_id); ?></p>
            <p><strong>Status:</strong> Completed</p>
            <p><strong>Total Paid:</strong> $<?php echo number_format($total_price, 2); ?></p>
        </div>
        
        <p>Thank you for your purchase. Your stock has been automatically updated in the database.</p>
        <br>
        <a href="books.php" class="btn">Continue Shopping</a>
        
    <?php else: ?>
        <h2 class="error-text">Checkout Failed ❌</h2>
        <p>We encountered an issue while processing your order:</p>
        
        <div class="order-details">
            <p style="color: #dc3545;"><strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?></p>
        </div>
        
        <p>No changes were made to the database, and your cart is still intact.</p>
        <br>
        <a href="cart.php" class="btn btn-secondary">Return to Cart</a>
    <?php endif; ?>
</div>

</body>
</html>