<?php
session_start();


if (!isset($_SESSION['customer_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'A') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';
$message = "";
$error = "";


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = trim($_POST['status']);
    
    $update_stmt = $conn->prepare("UPDATE Orders SET status = ? WHERE order_id = ?");
    $update_stmt->bind_param("si", $new_status, $order_id);
    
    if ($update_stmt->execute()) {
        $message = "Order #$order_id status updated to '$new_status'.";
    } else {
        $error = "Failed to update order status.";
    }
    $update_stmt->close();
}


$query = "
    SELECT 
        o.order_id, o.creation_time, o.status, o.total_price,
        c.first_name, c.last_name, c.email,
        oi.quantity, oi.unit_price,
        b.book_name
    FROM Orders o
    JOIN Customers c ON o.customer_id = c.customer_id
    JOIN Order_items oi ON o.order_id = oi.order_id
    JOIN Books b ON oi.book_id = b.book_id
    ORDER BY o.creation_time DESC
";
$result = $conn->query($query);


$orders = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $oid = $row['order_id'];
        
        
        if (!isset($orders[$oid])) {
            $orders[$oid] = [
                'customer_name' => $row['first_name'] . ' ' . $row['last_name'],
                'email' => $row['email'],
                'date' => $row['creation_time'],
                'status' => $row['status'] ? $row['status'] : 'Pending', 
                'total_price' => $row['total_price'],
                'items' => []
            ];
        }
        
        
        $orders[$oid]['items'][] = [
            'book_name' => $row['book_name'],
            'quantity' => $row['quantity'],
            'unit_price' => $row['unit_price']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { display: flex; height: 100vh; background-color: #f4f7f6; }
        
        
        .sidebar { width: 250px; background-color: #2c3e50; color: white; display: flex; flex-direction: column; flex-shrink: 0;}
        .sidebar h2 { text-align: center; padding: 20px; background-color: #1a252f; margin-bottom: 20px; font-size: 1.2rem;}
        .sidebar a { padding: 15px 20px; color: #ecf0f1; text-decoration: none; border-left: 4px solid transparent; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; border-left: 4px solid #3498db; }
        .sidebar .logout { margin-top: auto; background-color: #c0392b; border-left: none; text-align: center;}
        
        
        .main-content { flex-grow: 1; padding: 30px; overflow-y: auto; }
        .header { margin-bottom: 20px; }
        
        .success-msg { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; }

        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; vertical-align: top; }
        th { background-color: #34495e; color: white; }
        tr:hover { background-color: #f9f9f9; }
        
        
        ul.item-list { list-style-type: none; padding: 0; margin: 0; }
        ul.item-list li { margin-bottom: 5px; font-size: 0.9rem; padding-bottom: 5px; border-bottom: 1px dashed #ddd; }
        ul.item-list li:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .item-qty { font-weight: bold; color: #2980b9; }

        
        .status-form { display: flex; gap: 5px; align-items: center; }
        select { padding: 5px; border-radius: 4px; border: 1px solid #ccc; }
        .btn-update { background-color: #27ae60; color: white; padding: 6px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85rem; }
        .btn-update:hover { background-color: #2ecc71; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_books.php">Manage Books</a>
        <a href="manage_authors.php">Manage Authors</a>
        <a href="manage_publishers.php">Manage Publishers</a>
        <a href="manage_genre.php">Manage Genres</a>
        <a href="manage_customers.php">Manage Customers</a>
        <a href="manage_orders.php" class="active">Manage Orders</a>
        <a href="../logout.php" class="logout">Log Out</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Manage Orders</h1>
        </div>

        <?php if ($message) echo "<div class='success-msg'>$message</div>"; ?>
        <?php if ($error) echo "<div class='error-msg'>$error</div>"; ?>

        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Details</th>
                    <th>Date</th>
                    <th>Items Purchased</th>
                    <th>Total Price</th>
                    <th>Status & Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order_id => $data): ?>
                        <tr>
                            <td><strong>#<?php echo $order_id; ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($data['customer_name']); ?><br>
                                <small style="color: #7f8c8d;"><?php echo htmlspecialchars($data['email']); ?></small>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($data['date'])); ?></td>
                            <td>
                                <ul class="item-list">
                                    <?php foreach ($data['items'] as $item): ?>
                                        <li>
                                            <span class="item-qty"><?php echo $item['quantity']; ?>x</span> 
                                            <?php echo htmlspecialchars($item['book_name']); ?> 
                                            <em style="color: #888;">($<?php echo number_format($item['unit_price'], 2); ?>)</em>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td><strong>$<?php echo number_format($data['total_price'], 2); ?></strong></td>
                            <td>
                                <form action="manage_orders.php" method="POST" class="status-form">
                                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                    <select name="status">
                                        <option value="Pending" <?php if($data['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                        <option value="Confirmed" <?php if($data['status'] == 'Confirmed') echo 'selected'; ?>>Confirmed</option>
                                        <option value="Shipped" <?php if($data['status'] == 'Shipped') echo 'selected'; ?>>Shipped</option>
                                        <option value="Delivered" <?php if($data['status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                                        <option value="Cancelled" <?php if($data['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-update">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>